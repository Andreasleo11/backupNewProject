<?php

namespace App\Console\Commands;

use App\Jobs\UpdateDepartmentComplianceSnapshot;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Console\Command;

class BackfillDeptCompliance extends Command
{
    protected $signature = 'compliance:backfill';

    protected $description = 'Compute compliance snapshots for all departments';

    public function handle(): int
    {
        Department::pluck('id')->each(fn ($id) => UpdateDepartmentComplianceSnapshot::dispatchSync($id, true));
        $this->info('Done.');

        return self::SUCCESS;
    }
}
