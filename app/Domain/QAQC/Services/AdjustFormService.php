<?php

declare(strict_types=1);

namespace App\Domain\QAQC\Services;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationItem;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use App\Models\FormAdjustMaster;
use App\Models\HeaderFormAdjust;
use App\Models\MasterDataAdjust;
use Illuminate\Support\Facades\Auth;

final class AdjustFormService
{
    /**
     * Get or create header form adjust.
     */
    public function getOrCreateHeader(int $reportId): HeaderFormAdjust
    {
        $found = HeaderFormAdjust::where('report_id', $reportId)->first();

        if (! $found) {
            $found = HeaderFormAdjust::create(['report_id' => $reportId]);
        }

        return $found;
    }

    /**
     * Get master data for report's parts.
     */
    public function getMasterDataForReport(int $reportId): \Illuminate\Support\Collection
    {
        $report = VerificationReport::with('items')->findOrFail($reportId);
        $firstParts = [];

        foreach ($report->items as $item) {
            $parts = explode('/', $item->part_name);
            $firstParts[] = $parts[0];
        }

        $masterDataCollection = collect();

        foreach ($firstParts as $part) {
            $masterData = MasterDataAdjust::where('fg_code', $part)->get();
            $masterDataCollection = $masterDataCollection->merge($masterData);
        }

        return $masterDataCollection;
    }

    /**
     * Save adjustment master data.
     */
    public function saveAdjustment(array $data): void
    {
        $masterData = MasterDataAdjust::findOrFail($data['master_id']);

        FormAdjustMaster::create([
            'detail_id' => $data['detail_id'],
            'header_id' => $data['header_id'],
            'rm_code' => $masterData->rm_code,
            'rm_description' => $masterData->rm_description,
            'rm_quantity' => $masterData->rm_quantity,
            'fg_measure' => $masterData->fg_measure,
            'rm_measure' => $masterData->rm_measure,
            'warehouse_name' => $data['rm_warehouse'],
        ]);

        VerificationItem::where('id', $data['detail_id'])->update([
            'fg_measure' => $masterData->fg_measure,
        ]);
    }

    /**
     * Update warehouse for detail.
     */
    public function saveWarehouse(int $detailId, string $fgWarehouse): void
    {
        VerificationItem::where('id', $detailId)->update(['fg_warehouse_name' => $fgWarehouse]);
    }

    /**
     * Add remark to detail.
     */
    public function addRemark(int $detailId, string $remark): void
    {
        VerificationItem::where('id', $detailId)->update(['remark' => $remark]);
    }

    /**
     * Save autograph for header form adjust.
     */
    public function saveAutograph(int $reportId, int $section): void
    {
        $username = Auth::user()->name;
        $imagePath = $username . '.png';

        $report = HeaderFormAdjust::findOrFail($reportId);
        $report->update(["autograph_{$section}" => $imagePath]);
    }
}
