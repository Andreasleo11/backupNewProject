<?php

namespace App\Livewire\Admin;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class AuditLogPage extends Component
{
    use WithPagination;

    public $search = '';
    public $eventFilter = '';
    public $subjectFilter = '';
    public $dateFilter = '';
    public $startDate = '';
    public $endDate = '';
    public $perPage = 25;
    public $selectedActivityId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'eventFilter' => ['except' => ''],
        'subjectFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
        'perPage' => ['except' => 25],
    ];

    public function mount()
    {
        abort_unless(auth()->user()?->hasRole('super-admin'), 403);
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingEventFilter() { $this->resetPage(); }
    public function updatingSubjectFilter() { $this->resetPage(); }
    public function updatingDateFilter() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'eventFilter', 'subjectFilter', 'dateFilter', 'startDate', 'endDate']);
        $this->perPage = 25;
        $this->resetPage();
    }

    public function inspectActivity($id)
    {
        $this->selectedActivityId = $id;
    }

    public function closeInspection()
    {
        $this->selectedActivityId = null;
    }

    public function render()
    {
        // 1. Calculate Metrics
        $totalLogs = Activity::count();
        $todayLogs = Activity::where('created_at', '>=', Carbon::today())->count();
        $deletedLogs = Activity::where('event', 'deleted')->count();
        $activeCausers = Activity::where('created_at', '>=', Carbon::now()->subHours(24))
            ->whereNotNull('causer_id')
            ->distinct('causer_id')
            ->count('causer_id');

        // 2. Fetch distinct Subject Types for dropdown
        $availableSubjects = Activity::query()
            ->whereNotNull('subject_type')
            ->distinct()
            ->pluck('subject_type')
            ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
            ->toArray();

        // 3. Build Filtered Query
        $query = Activity::with(['causer', 'subject'])->latest();

        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('description', 'like', $searchTerm)
                  ->orWhere('event', 'like', $searchTerm)
                  ->orWhere('log_name', 'like', $searchTerm)
                  ->orWhere('subject_id', 'like', $searchTerm)
                  ->orWhereHasMorph('causer', '*', function ($causerQuery) use ($searchTerm) {
                      $causerQuery->where('name', 'like', $searchTerm)
                                 ->orWhere('email', 'like', $searchTerm);
                  })
                  ->orWhere('subject_type', 'like', $searchTerm);
            });
        }

        if (!empty($this->eventFilter)) {
            $query->where('event', $this->eventFilter);
        }

        if (!empty($this->subjectFilter)) {
            $query->where('subject_type', $this->subjectFilter);
        }

        if (!empty($this->dateFilter)) {
            if ($this->dateFilter === 'today') {
                $query->where('created_at', '>=', Carbon::today());
            } elseif ($this->dateFilter === '7days') {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
            } elseif ($this->dateFilter === '30days') {
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
            } elseif ($this->dateFilter === 'custom') {
                if (!empty($this->startDate)) {
                    $query->where('created_at', '>=', Carbon::parse($this->startDate)->startOfDay());
                }
                if (!empty($this->endDate)) {
                    $query->where('created_at', '<=', Carbon::parse($this->endDate)->endOfDay());
                }
            }
        }

        $activities = $query->paginate($this->perPage);

        // 4. Selected Activity Inspection
        $selectedActivity = $this->selectedActivityId 
            ? Activity::with(['causer', 'subject'])->find($this->selectedActivityId)
            : null;

        $parsedDiff = [];
        if ($selectedActivity && !empty($selectedActivity->properties)) {
            $props = $selectedActivity->properties;
            $old = $props['old'] ?? [];
            $attributes = $props['attributes'] ?? [];

            // If attributes exist, map old vs attributes
            if (!empty($attributes) || !empty($old)) {
                $keys = array_unique(array_merge(array_keys($old), array_keys($attributes)));
                foreach ($keys as $key) {
                    $oldVal = $old[$key] ?? null;
                    $newVal = $attributes[$key] ?? null;

                    $parsedDiff[] = [
                        'field' => $key,
                        'old' => is_array($oldVal) || is_object($oldVal) ? json_encode($oldVal) : (string)$oldVal,
                        'new' => is_array($newVal) || is_object($newVal) ? json_encode($newVal) : (string)$newVal,
                        'changed' => $oldVal !== $newVal,
                    ];
                }
            }
        }

        return view('livewire.admin.audit-log-page', [
            'activities' => $activities,
            'totalLogs' => $totalLogs,
            'todayLogs' => $todayLogs,
            'deletedLogs' => $deletedLogs,
            'activeCausers' => $activeCausers,
            'availableSubjects' => $availableSubjects,
            'selectedActivity' => $selectedActivity,
            'parsedDiff' => $parsedDiff,
        ])->layout('new.layouts.app');
    }
}

