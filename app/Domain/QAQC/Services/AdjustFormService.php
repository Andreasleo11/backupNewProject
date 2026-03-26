<?php

declare(strict_types=1);

namespace App\Domain\QAQC\Services;

use App\Models\Detail;
use App\Models\FormAdjustMaster;
use App\Models\HeaderFormAdjust;
use App\Models\MasterDataAdjust;
use App\Models\Report;
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
        $report = Report::with('details')->findOrFail($reportId);
        $firstParts = [];

        foreach ($report->details as $detail) {
            $parts = explode('/', $detail->part_name);
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

        Detail::where('id', $data['detail_id'])->update([
            'fg_measure' => $masterData->fg_measure,
        ]);
    }

    /**
     * Update warehouse for detail.
     */
    public function saveWarehouse(int $detailId, string $fgWarehouse): void
    {
        Detail::where('id', $detailId)->update(['fg_warehouse_name' => $fgWarehouse]);
    }

    /**
     * Add remark to detail.
     */
    public function addRemark(int $detailId, string $remark): void
    {
        Detail::where('id', $detailId)->update(['remark' => $remark]);
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
