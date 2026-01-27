<?php

use App\Domain\Overtime\Services\OvertimeJPayrollService;
use App\Models\DetailFormOvertime;
use App\Models\HeaderFormOvertime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new OvertimeJPayrollService;
});

describe('OvertimeJPayrollService', function () {
    it('rejects detail without pushing to JPayroll', function () {
        $header = HeaderFormOvertime::factory()->create(['is_push' => 0]);
        $detail = DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
            'status' => null,
        ]);

        $result = $this->service->pushSingleDetail($detail, 'reject');

        expect($result['success'])->toBeTrue();
        expect($result['message'])->toBe('Data berhasil direject');

        $detail->refresh();
        expect($detail->status)->toBe('Rejected');
    });

    it('returns error when header already pushed', function () {
        $header = HeaderFormOvertime::factory()->create(['is_push' => 1]);
        $detail = DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
        ]);

        $result = $this->service->pushSingleDetail($detail, 'approve');

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toBe('Header sudah dipush');
        expect($result['code'])->toBe(400);
    });

    it('returns error for invalid action', function () {
        $header = HeaderFormOvertime::factory()->create(['is_push' => 0]);
        $detail = DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
        ]);

        $result = $this->service->pushSingleDetail($detail, 'invalid_action');

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toBe('Aksi tidak valid');
    });

    it('pushes detail to JPayroll on approve', function () {
        Http::fake([
            '*' => Http::response(['status' => '200', 'msg' => 'Success'], 200),
        ]);

        $header = HeaderFormOvertime::factory()->create(['is_push' => 0]);
        $employee = \App\Models\Employee::factory()->create([
            'NIK' => 'TEST001',
        ]);

        $detail = DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
            'NIK' => 'TEST001',
            'status' => null,
            'is_processed' => 0,
            'start_date' => '2026-01-15',
            'start_time' => '18:00:00',
            'end_date' => '2026-01-15',
            'end_time' => '22:00:00',
            'break' => 0,
        ]);

        $result = $this->service->pushSingleDetail($detail, 'approve');

        expect($result['success'])->toBeTrue();
        expect($result['message'])->toBe('Data berhasil dipush & diapprove');

        $detail->refresh();
        expect($detail->status)->toBe('Approved');
        expect($detail->is_processed)->toBe(1);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'JPayroll');
        });
    });

    it('handles JPayroll rejection response', function () {
        Http::fake([
            '*' => Http::response(['status' => '400', 'msg' => 'Data already exists'], 400),
        ]);

        $header = HeaderFormOvertime::factory()->create(['is_push' => 0]);
        $detail = DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
            'NIK' => 'TEST001',
            'status' => null,
        ]);

        $result = $this->service->pushSingleDetail($detail, 'approve');

        expect($result['success'])->toBeFalse();
        expect($result['message'])->toContain('Push ditolak oleh JPayroll');
    });

    it('checks and updates header push status', function () {
        $header = HeaderFormOvertime::factory()->create(['is_push' => 0]);

        DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
            'status' => 'Approved',
        ]);

        DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
            'status' => 'Rejected',
        ]);

        $result = $this->service->checkAndUpdateHeaderPushStatus($header->id);

        expect($result)->toBeTrue();

        $header->refresh();
        expect($header->is_push)->toBe(1);
    });

    it('does not update header when details pending', function () {
        $header = HeaderFormOvertime::factory()->create(['is_push' => 0]);

        DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
            'status' => 'Approved',
        ]);

        DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
            'status' => null, // Still pending
        ]);

        $result = $this->service->checkAndUpdateHeaderPushStatus($header->id);

        expect($result)->toBeFalse();

        $header->refresh();
        expect($header->is_push)->toBe(0);
    });

    it('pushes all details in batch', function () {
        Http::fake([
            '*' => Http::response(['status' => '200', 'msg' => 'Success'], 200),
        ]);

        $header = HeaderFormOvertime::factory()->create(['is_push' => 0]);

        $detail1 = DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
            'NIK' => 'TEST001',
            'status' => null,
        ]);

        $detail2 = DetailFormOvertime::factory()->create([
            'header_id' => $header->id,
            'NIK' => 'TEST002',
            'status' => null,
        ]);

        $result = $this->service->pushAllDetails($header->id);

        expect($result['success'])->toBeTrue();
        expect($result['total_success'])->toBe(2);
        expect($result['total_failed'])->toBe(0);
    });
});
