<?php

namespace App\Console\Commands;

use App\Models\PurchaseRequest;
use Illuminate\Console\Command;

/**
 * Command to monitor approval system reliability
 * Tracks success rates, average times, and bottlenecks
 */
class MonitorApprovalReliability extends Command
{
    protected $signature = 'pr:monitor-approvals 
                            {--days=30 : Number of days to analyze}
                            {--export : Export stats to JSON}';

    protected $description = 'Monitor approval system reliability and performance';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $export = $this->option('export');

        $this->info("Analyzing approval system reliability (last {$days} days)...");
        $this->newLine();

        $stats = $this->calculateStats($days);

        $this->displayOverview($stats);
        $this->displayWorkflowStats($stats);
        $this->displayPerformance($stats);

        if ($export) {
            $this->exportStats($stats);
        }

        return Command::SUCCESS;
    }

    private function calculateStats(int $days): array
    {
        $startDate = now()->subDays($days);

        $baseQuery = PurchaseRequest::where('created_at', '>=', $startDate);

        $total = $baseQuery->count();
        $approved = $baseQuery->whereHas('approvalRequest', fn($q) =>
                $q->where('status', 'APPROVED')
            )->count();
        $rejected = $baseQuery->clone()->whereHas('approvalRequest', fn($q) =>
                $q->where('status', 'REJECTED')
            )->count();
        $inReview = $baseQuery->clone()->whereHas('approvalRequest', fn($q) =>
                $q->where('status', 'IN_REVIEW')
            )->count();
        // Assuming 'DRAFT' and null workflow_status means no approvalRequest or a specific status in approvalRequest
        // For now, we'll count PRs without an approvalRequest or with a 'DRAFT' status if it exists in the related model.
        // If 'DRAFT' is a status on the PurchaseRequest itself, this needs adjustment.
        // For this change, we'll assume 'DRAFT' means no approval request has been initiated yet.
        $draft = $baseQuery->clone()->doesntHave('approvalRequest')->count();
        $canceled = $baseQuery->clone()->whereHas('approvalRequest', fn($q) =>
                $q->where('status', 'CANCELED')
            )->count();

        // Calculate average approval time
        $approvedPRs = PurchaseRequest::where('created_at', '>=', $startDate)
            ->whereHas('approvalRequest', fn($q) => $q->where('status', 'APPROVED'))
            ->with(['approvalRequest.steps' => fn($q) => $q->where('status', 'APPROVED')])
            ->get();

        $avgApprovalTime = $approvedPRs->avg(function ($pr) {
            $approvedAt = $pr->approvalRequest->steps->max('acted_at');
            return $approvedAt ? $pr->created_at->diffInHours($approvedAt) : null;
        });

        // Signature coverage
        $withSignatures = PurchaseRequest::where('created_at', '>=', $startDate)
            ->whereHas('signatures')
            ->count();

        return [
            'period_days' => $days,
            'overview' => [
                'total' => $total,
                'approved' => $approved,
                'rejected' => $rejected,
                'in_review' => $inReview,
                'draft' => $draft,
                'canceled' => $canceled,
            ],
            'rates' => [
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
                'rejection_rate' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0,
                'completion_rate' => $total > 0 ? round((($approved + $rejected) / $total) * 100, 2) : 0,
            ],
            'performance' => [
                'avg_approval_time_hours' => round($avgApprovalTime ?? 0, 2),
                'signature_coverage' => $total > 0 ? round(($withSignatures / $total) * 100, 2) : 0,
            ],
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    private function displayOverview(array $stats): void
    {
        $this->info('📊 Overview');
        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Total PRs', $stats['overview']['total'], '100%'],
                ['Approved', $stats['overview']['approved'], $stats['rates']['approval_rate'] . '%'],
                ['Rejected', $stats['overview']['rejected'], $stats['rates']['rejection_rate'] . '%'],
                ['In Review', $stats['overview']['in_review'], '-'],
                ['Draft', $stats['overview']['draft'], '-'],
                ['Canceled', $stats['overview']['canceled'], '-'],
            ]
        );
    }

    private function displayWorkflowStats(array $stats): void
    {
        $this->newLine();
        $this->info('🔄 Workflow Statistics');

        $approvalRate = $stats['rates']['approval_rate'];
        $completionRate = $stats['rates']['completion_rate'];

        if ($approvalRate >= 80) {
            $this->info("✅ Approval Rate: {$approvalRate}% (Healthy)");
        } elseif ($approvalRate >= 60) {
            $this->warn("⚠️  Approval Rate: {$approvalRate}% (Moderate)");
        } else {
            $this->error("❌ Approval Rate: {$approvalRate}% (Low)");
        }

        $this->line("   Completion Rate: {$completionRate}%");
    }

    private function displayPerformance(array $stats): void
    {
        $this->newLine();
        $this->info('⚡ Performance Metrics');

        $avgTime = $stats['performance']['avg_approval_time_hours'];
        $coverage = $stats['performance']['signature_coverage'];

        $this->line("   Average Approval Time: {$avgTime} hours");

        if ($coverage >= 90) {
            $this->info("✅ Signature Coverage: {$coverage}%");
        } elseif ($coverage >= 50) {
            $this->warn("⚠️  Signature Coverage: {$coverage}%");
        } else {
            $this->error("❌ Signature Coverage: {$coverage}% (Migration needed)");
        }
    }

    private function exportStats(array $stats): void
    {
        $filename = storage_path('app/approval_stats_' . now()->format('Y-m-d_H-i-s') . '.json');
        file_put_contents($filename, json_encode($stats, JSON_PRETTY_PRINT));

        $this->newLine();
        $this->info("📁 Stats exported to: {$filename}");
    }
}
