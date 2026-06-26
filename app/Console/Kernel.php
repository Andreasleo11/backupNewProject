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
        $schedule->command('app:send-approval-summary')
            ->dailyAt('08:00')
            ->timezone('Asia/Jakarta');

        // $schedule->command('inspire')->hourly();
        $schedule
            ->command('email:send-report-notification')
            ->days([Schedule::MONDAY, Schedule::THURSDAY])
            ->everyFourHours()
            ->between('0:30', '9:35');

        // Schedule the command to run daily at midnight
        // $schedule->command('logs:delete-old')->daily();
        $schedule->command('verification:cleanup-drafts --days=7')->daily();

        $schedule
            ->call(function () {
                // Replace with the actual user or fetch users dynamically
                $user = \App\Models\User::where('email', 'benny@daijo.co.id')->first();

                if ($user) {
                    $poCount = \App\Models\PurchaseOrder::approvedForCurrentMonth()->count();
                    $user->notify(new \App\Notifications\MonthlyPOStatus($poCount));
                }
            })
            ->monthlyOn(20, '00:30');

        $schedule->command('send:training-reminders')->dailyAt('01:00');

        $schedule
            ->command('employee-dashboard:update-from-api')
            ->dailyAt('09:30')
            ->timezone('Asia/Jakarta');

        $schedule->command('email:daily-stock-report')->dailyAt('01:30');

        //! under development
        // $schedule
        //     ->command('notify:missing-reports')
        //     ->weekdays()
        //     ->dailyAt('13:30')
        //     ->timezone('Asia/Jakarta');

        // $schedule->command('app:update-forecast')->dailyAt('13:00')->timezone('Asia/Jakarta');

        // Hourly refresh all department snapshots (lightweight)
        $schedule->call(function () {
            \App\Infrastructure\Persistence\Eloquent\Models\Department::query()->pluck('id')->each(function ($id) {
                \App\Jobs\UpdateDepartmentComplianceSnapshot::dispatch($id, writeMonthly: true);
            });
        })->hourly();

        // Weekly Compliance Threshold Notification
        $schedule->command('compliance:weekly-digest --threshold=70 --channel=both --limit=200')
            ->weeklyOn(1, '08:00')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping()
            ->onOneServer();

        // SAP Sync + Forecast Post-Processing (jam 12:00 WIB)
        $schedule->command('sap:sync --endpoint=all')
            ->dailyAt('12:00')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/sap-sync.log'));

        // SAP Sync + Forecast Post-Processing (jam 18:00 WIB)
        $schedule->command('sap:sync --endpoint=all')
            ->dailyAt('18:00')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/sap-sync.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
