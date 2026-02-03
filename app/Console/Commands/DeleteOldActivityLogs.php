<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class DeleteOldActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:delete-old
                            {--days=30 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete activity logs older than specified days (default: 30 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        // Delete logs older than specified days from Spatie's activity_log table
        $deletedCount = Activity::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deletedCount} activity log(s) older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')}).");

        // Optionally clean up old activity_logs table if it still exists
        if (\Schema::hasTable('activity_logs')) {
            $oldDeletedCount = \DB::table('activity_logs')
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            if ($oldDeletedCount > 0) {
                $this->comment("Also deleted {$oldDeletedCount} records from legacy activity_logs table.");
            }
        }

        return 0;
    }
}
