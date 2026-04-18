<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Employee\Entities\Employee as EmployeeEntity;
use App\Domain\Employee\Repositories\EmployeeRepository;
use App\Infrastructure\Persistence\Eloquent\Models\Employee as EmployeeModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentEmployeeRepository implements EmployeeRepository
{
    public function findById(int $id): ?EmployeeEntity
    {
        $model = EmployeeModel::find($id);

        if (! $model) {
            return null;
        }

        return $this->toEntity($model);
    }

    /**
     * @return EmployeeEntity[]
     */
    public function search(string $term, int $limit = 10): array
    {
        $query = EmployeeModel::query();

        $query->where(function ($q) use ($term) {
            $q->where('nik', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });

        $models = $query
            ->orderBy('nik')
            ->limit($limit)
            ->get();

        return $models
            ->map(fn (EmployeeModel $model) => $this->toEntity($model))
            ->all();
    }

    /**
     * @return EmployeeEntity[]
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $models = EmployeeModel::whereIn('id', $ids)->get();

        return $models
            ->map(fn (EmployeeModel $model) => $this->toEntity($model))
            ->all();
    }

    public function paginate(
        ?string $search,
        int $perPage = 10,
        ?string $sortBy = null,
        string $sortDirection = 'asc',
        ?string $branch = null,
        ?string $deptCode = null,
        ?string $employmentType = null,
    ): LengthAwarePaginator {
        $query = EmployeeModel::query()
            ->with(['evaluationData', 'warningLogs', 'latestDailyReport', 'department']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($branch) {
            $query->where('branch', $branch);
        }

        if ($deptCode) {
            $query->where('dept_code', $deptCode);
        }

        if ($employmentType) {
            $query->where('employment_type', $employmentType);
        }

        // Sorting (with whitelist)
        $sortable = [
            'nik', 'name', 'start_date', 'jatah_cuti_tahun', 'end_date',
            'branch', 'dept_code', 'employment_type', 'position', 'grade_level',
        ];

        if ($sortBy && in_array($sortBy, $sortable)) {
            $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Default sort
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($perPage);
    }

    public function getGlobalStats(): array
    {
        $today = now()->format('Y-m-d');
        $statusMap = config('payroll.status_map', []);

        // Active Base Query: No end_date or end_date in future
        $activeQuery = EmployeeModel::where(function ($q) use ($today) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>', $today);
        })->where('employment_type', '!=', 'NOT ACTIVE');

        $totalActive = (clone $activeQuery)->count();

        // Dynamic Grouping Based on config/payroll.php status_map
        // Keys = values currently in DB, Values = internal categories (TETAP, KONTRAK, etc)
        $counts = [];
        foreach ($statusMap as $dbKey => $internalCategory) {
            $counts[$internalCategory] = ($counts[$internalCategory] ?? 0) +
                (clone $activeQuery)->where('employment_type', $dbKey)->count();
        }

        return [
            'total' => $totalActive,
            'permanent' => $counts['TETAP'] ?? 0,
            'contract' => ($counts['KONTRAK'] ?? 0) + ($counts['MAGANG'] ?? 0),
            'karawang' => (clone $activeQuery)->where('branch', 'KARAWANG')->count(),
            'metadata' => [
                'status_mapping' => $statusMap,
            ],
        ];
    }

    /**
     * Get approval insights for employees on a given date.
     * Returns array keyed by NIK with monthly_hours and streak_days.
     */
    public function getApprovalInsights(array $niks, \Carbon\Carbon $date): array
    {
        if (empty($niks)) {
            return [];
        }

        // Get the month for the given date
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        // Query approved overtime details for the month
        $monthlyData = DB::table('detail_form_overtime as d')
            ->join('header_form_overtime as h', 'd.header_id', '=', 'h.id')
            ->whereIn('d.NIK', $niks)
            ->where('h.status', 'Approved')
            ->whereBetween('d.overtime_date', [$monthStart, $monthEnd])
            ->selectRaw('
                d.NIK,
                SUM(TIMESTAMPDIFF(MINUTE, CONCAT(d.start_date, " ", d.start_time), CONCAT(d.end_date, " ", d.end_time)) - IFNULL(d.break, 0)) / 60 as monthly_hours,
                COUNT(DISTINCT DATE(d.overtime_date)) as active_days
            ')
            ->groupBy('d.NIK')
            ->get()
            ->keyBy('NIK');

        // Calculate insights for each NIK
        $insights = [];
        foreach ($niks as $nik) {
            $data = $monthlyData->get($nik);

            $insights[$nik] = [
                'monthly_hours' => $data ? round($data->monthly_hours, 1) : 0,
                'active_days' => $data ? (int) $data->active_days : 0,
            ];
        }

        return $insights;
    }

    private function toEntity(EmployeeModel $model): EmployeeEntity
    {
        return new EmployeeEntity(
            id: $model->id,
            nik: $model->nik,
            name: $model->name,
            branch: $model->branch,
            deptCode: $model->dept_code,
            startDate: $model->start_date ? new \DateTimeImmutable($model->start_date->format('Y-m-d')) : null,
            endDate: $model->end_date ? new \DateTimeImmutable($model->end_date->format('Y-m-d')) : null,
        );
    }
}
