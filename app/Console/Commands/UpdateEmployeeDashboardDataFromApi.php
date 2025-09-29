<?php

namespace App\Console\Commands;

use App\Services\JPayrollService;
use Illuminate\Console\Command;

class UpdateEmployeeDashboardDataFromApi extends Command
{
    protected $signature = 'employee-dashboard:update-from-api';

    protected $description = 'Fetch and update employee dashboard data from JPayroll API';

    public function handle(JPayrollService $service)
    {
        $this->info('Syncing employee data...');
        $result = $service->syncEmployeesLeaveAndAttendanceFromApi();

        $this->error($result['message']);
    }
}
