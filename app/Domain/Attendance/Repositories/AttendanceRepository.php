<?php

declare(strict_types=1);

namespace App\Domain\Attendance\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Read-side repository contract for attendance_records.
 * Mirrors the same Domain → Interface → EloquentImpl pattern used by EmployeeRepository.
 */
interface AttendanceRepository
{
    /**
     * Return aggregated per-employee totals for a date range, grouped by department.
     *
     * @return Collection<int, object{department_name: string, alpha: int, telat: int, izin: int, sakit: int}>
     */
    public function aggregateByDepartment(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?string $branch = null,
        ?string $deptNo = null,
        ?string $employmentType = null,
        ?string $gender = null,
    ): Collection;

    /**
     * Distinct months that have attendance data, for filter dropdowns.
     *
     * @return Collection<int, array{value: string, name: string}>  value = 'm-Y', name = 'Month YYYY'
     */
    public function distinctMonths(): Collection;

    /**
     * Latest synced date across all attendance records.
     */
    public function latestShiftDate(): ?CarbonImmutable;

    /**
     * Latest year with data (for default year picker).
     */
    public function latestYear(): ?int;

    /**
     * Per-employee attendance totals for a given week, optionally scoped to a department.
     *
     * @return Collection
     */
    public function weeklyByEmployee(
        int $year,
        int $week,
        string $department,
        ?string $category = null,
    ): Collection;

    /**
     * Global attendance sums (all time).
     *
     * @return array{alpha: int, telat: int, izin: int, sakit: int}
     */
    public function globalSums(): array;

    /**
     * Latest updated_at timestamp across all records (for "last synced" display).
     */
    public function latestUpdatedAt(): ?\Carbon\Carbon;
}
