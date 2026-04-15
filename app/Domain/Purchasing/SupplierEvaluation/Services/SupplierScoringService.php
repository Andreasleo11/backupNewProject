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
        'January' => '01',
        'February' => '02',
        'March' => '03',
        'April' => '04',
        'May' => '05',
        'June' => '06',
        'July' => '07',
        'August' => '08',
        'September' => '09',
        'October' => '10',
        'November' => '11',
        'December' => '12',
    ];

    /**
     * Calculate all criteria scores for an evaluation.
     */
    public function calculateAllCriteria(
        int $headerId,
        string $supplierName,
        string $supplierCode,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $this->calculateKualitasBarang($headerId, $supplierName, $startDate, $endDate);
        $this->calculateCustomerStopline($headerId, $supplierName, $startDate, $endDate);
        $this->calculateKuantitas($headerId, $supplierName, $startDate, $endDate);
        $this->calculateWaktuPengiriman($headerId, $supplierName, $startDate, $endDate);
        $this->calculatePermintaanMendadak($headerId, $supplierName, $startDate, $endDate);
        $this->calculateResponKlaim($headerId, $supplierName, $startDate, $endDate);
        $this->calculateSertifikasi($headerId, $supplierCode);

        $this->updateHeaderGradeAndStatus($headerId);
    }

    /**
     * Calculate quality score (Kriteria 1: Kualitas Barang).
     */
    private function calculateKualitasBarang(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $claims = PurchasingVendorClaim::where('vendor_name', $supplierName)
            // Vendor not found, set kualitas_barang to 20 for all details
            ->whereBetween('claim_start_date', [$startDate, $endDate])
            ->get();

        $details = PurchasingDetailEvaluationSupplier::where('header_id', $headerId)->get();

        if ($claims->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['kualitas_barang' => 20]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];
            $monthlyClaims = $claims->filter(fn ($claim) => Carbon::parse($claim->claim_start_date)->format('m') == $monthNumber);

            if ($monthlyClaims->isEmpty()) {
                $detail->kualitas_barang = 20;
            } else {
                $point = 20;

                foreach ($monthlyClaims as $claim) {
                    $point -= 5;
                }

                $detail->kualitas_barang = max($point, 0);
            }

            $detail->save();
        }
    }

    /**
     * Calculate customer stopline score (Kriteria 7).
     */
    private function calculateCustomerStopline(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $claims = PurchasingVendorClaim::where('vendor_name', $supplierName)
            ->whereBetween('claim_start_date', [$startDate, $endDate])
            ->get();

        $details = PurchasingDetailEvaluationSupplier::where('header_id', $headerId)->get();

        if ($claims->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['customer_stopline' => 10]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];

            $monthlyClaims = $claims->filter(
                fn ($claim) => Carbon::parse($claim->claim_start_date)->format('m') === $monthNumber
            );

            if ($monthlyClaims->isEmpty()) {
                $detail->customer_stopline = 10;
            } else {
                $yesCount = 0;

                foreach ($monthlyClaims as $claim) {
                    if ($claim->customer_stopline === 'Yes') {
                        $yesCount++;
                    }
                    // null, '', 'No' → ignored (no penalty)
                }

                $score = 10 - ($yesCount * 5);
                $detail->customer_stopline = max($score, 0);
            }

            $detail->save();
        }
    }

    /**
     * Calculate quantity accuracy score (Kriteria 2).
     */
    private function calculateKuantitas(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $accuracyGoods = PurchasingVendorAccuracyGood::where('vendor_name', $supplierName)
            ->whereBetween('incoming_date', [$startDate, $endDate])
            ->get();

        $details = PurchasingDetailEvaluationSupplier::where('header_id', $headerId)->get();

        if ($accuracyGoods->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['ketepatan_kuantitas_barang' => 20]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];

            $monthlyAccuracyGoods = $accuracyGoods->filter(
                fn ($good) => Carbon::parse($good->incoming_date)->format('m') === $monthNumber
            );

            if ($monthlyAccuracyGoods->isEmpty()) {
                $detail->ketepatan_kuantitas_barang = 20;
            } else {
                $deduction = $monthlyAccuracyGoods->count() * 5;
                $detail->ketepatan_kuantitas_barang = max(20 - $deduction, 0);
            }

            $detail->save();
        }
    }

    /**
     * Calculate on-time delivery score (Kriteria 3).
     */
    private function calculateWaktuPengiriman(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $ontimeDeliveries = PurchasingVendorOntimeDelivery::where('vendor_name', $supplierName)
            ->whereBetween('actual_date', [$startDate, $endDate])
            ->get();

        $details = PurchasingDetailEvaluationSupplier::where('header_id', $headerId)->get();

        if ($ontimeDeliveries->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['ketepatan_waktu_pengiriman' => 20]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];

            $monthlyDeliveries = $ontimeDeliveries->filter(
                fn ($delivery) => Carbon::parse($delivery->actual_date)->format('m') === $monthNumber
            );

            if ($monthlyDeliveries->isEmpty()) {
                $detail->ketepatan_waktu_pengiriman = 20;
            } else {
                $deduction = $monthlyDeliveries->count() * 5;
                $detail->ketepatan_waktu_pengiriman = max(20 - $deduction, 0);
            }

            $detail->save();
        }
    }

    /**
     * Calculate urgent request cooperation score (Kriteria 4).
     */
    private function calculatePermintaanMendadak(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $urgentRequests = PurchasingVendorUrgentRequest::where('vendor_name', $supplierName)
            ->whereBetween('request_date', [$startDate, $endDate])
            ->get();

        $details = PurchasingDetailEvaluationSupplier::where('header_id', $headerId)->get();

        if ($urgentRequests->isEmpty()) {
            foreach ($details as $detail) {
                $detail->update(['kerjasama_permintaan_mendadak' => 10]);
            }

            return;
        }

        foreach ($details as $detail) {
            $monthNumber = self::MONTH_MAPPING[$detail->month];

            $monthlyRequests = $urgentRequests->filter(
                fn ($request) => Carbon::parse($request->request_date)->format('m') === $monthNumber
            );

            if ($monthlyRequests->isEmpty()) {
                $detail->kerjasama_permintaan_mendadak = 10;
            } else {
                $totalPoint = 0;
                $count = $monthlyRequests->count();

                foreach ($monthlyRequests as $request) {
                    $requestDate = Carbon::parse($request->request_date);
                    $incomingDate = Carbon::parse($request->incoming_date);

                    if ($requestDate->eq($incomingDate)) {
                        $totalPoint += $request->special_price === 'No' ? 10 : 5;
                    }
                    // else → +0 (already default)
                }

                $detail->kerjasama_permintaan_mendadak = ceil($totalPoint / $count);
            }

            $detail->save();
        }
    }

    /**
     * Calculate claim response score (Kriteria 5).
     */
    private function calculateResponKlaim(
        int $headerId,
        string $supplierName,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $claimResponses = PurchasingVendorClaimResponse::where('vendor_name', $supplierName)
            ->whereBetween('cpar_sent_date', [$startDate, $endDate])
            ->get();

        // Default: set 10 for ALL months
        PurchasingDetailEvaluationSupplier::where('header_id', $headerId)
            ->update(['respon_klaim' => 10]);

        if ($claimResponses->isEmpty()) {
            return;
        }

        // Group by sent year-month
        $monthlyClaimResponses = $claimResponses->groupBy(
            fn ($item) => Carbon::parse($item->cpar_sent_date)->format('Y-m')
        );

        foreach ($monthlyClaimResponses as $yearMonth => $responses) {
            $totalPoint = 0;
            $count = $responses->count();

            foreach ($responses as $response) {
                $point = 0;

                if ($response->close_status === 'Yes') {
                    $sentDate = Carbon::parse($response->cpar_sent_date);
                    $responseDate = Carbon::parse($response->cpar_response_date);
                    $daysDifference = $responseDate->diffInDays($sentDate);

                    if ($daysDifference >= 1 && $daysDifference <= 3) {
                        $point = 10;
                    } elseif ($daysDifference >= 4 && $daysDifference <= 5) {
                        $point = 5;
                    }
                    // >5 days or 0 days → remains 0
                }

                $totalPoint += $point;
            }

            $finalScore = $count > 0 ? ceil($totalPoint / $count) : 0;

            // Update only the month that matches this Y-m
            $monthName = Carbon::parse($yearMonth)->format('F'); // e.g. "January"

            PurchasingDetailEvaluationSupplier::where('header_id', $headerId)
                ->where('month', $monthName)
                ->update(['respon_klaim' => $finalScore]);
        }
    }

    /**
     * Calculate certification score (Kriteria 6).
     */
    private function calculateSertifikasi(int $headerId, string $supplierCode): void
    {
        $certificates = PurchasingVendorListCertificate::where('vendor_code', $supplierCode)->first();

        if (! $certificates) {
            PurchasingDetailEvaluationSupplier::where('header_id', $headerId)
                ->update(['sertifikasi' => 0]);

            return;
        }

        $sertifikasiScore = 0; // default: no valid certification documents

        if (
            ! is_null($certificates->iatf_16949_doc) &&
            trim($certificates->iatf_16949_doc) !== ''
        ) {
            $sertifikasiScore = 10;
        } elseif (
            (
                ! is_null($certificates->iso_9001_doc) &&
                trim($certificates->iso_9001_doc) !== ''
            ) ||
            (
                ! is_null($certificates->iso_14001_doc) &&
                trim($certificates->iso_14001_doc) !== ''
            )
        ) {
            $sertifikasiScore = 5;
        }

        PurchasingDetailEvaluationSupplier::where('header_id', $headerId)
            ->update(['sertifikasi' => $sertifikasiScore]);
    }

    /**
     * Update header grade and status based on average scores.
     */
    private function updateHeaderGradeAndStatus(int $headerId): void
    {
        $details = PurchasingDetailEvaluationSupplier::where('header_id', $headerId)->get();

        $totalSum = $details->sum(
            fn ($detail) => $detail->kualitas_barang +
                $detail->ketepatan_kuantitas_barang +
                $detail->ketepatan_waktu_pengiriman +
                $detail->kerjasama_permintaan_mendadak +
                $detail->respon_klaim +
                $detail->sertifikasi +
                $detail->customer_stopline
        );

        $count = $details->count();
        $averageScore = $count > 0 ? $totalSum / $count : 0;

        $grade = $this->determineGrade($averageScore);
        $status = $this->determineStatus($averageScore);

        \App\Models\PurchasingHeaderEvaluationSupplier::where('id', $headerId)->update([
            'grade' => $grade,
            'status' => $status,
        ]);
    }

    /**
     * Determine grade based on average score.
     */
    public function determineGrade(float $averageScore): string
    {
        return match (true) {
            $averageScore >= 81 => 'A',
            $averageScore >= 61 => 'B',
            default => 'C',
        };
    }

    /**
     * Determine status based on average score.
     */
    public function determineStatus(float $averageScore): string
    {
        return match (true) {
            $averageScore >= 81 => 'Diteruskan',
            $averageScore >= 61 => 'Dipertahankan dan dilakukan Audit Supplier setelah 1-3 bulan dari Evaluasi Supplier tahunan',
            default => 'Dilakukan Monitoring performa selama 3 bulan dan dilakukan Audit Supplier di bulan berikutnya. Gradenya harus naik, bila gradenya tidak naik, akan dipertimbangkan untuk pemutusan kerjasama.',
        };
    }
}
