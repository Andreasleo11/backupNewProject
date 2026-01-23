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

        $supplierName = $data['supplier'];
        $startMonthNum = $monthMapping[$data['start_month']];
        $endMonthNum = $monthMapping[$data['end_month']];

        $startDate = Carbon::create($data['start_year'], $startMonthNum, 1)->startOfMonth();
        $endDate = Carbon::create($data['end_year'], $endMonthNum, 1)->endOfMonth();

        if ($startDate->greaterThan($endDate)) {
            return [
                'success' => false,
                'message' => 'Start date cannot be later than end date',
            ];
        }

        $supplier = PurchasingListPo::where('supplier_name', $supplierName)->first();

        if (! $supplier) {
            return [
                'success' => false,
                'message' => 'Supplier not found',
            ];
        }

        // Create header
        $header = PurchasingHeaderEvaluationSupplier::create([
            'doc_num' => $this->generateDocNum(),
            'vendor_code' => $supplier->supplier_code,
            'vendor_name' => $supplierName,
            'start_month' => $data['start_month'],
            'year' => $data['start_year'],
            'end_month' => $data['end_month'],
            'year_end' => $data['end_year'],
            'grade' => null,
            'status' => null,
        ]);

        // Create monthly details
        $this->createMonthlyDetails($header->id, $supplierName, $startDate, $endDate);

        // Calculate all criteria scores
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

        $masters = PurchasingListPo::select('supplier_name', 'posting_date')
            ->distinct()
            ->get();

        foreach ($masters->groupBy('supplier_name') as $supplier_name => $records) {
            $years = [];

            foreach ($records as $record) {
                $year = Carbon::parse($record->posting_date)->format('Y');
                $years[] = $year;
            }

            $supplierData[$supplier_name] = array_values(array_unique($years));
        }

        return $supplierData;
    }
}
