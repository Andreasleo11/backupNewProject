<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\SupplierEvaluation\Services;

use App\Models\PurchasingDetailEvaluationSupplier;
use App\Models\PurchasingHeaderEvaluationSupplier;
use App\Models\PurchasingListPo;
use Carbon\Carbon;

final class SupplierEvaluationService
{
    public function __construct(
        private readonly SupplierScoringService $scoringService
    ) {}

    /**
     * Create supplier evaluation with header and details.
     */
    public function createEvaluation(array $data): array
    {
        $monthMapping = [
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12,
        ];

        // ── Validation (you can keep Laravel validator or do manual checks)
        if (! isset($data['supplier'], $data['start_month'], $data['start_year'], $data['end_month'], $data['end_year'])) {
            return [
                'success' => false,
                'message' => 'Missing required fields',
            ];
        }

        // Parse supplier input: "Name - Code"
        $supplierParts = array_map('trim', explode(' - ', $data['supplier']));

        if (count($supplierParts) !== 2) {
            return [
                'success' => false,
                'message' => 'Invalid supplier format. Expected: "Name - Code"',
            ];
        }

        [$supplierName, $supplierCode] = $supplierParts;

        $startMonthNum = $monthMapping[$data['start_month']] ?? null;
        $endMonthNum = $monthMapping[$data['end_month']] ?? null;

        if ($startMonthNum === null || $endMonthNum === null) {
            return [
                'success' => false,
                'message' => 'Invalid month name',
            ];
        }

        $startDate = Carbon::create($data['start_year'], $startMonthNum, 1)->startOfMonth();
        $endDate = Carbon::create($data['end_year'], $endMonthNum, 1)->endOfMonth();

        if ($startDate->greaterThan($endDate)) {
            return [
                'success' => false,
                'message' => 'Start date cannot be later than end date',
            ];
        }

        // Find supplier by CODE (most reliable/unique identifier)
        $supplier = PurchasingListPo::where('supplier_code', $supplierCode)->first();

        if (! $supplier) {
            return [
                'success' => false,
                'message' => 'Supplier not found',
            ];
        }

        // Optional: safety check that name roughly matches (prevents typos/mismatches)
        if (strtolower(trim($supplier->supplier_name)) !== strtolower(trim($supplierName))) {
            return [
                'success' => false,
                'message' => 'Supplier name does not match the provided code',
            ];
        }

        // Create header
        $header = PurchasingHeaderEvaluationSupplier::create([
            'doc_num' => $this->generateDocNum(),
            'vendor_code' => $supplier->supplier_code,   // ← from DB, more trustworthy
            'vendor_name' => $supplierName,
            'start_month' => $data['start_month'],
            'year' => $data['start_year'],
            'end_month' => $data['end_month'],
            'year_end' => $data['end_year'],
            'grade' => null,
            'status' => null,
        ]);

        // Continue with monthly details + scoring (same as your Code 2)
        $this->createMonthlyDetails($header->id, $supplierName, $startDate, $endDate);

        $this->scoringService->calculateAllCriteria(
            $header->id,
            $supplierName,
            $supplier->supplier_code,
            $startDate,
            $endDate
        );

        return [
            'success' => true,
            'message' => 'Supplier evaluation created successfully',
            'header' => $header,
        ];
    }

    /**
     * Create detail records for each month in the evaluation period.
     */
    private function createMonthlyDetails(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $matchingPurchasingList = PurchasingListPo::where('supplier_name', $supplierName)
            ->whereBetween('posting_date', [$startDate, $endDate])
            ->orderBy('posting_date')
            ->get()
            ->groupBy(fn ($item) => Carbon::parse($item->posting_date)->format('Y-m'));

        foreach ($matchingPurchasingList as $monthGroup) {
            $firstRecord = $monthGroup->first();
            $postingDate = Carbon::parse($firstRecord->posting_date);

            PurchasingDetailEvaluationSupplier::create([
                'header_id' => $headerId,
                'month' => $postingDate->format('F'),
                'year' => $postingDate->format('Y'),
                'kualitas_barang' => null,
                'ketepatan_kuantitas_barang' => null,
                'ketepatan_waktu_pengiriman' => null,
                'kerjasama_permintaan_mendadak' => null,
                'respon_klaim' => null,
                'sertifikasi' => null,
                'customer_stopline' => null,
            ]);
        }
    }

    /**
     * Generate unique document number.
     */
    private function generateDocNum(): string
    {
        return 'DOC-' . now()->format('YmdHis');
    }

    /**
     * Get supplier data for index page.
     */
    public function getSupplierData(): array
    {
        $supplierData = [];

        $masters = PurchasingListPo::select(
            'supplier_code',
            'supplier_name',
            'posting_date'
        )->get();

        // Group by both supplier_code and supplier_name
        $grouped = $masters->groupBy(['supplier_code', 'supplier_name']);

        foreach ($grouped as $supplier_code => $byName) {
            foreach ($byName as $supplier_name => $records) {
                // Get unique years
                $years = $records->map(function ($record) {
                    return Carbon::parse($record->posting_date)->format('Y');
                })->unique()->values()->toArray();

                // Create the same key format as Code 1
                $namePart = $supplier_name ?: 'Unknown';
                $key = $namePart . ' - ' . $supplier_code;

                $supplierData[$key] = $years;
            }
        }

        // Sort keys case-insensitively (same as ksort with SORT_STRING | SORT_FLAG_CASE)
        ksort($supplierData, SORT_STRING | SORT_FLAG_CASE);

        return $supplierData;
    }
}
