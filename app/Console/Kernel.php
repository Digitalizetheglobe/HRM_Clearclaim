<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected $commands = [
        \App\Console\Commands\AllocateMonthlyLeaves::class,
        \App\Console\Commands\CreditMonthlyLeave::class,
        \App\Console\Commands\FixLoanAmounts::class,
        \App\Console\Commands\MarkAbsentees::class,
        \App\Console\Commands\RecalculateLoanAmounts::class,
        \App\Console\Commands\RepairEducationDocuments::class,
        \App\Console\Commands\CreateOffboardingProcesses::class,
    ];


    
    protected function schedule(Schedule $schedule)
    {
        // Schedule the custom command to run daily
        $schedule->command('todos:delete-old')->daily();
        $schedule->command('leaves:allocate-monthly')
             ->monthlyOn(1, '00:00');
        $schedule->command('attendance:mark-absentees')->dailyAt('23:59');



    }
    

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        
        // Load commands from both possible casings to avoid Linux case-sensitivity issues
        // when projects were developed/deployed from case-insensitive filesystems.
        $commandsDir = __DIR__ . '/Commands';
        $commandsDirLower = __DIR__ . '/commands';

        if (is_dir($commandsDir)) {
            $this->load($commandsDir);
        }
        if (is_dir($commandsDirLower)) {
            $this->load($commandsDirLower);
        }

        require base_path('routes/console.php');
    }

    
}
