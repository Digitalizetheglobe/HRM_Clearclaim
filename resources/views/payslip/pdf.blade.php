@php
// Enable detailed error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    \Log::info('Starting payslip generation', ['timestamp' => now()]);

    // Initialize with null checks and logging
    $employee = $employee ?? null;
    $payslip = $payslip ?? null;
    
    if (!$employee || !$payslip) {
        $errorMessage = 'Payslip Error: Missing employee or payslip data';
        \Log::error($errorMessage, [
            'employee_exists' => isset($employee),
            'payslip_exists' => isset($payslip),
            'route' => request()->fullUrl()
        ]);
        abort(404, $errorMessage);
    }

    \Log::info('Generating payslip for employee', [
        'employee_id' => $employee->id,
        'payslip_id' => $payslip->id ?? 'N/A',
        'salary_month' => $payslip->salary_month ?? 'N/A'
    ]);

    // Handle logo loading with error logging
    try {
        $logo = \App\Models\Utility::get_file('uploads/logo/');
        $company_logo = Utility::get_company_logo();
        \Log::debug('Logo loaded successfully', ['logo_path' => $logo]);
    } catch (\Exception $e) {
        \Log::error('Logo Loading Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $logo = null;
        $company_logo = null;
    }

    // Date calculations with error handling
    try {
        $totalDays = date('t', strtotime($payslip->salary_month . '-01'));
        if ($totalDays === false) {
            throw new \Exception('Invalid date format for salary month');
        }
        \Log::debug('Calculated total days in month', ['totalDays' => $totalDays]);
    } catch (\Exception $e) {
        \Log::error('Date Calculation Error', [
            'salary_month' => $payslip->salary_month,
            'error' => $e->getMessage()
        ]);
        $totalDays = 30; // Fallback value
    }

    // Initialize counters
    $presentDays = 0;
    $absentDays = 0;
    $leaveDays = 0;
    $casualLeaveDays = 0;
    $unlimitedLeaveDays = 0;

    // Date period setup
    try {
        $startDate = new DateTime($payslip->salary_month . '-01');
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
        \Log::debug('Date period created successfully');
    } catch (\Exception $e) {
        \Log::error('Date Period Creation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        abort(500, 'Failed to process date range');
    }

    // Get attendance records with error handling
    try {
        $attendanceRecords = DB::table('attendance_employees')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        \Log::debug('Attendance records retrieved', ['count' => count($attendanceRecords)]);
    } catch (\Exception $e) {
        \Log::error('Attendance Records Query Error', [
            'error' => $e->getMessage(),
            'query' => 'attendance_employees for employee '.$employee->id,
            'trace' => $e->getTraceAsString()
        ]);
        $attendanceRecords = collect();
    }

    // Get approved leaves with error handling
    try {
        $leaves = DB::table('leaves')
            ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
            ->where('leaves.employee_id', $employee->id)
            ->where('leaves.status', 'Approved')
            ->whereBetween('leaves.start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orWhereBetween('leaves.end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select('leaves.*', 'leave_types.title as leave_type')
            ->get();
        \Log::debug('Leave records retrieved', ['count' => count($leaves)]);
    } catch (\Exception $e) {
        \Log::error('Leave Records Query Error', [
            'error' => $e->getMessage(),
            'query' => 'leaves for employee '.$employee->id,
            'trace' => $e->getTraceAsString()
        ]);
        $leaves = collect();
    }

    // Calculate attendance, leaves, and deductions
    try {
        $presentDays = count($attendanceRecords);
        
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            
            // Check if employee clocked in
            $attended = $attendanceRecords->contains('date', $dateStr);
            
            if (!$attended) {
                $onLeave = false;
                $leaveType = '';
                
                // Check if employee was on leave
                foreach ($leaves as $leave) {
                    try {
                        $leaveStart = new DateTime($leave->start_date);
                        $leaveEnd = new DateTime($leave->end_date);
                        $leavePeriod = new DatePeriod($leaveStart, $interval, $leaveEnd->modify('+1 day'));
                        
                        foreach ($leavePeriod as $leaveDay) {
                            if ($leaveDay->format('Y-m-d') == $dateStr) {
                                $onLeave = true;
                                $leaveType = strtolower($leave->leave_type ?? '');
                                break 2;
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Leave Date Processing Error', [
                            'leave_id' => $leave->id ?? 'N/A',
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
                
                if ($onLeave) {
                    if ($leaveType == 'unlimited leave') {
                        $unlimitedLeaveDays++;
                        $absentDays++; // Count unlimited leave as absent
                    } elseif ($leaveType == 'casual leave') {
                        $casualLeaveDays++;
                        $leaveDays++;
                    } elseif (!empty($leaveType)) {
                        $leaveDays++; // Other approved leaves
                    } else {
                        $absentDays++; // No leave, no attendance - pure absent
                    }
                } else {
                    $absentDays++; // No leave, no attendance - pure absent
                }
            }
        }
        
        \Log::info('Attendance calculations completed', [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'casual_leave_days' => $casualLeaveDays,
            'unlimited_leave_days' => $unlimitedLeaveDays
        ]);
    } catch (\Exception $e) {
        \Log::error('Attendance Calculation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        abort(500, 'Failed to calculate attendance');
    }

    // Calculate salary components with error handling
    try {
        // Ensure all values are properly converted to float
        $grossSalary = is_numeric($payslip->basic_salary) ? (float)$payslip->basic_salary : 0;
        if ($grossSalary <= 0) {
            throw new \Exception('Invalid gross salary amount: ' . $payslip->basic_salary);
        }
        
        // Log the raw values before calculation
        \Log::debug('Salary calculation inputs', [
            'basic_salary_raw' => $payslip->basic_salary,
            'type' => gettype($payslip->basic_salary),
            'loan_raw' => $payslip->loan ?? 'N/A',
            'loan_type' => isset($payslip->loan) ? gettype($payslip->loan) : 'N/A'
        ]);
        
        $basicComponent = $grossSalary * (float)0.40;
        $hraComponent = $grossSalary * (float)0.20;
        $medicalComponent = $grossSalary * (float)0.05;
        $specialComponent = $grossSalary * (float)0.35;
        
        \Log::debug('Salary components calculated', [
            'gross_salary' => $grossSalary,
            'basic' => $basicComponent,
            'hra' => $hraComponent,
            'medical' => $medicalComponent,
            'special' => $specialComponent
        ]);
    } catch (\Exception $e) {
        \Log::error('Salary Component Calculation Error', [
            'error' => $e->getMessage(),
            'basic_salary' => $payslip->basic_salary ?? 'N/A',
            'type' => isset($payslip->basic_salary) ? gettype($payslip->basic_salary) : 'N/A',
            'trace' => $e->getTraceAsString()
        ]);
        abort(500, 'Invalid salary data: ' . $e->getMessage());
    }

    // Calculate salary deductions
    try {
        $perDaySalary = $grossSalary / (float)30;
        $deductionForAbsent = (float)$absentDays * $perDaySalary;
        $deductionForCasualLeave = (float)$casualLeaveDays * $perDaySalary;
        $ptDeduction = is_numeric($payslip->professional_tax ?? 200) ? (float)($payslip->professional_tax ?? 200) : 200;
        
        \Log::debug('Deductions calculated', [
            'per_day_salary' => $perDaySalary,
            'absent_deduction' => $deductionForAbsent,
            'casual_leave_deduction' => $deductionForCasualLeave,
            'professional_tax' => $ptDeduction,
            'absent_days_type' => gettype($absentDays),
            'casual_leave_days_type' => gettype($casualLeaveDays)
        ]);
    } catch (\Exception $e) {
        \Log::error('Deduction Calculation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'values' => [
                'grossSalary' => $grossSalary ?? 'N/A',
                'absentDays' => $absentDays ?? 'N/A',
                'casualLeaveDays' => $casualLeaveDays ?? 'N/A'
            ]
        ]);
        abort(500, 'Failed to calculate deductions: ' . $e->getMessage());
    }

    // Loan calculations with error handling
    try {
        $loanDeduction = 0;
        $remainingLoan = 0;

        if (isset($payslip->loan)) {
            // Handle case where loan is stored as JSON array
            if (is_string($payslip->loan) && str_starts_with($payslip->loan, '[')) {
                $loanArray = json_decode($payslip->loan, true);
                $loanDeduction = is_array($loanArray) ? array_sum($loanArray) : 0;
            } else {
                $loanDeduction = is_numeric($payslip->loan) ? max(0, (float)$payslip->loan) : 0;
            }

            if ($loanDeduction > 0) {
                $totalLoans = \App\Models\EmployeeLoan::where('employee_id', $employee->id)
                    ->sum('total_amount');
                $totalPaid = $totalLoans - \App\Models\EmployeeLoan::where('employee_id', $employee->id)
                    ->sum('remaining_amount');
                $remainingLoan = $totalLoans - $totalPaid - $loanDeduction;
            }
        }

        \Log::debug('Loan calculations completed', [
            'loan_deduction' => $loanDeduction,
            'remaining_loan' => $remainingLoan,
            'loan_raw_value' => $payslip->loan ?? 'N/A',
            'loan_raw_type' => isset($payslip->loan) ? gettype($payslip->loan) : 'N/A'
        ]);
    } catch (\Exception $e) {
        \Log::error('Loan Calculation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'loan_value' => $payslip->loan ?? 'N/A'
        ]);
        $loanDeduction = 0;
        $remainingLoan = 0;
    }

    // Final calculations with strict type checking
    try {
        $loanDeduction = isset($payslip->loan) ? (float)$payslip->loan : 0;

        $totalDeductions = (float)$deductionForAbsent + (float)$deductionForCasualLeave + (float)$ptDeduction + (float)$loanDeduction;
        $netSalary = (float)$grossSalary - (float)$totalDeductions;
        
        \Log::info('Final salary calculations', [
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'type_checks' => [
                'grossSalary' => gettype($grossSalary),
                'deductionForAbsent' => gettype($deductionForAbsent),
                'deductionForCasualLeave' => gettype($deductionForCasualLeave),
                'ptDeduction' => gettype($ptDeduction),
                'loanDeduction' => gettype($loanDeduction),
                'totalDeductions' => gettype($totalDeductions)
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Final Calculation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'values' => [
                'grossSalary' => $grossSalary ?? 'N/A',
                'deductionForAbsent' => $deductionForAbsent ?? 'N/A',
                'deductionForCasualLeave' => $deductionForCasualLeave ?? 'N/A',
                'ptDeduction' => $ptDeduction ?? 'N/A',
                'loanDeduction' => $loanDeduction ?? 'N/A'
            ],
            'types' => [
                'grossSalary' => isset($grossSalary) ? gettype($grossSalary) : 'N/A',
                'deductionForAbsent' => isset($deductionForAbsent) ? gettype($deductionForAbsent) : 'N/A',
                'deductionForCasualLeave' => isset($deductionForCasualLeave) ? gettype($deductionForCasualLeave) : 'N/A',
                'ptDeduction' => isset($ptDeduction) ? gettype($ptDeduction) : 'N/A',
                'loanDeduction' => isset($loanDeduction) ? gettype($loanDeduction) : 'N/A'
            ]
        ]);
        abort(500, 'Failed to calculate final salary: ' . $e->getMessage());
    }

    // Number to words conversion with error handling
    function numberToWords($number) {
        try {
            $number = max(0, floatval($number));
            $no = floor($number);
            $point = round(($number - $no) * 100);
            
            $words = array(
                '0' => '', '1' => 'One', '2' => 'Two',
                '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
                '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
                '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
                '13' => 'Thirteen', '14' => 'Fourteen',
                '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
                '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
                '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
                '60' => 'Sixty', '70' => 'Seventy',
                '80' => 'Eighty', '90' => 'Ninety'
            );
            
            $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
            
            $str = array();
            $i = 0;
            
            while ($no > 0) {
                $divider = ($i == 1) ? 10 : 100;
                $number = floor($no % $divider);
                $no = floor($no / $divider);
                $i += ($divider == 10) ? 1 : 2;
                
                if ($number) {
                    $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                    $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                    $digitLabel = isset($digits[$counter]) ? $digits[$counter] : '';
                    
                    if ($number < 21) {
                        $str[] = ($words[$number] ?? '') . " " . $digitLabel . $plural . " " . $hundred;
                    } else {
                        $tens = floor($number / 10) * 10;
                        $units = $number % 10;
                        $str[] = ($words[$tens] ?? '') . " " . ($words[$units] ?? '') . " " . $digitLabel . $plural . " " . $hundred;
                    }
                }
            }
            
            $str = array_reverse($str);
            $result = implode('', $str);
            
            $points = '';
            if ($point > 0) {
                $points = " and ";
                if ($point < 21) {
                    $points .= ($words[$point] ?? '') . " Paise";
                } else {
                    $tens = floor($point / 10) * 10;
                    $units = $point % 10;
                    $points .= ($words[$tens] ?? '') . " " . ($words[$units] ?? '') . " Paise";
                }
            }
            
            return trim($result . " Rupees" . $points) . " Only";
        } catch (\Exception $e) {
            \Log::error('Number to Words Conversion Error', [
                'number' => $number,
                'error' => $e->getMessage()
            ]);
            return 'Amount in words conversion failed';
        }
    }

    $netSalaryInWords = numberToWords($netSalary);
    \Log::info('Payslip generation completed successfully');

} catch (\Throwable $th) {
    \Log::error('Payslip Generation Failed', [
        'error' => $th->getMessage(),
        'trace' => $th->getTraceAsString(),
        'employee_id' => $employee->id ?? 'N/A',
        'payslip_id' => $payslip->id ?? 'N/A',
        'request_data' => request()->all()
    ]);
    throw $th; // Re-throw after logging
}
@endphp

<div class="modal-body">
    <div class="text-md-end mb-2">
        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom"
            title="{{ __('Download') }}" onclick="saveAsPDF()"><span class="fa fa-download"></span></a>

        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
            <a title="Mail Send" href="{{ route('payslip.send', [$employee->id, $payslip->salary_month]) }}" 
                class="btn btn-sm btn-warning"><span class="fa fa-paper-plane"></span></a>
        @endif
    </div>
    
    <div class="invoice" id="printableArea">
        <div class="row">
            <div class="col-form-label">
                <!-- Main Container with Border -->
                <div style="border: 3px solid #000; padding: 0; font-family: Arial, sans-serif;">
                    
                    <!-- Header Section -->
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 25%; border-right: 2px solid #000; padding: 15px; text-align: center; vertical-align: middle;">
                                <img src="{{ url('storage/uploads/logo/logo.svg' ) }}" width="100px">
                                <br>
                           
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <h2 style="margin: 0; font-size: 24px; font-weight: bold;">{{ \Utility::getValByName('company_name') }}</h2>
                                <div style="font-size: 14px; margin: 8px 0;">
                                    <strong>Office Address :</strong> {{ \Utility::getValByName('company_address') }}, {{ \Utility::getValByName('company_city') }}
                                </div>
                                
                            </td>
                        </tr>
                    </table>

                    <!-- Salary Slip Title -->
                    <div style="border-top: 2px solid #000; border-bottom: 1px solid #000; padding: 10px; text-align: center; background-color: #f8f9fa;">
                        <h3 style="margin: 0; font-size: 18px; font-weight: bold;">Salary Slip for {{ strtoupper(date('F - Y', strtotime($payslip->salary_month))) }}</h3>
                    </div>

                    <div style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px; text-align: center; background-color: #f8f9fa;">
                        <!-- <h3 style="margin: 0; font-size: 18px; font-weight: bold;">Salary Slip for {{ strtoupper(date('F - Y', strtotime($payslip->salary_month))) }}</h3> -->
                    </div>

                    <!-- Employee Details Section -->
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <tr>
                            <!-- Left Column -->
                            <td style="width: 33.33%; border-right: 2px solid #000; padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Employee Name :</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $employee->name }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Designation:</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $employee->designation->name ?? 'Assistant Manager - Talent Acquisition' }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Total Day:</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $totalDays }}</td>
                                    </tr>
                                    <tr style="">
                                        <td style="padding: 8px; font-weight: bold;">Date of Joining:</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                                    </tr>
                                    
                                </table>
                            </td>
                            
                            <!-- Middle Column -->
                            <td style="width: 33.33%;  padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Employee ID :</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $employeesId ?? 'N/A' }}</td>   
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Salary Month :</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ strtoupper(date('F - Y', strtotime($payslip->salary_month))) }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Payable Days :</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $totalDays - $absentDays - $casualLeaveDays }}</td>
                                    </tr>
       
                                </table>
                            </td>
                        </tr>
                    </table>

                    <div style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px; text-align: center; background-color: #f8f9fa;">
                        <table>
                            <tr>
                            </tr>
                        </table>
                    </div>

                    <!-- Earnings and Deductions Section -->
                    <div style="border-top: 0px solid #000;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <!-- Earnings Column -->
                                <td style="width: 50%; border-right: 2px solid #000; padding: 0; vertical-align: top;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr style="background-color: #f8f9fa;">
                                            <th colspan="2" style="padding: 10px; text-align: center; font-size: 16px; font-weight: bold; border-bottom: 1px solid #000;">Earnings</th>
                                        </tr>
                                        <tr style="background-color: #f8f9fa;">
                                            <th style="padding: 8px; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000;">Components</th>
                                            <th style="padding: 8px; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; text-align: right;">Amount (Rs.)</th>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Basic</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($basicComponent) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">HRA</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($hraComponent) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Medical</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($medicalComponent) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Special Allowance</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($specialComponent) }}</td>
                                        </tr>
                                         <tr style="height: 35px;">
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;"></td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;"></td>
                                        </tr>
                                        <tr style="background-color: #f8f9fa;">
                                            <td style="padding: 10px; font-size: 14px; font-weight: bold; border-right: 1px solid #000;">Gross Earning (A)</td>
                                            <td style="padding: 10px; font-size: 14px; font-weight: bold; text-align: right;">{{ \Auth::user()->priceFormat($grossSalary) }}</td>
                                        </tr>
                                    </table>
                                </td>
                                
                                <!-- Deductions Column -->
                                <td style="width: 50%; padding: 0; vertical-align: top;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr style="background-color: #f8f9fa;">
                                            <th colspan="2" style="padding: 10px; text-align: center; font-size: 16px; font-weight: bold; border-bottom: 1px solid #000;">Deductions</th>
                                        </tr>
                                        <tr style="background-color: #f8f9fa;">
                                            <th style="padding: 8px; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000;">Common Deductions</th>
                                            <th style="padding: 8px; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; text-align: right;">Amount (Rs.)</th>
                                        </tr>

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Professional Tax</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($ptDeduction) }}</td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Absent Days ({{ $absentDays }} days)</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($deductionForAbsent) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Casual Leave ({{ $casualLeaveDays }} days)</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($deductionForCasualLeave) }}</td>
                                        </tr>
                                        
                                        
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Loan Deduction</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">
                                                @php
                                                    // Handle loan display
                                                    $loanValue = 0;
                                                    if (isset($payslip->loan)) {
                                                        if (is_string($payslip->loan) && str_starts_with($payslip->loan, '[')) {
                                                            $loanArray = json_decode($payslip->loan, true);
                                                            $loanValue = is_array($loanArray) ? array_sum($loanArray) : 0;
                                                        } else {
                                                            $loanValue = is_numeric($payslip->loan) ? $payslip->loan : 0;
                                                        }
                                                    }
                                                @endphp
                                                
                                                {{ \Auth::user()->priceFormat($loanValue) }}
                                                
                                                @if($loanValue > 0)
                                                    <div style="font-size: 10px; color: #666;">
                                                        @php
                                                            $loanDetails = \App\Models\EmployeeLoan::where('employee_id', $employee->id)
                                                                ->where('remaining_amount', '>', 0)
                                                                ->with(['deductions' => function($q) use ($payslip) {
                                                                    $q->where('month', 'like', $payslip->salary_month.'%');
                                                                }])
                                                                ->get();
                                                        @endphp
                                                        
                                                        @foreach($loanDetails as $loan)
                                                            EMI: {{ \Auth::user()->priceFormat($loan->monthly_emi) }}<br>
                                                            Remaining: {{ \Auth::user()->priceFormat($loan->remaining_amount) }}
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                        
                                        <tr style="height: 35px;">
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;"></td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;"></td>
                                        </tr>
                                        
                                        <tr style="background-color: #f8f9fa; border-bottom: 1px solid #000;">
                                            <td style="padding: 10px; font-size: 14px; font-weight: bold; border-right: 1px solid #000;">Total Deductions (B)</td>
                                            <td style="padding: 10px; font-size: 14px; font-weight: bold; text-align: right;">{{ \Auth::user()->priceFormat($totalDeductions) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>


                    <div style="border-top: 1px solid #000;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="background-color: #f8f9fa;">
                                <td style="padding: 12px; font-size: 16px; font-weight: bold;  "></td>
                                <td style="padding: 12px; font-size: 16px; font-weight: bold; text-align: right; "></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Net Pay Section -->
                    <div style="border-top: 2px solid #000;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="background-color: #f8f9fa;">
                                <td style="padding: 12px; font-size: 16px; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000;">Net Pay (A - B)</td>
                                <td style="padding: 12px; font-size: 16px; font-weight: bold; text-align: left; border-bottom: 1px solid #000;">{{ \Auth::user()->priceFormat($netSalary) }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; font-size: 12px; font-weight: bold; border-right: 1px solid #000;">Total Pay</td>
                                <td style="padding: 10px; font-size: 12px;">{{ ucwords($netSalaryInWords) }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Footer Note -->
                    <div style="border-top: 2px solid #000; padding: 15px; text-align: center; font-size: 12px; font-weight: bold;">
                        Note: This is a Computer Generated Slip and does not require signature
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: '{{ $employee->name }}_{{ $payslip->salary_month }}_payslip',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A4'
            }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>