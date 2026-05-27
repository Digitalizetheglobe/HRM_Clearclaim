<table>
    <tr>
        <td colspan="{{ count($dates) + 1 }}"><strong>{{ \Carbon\Carbon::parse($start_date)->format('M d Y') }} To {{ \Carbon\Carbon::parse($end_date)->format('M d Y') }}</strong></td>
    </tr>

        <tr>
            <td colspan="{{ count($dates) + 1 }}"></td>
        </tr>


    
    @foreach($employees as $employee)
        <!-- Employee Header -->
        <tr>
            <td colspan="{{ count($dates) + 1 }}"><strong>Empployee Code:</strong> {{ $employee->employee_id }} </td>
        </tr>
        <tr>
            <td colspan="{{ count($dates) + 1 }}"><strong>Empployee. Name:</strong> {{ $employee->name }}</td>
        </tr>
        
        <!-- Status Row -->
        <tr>
            <td><strong>Days</strong></td>
            @foreach($dates as $date)
                <td>{{ \Carbon\Carbon::parse($date)->format('d D') }}</td>
            @endforeach
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            @foreach($dates as $date)
                <td>
                    @isset($attendanceData[$employee->id][$date]['status'])
                        @php
                            $status = $attendanceData[$employee->id][$date]['status'];
                            $abbreviation = 'A'; // Default for Absent
                            
                            switch($status) {
                                case 'Present':
                                    $abbreviation = 'P';
                                    break;
                                case 'Present (Late)':
                                    $abbreviation = 'PL';
                                    break;
                                case 'Late':
                                    $abbreviation = 'L';
                                    break;
                                case 'Half Day (Late)':
                                    $abbreviation = 'HL';
                                    break;
                                case 'Half Day (Punch Miss)':
                                    $abbreviation = 'HP';
                                    break;
                                case 'Half Day':
                                    $abbreviation = 'H';
                                    break;
                                case 'Absent':
                                    $abbreviation = 'A';
                                    break;
                                case 'Leave':
                                    $abbreviation = 'Leave';
                                    break;
                                case 'LOP':
                                    $abbreviation = 'LOP';
                                    break;
                                case 'Holiday':
                                    $abbreviation = 'H-Day';
                                    break;
                                default:
                                    $abbreviation = substr($status, 0, 1);
                            }
                        @endphp
                        {{ $abbreviation }}
                    @else
                        A
                    @endisset
                </td>
            @endforeach
        </tr>
        
        <!-- In Time Row -->
        <tr>
            <td><strong>InTime</strong></td>
            @foreach($dates as $date)
                <td>
                    @isset($attendanceData[$employee->id][$date]['clock_in'])
                        {{ substr($attendanceData[$employee->id][$date]['clock_in'], 0, 5) }}
                    @endisset
                </td>
            @endforeach
        </tr>
        
        <!-- Out Time Row -->
        <tr>
            <td><strong>OutTime</strong></td>
            @foreach($dates as $date)
                <td>
                    @isset($attendanceData[$employee->id][$date]['clock_out'])
                        {{ substr($attendanceData[$employee->id][$date]['clock_out'], 0, 5) }}
                    @endisset
                </td>
            @endforeach
        </tr>
        
        <!-- Total Time Row -->
        <tr>
            <td><strong>Total</strong></td>
            @foreach($dates as $date)
                <td>
                    @isset($attendanceData[$employee->id][$date]['total'])
                        {{ $attendanceData[$employee->id][$date]['total'] }}
                    @else
                        00:00
                    @endisset
                </td>
            @endforeach
        </tr>
    @endforeach
</table>