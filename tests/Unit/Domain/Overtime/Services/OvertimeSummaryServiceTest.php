<?php

use App\Domain\Overtime\Services\OvertimeSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new OvertimeSummaryService;
});

describe('OvertimeSummaryService', function () {
    it('generates summary for date range', function () {
        // Create records directly in database
        DB::table('detail_form_overtime')->insert([
            [
                'header_id' => 1,
                'NIK' => 'EMP001',
                'name' => 'John Doe',
                'start_date' => '2026-01-15',
                'start_time' => '18:00:00',
                'end_date' => '2026-01-15',
                'end_time' => '22:00:00',
                'break' => 0,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'header_id' => 1,
                'NIK' => 'EMP001',
                'name' => 'John Doe',
                'start_date' => '2026-01-16',
                'start_time' => '18:00:00',
                'end_date' => '2026-01-16',
                'end_time' => '21:00:00',
                'break' => 0,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $summary = $this->service->generateSummary('2026-01-15', '2026-01-16');

        expect($summary)->toHaveCount(1);
        expect($summary->first()['nik'])->toBe('EMP001');
        expect($summary->first()['nama'])->toBe('John Doe');
        expect($summary->first()['total_ot'])->toBe(7.0); // 4 + 3 hours
    });

    it('handles break time correctly', function () {
        DB::table('detail_form_overtime')->insert([
            'header_id' => 1,
            'NIK' => 'EMP002',
            'name' => 'Jane Smith',
            'start_date' => '2026-01-15',
            'start_time' => '18:00:00',
            'end_date' => '2026-01-15',
            'end_time' => '22:00:00',
            'break' => 30, // 30 minutes break
            'status' => 'Approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $summary = $this->service->generateSummary('2026-01-15', '2026-01-15');

        expect($summary->first()['total_ot'])->toBe(3.5); // 4 hours - 0.5 hour break
    });

    it('handles overnight overtime', function () {
        DB::table('detail_form_overtime')->insert([
            'header_id' => 1,
            'NIK' => 'EMP003',
            'name' => 'Night Worker',
            'start_date' => '2026-01-15',
            'start_time' => '22:00:00',
            'end_date' => '2026-01-16',
            'end_time' => '02:00:00',
            'break' => 0,
            'status' => 'Approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $summary = $this->service->generateSummary('2026-01-15', '2026-01-16');

        expect($summary->first()['total_ot'])->toBe(4.0);
    });

    it('groups multiple records by same employee', function () {
        DB::table('detail_form_overtime')->insert([
            [
                'header_id' => 1,
                'NIK' => 'EMP001',
                'name' => 'John Doe',
                'start_date' => '2026-01-15',
                'start_time' => '18:00:00',
                'end_date' => '2026-01-15',
                'end_time' => '20:00:00',
                'break' => 0,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'header_id' => 1,
                'NIK' => 'EMP001',
                'name' => 'John Doe',
                'start_date' => '2026-01-16',
                'start_time' => '18:00:00',
                'end_date' => '2026-01-16',
                'end_time' => '21:00:00',
                'break' => 0,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'header_id' => 1,
                'NIK' => 'EMP002',
                'name' => 'Jane Smith',
                'start_date' => '2026-01-15',
                'start_time' => '18:00:00',
                'end_date' => '2026-01-15',
                'end_time' => '20:00:00',
                'break' => 0,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $summary = $this->service->generateSummary('2026-01-15', '2026-01-16');

        expect($summary)->toHaveCount(2);

        $emp001 = $summary->firstWhere('NIK', 'EMP001');
        expect($emp001['total_ot'])->toBe(5.0); // 2 + 3 hours
    });

    it('excludes rejected overtime records', function () {
        DB::table('detail_form_overtime')->insert([
            'header_id' => 1,
            'NIK' => 'EMP001',
            'name' => 'John Doe',
            'start_date' => '2026-01-15',
            'start_time' => '18:00:00',
            'end_date' => '2026-01-15',
            'end_time' => '22:00:00',
            'break' => 0,
            'status' => 'Rejected',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $summary = $this->service->generateSummary('2026-01-15', '2026-01-15');

        expect($summary)->toHaveCount(0);
    });

    it('calculates total hours for specific employee', function () {
        DB::table('detail_form_overtime')->insert([
            [
                'header_id' => 1,
                'NIK' => 'EMP001',
                'name' => 'John Doe',
                'start_date' => '2026-01-15',
                'start_time' => '18:00:00',
                'end_date' => '2026-01-15',
                'end_time' => '22:00:00',
                'break' => 0,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'header_id' => 1,
                'NIK' => 'EMP001',
                'name' => 'John Doe',
                'start_date' => '2026-01-16',
                'start_time' => '18:00:00',
                'end_date' => '2026-01-16',
                'end_time' => '21:00:00',
                'break' => 0,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $total = $this->service->calculateTotalHours('EMP001', '2026-01-15', '2026-01-16');

        expect($total)->toBe(7.0);
    });
});
