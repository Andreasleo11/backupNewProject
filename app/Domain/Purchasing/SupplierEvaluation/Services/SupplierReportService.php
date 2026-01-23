<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\SupplierEvaluation\Services;

use App\Models\PurchasingHeaderEvaluationSupplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class SupplierReportService
{
    /**
     * Get detailed view data for evaluation.
     */
    public function getDetailedView(int $headerId): array
    {
        $header = PurchasingHeaderEvaluationSupplier::with(['details', 'contact'])->findOrFail($headerId);
        $detailsCount = $header->details->count();

        $monthMap = [
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

        $dates = $header->details->map(fn ($d) => Carbon::create($d->year, $monthMap[$d->month] ?? 1, 1));

        $start = $dates->min();
        $end = $dates->max();

        // Generate 12-month range
        $months = $this->generateMonthRange($start, $end);

        // Calculate values per month
        $result = $this->calculateMonthlyValues($header, $months, $detailsCount);

        return [
            'header' => $header,
            'result' => $result,
        ];
    }

    /**
     * Generate month range for display (minimum 12 months).
     */
    private function generateMonthRange(Carbon $start, Carbon $end): Collection
    {
        $months = collect();
        $current = $start->copy();

        while ($current <= $end) {
            $months->push([
                'month' => $current->format('F'),
                'year' => $current->year,
                'label' => $current->format('F Y'),
            ]);
            $current->addMonth();
        }

        // Fill to 12 months if needed
        while ($months->count() < 12) {
            $months->push([
                'month' => $current->format('F'),
                'year' => $current->year,
                'label' => $current->format('F Y'),
            ]);
            $current->addMonth();
        }

        return $months;
    }

    /**
     * Calculate monthly values and averages.
     */
    private function calculateMonthlyValues($header, Collection $months, int $detailsCount): array
    {
        $result = [];

        $categorySums = [
            'kualitas_barang' => 0,
            'ketepatan_kuantitas_barang' => 0,
            'ketepatan_waktu_pengiriman' => 0,
            'kerjasama_permintaan_mendadak' => 0,
            'respon_klaim' => 0,
            'sertifikasi' => 0,
            'customer_stopline' => 0,
        ];
        $categoryCounts = $categorySums;

        foreach ($months as $m) {
            $detailsForMonth = $header->details->firstWhere(function ($d) use ($m) {
                return $d->month === $m['month'] && $d->year == $m['year'];
            });

            $result[$m['label']] = [
                'kualitas_barang' => $detailsForMonth->kualitas_barang ?? 0,
                'ketepatan_kuantitas_barang' => $detailsForMonth->ketepatan_kuantitas_barang ?? 0,
                'ketepatan_waktu_pengiriman' => $detailsForMonth->ketepatan_waktu_pengiriman ?? 0,
                'kerjasama_permintaan_mendadak' => $detailsForMonth->kerjasama_permintaan_mendadak ?? 0,
                'respon_klaim' => $detailsForMonth->respon_klaim ?? 0,
                'sertifikasi' => $detailsForMonth->sertifikasi ?? 0,
                'customer_stopline' => $detailsForMonth->customer_stopline ?? 0,
            ];

            foreach ($result[$m['label']] as $category => $value) {
                if ($value > 0) {
                    $categorySums[$category] += $value;
                    $categoryCounts[$category]++;
                }
            }
        }

        // Calculate averages
        $result['rata-rata'] = [];
        foreach ($categorySums as $category => $sum) {
            $result['rata-rata'][$category] = $categoryCounts[$category] > 0 ? $sum / $detailsCount : 0;
        }

        return $result;
    }
}
