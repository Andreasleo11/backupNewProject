<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync;

use App\Services\Payroll\Sync\EmployeeWriteRepository;
use App\Services\Payroll\Dto\AnnualLeaveDto;

final class AnnualLeaveSync
{
    public function __construct(private readonly EmployeeWriteRepository $repo) {}

    /** @param AnnualLeaveDto[] $items */
    public function sync(array $items): void
    {
        $map = [];
        foreach ($items as $it) {
            if ($it->remain !== null) {
                $map[$it->nik] = $it->remain;
            }
        }
        if ($map) {
            $this->repo->updateLeaveBalances($map);
        }
    }

    /**
     * Detailed comparison of incoming annual leave data vs database.
     *
     * @param AnnualLeaveDto[] $items
     * @return array{summary: array, details: array}
     */
    public function preview(array $items): array
    {
        $incoming = collect($items)->filter(fn($it) => $it->remain !== null)->keyBy('nik');
        $current = collect($this->repo->getAllForDiff())->map(fn ($r) => (array) $r);

        $new = []; // Not possible for AnnualLeave sync, it only updates existing
        $updated = [];
        $unchanged = 0;
        $unknown = [];

        foreach ($incoming as $nik => $item) {
            if (!isset($current[$nik])) {
                $unknown[] = ['nik' => $nik, 'remain' => $item->remain];
                continue;
            }

            $curr = $current[$nik];
            $currRemain = $curr['jatah_cuti_tahun'] !== null ? (int) $curr['jatah_cuti_tahun'] : null;

            if ($currRemain !== $item->remain) {
                $updated[] = [
                    'nik' => $nik,
                    'name' => $curr['name'],
                    'branch' => $curr['branch'] ?? 'JAKARTA',
                    'diffs' => [
                        'jatah_cuti_tahun' => [
                            'old' => $currRemain,
                            'new' => $item->remain,
                        ]
                    ]
                ];
            } else {
                $unchanged++;
            }
        }

        return [
            'summary' => [
                'new' => 0,
                'updated' => count($updated),
                'unchanged' => $unchanged,
                'inactive' => 0, // Not applicable
                'unknown' => count($unknown), // NIKs that don't exist locally
            ],
            'details' => [
                'new' => [],
                'updated' => $updated,
                'inactive' => [],
                'unknown' => $unknown,
            ],
        ];
    }
}
