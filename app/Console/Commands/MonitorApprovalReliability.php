<?php

namespace App\Console\Commands;

use App\Models\PurchaseRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        $total = PurchaseRequest::where('created_at', '>=', $startDate)->count();
        $approved = PurchaseRequest::where('created_at', '>=', $startDate)
            ->where('workflow_status', 'APPROVED')
            ->count();
        $rejected = PurchaseRequest::where('created_at', '>=', $startDate)
            ->where('workflow_status', 'REJECTED')
            ->count();
        $inReview = PurchaseRequest::where('created_at', '>=', $startDate)
            ->where('workflow_status', 'IN_REVIEW')
            ->count();
        $draft = PurchaseRequest::where('created_at', '>=', $startDate)
            ->whereIn('workflow_status', ['DRAFT', null])
            ->count();
        $canceled = PurchaseRequest::where('created_at', '>=', $startDate)
            ->where('workflow_status', 'CANCELED')
            ->count();

        // Calculate average approval time
        $approvedPRs = PurchaseRequest::where('created_at', '>=', $startDate)
            ->where('workflow_status', 'APPROVED')
            ->whereNotNull('approved_at')
            ->get();

        $avgApprovalTime = $approvedPRs->avg(function ($pr) {
            return $pr->created_at->diffInHours($pr->approved_at);
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
        $filename = storage_path("app/approval_stats_" . now()->format('Y-m-d_H-i-s') . ".json");
        file_put_contents($filename, json_encode($stats, JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->info("📁 Stats exported to: {$filename}");
    }
}
