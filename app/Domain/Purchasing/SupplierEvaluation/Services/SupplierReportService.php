<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\SupplierEvaluation\Services;

use App\Models\PurchasingHeaderEvaluationSupplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class SupplierReportService
{
    public function getDetailedView(int $headerId): array
    {
        $header       = PurchasingHeaderEvaluationSupplier::with(['details', 'contact'])->findOrFail($headerId);
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

        $dates = $header->details->map(fn($d) => Carbon::create($d->year, $monthMap[$d->month] ?? 1, 1));
        $start = $dates->min();
        $end   = $dates->max();

        $months = $this->generateMonthRange($start, $end);
        $result = $this->calculateMonthlyValues($header, $months, $detailsCount);

        return [
            'header' => $header,
            'result' => $result,
        ];
    }

    private function generateMonthRange(Carbon $start, Carbon $end): Collection
    {
        $months  = collect();
        $current = $start->copy();

        while ($current <= $end) {
            $months->push([
                'month' => $current->format('F'),
                'year'  => $current->year,
                'label' => $current->format('F Y'),
            ]);
            $current->addMonth();
        }

        while ($months->count() < 12) {
            $months->push([
                'month' => $current->format('F'),
                'year'  => $current->year,
                'label' => $current->format('F Y'),
            ]);
            $current->addMonth();
        }

        return $months;
    }

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

        $categorySums   = array_fill_keys($categories, 0);
        $categoryCounts = array_fill_keys($categories, 0);

        foreach ($months as $m) {
            $detail = $header->details->first(
                fn($d) => $d->month === $m['month'] && $d->year == $m['year']
            );

            // Kalau tidak ada detail atau semua nilainya null = tidak ada PO bulan ini
            $hasPo = $detail && collect($categories)->contains(fn($c) => !is_null($detail->$c));

            $row = [];
            foreach ($categories as $cat) {
                $value      = $hasPo ? ($detail->$cat ?? 0) : null;
                $row[$cat]  = $value;

                if (!is_null($value) && $value > 0) {
                    $categorySums[$cat]   += $value;
                    $categoryCounts[$cat]++;
                }
            }

            $row['has_po'] = $hasPo;
            $result[$m['label']] = $row;
        }

        // Average hanya dari bulan yang ada PO
        $validCount = collect($result)->filter(fn($r) => $r['has_po'])->count();

        $result['rata-rata'] = [];
        foreach ($categories as $cat) {
            $result['rata-rata'][$cat] = $validCount > 0
                ? ($categorySums[$cat] / $validCount)
                : 0;
        }

        return $result;
    }
}