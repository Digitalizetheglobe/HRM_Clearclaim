<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\AttendanceEmployee;
use Carbon\Carbon;

echo "Updating attendance to use new 'Present (Late)' status...\n";

// Get all records and update first 3 late marks
$allRecords = AttendanceEmployee::all();

echo "Processing {$allRecords->count()} attendance records...\n";

$updatedCount = 0;

foreach ($allRecords as $record) {
    // Skip if no clock in or not a late mark
    if (empty($record->clock_in) || $record->clock_in == '00:00:00') {
        continue;
    }
    
    $isLateMark = AttendanceEmployee::isLateMarkForEmployee($record->employee_id, $record->clock_in);
    
    if (!$isLateMark) {
        continue; // Only process late marks
    }
    
    // Count late marks for this employee in the month (excluding current day)
    $carbonDate = Carbon::parse($record->date);
    $startOfMonth = $carbonDate->copy()->startOfMonth()->format('Y-m-d');
    
    $lateMarksCount = AttendanceEmployee::where('employee_id', $record->employee_id)
        ->where('date', '>=', $startOfMonth)
        ->where('date', '<', $record->date) // Only dates BEFORE current date
        ->where('clock_in', '!=', '00:00:00')
        ->get()
        ->filter(function($attendance) use ($record) {
            return AttendanceEmployee::isLateMarkForEmployee($attendance->employee_id, $attendance->clock_in);
        })
        ->count();
    
    // Only update first 3 late marks
    if ($lateMarksCount < 3) {
        // This is one of the first 3 late marks - should be Present (Late)
        if ($record->status !== 'Present (Late)') {
            $record->status = 'Present (Late)';
            
            // Ensure they have full day hours (8.5 hours)
            $clockInTime = Carbon::parse($record->date . ' ' . $record->clock_in);
            $fullDayClockOut = $clockInTime->copy()->addMinutes(510); // 8.5 hours
            
            // Cap at reasonable end time
            $endOfDay = Carbon::parse($record->date . ' 20:00:00');
            if ($fullDayClockOut->gt($endOfDay)) {
                $fullDayClockOut = $endOfDay;
            }
            
            $record->clock_out = $fullDayClockOut->format('H:i:s');
            $record->save();
            
            $updatedCount++;
            
            echo "Updated ID {$record->id}: {$record->date} - Employee {$record->employee_id} - Late mark #" . ($lateMarksCount + 1) . " → Present (Late) (8.5 hours)\n";
        }
    }
}

echo "\n✅ Updated {$updatedCount} records to 'Present (Late)' status!\n";
echo "✅ First 3 late marks now show as 'Present (Late)' with 8.5 hours\n";
echo "✅ 4th+ late marks remain 'Half Day (Late)' with 4.5 hours\n";
