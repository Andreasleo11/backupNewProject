<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\JPayrollService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateEmployeeDashboardDataFromApi extends Command
{
    protected $signature = "employee-dashboard:update-from-api";
    protected $description = "Fetch and update employee dashboard data from JPayroll API";

    public function handle(JPayrollService $service)
    {
        $this->info("Syncing employee data...");
        $result = $service->syncEmployeesLeaveAndAttendanceFromApi();

        $this->error($result["message"]);
    }
}
