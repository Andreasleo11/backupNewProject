<?php

namespace App\Services;

use App\Helpers\ApiHelper;
use App\Models\Employee;
use App\Models\EvaluationDataWeekly;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JPayrollService
{
    protected $baseUrl;
    protected $auth;

    public function __construct()
    {
        $this->baseUrl = config("services.jpayroll.base_url");
        $this->auth = config("services.jpayroll.auth");
    }

    protected function request($endpoint, array $payload = [])
    {
        $response = Http::withHeaders([
            "Authorization" => "Basic " . $this->auth,
            "Content-Type" => "application/json",
        ])->post($this->baseUrl . $endpoint, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            "error" => true,
            "status" => $response->status(),
            "message" => $response->body(),
        ];
    }

    public function fetchMasterEmployee($companyArea, $nik = null)
    {
        $params = [
            "CompanyArea" => $companyArea,
            "NIK" => $nik,
        ];

        $validation = ApiHelper::validateParams($params, ["CompanyArea"]);
        if ($validation) {
            return $validation;
        }

        $response = $this->request("API_View_Master_Employee.php", array_filter($params));

        return ApiHelper::handleApiResponse($response);
    }

    public function fetchAttendance($companyArea, $nik = null, $date1 = null, $date2 = null)
    {
        $params = [
            "CompanyArea" => $companyArea,
            "NIK" => $nik,
            "Date1" => $date1->format("d/m/Y"),
            "Date2" => $date2->format("d/m/Y"),
        ];

        $validation = ApiHelper::validateParams($params, ["CompanyArea", "Date1", "Date2"]);
        if ($validation) {
            return $validation;
        }

        $response = $this->request("API_View_Attendance.php", array_filter($params));

        return ApiHelper::handleApiResponse($response);
    }

    public function fetchAnnualLeave($companyArea, $year = null, $nik = null)
    {
        $params = [
            "CompanyArea" => $companyArea,
            "Year" => $year,
            "NIK" => $nik,
        ];

        $validation = ApiHelper::validateParams($params, ["CompanyArea", "Year"]);
        if ($validation) {
            return $validation;
        }

        $response = $this->request("API_View_AnnualLeave.php", array_filter($params));

        return ApiHelper::handleApiResponse($response);
    }

    public function syncEmployeesLeaveAndAttendanceFromApi(
        string $companyArea = "10000",
        ?int $year = null,
        $fromDate = null,
        $toDate = null,
    ): array {
        DB::beginTransaction();
        $year = $year ?? Carbon::now()->year;
        $fromDate = $fromDate ?? Carbon::now("Asia/Jakarta")->startOfMonth();
        $toDate = $toDate ?? Carbon::now("Asia/Jakarta")->subDay()->endOfDay();

        try {
            $progressContext = [
                "processed" => 0,
                "total" => 0,
                "companyArea" => $companyArea,
            ];

            $this->prepareSyncProgressCount($year, $fromDate, $toDate, $progressContext);

            $this->syncMasterEmployees($companyArea, $progressContext);
            $this->syncAnnualLeave($companyArea, $year, $progressContext);
            $this->syncAttendanceData($companyArea, $fromDate, $toDate, $progressContext);

            DB::commit();

            return [
                "success" => true,
                "message" => "Employees and annual leave updated successfully.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Sync failed", ["error" => $e->getMessage()]);

            return [
                "success" => false,
                "message" => "Sync failed: " . $e->getMessage(),
            ];
        }
    }

    private function prepareSyncProgressCount($year, $fromDate, $toDate, array &$context): void
    {
        $context["total"] += count(
            $this->fetchMasterEmployee($context["companyArea"])["data"] ?? [],
        );
        $context["total"] += count(
            $this->fetchAnnualLeave($context["companyArea"], $year)["data"] ?? [],
        );
        $context["total"] += count(
            $this->fetchAttendance($context["companyArea"], null, $fromDate, $toDate)["data"] ?? [],
        );
    }

    private function updateUnifiedProgress(array $context): void
    {
        $percent =
            $context["total"] === 0 ? 0 : intval(($context["processed"] / $context["total"]) * 100);

        Cache::put("sync_progress_{$context["companyArea"]}", $percent, now()->addMinutes(30));
    }

    private function syncMasterEmployees(string $companyArea, array &$context): void
    {
        $response = $this->fetchMasterEmployee($companyArea);

        if (!isset($response["data"]) || !is_array($response["data"])) {
            throw new \Exception("Invalid employee response from API.");
        }

        foreach ($response["data"] as $item) {
            $data = [
                "Nama" => $item["Name"],
                "Gender" => $item["Sex"],
                "Dept" => substr($item["CostCenterCode"], 0, 3),
                "start_date" => Carbon::createFromFormat("d/m/Y", $item["StartDate"])->format(
                    "Y-m-d",
                ),
                "end_date" => $item["EndDate"]
                    ? Carbon::createFromFormat("d/m/Y", $item["EndDate"])->format("Y-m-d")
                    : null,
                "Grade" => $item["GradeCode"],
                "employee_status" => match (true) {
                    $item["EmployeeStatus"] === "ALL IN MANAJEMEN" ||
                        str_contains($item["EmployeeStatus"], "ALL IN ASING")
                        => "TETAP",
                    str_contains($item["EmployeeStatus"], "KONTRAK GAMA") => "MAGANG",
                    str_contains($item["EmployeeStatus"], "TETAP") => "TETAP",
                    str_contains($item["EmployeeStatus"], "YAYASAN") => "YAYASAN",
                    str_contains($item["EmployeeStatus"], "KONTRAK") => "KONTRAK",
                    str_contains($item["EmployeeStatus"], "MAGANG") => "MAGANG",
                    default => "UNKNOWN",
                },
                "Branch" => str_contains($item["EmployeeStatus"], "KARAWANG")
                    ? "KARAWANG"
                    : "JAKARTA",
                "status" => $item["EmployeeStatus"],
                "organization_structure" => $item["OrganizationStructure"],
            ];

            $requiredFields = [
                "Nama",
                "Gender",
                "Dept",
                "start_date",
                "Grade",
                "employee_status",
                "Branch",
                "status",
                "organization_structure",
            ];
            $hasNull = collect($data)->only($requiredFields)->contains(fn($v) => is_null($v));

            if ($hasNull) {
                Log::warning(
                    "Skipping employee with NIK {$item["NIK"]} due to missing required fields.",
                );
                continue;
            }

            Employee::updateOrCreate(["NIK" => $item["NIK"]], $data);

            $context["processed"]++;
            $this->updateUnifiedProgress($context);
        }
    }

    private function syncAnnualLeave(string $companyArea, int $year, array &$context): void
    {
        $response = $this->fetchAnnualLeave($companyArea, $year);

        $leaveMap = collect($response["data"] ?? [])->keyBy("NIK");

        foreach ($leaveMap as $nik => $item) {
            $employee = Employee::where("NIK", $nik)->first();

            if (!$employee) {
                Log::warning("Annual leave sync skipped. Employee not found for NIK: {$nik}");
                continue;
            }

            if (isset($item["Remain"])) {
                $employee->update(["jatah_cuti_tahun" => $item["Remain"]]);
            }

            $context["processed"]++;
            $this->updateUnifiedProgress($context);
        }
    }

    private function syncAttendanceData(
        string $companyArea,
        ?Carbon $from,
        ?Carbon $to,
        array &$context,
    ): void {
        $weekStart = Carbon::now("Asia/Jakarta")->subDay()->copy()->startOfWeek(Carbon::MONDAY);

        // Ensure $to is at the end of today if it's within the current week
        $effectiveTo = $to->copy()->endOfDay();

        $cursor = $from->copy();

        while ($cursor < $weekStart && $cursor < $to) {
            $weekStart = $cursor->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $cursor
                ->copy()
                ->endOfWeek(Carbon::SUNDAY)
                ->min($weekStart->copy()->subDay(), $to);

            $this->syncAttendanceRange($companyArea, $weekStart, $weekEnd, $context);

            Log::info(
                "Syncing attendance data from {$weekStart->toDateString()} to {$weekEnd->toDateString()}",
            );

            $cursor->addWeek();
        }

        // Sync current week up to the requested "to" date
        if ($weekStart <= $to) {
            $this->syncAttendanceRange($companyArea, $weekStart, $effectiveTo, $context);

            Log::info(
                "Syncing attendance data from {$weekStart->toDateString()} to {$effectiveTo->toDateString()}",
            );
        }
    }

    private function syncAttendanceRange(
        string $companyArea,
        Carbon $from,
        Carbon $to,
        array &$context,
    ): void {
        $response = $this->fetchAttendance($companyArea, null, $from, $to);

        if (!isset($response["data"]) || !is_array($response["data"])) {
            Log::warning(
                "Attendance sync skipped — invalid response from {$from->toDateString()} to {$to->toDateString()}.",
            );
            return;
        }

        foreach ($response["data"] as $item) {
            $employee = Employee::where("NIK", $item["NIK"])->first();
            Log::info("Processing attendance for NIK: {$item["NIK"]}");

            if (!$employee) {
                Log::warning("Attendance sync skipped. Employee not found for NIK: {$item["NIK"]}");
                continue;
            }

            $shiftDate = Carbon::createFromFormat("d/m/Y", $item["ShiftDate"])->format("Y-m-d");

            $data = [
                "Month" => $shiftDate,
                "dept" => $employee->Dept,
                "Alpha" => $item["ABS"],
                "Telat" => $item["LT"],
                "Izin" => $item["CT"],
                "Sakit" => $item["OP"] + $item["HOS"] + $item["WA"] + $item["HOSWA"],
            ];

            $existing = EvaluationDataWeekly::where("NIK", $item["NIK"])
                ->where("Month", $shiftDate)
                ->first();

            if (!$existing) {
                EvaluationDataWeekly::create(
                    array_merge(
                        [
                            "NIK" => $item["NIK"],
                        ],
                        $data,
                    ),
                );
            } elseif (
                collect($data)
                    ->diffAssoc($existing->only(array_keys($data)))
                    ->isNotEmpty()
            ) {
                $existing->update($data);
            }

            $context["processed"]++;
            $this->updateUnifiedProgress($context);
        }
    }

    private function showOvertimePerEmployee(
        string $companyArea,
        string $nik,
        ?string $noVoucher = null,
        ?string $date1 = null,
        ?string $date2 = null,
    ): array {
        $params = [
            "CompanyArea" => $companyArea,
            "NIK" => $nik,
            "NoVoucher" => $noVoucher, //optional
            "Date1" => $date1, // Format: "DD/MM/YYYY"
            "Date2" => $date2, // Format: "DD/MM/YYYY"
        ];

        // Validasi hanya CompanyArea dan NIK yang wajib
        $validation = ApiHelper::validateParams($params, ["CompanyArea", "NIK"]);
        if ($validation) {
            return $validation;
        }

        // Kirim request ke API
        $response = $this->request("API_View_Overtime.php", array_filter($params));

        return ApiHelper::handleApiResponse($response);
    }
}
