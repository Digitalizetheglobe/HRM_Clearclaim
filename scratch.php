<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$data = \App\Models\AttendanceEmployee::whereHas('employee', function($q) { $q->where('name', 'like', '%Jitendra%'); })
    ->whereMonth('date', '05')->whereYear('date', '2026')
    ->orderBy('date', 'asc')
    ->get(['date', 'status', 'clock_in', 'clock_out'])->toArray();

echo json_encode($data, JSON_PRETTY_PRINT);
