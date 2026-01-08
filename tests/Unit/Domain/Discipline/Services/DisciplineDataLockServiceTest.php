<?php

use App\Domain\Discipline\Services\DisciplineDataLockService;
use App\Models\Employee;
use App\Models\EvaluationData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = new DisciplineDataLockService;
});

describe('DisciplineDataLockService', function () {
    describe('lockByDepartmentAndMonth', function () {
        it('locks evaluation data for a department and month', function () {
            // Arrange
            $department = '001';
            $month = 1;

            $employee = Employee::factory()->create([
                'Dept' => $department,
            ]);

            $evaluationData = EvaluationData::factory()->create([
                'nik' => $employee->NIK,
                'Month' => '2026-01-15',
                'is_lock' => false,
            ]);

            // Act
            $count = $this->service->lockByDepartmentAndMonth($department, $month);

            // Assert
            expect($count)->toBe(1);
            expect($evaluationData->fresh()->is_lock)->toBeTrue();
        });

        it('locks multiple records for the same department and month', function () {
            // Arrange
            $department = '002';
            $month = 2;

            $emp1 = Employee::factory()->create(['Dept' => $department]);
            $emp2 = Employee::factory()->create(['Dept' => $department]);

            EvaluationData::factory()->create([
                'nik' => $emp1->NIK,
                'Month' => '2026-02-01',
                'is_lock' => false,
            ]);

            EvaluationData::factory()->create([
                'nik' => $emp2->NIK,
                'Month' => '2026-02-15',
                'is_lock' => false,
            ]);

            // Act
            $count = $this->service->lockByDepartmentAndMonth($department, $month);

            // Assert
            expect($count)->toBe(2);
            expect(EvaluationData::where('is_lock', true)->count())->toBe(2);
        });

        it('does not lock records from different departments', function () {
            // Arrange
            $targetDept = '003';
            $otherDept = '004';
            $month = 3;

            $emp1 = Employee::factory()->create(['Dept' => $targetDept]);
            $emp2 = Employee::factory()->create(['Dept' => $otherDept]);

            $targetData = EvaluationData::factory()->create([
                'nik' => $emp1->NIK,
                'Month' => '2026-03-01',
                'is_lock' => false,
            ]);

            $otherData = EvaluationData::factory()->create([
                'nik' => $emp2->NIK,
                'Month' => '2026-03-01',
                'is_lock' => false,
            ]);

            // Act
            $count = $this->service->lockByDepartmentAndMonth($targetDept, $month);

            // Assert
            expect($count)->toBe(1);
            expect($targetData->fresh()->is_lock)->toBeTrue();
            expect($otherData->fresh()->is_lock)->toBeFalse();
        });

        it('does not lock records from different months', function () {
            // Arrange
            $department = '005';
            $targetMonth = 4;

            $employee = Employee::factory()->create(['Dept' => $department]);

            $targetData = EvaluationData::factory()->create([
                'nik' => $employee->NIK,
                'Month' => '2026-04-15',
                'is_lock' => false,
            ]);

            $otherData = EvaluationData::factory()->create([
                'nik' => $employee->NIK,
                'Month' => '2026-05-15',
                'is_lock' => false,
            ]);

            // Act
            $count = $this->service->lockByDepartmentAndMonth($department, $targetMonth);

            // Assert
            expect($count)->toBe(1);
            expect($targetData->fresh()->is_lock)->toBeTrue();
            expect($otherData->fresh()->is_lock)->toBeFalse();
        });

        it('returns zero when no records found', function () {
            // Arrange
            $department = '999';
            $month = 1;

            // Act
            $count = $this->service->lockByDepartmentAndMonth($department, $month);

            // Assert
            expect($count)->toBe(0);
        });
    });

    describe('getLockedData', function () {
        it('retrieves all locked evaluation data', function () {
            // Arrange
            $emp1 = Employee::factory()->create();
            $emp2 = Employee::factory()->create();

            EvaluationData::factory()->create([
                'nik' => $emp1->NIK,
                'is_lock' => true,
            ]);

            EvaluationData::factory()->create([
                'nik' => $emp2->NIK,
                'is_lock' => false,
            ]);

            // Act
            $lockedData = $this->service->getLockedData();

            // Assert
            expect($lockedData)->toHaveCount(1);
            expect($lockedData->first()->is_lock)->toBeTrue();
        });

        it('returns empty collection when no locked data exists', function () {
            // Arrange
            $employee = Employee::factory()->create();
            EvaluationData::factory()->create([
                'nik' => $employee->NIK,
                'is_lock' => false,
            ]);

            // Act
            $lockedData = $this->service->getLockedData();

            // Assert
            expect($lockedData)->toBeEmpty();
        });

        it('loads employee relationship', function () {
            // Arrange
            $employee = Employee::factory()->create();
            EvaluationData::factory()->create([
                'nik' => $employee->NIK,
                'is_lock' => true,
            ]);

            // Act
            $lockedData = $this->service->getLockedData();

            // Assert
            expect($lockedData->first()->relationLoaded('karyawan'))->toBeTrue();
        });
    });

    describe('unlock', function () {
        it('unlocks a locked evaluation data record', function () {
            // Arrange
            $employee = Employee::factory()->create();
            $evaluationData = EvaluationData::factory()->create([
                'nik' => $employee->NIK,
                'is_lock' => true,
            ]);

            // Act
            $result = $this->service->unlock($evaluationData->id);

            // Assert
            expect($result)->toBeTrue();
            expect($evaluationData->fresh()->is_lock)->toBeFalse();
        });

        it('returns false when record does not exist', function () {
            // Act
            $result = $this->service->unlock(99999);

            // Assert
            expect($result)->toBeFalse();
        });

        it('handles already unlocked records gracefully', function () {
            // Arrange
            $employee = Employee::factory()->create();
            $evaluationData = EvaluationData::factory()->create([
                'nik' => $employee->NIK,
                'is_lock' => false,
            ]);

            // Act
            $result = $this->service->unlock($evaluationData->id);

            // Assert
            expect($result)->toBeTrue();
            expect($evaluationData->fresh()->is_lock)->toBeFalse();
        });
    });

    describe('unlockMultiple', function () {
        it('unlocks multiple evaluation data records', function () {
            // Arrange
            $emp1 = Employee::factory()->create();
            $emp2 = Employee::factory()->create();
            $emp3 = Employee::factory()->create();

            $data1 = EvaluationData::factory()->create([
                'nik' => $emp1->NIK,
                'is_lock' => true,
            ]);

            $data2 = EvaluationData::factory()->create([
                'nik' => $emp2->NIK,
                'is_lock' => true,
            ]);

            $data3 = EvaluationData::factory()->create([
                'nik' => $emp3->NIK,
                'is_lock' => true,
            ]);

            $idsToUnlock = [$data1->id, $data2->id];

            // Act
            $count = $this->service->unlockMultiple($idsToUnlock);

            // Assert
            expect($count)->toBe(2);
            expect($data1->fresh()->is_lock)->toBeFalse();
            expect($data2->fresh()->is_lock)->toBeFalse();
            expect($data3->fresh()->is_lock)->toBeTrue(); // Should remain locked
        });

        it('returns zero when no IDs provided', function () {
            // Act
            $count = $this->service->unlockMultiple([]);

            // Assert
            expect($count)->toBe(0);
        });

        it('handles non-existent IDs gracefully', function () {
            // Act
            $count = $this->service->unlockMultiple([99998, 99999]);

            // Assert
            expect($count)->toBe(0);
        });
    });
});
