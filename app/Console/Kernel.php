<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        // Register your WeeklyNewsletter command
        \App\Console\Commands\WeeklyNewsletter::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Runs your weekly newsletter every Monday at 10:00 AM
        $schedule->command('newsletter:weekly')->weeklyOn(1, '10:00');

        // You can add more scheduled commands here if needed
        // Example:
        // $schedule->command('another:command')->dailyAt('08:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}