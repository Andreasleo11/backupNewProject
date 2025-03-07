<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Stringable;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('email:send-report-notification')
            ->days([Schedule::MONDAY, Schedule::THURSDAY])
            ->everyFourHours()
            ->between('0:30', '9:35');

        // Schedule the command to run daily at midnight
        $schedule->command('logs:delete-old')->daily();

        $schedule->call(function() {
            // Replace with the actual user or fetch users dynamically
            $user = \App\Models\User::where('email', 'benny@daijo.co.id')->first();

            if ($user) {
                $poCount = \App\Models\PurchaseOrder::approvedForCurrentMonth()->count();
                $user->notify(new \App\Notifications\MonthlyPOStatus($poCount));
            }
        })->monthlyOn(20, '00:30');;

        $schedule->command('send:training-reminders')->dailyAt('01:00');

        $schedule->command('email:daily-stock-report')->dailyAt('07:03');
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
