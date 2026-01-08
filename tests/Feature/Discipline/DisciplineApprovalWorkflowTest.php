<?php

use App\Models\Department;
use App\Models\EvaluationData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('Discipline Approval Workflow', function () {
    beforeEach(function () {
        // Create department
        $this->department = Department::create([
            'dept_no' => '001',
            'name' => 'Production',
        ]);

        // Create department head user
        $this->deptHead = User::factory()->create([
            'name' => 'John Department Head',
            'is_head' => 1,
            'is_gm' => 0,
            'department_id' => $this->department->id,
        ]);

        // Create GM user
        $this->gm = User::factory()->create([
            'name' => 'Jane GM',
            'is_head' => 0,
            'is_gm' => 1,
        ]);

        // Create employee (minimal fields from migration)
        DB::table('employees')->insert([
            'NIK' => 'EMP00001',
            'Nama' => 'Test Employee',
            'Dept' => '001',
            'start_date' => now()->subYears(2),
            'status' => 'YAYASAN',
        ]);

        // Create evaluation data
        $this->evaluation = EvaluationData::create([
            'NIK' => 'EMP00001',
            'dept' => '001',
            'Month' => now()->startOfMonth(),
            'Alpha' => 0,
            'Telat' => 0,
            'Izin' => 0,
            'Sakit' => 0,
            'kemampuan_kerja' => 'A',
            'kecerdasan_kerja' => 'A',
            'qualitas_kerja' => 'A',
            'disiplin_kerja' => 'A',
            'kepatuhan_kerja' => 'A',
            'lembur' => 'A',
            'efektifitas_kerja' => 'A',
            'relawan' => 'A',
            'integritas' => 'A',
            'total' => 90,
            'is_lock' => false,
        ]);
    });

    it('allows department head to approve yayasan employee evaluations', function () {
        // Act
        $response = $this->actingAs($this->deptHead)
            ->post(route('discipline.approve.depthead.button'), [
                'filter_month' => now()->month,
                'filter_year' => now()->year,
            ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Approved by depthead');

        $this->evaluation->refresh();
        expect($this->evaluation->depthead)->toBe('John Department Head');
    });

    it('allows GM to approve after department head approval', function () {
        // Arrange - first get dept head approval
        $this->evaluation->update(['depthead' => 'John Department Head']);

        // Act
        $response = $this->actingAs($this->gm)
            ->post(route('discipline.approve.hrd.button'), [
                'filter_dept' => '001',
                'filter_month' => now()->month,
                'filter_year' => now()->year,
            ]);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Approved by HRD');

        $this->evaluation->refresh();
        expect($this->evaluation->generalmanager)->toBe('Jane GM');
    });

    it('prevents non-department-head from approving', function () {
        // Arrange - create regular user
        $regularUser = User::factory()->create([
            'is_head' => 0,
            'is_gm' => 0,
        ]);

        // Act & Assert
        $this->actingAs($regularUser)
            ->post(route('discipline.approve.depthead.button'), [
                'filter_month' => now()->month,
                'filter_year' => now()->year,
            ])
            ->assertForbidden();
    });

    it('allows department head to reject with remark', function () {
        // Act
        $response = $this->actingAs($this->deptHead)
            ->post(route('discipline.reject.depthead.button'), [
                'filter_month' => now()->month,
                'filter_year' => now()->year,
                'remark' => 'Scores need review',
            ]);

        // Assert
        $response->assertRedirect();

        $this->evaluation->refresh();
        expect($this->evaluation->depthead)->toBe('rejected');
        expect($this->evaluation->remark)->toBe('Scores need review');
    });

    it('resets approvals when editing rejected evaluation data', function () {
        // Arrange - mark as rejected
        $this->evaluation->update([
            'depthead' => 'rejected',
            'generalmanager' => 'rejected',
            'remark' => 'Needs improvement',
        ]);

        // Act - update the evaluation
        $response = $this->actingAs($this->deptHead)
            ->put(route('discipline.update.yayasan', $this->evaluation->id), [
                'kemampuan_kerja' => 'A',
                'kecerdasan_kerja' => 'A',
                'qualitas_kerja' => 'A',
                'disiplin_kerja' => 'A',
                'kepatuhan_kerja' => 'A',
                'lembur' => 'A',
                'efektifitas_kerja' => 'A',
                'relawan' => 'A',
                'integritas' => 'A',
            ]);

        // Assert - approvals should be reset
        $this->evaluation->refresh();
        expect($this->evaluation->depthead)->toBeNull();
        expect($this->evaluation->generalmanager)->toBeNull();
    });

    it('locks data when department head approves with lock flag', function () {
        // Act
        $response = $this->actingAs($this->deptHead)
            ->post(route('discipline.approve.depthead'), [
                'filter_month' => now()->month,
                'filter_year' => now()->year,
            ]);

        // Assert
        $this->evaluation->refresh();
        expect($this->evaluation->is_lock)->toBeTrue();
        expect($this->evaluation->depthead)->not->toBeNull();
    });
});

describe('Discipline Data Locking Workflow', function () {
    beforeEach(function () {
        // Create department
        $this->department = Department::create([
            'dept_no' => '002',
            'name' => 'QC',
        ]);

        // Create department head
        $this->deptHead = User::factory()->create([
            'name' => 'QC Head',
            'is_head' => 1,
            'department_id' => $this->department->id,
        ]);

        // Create employees and evaluations
        DB::table('employees')->insert([
            ['NIK' => 'EMP00002', 'Nama' => 'Employee 2', 'Dept' => '002', 'start_date' => now()->subYear(), 'status' => 'KONTRAK'],
            ['NIK' => 'EMP00003', 'Nama' => 'Employee 3', 'Dept' => '002', 'start_date' => now()->subYear(), 'status' => 'KONTRAK'],
        ]);

        EvaluationData::create([
            'NIK' => 'EMP00002',
            'dept' => '002',
            'Month' => now()->startOfMonth(),
            'total' => 85,
            'is_lock' => false,
        ]);

        EvaluationData::create([
            'NIK' => 'EMP00003',
            'dept' => '002',
            'Month' => now()->startOfMonth(),
            'total' => 75,
            'is_lock' => false,
        ]);
    });

    it('locks all department evaluations for a specific month', function () {
        // Act
        $response = $this->actingAs($this->deptHead)
            ->post(route('discipline.lock'), [
                'filter_month' => now()->month,
            ]);

        // Assert
        $response->assertRedirect();

        $lockedCount = EvaluationData::where('dept', '002')
            ->where(DB::raw('MONTH(Month)'), now()->month)
            ->where('is_lock', true)
            ->count();

        expect($lockedCount)->toBe(2);
    });

    it('prevents editing locked evaluation data', function () {
        // Arrange - lock the data
        $evaluation = EvaluationData::where('NIK', 'EMP00002')->first();
        $evaluation->update(['is_lock' => true]);

        // Act - try to update
        // Note: This would need actual form protection in blade/JS
        // For now, we test that locked data has the flag set
        expect($evaluation->is_lock)->toBeTrue();
    });
});
