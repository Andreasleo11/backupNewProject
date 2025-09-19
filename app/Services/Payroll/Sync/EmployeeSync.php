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
        $statusMap = config("payroll.status_map", []);
        $branchHints = config("payroll.branch_hints", []);

        $rows = [];
        foreach ($items as $e) {
            $empStatus = $this->normalizeStatus($e->employeeStatusRaw, $statusMap);
            $branch = $this->inferBranch($e->employeeStatusRaw, $branchHints);

            $rows[] = [
                "NIK" => $e->nik,
                "Nama" => $e->name,
                "Gender" => $e->sex,
                "Dept" => substr($e->costCenterCode, 0, 3),
                "start_date" => $e->startDate?->toDateString(),
                "end_date" => $e->endDate?->toDateString(),
                "Grade" => $e->gradeCode,
                "employee_status" => $empStatus ?? "UNKNOWN",
                "Branch" => $branch ?? "JAKARTA",
                "status" => $e->employeeStatusRaw,
                "organization_structure" => $e->organizationStructure,
            ];
        }

        return $this->repo->upsert($rows);
    }

    private function normalizeStatus(string $raw, array $map): ?string
    {
        foreach ($map as $needle => $value) {
            if (str_contains($raw, $needle)) {
                return $value;
            }
        }
        return null;
    }

    private function inferBranch(string $raw, array $hints): ?string
    {
        foreach ($hints as $needle => $branch) {
            if (str_contains($raw, $needle)) {
                return $branch;
            }
        }
        return null;
    }
}
