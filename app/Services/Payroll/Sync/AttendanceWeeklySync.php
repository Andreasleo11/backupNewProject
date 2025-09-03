<?php
declare(strict_types=1);

namespace App\Services\Payroll\Sync;

use App\Repositories\EmployeeRepository;
use App\Repositories\EvaluationWeeklyRepository;
use App\Services\Payroll\Dto\AttendanceDto;
use Carbon\CarbonInterface;

final class AttendanceWeeklySync
{
    public function __construct(
        private readonly EvaluationWeeklyRepository $repo,
        private readonly EmployeeRepository $employees
    ) {}

    /** @param AttendanceDto[] $items */
    public function sync(array $items): int
    {
        $bucket = []; // key: nik|month

        foreach ($items as $it) {
            $week = $it->shiftDate->startOfWeek(CarbonInterface::MONDAY)->toDateString();
            $key  = $it->nik.'|'.$week;

            if (!isset($bucket[$key])) {
                $dept = $this->employees->getDeptForNik($it->nik);
                if (!$dept) {
                    // skip if no employee row yet
                    continue;
                }

                $bucket[$key] = [
                    'NIK' => $it->nik,
                    'Month' => $week,
                    'dept' => $dept,
                    'Alpha' => 0,
                    'Telat' => 0,
                    'Izin' => 0,
                    'Sakit' => 0,
                ];
            }

            $bucket[$key]['Alpha'] += $it->alpha;
            $bucket[$key]['Telat'] += $it->telat;
            $bucket[$key]['Izin']  += $it->izin;
            $bucket[$key]['Sakit'] += $it->sakit;
        }

        return $this->repo->upsertWeekly(array_values($bucket));
    }
}
