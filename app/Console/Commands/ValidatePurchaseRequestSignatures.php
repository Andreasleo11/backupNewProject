<?php

namespace App\Console\Commands;

use App\Models\PurchaseRequest;
use Illuminate\Console\Command;

/**
 * Validation command to check signature data integrity
 * Part of Phase 1 monitoring tools
 */
class ValidatePurchaseRequestSignatures extends Command
{
    protected $signature = 'pr:validate-signatures 
                            {--days=30 : Number of days to check}';

    protected $description = 'Validate PR signatures integrity and detect any autograph writes';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $verbose = $this->getOutput()->isVerbose();

        $this->info("Validating Purchase Request signatures (last {$days} days)...");
        $this->newLine();

        $prs = PurchaseRequest::query()
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNull('deleted_at')
            ->with('approvalRequest.steps', 'signatures')
            ->get();

        $stats = [
            'total_prs' => $prs->count(),
            'with_approval_signatures' => 0,
            'with_legacy_signatures' => 0,
            'with_old_autographs' => 0,
            'with_both_systems' => 0,
            'autograph_writes' => 0,
            'mismatches' => [],
        ];

        foreach ($prs as $pr) {
            // Check approval system
            $hasApprovalSigs = $pr->approvalRequest?->steps()
                ->whereNotNull('signature_image_path')
                ->exists() ?? false;

            // Check legacy table
            $legacyCount = $pr->signatures()->count();

            // Check autographs
            $oldCount = $this->countAutographs($pr);

            if ($hasApprovalSigs) {
                $stats['with_approval_signatures']++;
            }

            if ($legacyCount > 0) {
                $stats['with_legacy_signatures']++;
            }

            if ($oldCount > 0) {
                $stats['with_old_autographs']++;

                // Check if created recently (potential new write to old system)
                if ($pr->created_at >= now()->subWeek()) {
                    $stats['autograph_writes']++;
                    if ($verbose) {
                        $this->warn("⚠️  PR #{$pr->id} (created {$pr->created_at->diffForHumans()}) has {$oldCount} autograph(s)");
                    }
                }
            }

            if (($hasApprovalSigs || $legacyCount > 0) && $oldCount > 0) {
                $stats['with_both_systems']++;
            }
        }

        $this->displayResults($stats, $verbose);

        return Command::SUCCESS;
    }

    private function countAutographs(PurchaseRequest $pr): int
    {
        $count = 0;
        for ($i = 1; $i <= 7; $i++) {
            if ($pr->{"autograph_$i"}) {
                $count++;
            }
        }

        return $count;
    }

    private function displayResults(array $stats, bool $verbose): void
    {
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total PRs', $stats['total_prs']],
                ['With Approval Signatures ✅', $stats['with_approval_signatures']],
                ['With Legacy Table Sigs', $stats['with_legacy_signatures']],
                ['With Old Autographs', $stats['with_old_autographs']],
                ['With Mixed Systems', $stats['with_both_systems']],
                ['Recent Autograph Writes ⚠️', $stats['autograph_writes']],
            ]
        );

        if ($stats['autograph_writes'] > 0) {
            $this->newLine();
            $this->error("⚠️  WARNING: {$stats['autograph_writes']} PR(s) created in the last week still using old autograph system!");
            $this->warn('   This indicates code is still writing to autograph columns.');
        } else {
            $this->newLine();
            $this->info('✅ No recent autograph writes detected!');
        }

        if ($verbose && $stats['with_approval_signatures'] > 0) {
            $this->newLine();
            $this->info("ℹ️  {$stats['with_approval_signatures']} PRs using modern approval system (approval_steps)");
        }

        if ($verbose && $stats['with_legacy_signatures'] > 0) {
            $this->newLine();
            $this->warn("ℹ️  {$stats['with_legacy_signatures']} PRs have signatures in legacy table (purchase_request_signatures)");
        }

        $this->newLine();
        $this->info('💡 Tip: Run with -v for detailed output');
    }
}
