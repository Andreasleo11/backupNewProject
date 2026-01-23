<?php

declare(strict_types=1);

namespace App\Domain\Overtime\Services;

use App\Models\DetailFormOvertime;
use App\Models\HeaderFormOvertime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class OvertimeJPayrollService
{
    private const JPAYROLL_URL = 'http://192.168.6.75/JPayroll/thirdparty/ext/API_Store_Overtime.php';

    private const AUTHORIZATION_HEADER = 'Basic QVBJPUV4VCtEQCFqMDpEQCFqMEBKcDR5cjAxMQ==';

    /**
     * Push single overtime detail to J-Payroll system.
     */
    public function pushSingleDetail(DetailFormOvertime $detail, string $action): array
    {
        // Validate header not already pushed
        if ($detail->header->is_push == 1) {
            return [
                'success' => false,
                'message' => 'Header sudah dipush',
                'code' => 400,
            ];
        }

        // Handle rejection
        if ($action === 'reject') {
            return $this->rejectDetail($detail);
        }

        // Handle approval - validate action
        if ($action !== 'approve') {
            return [
                'success' => false,
                'message' => 'Aksi tidak valid',
                'code' => 400,
            ];
        }

        return $this->approveAndPushDetail($detail);
    }

    /**
     * Push all pending overtime details for a header to J-Payroll.
     */
    public function pushAllDetails(int $headerId): array
    {
        $header = HeaderFormOvertime::with('details.employee')->find($headerId);

        if (! $header) {
            return [
                'success' => false,
                'message' => 'Header tidak ditemukan',
                'code' => 404,
            ];
        }

        if ($header->is_push == 1) {
            return [
                'success' => false,
                'message' => 'Header sudah dipush sebelumnya',
                'code' => 400,
            ];
        }

        $successCount = 0;
        $failed = [];

        foreach ($header->details as $detail) {
            // Skip rejected or already processed
            if ($detail->status === 'Rejected') {
                continue;
            }

            if ($detail->status === 'Approved' && $detail->is_processed == 1) {
                continue;
            }

            $result = $this->pushDetailToJPayroll($detail, $header);

            if ($result['success']) {
                $successCount++;
            } else {
                $failed[] = [
                    'detail_id' => $detail->id,
                    'NIK' => $detail->NIK,
                    'reason' => $result['reason'],
                ];
            }
        }

        // Update header push status
        $this->checkAndUpdateHeaderPushStatus($headerId);

        return [
            'success' => true,
            'message' => 'Proses push selesai',
            'total_success' => $successCount,
            'total_failed' => count($failed),
            'failed_details' => $failed,
        ];
    }

    /**
     * Check if all details are processed and update header status.
     */
    public function checkAndUpdateHeaderPushStatus(int $headerId): bool
    {
        $header = HeaderFormOvertime::with('details')->find($headerId);

        if (! $header) {
            return false;
        }

        // Check if any detail has pending status (null)
        $hasPending = $header->details->contains(function ($detail) {
            return is_null($detail->status);
        });

        if (! $hasPending) {
            $header->is_push = 1;
            $header->save();
        }

        return ! $hasPending;
    }

    /**
     * Reject a detail without pushing to J-Payroll.
     */
    private function rejectDetail(DetailFormOvertime $detail): array
    {
        $detail->status = 'Rejected';
        $detail->save();

        $this->checkAndUpdateHeaderPushStatus($detail->header_id);

        return [
            'success' => true,
            'message' => 'Data berhasil direject',
            'code' => 200,
        ];
    }

    /**
     * Approve detail and push to J-Payroll.
     */
    private function approveAndPushDetail(DetailFormOvertime $detail): array
    {
        $payload = $this->buildJPayrollPayload($detail);

        try {
            $response = Http::withHeaders([
                'Authorization' => self::AUTHORIZATION_HEADER,
                'Content-Type' => 'application/json',
            ])->post(self::JPAYROLL_URL, $payload);

            $responseData = [
                'NIK' => $detail->NIK,
                'status' => $response->status(),
                'body' => $response->body(),
            ];

            $responseJson = json_decode($response->body(), true);

            if (
                $response->successful() &&
                isset($responseJson['status']) &&
                $responseJson['status'] == '200'
            ) {
                $detail->is_processed = 1;
                $detail->status = 'Approved';
                $detail->save();

                $this->checkAndUpdateHeaderPushStatus($detail->header_id);

                Log::info("✅ Success push for detail ID: {$detail->id}", $responseData);

                return [
                    'success' => true,
                    'message' => 'Data berhasil dipush & diapprove',
                    'response' => $responseData,
                    'code' => 200,
                ];
            } else {
                Log::warning(
                    "⚠️ Push rejected for detail ID: {$detail->id} - JPayroll response not success",
                    $responseData
                );

                return [
                    'success' => false,
                    'message' => 'Push ditolak oleh JPayroll: Data Karyawan sudah ada - Error Note: ' .
                        ($responseJson['msg'] ?? 'Unknown error'),
                    'response' => $responseData,
                    'code' => 400,
                ];
            }
        } catch (\Exception $e) {
            Log::error("❌ Exception push for detail ID: {$detail->id}", [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi exception saat push',
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Push detail to J-Payroll (used in batch processing).
     */
    private function pushDetailToJPayroll(DetailFormOvertime $detail, HeaderFormOvertime $header): array
    {
        $payload = $this->buildJPayrollPayload($detail);

        try {
            $response = Http::withHeaders([
                'Authorization' => self::AUTHORIZATION_HEADER,
                'Content-Type' => 'application/json',
            ])->post(self::JPAYROLL_URL, $payload);

            $responseJson = $response->json();
            $responseData = [
                'NIK' => $detail->NIK,
                'status' => $response->status(),
                'body' => $response->body(),
            ];

            if (
                $response->successful() &&
                isset($responseJson['status']) &&
                $responseJson['status'] == '200'
            ) {
                $detail->is_processed = 1;
                $detail->status = 'Approved';
                $detail->save();

                Log::info("✅ Success push for detail ID: {$detail->id}", $responseData);

                return ['success' => true];
            } else {
                $msg = $responseJson['msg'] ?? 'Unknown error';

                $detail->status = 'Rejected';
                $detail->reason = "Reject JPAYROLL karena {$msg}";
                $detail->save();

                Log::warning(
                    "⚠️ Push rejected & status updated for detail ID: {$detail->id}",
                    $responseData
                );

                return [
                    'success' => false,
                    'reason' => 'Rejected by JPayroll',
                ];
            }
        } catch (\Exception $e) {
            Log::error("❌ Exception push for detail ID: {$detail->id}", [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'reason' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build J-Payroll API payload from overtime detail.
     */
    private function buildJPayrollPayload(DetailFormOvertime $detail): array
    {
        $employee = $detail->employee;
        $header = $detail->header;

        return [
            'OTType' => '1',
            'OTDate' => Carbon::parse($detail->overtime_date)->format('d/m/Y'),
            'JobDesc' => Str::limit($detail->job_desc, 250),
            'Department' => $employee->organization_structure ?? 0,
            'StartDate' => Carbon::parse($detail->start_date)->format('d/m/Y'),
            'StartTime' => Carbon::parse($detail->start_time)->format('H:i'),
            'EndDate' => Carbon::parse($detail->end_date)->format('d/m/Y'),
            'EndTime' => Carbon::parse($detail->end_time)->format('H:i'),
            'BreakTime' => $detail->break,
            'Remark' => Str::limit(
                "({$detail->NIK}) Reference from LINE {$detail->id} ID {$header->id}",
                250
            ),
            'Choice' => '1',
            'CompanyArea' => '10000',
            'EmpList' => [
                'NIK1' => $detail->NIK,
            ],
        ];
    }
}
