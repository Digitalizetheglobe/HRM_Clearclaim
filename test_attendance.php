<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employee = App\Models\Employee::where('name', 'Dnyaneshwar Sathe')->first();
$leaves = App\Models\Leave::where('employee_id', $employee->id)
    ->where(function($query) {
        $query->whereBetween('start_date', ['2026-06-01', '2026-06-30'])
            ->orWhereBetween('end_date', ['2026-06-01', '2026-06-30']);
    })->get();

foreach($leaves as $l) {
    echo "Leave: {$l->start_date} to {$l->end_date}, status: {$l->status}, is_lop: {$l->is_lop}, duration: {$l->leave_duration}\n";
}
