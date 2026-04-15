<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CancelOutdatedApprovalRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:cancel-outdated 
                            {--year= : The threshold year. Defaults to current year.} 
                            {--force : Run without interaction.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels all pending/active unified Approval Requests from previous years across all modules.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?? now()->year;

        $this->info("Scanning for active unified Approval Requests submitted prior to year: {$year}...");

        $requests = ApprovalRequest::whereYear('submitted_at', '<', $year)
            ->whereNotIn('status', ['APPROVED', 'CANCELED', 'REJECTED'])
            ->with(['approvable', 'steps'])
            ->get();

        if ($requests->isEmpty()) {
            $this->info('No outdated active Approval Requests found. The queue is clean!');

            return static::SUCCESS;
        }

        $count = $requests->count();
        $this->warn("Found {$count} active Approval Request(s) from before {$year}.");
        $this->warn('These requests will be cancelled, removing them from all pending queues system-wide.');

        if (! $this->option('force') && ! $this->confirm('Do you want to proceed and cancel them now?')) {
            $this->info('Operation manually cancelled.');

            return static::SUCCESS;
        }

        $this->withProgressBar($requests, function ($req) {
            DB::transaction(function () use ($req) {
                // Route 1: Cancel the unified approval request itself
                $req->update(['status' => 'CANCELED']);

                // Route 2: Cancel the current outstanding workflow step
                if ($req->current_step) {
                    $step = $req->steps()->where('sequence', $req->current_step)->first();
                    if ($step && $step->status === 'PENDING') {
                        $step->update([
                            'status' => 'CANCELED',
                            'acted_at' => now(),
                            'remarks' => 'System Auto-Cancelled: Outdated request from previous year.',
                        ]);
                    }
                }

                // Route 3: Cascade cancellation status down into the polymorphic sub-module layer (Overtime, Purchase Requests, Budgets, etc!)
                if ($req->approvable) {
                    $approvable = $req->approvable;
                    $table = $approvable->getTable();
                    $updates = [];

                    // Auto-detect cancellation flags on the respective child table schemas safely
                    if (Schema::hasColumn($table, 'is_cancel')) {
                        $updates['is_cancel'] = true;
                    }
                    if (Schema::hasColumn($table, 'is_cancelled')) {
                        $updates['is_cancelled'] = true;
                    }

                    // Auto-detect workflow state fields and close them
                    if (Schema::hasColumn($table, 'workflow_status')) {
                        $updates['workflow_status'] = 'CANCELED';
                    } elseif (Schema::hasColumn($table, 'status')) {
                        $updates['status'] = 'CANCELED';
                    }

                    if (! empty($updates)) {
                        $approvable->update($updates);
                    }
                }
            });
        });

        $this->newLine(2);
        $this->info("Successfully cancelled {$count} outdated Approval Request(s) across all modules.");

        return static::SUCCESS;
    }
}
