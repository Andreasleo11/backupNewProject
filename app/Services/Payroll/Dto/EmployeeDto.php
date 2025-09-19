<?php
declare(strict_types=1);

namespace App\Services\Payroll\Dto;

use Carbon\CarbonImmutable;

final class EmployeeDto
{
    public function __construct(
        public readonly string $nik,
        public readonly string $name,
        public readonly string $sex,
        public readonly string $costCenterCode,
        public readonly ?CarbonImmutable $startDate,
        public readonly ?CarbonImmutable $endDate,
        public readonly ?string $gradeCode,
        public readonly string $employeeStatusRaw,
        public readonly ?string $organizationStructure,
    ) {}

    public static function fromApi(array $r): self
    {
        return new self(
            nik: (string) ($r["NIK"] ?? ""),
            name: (string) ($r["Name"] ?? ""),
            sex: (string) ($r["Sex"] ?? ""),
            costCenterCode: (string) ($r["CostCenterCode"] ?? ""),
            startDate: !empty($r["StartDate"])
                ? CarbonImmutable::createFromFormat("d/m/Y", $r["StartDate"])
                : null,
            endDate: !empty($r["EndDate"])
                ? CarbonImmutable::createFromFormat("d/m/Y", $r["EndDate"])
                : null,
            gradeCode: $r["GradeCode"] ?? null,
            employeeStatusRaw: (string) ($r["EmployeeStatus"] ?? ""),
            organizationStructure: $r["OrganizationStructure"] ?? null,
        );
    }
}
