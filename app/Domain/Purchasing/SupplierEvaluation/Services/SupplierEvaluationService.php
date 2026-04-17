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

        $supplierParts = array_map('trim', explode(' - ', $data['supplier']));
        if (count($supplierParts) !== 2) {
            return ['success' => false, 'message' => 'Invalid supplier format. Expected: "Name - Code"'];
        }

        [$supplierName, $supplierCode] = $supplierParts;

        $startMonthNum = $monthMapping[$data['start_month']] ?? null;
        $endMonthNum = $monthMapping[$data['end_month']] ?? null;

        if ($startMonthNum === null || $endMonthNum === null) {
            return ['success' => false, 'message' => 'Invalid month name'];
        }

        $startDate = Carbon::create($data['start_year'], $startMonthNum, 1)->startOfMonth();
        $endDate = Carbon::create($data['end_year'], $endMonthNum, 1)->endOfMonth();

        if ($startDate->greaterThan($endDate)) {
            return ['success' => false, 'message' => 'Start date cannot be later than end date'];
        }

        $supplier = PurchasingListPo::where('supplier_code', $supplierCode)->first();

        if (! $supplier) {
            return [
                'success' => false,
                'message' => 'Supplier not found',
            ];
        }

        if (strtolower(trim($supplier->supplier_name)) !== strtolower(trim($supplierName))) {
            return ['success' => false, 'message' => 'Supplier name does not match the provided code'];
        }

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

        // Ambil bulan yang benar-benar ada PO-nya
        $validMonths = PurchasingListPo::where('supplier_name', $supplierName)
            ->whereBetween('posting_date', [$startDate, $endDate])
            ->get()
            ->map(fn($p) => Carbon::parse($p->posting_date)->format('Y-m'))
            ->unique()
            ->values()
            ->toArray();

        $this->createMonthlyDetails($header->id, $startDate, $endDate, $validMonths);

        $this->scoringService->calculateAllCriteria(
            $header->id,
            $supplierName,
            $supplier->supplier_code,
            $startDate,
            $endDate,
            $validMonths
        );

        return [
            'success' => true,
            'message' => 'Supplier evaluation created successfully',
            'header' => $header,
        ];
    }

    private function createMonthlyDetails(
        int $headerId,
        Carbon $startDate,
        Carbon $endDate,
        array $validMonths
    ): void {
        $current = $startDate->copy()->startOfMonth();

        while ($current->lte($endDate)) {
            $monthKey = $current->format('Y-m');
            $hasPo    = in_array($monthKey, $validMonths);

            PurchasingDetailEvaluationSupplier::create([
                'header_id'                     => $headerId,
                'month'                         => $current->format('F'),
                'year'                          => $current->format('Y'),
                // Kalau tidak ada PO — semua null, tidak di-scoring
                'kualitas_barang'               => $hasPo ? null : null,
                'ketepatan_kuantitas_barang'    => $hasPo ? null : null,
                'ketepatan_waktu_pengiriman'    => $hasPo ? null : null,
                'kerjasama_permintaan_mendadak' => $hasPo ? null : null,
                'respon_klaim'                  => $hasPo ? null : null,
                'sertifikasi'                   => $hasPo ? null : null,
                'customer_stopline'             => $hasPo ? null : null,
                'has_po'                        => $hasPo, // flag biar gampang filter
            ]);

            $current->addMonth();
        }
    }

    private function generateDocNum(): string
    {
        return 'DOC-' . now()->format('YmdHis');
    }

    public function getSupplierData(): array
    {
        $supplierData = [];

        $masters = PurchasingListPo::select('supplier_code', 'supplier_name', 'posting_date')->get();
        $grouped = $masters->groupBy(['supplier_code', 'supplier_name']);

        foreach ($grouped as $supplier_code => $byName) {
            foreach ($byName as $supplier_name => $records) {
                $years = $records->map(fn($r) => Carbon::parse($r->posting_date)->format('Y'))
                    ->unique()->values()->toArray();

                $key = ($supplier_name ?: 'Unknown') . ' - ' . $supplier_code;
                $supplierData[$key] = $years;
            }
        }

        ksort($supplierData, SORT_STRING | SORT_FLAG_CASE);

        return $supplierData;
    }
}