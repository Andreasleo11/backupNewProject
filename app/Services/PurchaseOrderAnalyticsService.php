<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PurchaseOrderAnalyticsService
{
    /**
     * Get comprehensive dashboard metrics for operational command center
     */
    public function getOperationalMetrics(array $filters = []): array
    {
        $cacheKey = 'po_operational_metrics_' . md5(serialize($filters));

        return Cache::remember($cacheKey, 300, function () use ($filters) {
            $dateRange = $filters['date_range'] ?? [now()->subDays(30), now()];

            return [
                // Core KPIs
                'totalSpend' => $this->getTotalSpend($dateRange, $filters),
                'orderCount' => $this->getOrderCount($dateRange, $filters),
                'averageOrderValue' => $this->getAverageOrderValue($dateRange, $filters),

                // Fulfillment & Aging Metrics
                'fulfillmentRate' => $this->getFulfillmentRate($dateRange, $filters),
                'agingAnalysis' => $this->getAgingAnalysis($dateRange, $filters),
                'overduePOs' => $this->getOverduePOs($dateRange, $filters),

                // Approval Workflow Metrics
                'approvalMetrics' => $this->getApprovalMetrics($dateRange, $filters),
                'bottleneckAnalysis' => $this->getBottleneckAnalysis($dateRange, $filters),

                // Supplier Performance
                'supplierPerformance' => $this->getSupplierPerformance($dateRange, $filters),
                'supplierLeadTimes' => $this->getSupplierLeadTimes($dateRange, $filters),

                // Predictive Analytics
                'trendForecast' => $this->getTrendForecast($dateRange, $filters),
                'budgetVariance' => $this->getBudgetVariance($dateRange, $filters),

                // Real-time Status
                'urgentAlerts' => $this->getUrgentAlerts($dateRange, $filters),
                'pendingActions' => $this->getPendingActions($filters),
            ];
        });
    }

    /**
     * Calculate order fulfillment rate (approved POs with payment dates vs total approved)
     */
    private function getFulfillmentRate(array $dateRange, array $filters): array
    {
        $baseQuery = PurchaseOrder::whereBetween('invoice_date', $dateRange)
            ->where('status', 2) // Approved
            ->when(isset($filters['categories']) && ! empty($filters['categories']), fn ($q) => $q->whereIn('purchase_order_category_id', $filters['categories']));

        $totalApproved = (clone $baseQuery)->count();
        $fulfilled = (clone $baseQuery)->whereNotNull('tanggal_pembayaran')->count();

        $rate = $totalApproved > 0 ? round(($fulfilled / $totalApproved) * 100, 1) : 0;

        return [
            'rate' => $rate,
            'fulfilled' => $fulfilled,
            'total' => $totalApproved,
            'pending' => $totalApproved - $fulfilled,
        ];
    }

    /**
     * Analyze aging of purchase orders (days since approval without payment)
     */
    private function getAgingAnalysis(array $dateRange, array $filters): array
    {
        $agingBuckets = [0, 7, 14, 30, 60, 90]; // Days

        $results = PurchaseOrder::selectRaw('
                CASE
                    WHEN tanggal_pembayaran IS NOT NULL THEN 0
                    WHEN approved_date IS NULL THEN DATEDIFF(CURDATE(), invoice_date)
                    ELSE DATEDIFF(CURDATE(), approved_date)
                END as days_aging,
                COUNT(*) as count,
                SUM(total) as total_value
            ')
            ->whereBetween('invoice_date', $dateRange)
            ->where('status', 2) // Approved
            ->when(isset($filters['categories']) && ! empty($filters['categories']), fn ($q) => $q->whereIn('purchase_order_category_id', $filters['categories']))
            ->groupBy('days_aging')
            ->orderBy('days_aging')
            ->get();

        $aging = [];
        foreach ($agingBuckets as $i => $days) {
            $nextDays = $agingBuckets[$i + 1] ?? PHP_INT_MAX;
            $bucketOrders = $results->filter(function ($item) use ($days, $nextDays) {
                return $item->days_aging >= $days && $item->days_aging < $nextDays;
            });

            $aging[] = [
                'bucket' => $days === 0 ? 'Current' : "{$days}-" . ($nextDays === PHP_INT_MAX ? '+' : ($nextDays - 1)) . ' days',
                'count' => $bucketOrders->sum('count'),
                'value' => $bucketOrders->sum('total_value'),
                'avg_age' => $bucketOrders->avg('days_aging') ?? 0,
            ];
        }

        return $aging;
    }

    /**
     * Get overdue purchase orders (past expected payment dates)
     */
    private function getOverduePOs(array $dateRange, array $filters): array
    {
        $overdue = PurchaseOrder::where('status', 2) // Approved
            ->whereNotNull('approved_date')
            ->where('tanggal_pembayaran', '<', now())
            ->whereBetween('invoice_date', $dateRange)
            ->when(isset($filters['categories']) && ! empty($filters['categories']), fn ($q) => $q->whereIn('purchase_order_category_id', $filters['categories']))
            ->with(['category', 'user'])
            ->orderBy('tanggal_pembayaran')
            ->take(10)
            ->get();

        return $overdue->map(function ($po) {
            return [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'vendor' => $po->vendor_name,
                'amount' => $po->total,
                'approved_date' => $po->approved_date,
                'expected_payment' => $po->tanggal_pembayaran,
                'days_overdue' => now()->diffInDays($po->tanggal_pembayaran),
                'category' => $po->category?->name,
                'creator' => $po->user?->name,
            ];
        })->toArray();
    }

    /**
     * Get approval workflow performance metrics
     */
    private function getApprovalMetrics(array $dateRange, array $filters): array
    {
        $query = DB::table('approval_requests')
            ->join('purchase_orders', 'approval_requests.approvable_id', '=', 'purchase_orders.id')
            ->where('approval_requests.approvable_type', 'App\\Models\\PurchaseOrder')
            ->whereBetween('purchase_orders.invoice_date', $dateRange);

        // Apply filters if provided
        if (isset($filters['categories']) && ! empty($filters['categories'])) {
            $query->whereIn('purchase_orders.purchase_order_category_id', $filters['categories']);
        }

        $approvalStats = $query->selectRaw('
                AVG(TIMESTAMPDIFF(HOUR, approval_requests.submitted_at, approval_requests.updated_at)) as avg_approval_hours,
                COUNT(*) as total_requests,
                SUM(CASE WHEN approval_requests.status = "approved" THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN approval_requests.status = "rejected" THEN 1 ELSE 0 END) as rejected_count
            ')
            ->first();

        return [
            'avg_approval_time_hours' => round($approvalStats->avg_approval_hours ?? 0, 1),
            'approval_rate' => $approvalStats->total_requests > 0
                ? round(($approvalStats->approved_count / $approvalStats->total_requests) * 100, 1)
                : 0,
            'total_requests' => $approvalStats->total_requests ?? 0,
        ];
    }

    /**
     * Identify approval bottlenecks and slow performers
     */
    private function getBottleneckAnalysis(array $dateRange, array $filters): array
    {
        $query = DB::table('approval_steps')
            ->join('approval_requests', 'approval_steps.approval_request_id', '=', 'approval_requests.id')
            ->join('purchase_orders', function ($join) {
                $join->on('approval_requests.approvable_id', '=', 'purchase_orders.id')
                    ->where('approval_requests.approvable_type', '=', 'App\\Models\\PurchaseOrder');
            })
            ->whereBetween('purchase_orders.invoice_date', $dateRange)
            ->where('approval_steps.status', 'pending')
            ->where('approval_steps.created_at', '<', now()->subDays(7));

        // Apply filters if provided
        if (isset($filters['categories']) && ! empty($filters['categories'])) {
            $query->whereIn('purchase_orders.purchase_order_category_id', $filters['categories']);
        }

        $slowSteps = $query->selectRaw('
                approval_steps.approver_snapshot_name,
                approval_steps.approver_snapshot_role_slug,
                COUNT(*) as pending_count,
                AVG(TIMESTAMPDIFF(DAY, approval_steps.created_at, NOW())) as avg_days_pending
            ')
            ->groupBy('approval_steps.approver_snapshot_name', 'approval_steps.approver_snapshot_role_slug')
            ->having('pending_count', '>', 0)
            ->orderByDesc('avg_days_pending')
            ->take(5)
            ->get();

        return $slowSteps->map(function ($step) {
            return [
                'approver_name' => $step->approver_snapshot_name,
                'role' => $step->approver_snapshot_role_slug,
                'pending_count' => $step->pending_count,
                'avg_days_pending' => round($step->avg_days_pending, 1),
            ];
        })->toArray();
    }

    /**
     * Get supplier performance metrics including lead times
     */
    private function getSupplierLeadTimes(array $dateRange, array $filters): array
    {
        return PurchaseOrder::selectRaw('
                vendor_name,
                COUNT(*) as total_orders,
                AVG(DATEDIFF(tanggal_pembayaran, invoice_date)) as avg_lead_time_days,
                MIN(DATEDIFF(tanggal_pembayaran, invoice_date)) as min_lead_time,
                MAX(DATEDIFF(tanggal_pembayaran, invoice_date)) as max_lead_time
            ')
            ->whereBetween('invoice_date', $dateRange)
            ->whereNotNull('tanggal_pembayaran')
            ->where('status', 2) // Approved
            ->when(isset($filters['categories']) && ! empty($filters['categories']), fn ($q) => $q->whereIn('purchase_order_category_id', $filters['categories']))
            ->groupBy('vendor_name')
            ->having('total_orders', '>=', 3) // Minimum sample size
            ->orderBy('avg_lead_time_days')
            ->take(10)
            ->get()
            ->map(function ($supplier) {
                return [
                    'vendor' => $supplier->vendor_name,
                    'total_orders' => $supplier->total_orders,
                    'avg_lead_time' => round($supplier->avg_lead_time_days ?? 0, 1),
                    'lead_time_range' => [
                        'min' => $supplier->min_lead_time,
                        'max' => $supplier->max_lead_time,
                    ],
                    'reliability_score' => $this->calculateSupplierReliability($supplier),
                ];
            })
            ->toArray();
    }

    /**
     * Calculate supplier reliability score based on consistency
     */
    private function calculateSupplierReliability($supplier): float
    {
        if (! $supplier->avg_lead_time_days) {
            return 0;
        }

        // Calculate coefficient of variation (lower is more reliable)
        $variation = ($supplier->max_lead_time - $supplier->min_lead_time) / $supplier->avg_lead_time_days;
        $reliability = max(0, 100 - ($variation * 50)); // Scale to 0-100

        return round($reliability, 1);
    }

    /**
     * Generate trend forecast using historical data
     */
    private function getTrendForecast(array $dateRange, array $filters): array
    {
        // Simple linear regression for forecasting
        $query = PurchaseOrder::selectRaw("
                DATE_FORMAT(invoice_date, '%Y-%m') as month,
                SUM(total) as monthly_spend,
                COUNT(*) as order_count
            ")
            ->where('invoice_date', '>=', now()->subMonths(12))
            ->when(isset($filters['categories']) && ! empty($filters['categories']), fn ($q) => $q->whereIn('purchase_order_category_id', $filters['categories']))
            ->when(isset($filters['statuses']) && ! empty($filters['statuses']), fn ($q) => $q->whereIn('status', $filters['statuses']))
            ->groupBy('month')
            ->orderBy('month');

        $historicalData = $query->get();

        // Check if we have enough data for forecasting
        if ($historicalData->isEmpty()) {
            return [
                'next_month_prediction' => 0,
                'trend_direction' => 'insufficient_data',
                'confidence_level' => 0,
                'seasonal_factors' => [],
            ];
        }

        $forecast = $this->calculateLinearRegression($historicalData->pluck('monthly_spend')->toArray());

        return [
            'next_month_prediction' => round($forecast['next_value'], 2),
            'trend_direction' => $forecast['slope'] > 0 ? 'increasing' : 'decreasing',
            'confidence_level' => round($forecast['r_squared'] * 100, 1),
            'seasonal_factors' => $this->detectSeasonalPatterns($historicalData),
        ];
    }

    /**
     * Simple linear regression calculation
     */
    private function calculateLinearRegression(array $values): array
    {
        $n = count($values);

        // Need at least 2 data points for regression
        if ($n < 2) {
            return [
                'slope' => 0,
                'intercept' => array_sum($values) / max(1, $n),
                'next_value' => array_sum($values) / max(1, $n),
                'r_squared' => 0,
            ];
        }

        $x = range(1, $n);

        $sumX = array_sum($x);
        $sumY = array_sum($values);
        $sumXY = array_sum(array_map(fn ($xi, $yi) => $xi * $yi, $x, $values));
        $sumX2 = array_sum(array_map(fn ($xi) => $xi * $xi, $x));

        $denominator = ($n * $sumX2 - $sumX * $sumX);

        // Avoid division by zero
        if ($denominator == 0) {
            return [
                'slope' => 0,
                'intercept' => $sumY / $n,
                'next_value' => $sumY / $n,
                'r_squared' => 0,
            ];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $intercept = ($sumY - $slope * $sumX) / $n;

        $nextValue = $intercept + $slope * ($n + 1);

        // Calculate R-squared
        $yMean = $sumY / $n;
        $ssRes = array_sum(array_map(function ($xi, $yi) use ($slope, $intercept) {
            $predicted = $intercept + $slope * $xi;

            return pow($yi - $predicted, 2);
        }, $x, $values));

        $ssTot = array_sum(array_map(fn ($yi) => pow($yi - $yMean, 2), $values));
        $rSquared = $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'next_value' => $nextValue,
            'r_squared' => $rSquared,
        ];
    }

    /**
     * Get urgent alerts requiring immediate attention
     */
    private function getUrgentAlerts(array $dateRange, array $filters): array
    {
        $alerts = [];

        // Critical overdue POs (>90 days)
        $criticalOverdue = PurchaseOrder::where('status', 1)
            ->whereNotNull('approved_date')
            ->where('tanggal_pembayaran', '<', now()->subDays(90))
            ->whereBetween('invoice_date', $dateRange)
            ->count();

        if ($criticalOverdue > 0) {
            $alerts[] = [
                'type' => 'critical',
                'message' => "{$criticalOverdue} POs critically overdue (>90 days)",
                'action_required' => 'Immediate review needed',
                'priority' => 'high',
            ];
        }

        // Stuck approvals (>7 days)
        $stuckApprovals = DB::table('approval_steps')
            ->join('approval_requests', 'approval_steps.approval_request_id', '=', 'approval_requests.id')
            ->where('approval_steps.status', 'pending')
            ->where('approval_steps.created_at', '<', now()->subDays(7))
            ->count();

        if ($stuckApprovals > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$stuckApprovals} approvals stuck for more than 7 days",
                'action_required' => 'Follow up with approvers',
                'priority' => 'medium',
            ];
        }

        return $alerts;
    }

    /**
     * Get pending actions for current user
     */
    private function getPendingActions(array $filters): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $pendingApprovals = DB::table('approval_steps')
            ->join('approval_requests', 'approval_steps.approval_request_id', '=', 'approval_requests.id')
            ->where('approval_steps.status', 'pending')
            ->where(function ($query) use ($user) {
                $query->where('approval_steps.approver_id', $user->id)
                    ->orWhere('approval_steps.approver_snapshot_role_slug', 'LIKE', '%director%'); // Simplified
            })
            ->count();

        $pendingPOs = PurchaseOrder::where('status', 1) // Draft
            ->where('creator_id', $user->id)
            ->count();

        return [
            'pending_approvals' => $pendingApprovals,
            'draft_pos' => $pendingPOs,
            'total_actions' => $pendingApprovals + $pendingPOs,
        ];
    }

    /**
     * Parse date range string to array format
     */
    private function parseDateRange(?string $range): array
    {
        return match ($range) {
            'last_7_days' => [now()->subDays(7), now()],
            'last_30_days' => [now()->subDays(30), now()],
            'last_90_days' => [now()->subDays(90), now()],
            'last_6_months' => [now()->subMonths(6), now()],
            'last_year' => [now()->subYear(), now()],
            default => [now()->subDays(30), now()],
        };
    }

    /**
     * Get total spend within date range
     */
    private function getTotalSpend(array $dateRange, array $filters): float
    {
        return PurchaseOrder::whereBetween('invoice_date', $dateRange)
            ->when(isset($filters['statuses']) && ! empty($filters['statuses']), fn ($q) => $q->whereIn('status', $filters['statuses']))
            ->when(isset($filters['categories']) && ! empty($filters['categories']), fn ($q) => $q->whereIn('purchase_order_category_id', $filters['categories']))
            ->sum('total');
    }

    /**
     * Get order count within date range
     */
    private function getOrderCount(array $dateRange, array $filters): int
    {
        return PurchaseOrder::whereBetween('invoice_date', $dateRange)
            ->when(isset($filters['statuses']) && ! empty($filters['statuses']), fn ($q) => $q->whereIn('status', $filters['statuses']))
            ->when(isset($filters['categories']) && ! empty($filters['categories']), fn ($q) => $q->whereIn('purchase_order_category_id', $filters['categories']))
            ->count();
    }

    /**
     * Get average order value within date range
     */
    private function getAverageOrderValue(array $dateRange, array $filters): float
    {
        return PurchaseOrder::whereBetween('invoice_date', $dateRange)
            ->when(isset($filters['statuses']) && ! empty($filters['statuses']), fn ($q) => $q->whereIn('status', $filters['statuses']))
            ->when(isset($filters['categories']) && ! empty($filters['categories']), fn ($q) => $q->whereIn('purchase_order_category_id', $filters['categories']))
            ->avg('total') ?? 0;
    }

    /**
     * Get supplier performance metrics
     */
    private function getSupplierPerformance(array $dateRange, array $filters): array
    {
        return PurchaseOrder::selectRaw('
                vendor_name,
                COUNT(*) as order_count,
                SUM(total) as total_spend,
                AVG(total) as avg_order_value,
                MIN(invoice_date) as first_order,
                MAX(invoice_date) as last_order
            ')
            ->whereBetween('invoice_date', $dateRange)
            ->when(isset($filters['statuses']) && ! empty($filters['statuses']), fn ($q) => $q->whereIn('status', $filters['statuses']))
            ->when(isset($filters['categories']) && ! empty($filters['categories']), fn ($q) => $q->whereIn('purchase_order_category_id', $filters['categories']))
            ->groupBy('vendor_name')
            ->orderByDesc('total_spend')
            ->take(20)
            ->get()
            ->map(function ($supplier) {
                return [
                    'vendor' => $supplier->vendor_name,
                    'order_count' => $supplier->order_count,
                    'total_spend' => $supplier->total_spend,
                    'avg_order_value' => round($supplier->avg_order_value, 2),
                    'order_frequency' => $this->calculateOrderFrequency($supplier),
                    'relationship_duration_days' => $supplier->first_order
                        ? now()->diffInDays(Carbon::parse($supplier->first_order))
                        : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate order frequency (orders per month)
     */
    private function calculateOrderFrequency($supplier): float
    {
        if (! $supplier->first_order || ! $supplier->last_order) {
            return 0;
        }

        $months = max(1, Carbon::parse($supplier->first_order)->diffInMonths(Carbon::parse($supplier->last_order)) + 1);

        return round($supplier->order_count / $months, 2);
    }

    /**
     * Get budget variance analysis (placeholder - implement based on budget system)
     */
    private function getBudgetVariance(array $dateRange, array $filters): array
    {
        // This would integrate with a budgeting system
        // For now, return mock data structure
        return [
            'budget_allocated' => 1000000,
            'actual_spend' => $this->getTotalSpend($dateRange, $filters),
            'variance_amount' => 0, // Calculate: actual - budget
            'variance_percentage' => 0, // Calculate: (actual - budget) / budget * 100
            'forecast_accuracy' => 85.5, // Based on historical forecasting accuracy
        ];
    }

    /**
     * Detect seasonal patterns in spending (placeholder)
     */
    private function detectSeasonalPatterns($data): array
    {
        // Simple seasonal analysis - identify peak months
        $monthlyAverages = [];
        foreach ($data as $item) {
            $month = Carbon::parse($item->month . '-01')->month;
            $monthlyAverages[$month][] = $item->monthly_spend;
        }

        $seasonal = [];
        foreach ($monthlyAverages as $month => $values) {
            $seasonal[$month] = [
                'month' => $month,
                'average' => array_sum($values) / count($values),
                'is_peak' => false, // Would calculate based on statistical analysis
            ];
        }

        return array_values($seasonal);
    }
}
