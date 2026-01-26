<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Services;

use App\Models\CategoryMaintenanceInventoryReport;
use App\Models\DetailMaintenanceInventoryReport;
use App\Models\HeaderMaintenanceInventoryReport;

final class MaintenanceReportService
{
    /**
     * Get reports with filtering and username status.
     */
    public function getReports(array $filters): array
    {
        $periode = $filters['periode'] ?? null;
        $year = $filters['year'] ?? date('Y');

        $reportsQuery = HeaderMaintenanceInventoryReport::with('master');

        if ($periode) {
            $this->applyPeriodeFilter($reportsQuery, (int) $periode);
        }

        $reportsQuery->whereYear('created_at', $year);

        $headerData = $reportsQuery->get();
        $reports = $reportsQuery->orderBy('created_at', 'desc')->paginate(10);

        return [
            'reports' => $reports,
            'headerData' => $headerData,
        ];
    }

    /**
     * Create maintenance report.
     */
    public function createReport(array $data): HeaderMaintenanceInventoryReport
    {
        $header = HeaderMaintenanceInventoryReport::create([
            'no_dokumen' => HeaderMaintenanceInventoryReport::generateNoDokumen(),
            'master_id' => $data['master_id'],
            'revision_date' => $data['revision_date'],
        ]);

        $this->createDetails($header->id, $data['items'] ?? [], $data);
        $this->createNewItems($header->id, $data['new_items'] ?? [], $data);

        return $header;
    }

    /**
     * Update maintenance report.
     */
    public function updateReport(int $id, array $data): HeaderMaintenanceInventoryReport
    {
        $header = HeaderMaintenanceInventoryReport::findOrFail($id);

        $header->update([
            'master_id' => $data['master_id'],
            'revision_date' => $data['revision_date'],
        ]);

        $this->updateDetails($data['items'] ?? [], $data);

        if (! empty($data['new_items'])) {
            $this->createNewItems($header->id, $data['new_items'], $data);
        }

        return $header;
    }

    /**
     * Get period based on month.
     */
    public function getPeriodeCaturwulan(int $month): int
    {
        if ($month >= 1 && $month <= 4) {
            return 1;
        } elseif ($month >= 5 && $month <= 8) {
            return 2;
        } else {
            return 3;
        }
    }

    /**
     * Apply period filter to query.
     */
    private function applyPeriodeFilter($query, int $periode): void
    {
        switch ($periode) {
            case 1:
                $query->whereMonth('created_at', '>=', 1)->whereMonth('created_at', '<=', 4);
                break;
            case 2:
                $query->whereMonth('created_at', '>=', 5)->whereMonth('created_at', '<=', 8);
                break;
            case 3:
                $query->whereMonth('created_at', '>=', 9)->whereMonth('created_at', '<=', 12);
                break;
        }
    }

    /**
     * Helper to create report details.
     */
    private function createDetails(int $headerId, array $items, array $data): void
    {
        foreach ($items as $itemId) {
            DetailMaintenanceInventoryReport::create([
                'header_id' => $headerId,
                'category_id' => $itemId,
                'condition' => $data['conditions'][$itemId] ?? null,
                'remark' => $data['remarks'][$itemId] ?? null,
                'checked_by' => $data['checked_by'][$itemId] ?? null,
            ]);
        }
    }

    /**
     * Helper to create new items (both category and detail).
     */
    private function createNewItems(int $headerId, array $newItems, array $data): void
    {
        foreach ($newItems as $newItemId) {
            $newItemName = $data['new_items_names'][$newItemId] ?? null;
            if (! $newItemName) {
                continue;
            }

            $category = CategoryMaintenanceInventoryReport::create([
                'group_id' => $data['new_group_ids'][$newItemId],
                'name' => $newItemName,
            ]);

            DetailMaintenanceInventoryReport::create([
                'header_id' => $headerId,
                'category_id' => $category->id,
                'condition' => $data['new_conditions'][$newItemId] ?? null,
                'remark' => $data['new_remarks'][$newItemId] ?? null,
                'checked_by' => $data['new_checked_by'][$newItemId] ?? null,
            ]);
        }
    }

    /**
     * Helper to update report details.
     */
    private function updateDetails(array $items, array $data): void
    {
        foreach ($items as $itemId) {
            $detail = DetailMaintenanceInventoryReport::find($itemId);
            if ($detail) {
                $detail->update([
                    'condition' => $data['conditions'][$itemId] ?? null,
                    'remark' => $data['remarks'][$itemId] ?? null,
                    'checked_by' => $data['checked_by'][$itemId] ?? null,
                ]);
            }
        }
    }
}
