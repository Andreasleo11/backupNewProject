<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync;

use App\Repositories\EmployeeRepository;
use App\Services\Payroll\Dto\EmployeeDto;

final class EmployeeSync
{
    public function __construct(private readonly EmployeeRepository $repo) {}

    /** @param EmployeeDto[] $items */
    public function sync(array $items): int
    {
        $rows = $this->mapRows($items);

        return $this->repo->upsert($rows);
    }

    /**
     * Detailed comparison of JPayroll data vs database.
     *
     * @param EmployeeDto[] $items
     * @return array{summary: array, details: array}
     */
    public function preview(array $items): array
    {
        $incoming = collect($this->mapRows($items))->keyBy('nik');
        $current = collect($this->repo->getAllForDiff())->map(fn ($r) => (array) $r);

        $new = $incoming->diffKeys($current);
        $inactive = $current->diffKeys($incoming);

        $updated = $incoming->intersectByKeys($current)->map(function ($row, $nik) use ($current) {
            $curr = $current[$nik];
            $diffs = [];

            foreach ($row as $key => $val) {
                if (array_key_exists($key, $curr) && (string) $curr[$key] !== (string) $val) {
                    $diffs[$key] = [
                        'old' => $curr[$key],
                        'new' => $val,
                    ];
                }
            }

            return $diffs ? ['nik' => $nik, 'name' => $row['name'], 'diffs' => $diffs] : null;
        })->filter();

        $unchangedCount = $incoming->count() - $new->count() - $updated->count();

        return [
            'summary' => [
                'new' => $new->count(),
                'updated' => $updated->count(),
                'unchanged' => $unchangedCount,
                'inactive' => $inactive->count(),
            ],
            'details' => [
                'new' => $new->values()->toArray(),
                'updated' => $updated->values()->toArray(),
                'inactive' => $inactive->values()->toArray(),
            ],
        ];
    }

    /** @param EmployeeDto[] $items */
    private function mapRows(array $items): array
    {
        $statusMap = config('payroll.status_map', []);
        $branchHints = config('payroll.branch_hints', []);

        return array_map(function ($e) use ($statusMap, $branchHints) {
            $empStatus = collect($statusMap)->first(fn ($v, $k) => str_contains($e->employeeStatusRaw, $k));
            $branch = collect($branchHints)->first(fn ($v, $k) => str_contains($e->employeeStatusRaw, $k)) ?? 'JAKARTA';

            return [
                'nik' => $e->nik,
                'name' => $e->name,
                'gender' => $e->sex,
                'dept_code' => substr($e->costCenterCode, 0, 3),
                'start_date' => $e->startDate?->toDateString(),
                'end_date' => $e->endDate?->toDateString(),
                'grade_code' => $e->gradeCode,
                'employment_type' => $empStatus ?? 'UNKNOWN',
                'branch' => $branch,
                'employment_scheme' => substr((string) $e->employeeStatusRaw, 0, 100),
                'organization_structure' => $e->organizationStructure,
            ];
        }, $items);
    }
}
