<?php

declare(strict_types=1);

namespace App\Domain\Overtime\Services;

use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Domain\Overtime\Models\OvertimeForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class OvertimeJPayrollService
{
    /**
     * Push single overtime detail to J-Payroll system (Add).
     */
    public function pushSingleDetail(OvertimeFormDetail $detail): array
    {
        if ($detail->is_processed == 1) {
            return ['success' => false, 'message' => 'Detail sudah dipush', 'code' => 400];
        }

        return $this->processRequest($detail, '1'); // Choice 1 = Add
    }

    /**
     * Remove single overtime detail from J-Payroll system (Delete).
     */
    public function removeSingleDetail(OvertimeFormDetail $detail): array
    {
        if ($detail->is_processed == 0 || !$detail->payroll_voucher_id) {
            return ['success' => false, 'message' => 'Detail belum dipush atau ID Voucher tidak ditemukan', 'code' => 400];
        }

        return $this->processRequest($detail, '3'); // Choice 3 = Delete
    }

    /**
     * Push all pending overtime details for a header to J-Payroll.
     */
    public function pushAllDetails(int $headerId): array
    {
        $header = OvertimeForm::with('details.employee')->find($headerId);

        if (!$header) {
            return ['success' => false, 'message' => 'Header tidak ditemukan', 'code' => 404];
        }

        $successCount = 0;
        $failed = [];

        foreach ($header->details as $detail) {
            // Skip rejected or already processed
            if ($detail->status === 'Rejected' || $detail->is_processed == 1) {
                continue;
            }

            $result = $this->pushSingleDetail($detail);

            if ($result['success']) {
                $successCount++;
            } else {
                $failed[] = [
                    'detail_id' => $detail->id,
                    'NIK' => $detail->NIK,
                    'reason' => $result['message'],
                ];
            }
        }

        $this->checkAndUpdateHeaderPushStatus($headerId);

        return [
            'success' => true,
            'message' => 'Proses batch selesai',
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
        $header = OvertimeForm::with('details')->find($headerId);

        if (!$header) return false;

        $hasUnprocessed = $header->details->contains(function ($detail) {
            return $detail->is_processed == 0 && $detail->status !== 'Rejected';
        });

        if (!$hasUnprocessed) {
            $header->is_push = 1;
            $header->save();
            return true;
        }

        return false;
    }

    /**
     * Internal request handler for JPayroll API.
     */
    private function processRequest(OvertimeFormDetail $detail, string $choice): array
    {
        $payload = $this->buildPayload($detail, $choice);
        $url = config('services.jpayroll.base_url') . 'API_Store_Overtime.php';
        $auth = config('services.jpayroll.auth');

        try {
            $response = Http::withHeaders([
                'Authorization' => $auth,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($url, $payload);

            $responseBody = $response->body();
            $responseJson = $response->json();
            
            $logData = [
                'detail_id' => $detail->id,
                'choice' => $choice,
                'status' => $response->status(),
                'response' => $responseBody
            ];

            if ($response->successful() && isset($responseJson['status']) && $responseJson['status'] == '200') {
                return $this->handleSuccess($detail, $choice, $responseJson, $logData);
            }

            return $this->handleBusinessError($detail, $choice, $responseJson, $logData);

        } catch (\Exception $e) {
            Log::error("❌ JPayroll Connection Exception [Detail: {$detail->id}]", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Koneksi ke JPayroll gagal (Timeout/Network)',
                'code' => 500
            ];
        }
    }

    private function handleSuccess(OvertimeFormDetail $detail, string $choice, array $response, array $log): array
    {
        Log::info("✅ JPayroll Success [Detail: {$detail->id}, Choice: {$choice}]", $log);

        if ($choice === '1') {
            $detail->is_processed = 1;
            $detail->status = 'Approved';
            $detail->payroll_voucher_id = $response['transactionid'];
        } else {
            $detail->is_processed = 0;
            $detail->status = null;
            $detail->payroll_voucher_id = null;
        }

        $detail->save();
        $this->checkAndUpdateHeaderPushStatus($detail->header_id);

        return ['success' => true, 'message' => 'Berhasil diproses', 'code' => 200];
    }

    private function handleBusinessError(OvertimeFormDetail $detail, string $choice, ?array $response, array $log): array
    {
        $message = $response['msg'] ?? 'Unknown Error';
        Log::warning("⚠️ JPayroll Business Error [Detail: {$detail->id}, Choice: {$choice}]", $log);

        // Only mark as Rejected if it's a "Real" business rejection (Duplicate, Invalid Emp, etc)
        // Choice 401 is persistent, so we can reject.
        if ($log['status'] === 401) {
            return ['success' => false, 'message' => 'Akses JPayroll Ditolak (401)', 'code' => 401];
        }

        // If it's a business rejection from JPayroll side (e.g. "Data already exists")
        if ($choice === '1') {
            $detail->status = 'Rejected';
            $detail->reason = "Ditolak Payroll: {$message}";
            $detail->save();
        }

        return ['success' => false, 'message' => "Payroll Error: {$message}", 'code' => 400];
    }

    private function buildPayload(OvertimeFormDetail $detail, string $choice): array
    {
        $employee = $detail->employee;
        $companyArea = config('services.jpayroll.company_area');

        $payload = [
            'OTType'      => '1', // 1=Hour
            'OTDate'      => Carbon::parse($detail->overtime_date)->format('d/m/Y'),
            'JobDesc'     => Str::limit($detail->job_desc, 250),
            'Department'  => $employee->organization_structure ?? 0,
            'StartDate'   => Carbon::parse($detail->start_date)->format('d/m/Y'),
            'StartTime'   => Carbon::parse($detail->start_time)->format('H:i'),
            'EndDate'     => Carbon::parse($detail->end_date)->format('d/m/Y'),
            'EndTime'     => Carbon::parse($detail->end_time)->format('H:i'),
            'BreakTime'   => $detail->break,
            'Remark'      => Str::limit("(#{$detail->header_id}) {$detail->remarks}", 250),
            'Choice'      => $choice,
            'CompanyArea' => $companyArea,
            'EmpList'     => ['NIK1' => $detail->NIK],
        ];

        if ($choice === '3') {
            $payload['NoVoucher'] = $detail->payroll_voucher_id;
        }

        return $payload;
    }
}
