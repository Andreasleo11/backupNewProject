<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Services\Payroll\Contracts\JPayrollClientContract;
use App\Services\Payroll\Dto\AnnualLeaveDto;
use App\Services\Payroll\Dto\AttendanceDto;
use App\Services\Payroll\Dto\EmployeeDto;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class JPayrollClient implements JPayrollClientContract
{
    public function __construct(private readonly string $baseUrl, private readonly string $auth) {}

    public static function fromConfig(): self
    {
        return new self(config('services.jpayroll.base_url'), config('services.jpayroll.auth'));
    }

    /** @return EmployeeDto[] */
    public function getMasterEmployees(string $companyArea): array
    {
        $json = $this->post('API_View_Master_Employee.php', ['CompanyArea' => $companyArea]);

        return array_map(fn ($r) => EmployeeDto::fromApi($r), $json['data'] ?? []);
    }

    /** @return AnnualLeaveDto[] */
    public function getAnnualLeave(string $companyArea, int $year): array
    {
        $json = $this->post('API_View_AnnualLeave.php', [
            'CompanyArea' => $companyArea,
            'Year' => $year,
        ]);

        return array_map(fn ($r) => AnnualLeaveDto::fromApi($r), $json['data'] ?? []);
    }

    /** @return AttendanceDto[] */
    public function getAttendance(
        string $companyArea,
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?string $nik = null,
    ): array {
        $json = $this->post('API_View_Attendance.php', [
            'CompanyArea' => $companyArea,
            'NIK' => $nik,
            'Date1' => $from->format('d/m/Y'),
            'Date2' => $to->format('d/m/Y'),
        ]);

        return array_map(fn ($r) => AttendanceDto::fromApi($r), $json['data'] ?? []);
    }

    private function post(string $endpoint, array $payload): array
    {
        $resp = Http::retry(3, 500)
            ->timeout(30)
            ->asJson()
            ->withHeaders(['Authorization' => 'Basic '.$this->auth])
            ->post(rtrim($this->baseUrl, '/').'/'.$endpoint, $payload);

        if ($resp->successful()) {
            return $resp->json();
        }

        Log::warning('JPayroll API error', [
            'endpoint' => $endpoint,
            'status' => $resp->status(),
            'body' => $resp->body(),
        ]);
        throw new \RuntimeException("JPayroll API error ({$resp->status()})");
    }
}
