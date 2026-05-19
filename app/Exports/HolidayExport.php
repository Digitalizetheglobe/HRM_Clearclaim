<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Holiday;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HolidayExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Holiday::where('created_by', \Auth::user()->creatorId())->get([
            'id',
            'date',
            'day',
            'occasion',
            'created_by'
        ]);
        foreach($data as $k => $holiday)
        {
            $data[$k]["created_by"] = Employee::login_user($holiday->created_by);
        }
        return $data;
    }
    public function headings(): array
    {
        return [
            "ID",
            "Date",
            "Day",
            "Occasion",
            "Created By"
        ];
    }
}
