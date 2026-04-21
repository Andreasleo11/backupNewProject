<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\SupplierEvaluation\Services;

use App\Models\PurchasingDetailEvaluationSupplier;
use App\Models\PurchasingVendorAccuracyGood;
use App\Models\PurchasingVendorClaim;
use App\Models\PurchasingVendorClaimResponse;
use App\Models\PurchasingVendorListCertificate;
use App\Models\PurchasingVendorOntimeDelivery;
use App\Models\PurchasingVendorUrgentRequest;
use Carbon\Carbon;

final class SupplierScoringService
{
    private const MONTH_MAPPING = [
        'January' => '01', 'February' => '02', 'March' => '03',
        'April' => '04', 'May' => '05', 'June' => '06',
        'July' => '07', 'August' => '08', 'September' => '09',
        'October' => '10', 'November' => '11', 'December' => '12',
    ];

    public function calculateAllCriteria(
        int $headerId,
        string $supplierName,
        string $supplierCode,
        Carbon $startDate,
        Carbon $endDate,
        array $validMonths = []
    ): void {
        // Ambil hanya detail yang ada PO-nya
        $details = PurchasingDetailEvaluationSupplier::where('header_id', $headerId)
            ->get()
            ->filter(fn ($d) => in_array(
                $d->year . '-' . self::MONTH_MAPPING[$d->month],
                $validMonths
            ));

        $this->calculateKualitasBarang($headerId, $supplierName, $startDate, $endDate, $details);
        $this->calculateCustomerStopline($headerId, $supplierName, $startDate, $endDate, $details);
        $this->calculateKuantitas($headerId, $supplierName, $startDate, $endDate, $details);
        $this->calculateWaktuPengiriman($headerId, $supplierName, $startDate, $endDate, $details);
        $this->calculatePermintaanMendadak($headerId, $supplierName, $startDate, $endDate, $details);
        $this->calculateResponKlaim($headerId, $supplierName, $startDate, $endDate, $details, $validMonths);
        $this->calculateSertifikasi($headerId, $supplierCode, $details);

        $this->updateHeaderGradeAndStatus($headerId, $validMonths);
    }

    private function calculateKualitasBarang(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate,
        $details
    ): void {
        $claims = PurchasingVendorClaim::where('vendor_name', $supplierName)
            ->whereBetween('claim_start_date', [$startDate, $endDate])
            ->get();

        if ($claims->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['kualitas_barang' => 20]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];
            $monthlyClaims = $claims->filter(
                fn ($c) => Carbon::parse($c->claim_start_date)->format('m') == $monthNumber
                    && Carbon::parse($c->claim_start_date)->format('Y') == $detail->year
            );

            $detail->kualitas_barang = $monthlyClaims->isEmpty()
                ? 20
                : max(20 - ($monthlyClaims->count() * 5), 0);

            $detail->save();
        }
    }

    private function calculateCustomerStopline(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate,
        $details
    ): void {
        $claims = PurchasingVendorClaim::where('vendor_name', $supplierName)
            ->whereBetween('claim_start_date', [$startDate, $endDate])
            ->get();

        if ($claims->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['customer_stopline' => 10]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];
            $monthlyClaims = $claims->filter(
                fn ($c) => Carbon::parse($c->claim_start_date)->format('m') === $monthNumber
                    && Carbon::parse($c->claim_start_date)->format('Y') == $detail->year
            );

            if ($monthlyClaims->isEmpty()) {
                $detail->customer_stopline = 10;
            } else {
                $yesCount = $monthlyClaims->filter(fn ($c) => $c->customer_stopline === 'Yes')->count();
                $detail->customer_stopline = max(10 - ($yesCount * 5), 0);
            }

            $detail->save();
        }
    }

    private function calculateKuantitas(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate,
        $details
    ): void {
        $accuracyGoods = PurchasingVendorAccuracyGood::where('vendor_name', $supplierName)
            ->whereBetween('incoming_date', [$startDate, $endDate])
            ->get();

        if ($accuracyGoods->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['ketepatan_kuantitas_barang' => 20]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];
            $monthly = $accuracyGoods->filter(
                fn ($g) => Carbon::parse($g->incoming_date)->format('m') === $monthNumber
                    && Carbon::parse($g->incoming_date)->format('Y') == $detail->year
            );

            $detail->ketepatan_kuantitas_barang = $monthly->isEmpty()
                ? 20
                : max(20 - ($monthly->count() * 5), 0);

            $detail->save();
        }
    }

    private function calculateWaktuPengiriman(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate,
        $details
    ): void {
        $ontimeDeliveries = PurchasingVendorOntimeDelivery::where('vendor_name', $supplierName)
            ->whereBetween('actual_date', [$startDate, $endDate])
            ->get();

        if ($ontimeDeliveries->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['ketepatan_waktu_pengiriman' => 20]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];
            $monthly = $ontimeDeliveries->filter(
                fn ($d) => Carbon::parse($d->actual_date)->format('m') === $monthNumber
                    && Carbon::parse($d->actual_date)->format('Y') == $detail->year
            );

            $detail->ketepatan_waktu_pengiriman = $monthly->isEmpty()
                ? 20
                : max(20 - ($monthly->count() * 5), 0);

            $detail->save();
        }
    }

    private function calculatePermintaanMendadak(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate,
        $details
    ): void {
        $urgentRequests = PurchasingVendorUrgentRequest::where('vendor_name', $supplierName)
            ->whereBetween('request_date', [$startDate, $endDate])
            ->get();

        if ($urgentRequests->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['kerjasama_permintaan_mendadak' => 10]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];
            $monthly = $urgentRequests->filter(
                fn ($r) => Carbon::parse($r->request_date)->format('m') === $monthNumber
                    && Carbon::parse($r->request_date)->format('Y') == $detail->year
            );

            if ($monthly->isEmpty()) {
                $detail->kerjasama_permintaan_mendadak = 10;
            } else {
                $totalPoint = 0;
                $count = $monthly->count();

                foreach ($monthly as $request) {
                    $requestDate = Carbon::parse($request->request_date);
                    $incomingDate = Carbon::parse($request->incoming_date);

                    if ($requestDate->eq($incomingDate)) {
                        $totalPoint += $request->special_price === 'No' ? 10 : 5;
                    }
                }

                $detail->kerjasama_permintaan_mendadak = ceil($totalPoint / $count);
            }

            $detail->save();
        }
    }

    private function calculateResponKlaim(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate,
        $details,
        array $validMonths
    ): void {
        // Default 10 hanya untuk detail yang ada PO
        foreach ($details as $detail) {
            $detail->update(['respon_klaim' => 10]);
        }

        $claimResponses = PurchasingVendorClaimResponse::where('vendor_name', $supplierName)
            ->whereBetween('cpar_sent_date', [$startDate, $endDate])
            ->get();

        if ($claimResponses->isEmpty()) {
            return;
        }

        $monthlyClaimResponses = $claimResponses->groupBy(
            fn ($item) => Carbon::parse($item->cpar_sent_date)->format('Y-m')
        );

        foreach ($monthlyClaimResponses as $yearMonth => $responses) {
            // Skip bulan yang tidak ada PO
            if (! in_array($yearMonth, $validMonths)) {
                continue;
            }

            $totalPoint = 0;
            $count = $responses->count();

            foreach ($responses as $response) {
                $point = 0;
                if ($response->close_status === 'Yes') {
                    $days = Carbon::parse($response->cpar_response_date)
                        ->diffInDays(Carbon::parse($response->cpar_sent_date));

                    if ($days >= 1 && $days <= 3) {
                        $point = 10;
                    } elseif ($days >= 4 && $days <= 5) {
                        $point = 5;
                    }
                }
                $totalPoint += $point;
            }

            $finalScore = $count > 0 ? ceil($totalPoint / $count) : 0;
            $monthName = Carbon::parse($yearMonth . '-01')->format('F');
            $year = Carbon::parse($yearMonth . '-01')->format('Y');

            PurchasingDetailEvaluationSupplier::where('header_id', $headerId)
                ->where('month', $monthName)
                ->where('year', $year)
                ->update(['respon_klaim' => $finalScore]);
        }
    }

    private function calculateSertifikasi(int $headerId, string $supplierCode, $details): void
    {
        $certificates = PurchasingVendorListCertificate::where('vendor_code', $supplierCode)->first();
        $sertifikasiScore = 0;

        if ($certificates) {
            if (! is_null($certificates->iatf_16949_doc) && trim($certificates->iatf_16949_doc) !== '') {
                $sertifikasiScore = 10;
            } elseif (
                (! is_null($certificates->iso_9001_doc) && trim($certificates->iso_9001_doc) !== '') ||
                (! is_null($certificates->iso_14001_doc) && trim($certificates->iso_14001_doc) !== '')
            ) {
                $sertifikasiScore = 5;
            }
        }

        // Hanya update bulan yang ada PO
        foreach ($details as $detail) {
            $detail->update(['sertifikasi' => $sertifikasiScore]);
        }
    }

    private function updateHeaderGradeAndStatus(int $headerId, array $validMonths): void
    {
        // Hitung average hanya dari bulan yang ada PO
        $details = PurchasingDetailEvaluationSupplier::where('header_id', $headerId)
            ->get()
            ->filter(fn ($d) => in_array(
                $d->year . '-' . self::MONTH_MAPPING[$d->month],
                $validMonths
            ));

        $count = $details->count();

        if ($count === 0) {
            return;
        }

        $totalSum = $details->sum(
            fn ($d) => ($d->kualitas_barang ?? 0) +
            ($d->ketepatan_kuantitas_barang ?? 0) +
            ($d->ketepatan_waktu_pengiriman ?? 0) +
            ($d->kerjasama_permintaan_mendadak ?? 0) +
            ($d->respon_klaim ?? 0) +
            ($d->sertifikasi ?? 0) +
            ($d->customer_stopline ?? 0)
        );

        $averageScore = $totalSum / $count;

        \App\Models\PurchasingHeaderEvaluationSupplier::where('id', $headerId)->update([
            'grade' => $this->determineGrade($averageScore),
            'status' => $this->determineStatus($averageScore),
        ]);
    }

    public function determineGrade(float $averageScore): string
    {
        return match (true) {
            $averageScore >= 81 => 'A',
            $averageScore >= 61 => 'B',
            default => 'C',
        };
    }

    public function determineStatus(float $averageScore): string
    {
        return match (true) {
            $averageScore >= 81 => 'Diteruskan',
            $averageScore >= 61 => 'Dipertahankan dan dilakukan Audit Supplier setelah 1-3 bulan dari Evaluasi Supplier tahunan',
            default => 'Dilakukan Monitoring performa selama 3 bulan dan dilakukan Audit Supplier di bulan berikutnya. Gradenya harus naik, bila gradenya tidak naik, akan dipertimbangkan untuk pemutusan kerjasama.',
        };
    }
}
