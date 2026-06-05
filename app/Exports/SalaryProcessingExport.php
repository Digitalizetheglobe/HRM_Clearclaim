<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalaryProcessingExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $exportData = [];
        foreach ($this->data as $row) {
            $exportData[] = [
                'Employee Name' => $row['employee_name'],
                'Total Monthly Days' => $row['total_monthly_days'],
                'Total Late Marks' => $row['total_late_marks'],
                'Late Mark Deduction' => $row['late_mark_deduction_amount'] ?? 0,
                'LOP Days' => $row['lop_days'] + 0,
                'Payable Days' => $row['payable_days'] + 0,
                'Actual Payable Days' => $row['actual_payable_days'] + 0,
                'Actual Salary' => $row['actual_salary'],
                'Daily Salary' => $row['daily_salary'],
                'Salary Arrears' => $row['salary_arrears'],
                'Monthly Salary' => $row['monthly_salary'],
                'Final Payable Salary' => $row['final_payable_salary'],
            ];
        }
        
        return collect($exportData);
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Total Monthly Days',
            'Total Late Marks',
            'Late Mark Deduction',
            'LOP Days',
            'Payable Days',
            'Actual Payable Days',
            'Actual Salary',
            'Daily Salary',
            'Salary Arrears',
            'Monthly Salary',
            'Final Payable Salary',
        ];
    }
}
