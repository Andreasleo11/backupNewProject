<?php

declare(strict_types=1);

namespace App\Domain\Overtime\Services;

use App\Models\DetailFormOvertime;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class OvertimeSummaryService
{
    /**
     * Generate overtime summary for a date range.
     */
    public function generateSummary(string $startDate, string $endDate): Collection
    {
        $data = DetailFormOvertime::query()
            ->whereBetween('start_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->where('status', 'Approved')
            ->get();

        return $this->groupAndCalculateSummary($data);
    }

    /**
     * Group overtime data by employee and calculate totals.
     */
    private function groupAndCalculateSummary(Collection $data): Collection
    {
        $grouped = [];

        foreach ($data as $item) {
            $start = Carbon::parse("{$item->start_date} {$item->start_time}");
            $end = Carbon::parse("{$item->end_date} {$item->end_time}");

            // Handle overnight overtime
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $totalMinutes = $start->diffInMinutes($end) - $item->break;
            $totalHours = $totalMinutes / 60;

            $key = $item->NIK . '|' . $item->name;

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'NIK' => $item->NIK,
                    'nama' => $item->name,
                    'start_date' => $item->start_date,
                    'end_date' => $item->end_date,
                    'total_ot' => $totalHours,
                ];
            } else {
                $grouped[$key]['total_ot'] += $totalHours;

                // Update date range
                if ($item->start_date < $grouped[$key]['start_date']) {
                    $grouped[$key]['start_date'] = $item->start_date;
                }
                if ($item->end_date > $grouped[$key]['end_date']) {
                    $grouped[$key]['end_date'] = $item->end_date;
                }
            }
        }

        return collect(array_values($grouped));
    }

    /**
     * Calculate total overtime hours for an employee in a period.
     */
    public function calculateTotalHours(string $nik, string $startDate, string $endDate): float
    {
        $data = DetailFormOvertime::query()
            ->where('NIK', $nik)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->where('status', 'Approved')
            ->get();

        $totalHours = 0;

        foreach ($data as $item) {
            $start = Carbon::parse("{$item->start_date} {$item->start_time}");
            $end = Carbon::parse("{$item->end_date} {$item->end_time}");

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $totalMinutes = $start->diffInMinutes($end) - $item->break;
            $totalHours += $totalMinutes / 60;
        }

        return $totalHours;
    }
}
