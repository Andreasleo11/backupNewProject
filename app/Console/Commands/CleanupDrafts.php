<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationDraft;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupDrafts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verification:cleanup-drafts
                            {--days=7 : Number of days to keep drafts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune abandoned verification report drafts older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $deletedCount = VerificationDraft::where('updated_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deletedCount} abandoned verification draft(s) older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')}).");

        return Command::SUCCESS;
    }
}
