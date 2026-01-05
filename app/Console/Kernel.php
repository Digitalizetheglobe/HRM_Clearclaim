<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Delete old todos daily
        $schedule->command('todos:delete-old')->daily();

        // Allocate monthly leaves on 1st day at midnight
        $schedule->command('leaves:allocate-monthly')
            ->monthlyOn(1, '00:00');

        // Mark absentees daily at 11:59 PM
        $schedule->command('attendance:mark-absentees')
            ->dailyAt('23:59');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Laravel auto-discovers all commands from this directory
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
