<?php

declare(strict_types=1);

namespace App\Domain\DailyReport\Services;

use App\Infrastructure\Persistence\Eloquent\Models\EmployeeDailyReport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

final class DailyReportAnalyticsService
{
    /**
     * Get reports for employee with calendar analytics.
     */
    public function getEmployeeReports(string $employeeId, array $filters, $user): array
    {
        $query = EmployeeDailyReport::where('employee_id', $employeeId);

        // Apply date filters
        $this->applyDateFilters($query, $filters);

        $reports = $query->orderByDesc('sort_datetime')->get();

        // Calculate calendar data
        $calendarData = $this->calculateCalendarData($reports);

        return [
            'reports' => $reports,
            'calendar_events' => $calendarData['events'],
            'submitted_dates' => $calendarData['submitted_dates'],
            'missing_dates' => $calendarData['missing_dates'],
            'start_date' => $calendarData['start_date'],
            'end_date' => $calendarData['end_date'],
        ];
    }

    /**
     * Check if user can view all departments.
     */
    private function canViewAllDepartments($user): bool
    {
        return $user->name === 'Bernadett' || $user->hasRole('DIRECTOR');
    }

    /**
     * Apply date filters to query.
     */
    private function applyDateFilters($query, array $filters): void
    {
        $startDate = $filters['filter_start_date'] ?? null;
        $endDate = $filters['filter_end_date'] ?? null;

        if ($startDate && $endDate) {
            $query->whereBetween('work_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('work_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('work_date', '<=', $endDate);
        }
    }

    /**
     * Calculate calendar data with submitted and missing dates.
     */
    private function calculateCalendarData($reports): array
    {
        $startDate = Carbon::parse($reports->min('work_date') ?? now()->subDays(30));
        $endDate = now()->subDay();

        $allDates = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn ($date) => $date->toDateString());

        $submittedDates = $reports
            ->pluck('work_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique();

        $missingDates = $allDates->diff($submittedDates);

        $calendarEvents = [];

        // Submitted reports: green
        foreach ($submittedDates as $date) {
            $calendarEvents[] = [
                'title' => '✔ Laporan Masuk',
                'start' => $date,
                'color' => '#28a745',
            ];
        }

        // Missing reports: red
        foreach ($missingDates as $date) {
            $calendarEvents[] = [
                'title' => '❌ Tidak Ada Laporan',
                'start' => $date,
                'color' => '#dc3545',
            ];
        }

        return [
            'events' => $calendarEvents,
            'submitted_dates' => $submittedDates,
            'missing_dates' => $missingDates,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
