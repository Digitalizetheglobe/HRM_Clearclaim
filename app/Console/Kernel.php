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
        \App\Console\Commands\MarkAbsentees::class,

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
        
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    
}
