<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Http\Controllers\MonthlyWorkingHoursController;
use App\Models\Employee;

class MonthlyWorkingHoursExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $request;
    protected $month;
    protected $year;

    public function __construct($request, $month, $year)
    {
        $this->request = $request;
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $employeesQuery = Employee::where('created_by', \Auth::user()->creatorId())
            ->whereNotIn('id', \App\Models\Termination::pluck('employee_id')->toArray());
        
        if (!empty($this->request['department'])) {
            $employeesQuery->where('department_id', $this->request['department']);
        }
        if (!empty($this->request['employee'])) {
            $employeesQuery->where('id', $this->request['employee']);
        }

        if (!empty($this->request['search'])) {
            $employeesQuery->whereHas('user', function($q) {
                $q->where('name', 'LIKE', "%{$this->request['search']}%");
            });
        }

        $employees = $employeesQuery->get();

        $controller = new MonthlyWorkingHoursController();
        $summary = $controller->calculateSummary($employees, $this->month, $this->year);

        return collect($summary['employees']);
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Department',
            'Working Days',
            'Expected Hours',
            'Actual Hours Worked',
            'Overtime (+)',
            'Shortfall (-)',
            'Net Hours',
            'Approved Leaves',
            'Holidays',
            'Weekly Offs',
        ];
    }

    public function map($row): array
    {
        return [
            $row['employee_id'],
            $row['name'],
            $row['department'],
            $row['working_days'],
            $row['expected_hours'],
            $row['actual_hours'],
            $row['overtime'],
            $row['shortfall'],
            $row['net_hours'],
            $row['approved_leaves'],
            $row['holidays'],
            $row['weekly_offs'],
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '004696']]],
        ];
    }
}
