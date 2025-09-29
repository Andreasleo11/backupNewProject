<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EvaluationDataWeekly;
use App\Services\JPayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('it syncs employees, leave, and attendance correctly', function () {
    // Mock API responses by endpoint
    Http::fake([
        '*/API_View_Master_Employee.php' => Http::response([
            'data' => [
                [
                    'NIK' => '00123',
                    'Name' => 'Test Employee',
                    'Sex' => 'M',
                    'CostCenterCode' => '310-K',
                    'StartDate' => '01/01/2020',
                    'EndDate' => null,
                    'GradeCode' => 'A1',
                    'EmployeeStatus' => 'ALL IN MANAJEMEN',
                ],
            ],
            'total' => 1,
        ]),
        '*/API_View_AnnualLeave.php' => Http::response([
            'data' => [
                [
                    'NIK' => '00123',
                    'Year' => '2024',
                    'Balance' => '12',
                    'Remain' => '10',
                    'StartDate' => '01/01/2024',
                    'EndDate' => '31/12/2024',
                ],
            ],
            'total' => 1,
        ]),
        '*/API_View_Attendance.php' => Http::response([
            'data' => [
                [
                    'NIK' => '00123',
                    'Name' => 'Test Employee',
                    'ShiftDate' => now()->format('d/m/Y'),
                    'ABS' => '1',
                    'LT' => '0',
                    'CT' => '1',
                    'OP' => '0',
                    'HOS' => '0',
                    'WA' => '0',
                    'HOSWA' => '0',
                ],
            ],
            'total' => 1,
        ]),
    ]);

    // Call the method
    $service = app(JPayrollService::class);
    $response = $service->syncEmployeesLeaveAndAttendanceFromApi('10000', now()->year);
    // Assertions
    expect(Employee::count())->toBe(1);

    $employee = Employee::first();
    expect($employee->Nama)
        ->toBe('Test Employee')
        ->and($employee->Dept)
        ->toBe('310')
        ->and($employee->employee_status)
        ->toBe('TETAP')
        ->and($employee->jatah_cuti_tahun)
        ->toBe(10);

    expect(EvaluationDataWeekly::count())->toBe(1);
    $evaluation = EvaluationDataWeekly::first();
    expect($evaluation->Alpha)->toBe(1)->and($evaluation->Izin)->toBe(1);
});
