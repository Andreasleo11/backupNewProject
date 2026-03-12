<?php

namespace App\Livewire\DailyReports;

use App\Domain\DailyReport\Services\DailyReportAnalyticsService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    public string $employee_id;
    public ?\App\Infrastructure\Persistence\Eloquent\Models\Employee $employee = null;

    #[Url(as: 'from')]
    public ?string $filter_start_date = null;

    #[Url(as: 'to')]
    public ?string $filter_end_date = null;

    public function mount(string $employee_id)
    {
        $user = auth()->user();
        $this->employee_id = $employee_id;
        $this->employee = \App\Infrastructure\Persistence\Eloquent\Models\Employee::where('nik', $employee_id)->first();
        
        if (!$this->employee) {
            abort(404, 'Employee not found');
        }

        // Authorization: Ensure Head can only see their department's employees
        // Bernadett and Director/super-admin can see everyone
        $canSeeAll = $user->name === 'Bernadett' || $user->hasRole('DIRECTOR') || $user->hasRole('super-admin');
        
        if (!$canSeeAll && $this->employee->dept_code !== $user->department?->dept_no) {
            abort(403, 'Anda tidak memiliki akses ke data karyawan ini.');
        }

        // Initialize filters from query string if available
        $this->filter_start_date = request()->query('filter_start_date', $this->filter_start_date);
        $this->filter_end_date = request()->query('filter_end_date', $this->filter_end_date);
    }

    public function resetFilters()
    {
        $this->filter_start_date = null;
        $this->filter_end_date = null;
    }

    public function render(DailyReportAnalyticsService $analyticsService)
    {
        $user = auth()->user();

        $filters = [
            'filter_start_date' => $this->filter_start_date,
            'filter_end_date' => $this->filter_end_date,
        ];

        $data = $analyticsService->getEmployeeReports(
            $this->employee_id,
            $filters,
            $user
        );

        return view('livewire.daily-reports.show', [
            'reports' => $data['reports'],
            'missingDates' => $data['missing_dates'],
            'submittedDates' => $data['submitted_dates'],
            'startDate' => $data['start_date'],
            'endDate' => $data['end_date'],
            'calendarEvents' => $data['calendar_events'],
        ])->layout('new.layouts.app');
    }
}
