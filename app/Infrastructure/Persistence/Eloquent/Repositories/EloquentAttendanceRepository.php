<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Attendance\Repositories\AttendanceRepository;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentAttendanceRepository implements AttendanceRepository
{
    public function aggregateByDepartment(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?string $branch = null,
        ?string $deptNo = null,
        ?string $employmentType = null,
        ?string $gender = null,
    ): Collection {
        $query = AttendanceRecord::join('employees', 'attendance_records.nik', '=', 'employees.NIK')
            ->join('departments', 'employees.dept_code', '=', 'departments.dept_no')
            ->whereBetween('attendance_records.shift_date', [$from->toDateString(), $to->toDateString()])
            ->select(
                'departments.name as department_name',
                DB::raw('SUM(alpha) as alpha'),
                DB::raw('SUM(telat) as telat'),
                DB::raw('SUM(izin) as izin'),
                DB::raw('SUM(sakit) as sakit'),
            )
            ->groupBy('departments.name');

        if ($branch) {
            $query->where('employees.Branch', $branch);
        }

        if ($deptNo) {
            $query->where('departments.dept_no', $deptNo);
        }

        if ($employmentType) {
            $query->where('employees.employment_type', $employmentType);
        }

        if ($gender) {
            $query->where('employees.Gender', $gender);
        }

        return $query->get();
    }

    public function distinctMonths(): Collection
    {
        return AttendanceRecord::selectRaw(
            'DISTINCT DATE_FORMAT(shift_date, "%Y-%m") as month_key, DATE_FORMAT(shift_date, "%M %Y") as month_label',
        )
            ->orderBy('month_key')
            ->get()
            ->map(fn ($item) => [
                'value' => CarbonImmutable::parse($item->month_key . '-01')->format('m-Y'),
                'name'  => $item->month_label,
            ]);
    }

    public function latestShiftDate(): ?CarbonImmutable
    {
        $date = AttendanceRecord::max('shift_date');

        return $date ? CarbonImmutable::parse($date) : null;
    }

    public function latestYear(): ?int
    {
        return AttendanceRecord::selectRaw('YEAR(shift_date) as year')
            ->orderByDesc('year')
            ->limit(1)
            ->value('year');
    }

    public function weeklyByEmployee(
        int $year,
        int $week,
        string $department,
        ?string $category = null,
    ): Collection {
        $validCategories = ['alpha', 'telat', 'izin', 'sakit'];

        $query = AttendanceRecord::join('employees', 'attendance_records.nik', '=', 'employees.NIK')
            ->join('departments', 'employees.dept_code', '=', 'departments.dept_no')
            ->whereYear('shift_date', $year)
            ->where(DB::raw('WEEK(shift_date, 1)'), $week)
            ->where('departments.name', $department);

        if (! $category || ! in_array($category, $validCategories, true)) {
            $query->select(
                'employees.NIK',
                'employees.Nama',
                'departments.name as department_name',
                'employees.employment_type',
            );
        } else {
            $query->select(
                'employees.NIK',
                'employees.Nama',
                'departments.name as department_name',
                'employees.employment_type',
                DB::raw("SUM(attendance_records.{$category}) as category_total"),
            )->groupBy(
                'employees.NIK',
                'employees.Nama',
                'departments.name',
                'employees.employment_type',
            );
        }

        return $query->get();
    }

    public function globalSums(): array
    {
        $row = AttendanceRecord::selectRaw('
            SUM(alpha) as alpha,
            SUM(telat) as telat,
            SUM(izin)  as izin,
            SUM(sakit) as sakit
        ')->first();

        return [
            'alpha' => (int) ($row->alpha ?? 0),
            'telat' => (int) ($row->telat ?? 0),
            'izin'  => (int) ($row->izin  ?? 0),
            'sakit' => (int) ($row->sakit ?? 0),
        ];
    }

    public function latestUpdatedAt(): ?Carbon
    {
        $record = AttendanceRecord::orderBy('updated_at', 'desc')->first();

        return $record?->updated_at;
    }
}
