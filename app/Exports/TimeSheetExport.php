<?php

namespace App\Exports;

use App\Models\TimeSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class TimeSheetExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $timeSheets;

    public function __construct($timeSheets)
    {
        $this->timeSheets = $timeSheets;
    }

    public function collection()
    {
        return $this->timeSheets->map(function ($timesheet) {
            // Process feedback information
            $feedbacks = [];
            if (!empty($timesheet->feedback_information)) {
                $feedbackData = json_decode($timesheet->feedback_information, true);
                if (is_array($feedbackData)) {
                    foreach ($feedbackData as $index => $feedback) {
                        $feedbacks['Feedback '.($index+1)] = $feedback['description'] ?? '';
                        $feedbacks['Followup Date '.($index+1)] = $feedback['followup_date'] ?? '';
                    }
                }
            }

            return array_merge([
                'ID' => $timesheet->id,
                'Employee Name' => $timesheet->employee->name ?? '',
                'Project Name' => $timesheet->project->project_name ?? '',
                'Date' => $timesheet->date,
                'Client Name' => $timesheet->full_name,
                'Mobile Number' => $timesheet->mobile_no,
                'Email ID' => $timesheet->email_id,
                'Address' => $timesheet->address,
                'Client Source' => $timesheet->recommended_by,
                'CP Name' => $timesheet->cp_data,
                'Reference Name' => $timesheet->refrence_data,
                'Other Data' => $timesheet->other_data,
                'Primary Reason' => $timesheet->primary_reason,
                'Area Requirement' => $timesheet->square_feet_range,
                'Price Range' => $timesheet->price_range,
                'Client Status' => $timesheet->client_status,
                'Executive Remark' => $timesheet->executive_remark,
            ], $feedbacks);
        });
    }

    public function headings(): array
    {
        // Base headings
        $headings = [
            'ID',
            'Employee Name',
            'Project Name',
            'Date',
            'Client Name',
            'Mobile Number',
            'Email ID',
            'Address',
            'Client Source',
            'CP Name',
            'Reference Name',
            'Other Data',
            'Primary Reason',
            'Area Requirement',
            'Price Range',
            'Client Status',
            'Executive Remark',
        ];

        // Add dynamic feedback headings (assuming max 5 feedbacks per timesheet)
        for ($i = 1; $i <= 5; $i++) {
            $headings[] = 'Feedback '.$i;
            $headings[] = 'Followup Date '.$i;
        }

        return $headings;
    }
}