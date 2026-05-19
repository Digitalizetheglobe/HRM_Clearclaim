<?php

namespace App\Http\Controllers;

use App\Exports\HolidayExport;
use App\Imports\HolidayImport;
use App\Models\Holiday as LocalHoliday;
use Illuminate\Http\Request;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class HolidayController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Holiday')) {
            $holidays = LocalHoliday::where('created_by', '=', \Auth::user()->creatorId());

            if (!empty($request->date)) {
                $holidays->where('date', '=', $request->date);
            }
            $holidays = $holidays->orderBy('date', 'asc')->get();

            return view('holiday.index', compact('holidays'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('Create Holiday')) {
            return view('holiday.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Holiday')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'occasion' => 'required',
                    'date' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $holiday             = new LocalHoliday();
            $holiday->occasion          = $request->occasion;
            $holiday->date              = $request->date;
            $holiday->day               = date('l', strtotime($request->date));
            $holiday->created_by = \Auth::user()->creatorId();
            $holiday->save();

            // slack
            $setting = Utility::settings(\Auth::user()->creatorId());
            if (isset($setting['Holiday_notification']) && $setting['Holiday_notification'] == 1) {
                $uArr = [
                    'occasion_name' => $request->occasion,
                    'start_date' => $request->date,
                    'end_date' => $request->date,
                ];

                Utility::send_slack_msg('new_holidays', $uArr);
            }

            // telegram
            $setting = Utility::settings(\Auth::user()->creatorId());
            if (isset($setting['telegram_Holiday_notification']) && $setting['telegram_Holiday_notification'] == 1) {
                $uArr = [
                    'occasion_name' => $request->occasion,
                    'start_date' => $request->date,
                    'end_date' => $request->date,
                ];

                Utility::send_telegram_msg('new_holidays', $uArr);
            }

            // google calendar
            if ($request->get('synchronize_type')  == 'google_calender') {

                $type = 'holiday';
                $request1 = new GoogleEvent();
                $request1->title = $request->occasion;
                $request1->start_date = $request->date;
                $request1->end_date = $request->date;
                Utility::addCalendarData($request1, $type);
            }

            //webhook
            $module = 'New Holidays';
            $webhook =  Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($holiday);
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->back()->with('success', __('Holiday successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }

            return redirect()->route('holiday.index')->with('success', 'Holiday successfully created.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id)
    {
        $holidays = LocalHoliday::where('id', $id)->first();
        return view('holiday.show', compact('holidays'));
    }


    public function edit(LocalHoliday $holiday)
    {
        if (\Auth::user()->can('Edit Holiday')) {
            return view('holiday.edit', compact('holiday'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, LocalHoliday $holiday)
    {
        if (\Auth::user()->can('Edit Holiday')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'occasion' => 'required',
                    'date' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $holiday->occasion          = $request->occasion;
            $holiday->date              = $request->date;
            $holiday->day               = date('l', strtotime($request->date));
            $holiday->save();

            return redirect()->route('holiday.index')->with(
                'success',
                'Holiday successfully updated.'
            );
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(LocalHoliday $holiday)
    {
        if (\Auth::user()->can('Delete Holiday')) {
            $holiday->delete();

            return redirect()->route('holiday.index')->with(
                'success',
                'Holiday successfully deleted.'
            );
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function calender(Request $request)
    {
        if (\Auth::user()->can('Manage Holiday')) {
            $transdate = date('Y-m-d', time());

            $holidays = LocalHoliday::where('created_by', '=', \Auth::user()->creatorId());

            if (!empty($request->date)) {
                $holidays->where('date', '=', $request->date);
            }
            $holidays = $holidays->orderBy('date', 'asc')->get();

            $arrHolidays = [];

            foreach ($holidays as $holiday) {
                $arr['id']        = $holiday['id'];
                $arr['title']     = $holiday['occasion'];
                $arr['start']     = $holiday['date'];
                $arr['end']       = $holiday['date'];
                $arr['className'] = 'event-primary';
                $arr['url']       = route('holiday.edit', $holiday['id']);
                $arrHolidays[]    = $arr;
            }
            $arrHolidays = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrHolidays)));

            return view('holiday.calender', compact('arrHolidays', 'transdate', 'holidays'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export(Request $request)
    {
        $name = 'holidays_' . date('Y-m-d i:h:s');
        $data = Excel::download(new HolidayExport(), $name . '.xlsx');

        return $data;
    }

    public function importFile(Request $request)
    {
        return view('holiday.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        try {
            $holidays = (new HolidayImport())->toArray(request()->file('file'))[0];

            $totalholiday = count($holidays);

            $errorArray    = [];
            foreach ($holidays as $holiday) {

                $holiydayData = LocalHoliday::whereDate('date', $holiday['date'])->where('occasion', $holiday['occasion'])->first();

                if (!empty($holiydayData)) {
                    $errorArray[] = $holiydayData;
                } else {
                    $holidays_data = new LocalHoliday();
                    $holidays_data->date = $holiday['date'];
                    $holidays_data->day = date('l', strtotime($holiday['date']));
                    $holidays_data->occasion = $holiday['occasion'];
                    $holidays_data->created_by = Auth::user()->id;
                    $holidays_data->save();
                }
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Something went wrong please try again.'));
        }

        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        } else {

            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalholiday . ' ' . 'record');


            foreach ($errorArray as $errorData) {
                $errorRecord[] = implode(',', $errorData->toArray());
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

    public function get_holiday_data(Request $request)
    {
        $arrayJson = [];
        if ($request->get('calender_type') == 'google_calender') {
            $type = 'holiday';
            $arrayJson =  Utility::getCalendarData($type);
        } else {
            $data = LocalHoliday::where('created_by', \Auth::user()->creatorId())->get();


            foreach ($data as $val) {
                if (Auth::user()->type == 'employee') {
                    $url = route('holiday.show', $val['id']);
                } else {
                    $url = route('holiday.edit', $val['id']);
                }
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => $val->occasion,
                    "start" => $val->date,
                    "end" => $val->date,
                    "className" => $val->color ?? 'event-primary',
                    "textColor" => '#FFF',
                    "allDay" => true,
                    "url" => $url,
                ];
            }
        }

        return $arrayJson;
    }
}
