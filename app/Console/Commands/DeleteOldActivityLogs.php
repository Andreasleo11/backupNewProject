<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteOldActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "logs:delete-old";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete activity logs older than one month";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Delete logs older than 1 month
        DB::table("activity_logs")
            ->where("created_at", "<", Carbon::now()->subMonth())
            ->delete();

        $this->info("Old activity logs deleted successfully.");
    }
}
