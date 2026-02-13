<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Plan;

echo "Plan count: " . Plan::count() . PHP_EOL;
$plans = Plan::all();
foreach($plans as $plan) {
    echo "Plan ID: " . $plan->id . ", Name: " . $plan->name . ", Price: " . $plan->price . PHP_EOL;
}
