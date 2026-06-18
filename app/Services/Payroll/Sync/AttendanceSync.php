<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync;

use App\Services\Payroll\Sync\AttendanceWriteRepository;
use App\Services\Payroll\Dto\AttendanceDto;

/**
 * Persists raw daily attendance records fetched from JPayroll.
 * All weekly / monthly aggregations are computed at query time in the UI layer.
 */
final class AttendanceSync
{
    public function __construct(
        private readonly AttendanceWriteRepository $repo,
        private readonly \App\Services\Payroll\Sync\EmployeeWriteRepository $employeeRepo,
    ) {}

    /** @param AttendanceDto[] $items */
    public function sync(array $items): int
    {
        // Filter out items without a nik (defensive guard)
        $items = array_filter($items, fn (AttendanceDto $dto) => $dto->nik !== '');

        return $this->repo->upsertDaily(array_values($items));
    }

    /**
     * Detailed comparison of incoming attendance vs database.
     *
     * @param AttendanceDto[] $items
     * @return array{summary: array, details: array}
     */
    public function preview(array $items): array
    {
        $items = array_filter($items, fn (AttendanceDto $dto) => $dto->nik !== '');
        
        if (empty($items)) {
            return [
                'summary' => ['new' => 0, 'updated' => 0, 'unchanged' => 0, 'inactive' => 0],
                'details' => ['new' => [], 'updated' => [], 'inactive' => []],
            ];
        }

        $minDate = min(array_map(fn($it) => $it->shiftDate->toDateString(), $items));
        $maxDate = max(array_map(fn($it) => $it->shiftDate->toDateString(), $items));

        $incoming = collect($items)->keyBy(fn($it) => $it->nik . '_' . $it->shiftDate->toDateString());
        $current = collect($this->repo->getForDiff($minDate, $maxDate))->map(fn ($r) => (array) $r);
        
        $employees = collect($this->employeeRepo->getAllForDiff())->map(fn ($r) => (array) $r);

        $new = [];
        $updated = [];
        $unchanged = 0;

        foreach ($incoming as $key => $item) {
            $emp = $employees[$item->nik] ?? null;
            $empName = $emp ? $emp['name'] : 'Unknown';
            $empBranch = $emp ? ($emp['branch'] ?? 'JAKARTA') : 'UNKNOWN';

            if (!isset($current[$key])) {
                $new[] = [
                    'nik' => $item->nik,
                    'name' => $empName,
                    'branch' => $empBranch,
                    'employment_type' => 'Standard',
                    'dept_code' => $item->shiftDate->toDateString(), // Using dept_code to display the date in the UI
                ];
                continue;
            }

            $curr = $current[$key];
            $diffs = [];

            if ((int)$curr['alpha'] !== $item->alpha) $diffs['alpha'] = ['old' => (int)$curr['alpha'], 'new' => $item->alpha];
            if ((int)$curr['telat'] !== $item->telat) $diffs['telat'] = ['old' => (int)$curr['telat'], 'new' => $item->telat];
            if ((int)$curr['izin'] !== $item->izin) $diffs['izin'] = ['old' => (int)$curr['izin'], 'new' => $item->izin];
            if ((int)$curr['sakit'] !== $item->sakit) $diffs['sakit'] = ['old' => (int)$curr['sakit'], 'new' => $item->sakit];

            if (!empty($diffs)) {
                $updated[] = [
                    'nik' => $item->nik,
                    'name' => $empName . ' (' . $item->shiftDate->toDateString() . ')',
                    'branch' => $empBranch,
                    'diffs' => $diffs
                ];
            } else {
                $unchanged++;
            }
        }

        return [
            'summary' => [
                'new' => count($new),
                'updated' => count($updated),
                'unchanged' => $unchanged,
                'inactive' => 0,
            ],
            'details' => [
                'new' => $new,
                'updated' => $updated,
                'inactive' => [],
            ],
        ];
    }
}
