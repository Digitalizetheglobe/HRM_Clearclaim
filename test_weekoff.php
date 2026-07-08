<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sat = Carbon\Carbon::parse('2026-06-06');
$sun = Carbon\Carbon::parse('2026-06-07');

echo "Sat is week off: " . (App\Models\Utility::isWeekOff($sat) ? "true" : "false") . "\n";
echo "Sun is week off: " . (App\Models\Utility::isWeekOff($sun) ? "true" : "false") . "\n";
