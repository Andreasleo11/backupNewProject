<?php

declare(strict_types=1);

namespace App\Services\Payroll\Contracts;

use App\Services\Payroll\Dto\AnnualLeaveDto;
use App\Services\Payroll\Dto\AttendanceDto;
use App\Services\Payroll\Dto\EmployeeDto;
use Carbon\CarbonImmutable;

interface JPayrollClientContract
{
    /** @return EmployeeDto[] */
    public function getMasterEmployees(string $companyArea): array;

    /** @return AnnualLeaveDto[] */
    public function getAnnualLeave(string $companyArea, int $year): array;

    /** @return AttendanceDto[] */
    public function getAttendance(
        string $companyArea,
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?string $nik = null,
    ): array;
}
