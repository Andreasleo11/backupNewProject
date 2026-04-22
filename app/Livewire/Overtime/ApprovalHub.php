<?php

namespace App\Livewire\Overtime;

use App\Application\Overtime\Queries\ApprovalHubQueryBuilder;
use App\Domain\Employee\Repositories\EmployeeRepository;
use App\Infrastructure\Approval\Services\ApprovalEngine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('new.layouts.app')]
class ApprovalHub extends Component
{
    use WithPagination;

    public array $selectedPackKeys = [];

    public array $expandedGroups = [];

    public array $insights = [];

    public int $bulkSelectionKey = 0;

    public string $groupingMode = '';

    public bool $hasSelectedGroupingMode = false;

    // Summary totals
    public int $totalForms = 0;

    public int $totalEmployees = 0;

    public float $totalHours = 0.0;

    public float $avgHours = 0.0;

    // Filters
    public string $search = '';

    public bool $showOnboarding = false;

    public bool $showRejectModal = false;

    public string $rejectReason = '';

    // Insights modal
    public bool $showInsightModal = false;

    public string $insightEmployeeNik = '';

    public string $insightEmployeeName = '';

    public array $insightDetails = [];

    public function mount()
    {
        $this->groupingMode = session('overtime_grouping_mode', 'department');
        $this->hasSelectedGroupingMode = session('overtime_grouping_mode_selected', false);
        $this->showOnboarding = ! session('approval_hub_onboarded', false);
    }

    public function setGroupingMode(string $mode)
    {
        $this->groupingMode = $mode;
        $this->hasSelectedGroupingMode = true;
        session(['overtime_grouping_mode' => $mode, 'overtime_grouping_mode_selected' => true]);
        $this->expandedGroups = []; // Reset expanded groups when switching grouping modes
        $this->selectedPackKeys = [];
        $this->bulkSelectionKey++;
    }

    public function setGroupingModeDepartment()
    {
        $this->setGroupingMode('department');
    }

    public function setGroupingModeCreator()
    {
        $this->setGroupingMode('creator');
    }

    public function setGroupingModeBranch()
    {
        $this->setGroupingMode('branch');
    }

    public function dismissOnboarding()
    {
        $this->showOnboarding = false;
        session(['approval_hub_onboarded' => true]);
    }

    public function expandAll()
    {
        $this->expandedGroups = [];
        $this->insights = []; // Reset insights to reload

        if ($this->groupingMode === 'department') {
            foreach ($this->groups as $deptId => $deptData) {
                $this->expandedGroups["dept-{$deptId}"] = true;
                foreach ($deptData['users'] as $userId => $userData) {
                    $this->expandedGroups["user-{$deptId}-{$userId}"] = true;
                    foreach ($userData['dates'] as $dateKey => $dateData) {
                        $groupKey = "date|department|{$deptId}|{$userId}|{$dateKey}";
                        $this->expandedGroups[$groupKey] = true;
                        $this->loadInsightsForGroup($groupKey);
                    }
                }
            }
        } elseif ($this->groupingMode === 'creator') {
            foreach ($this->groups as $userId => $userData) {
                $this->expandedGroups["user-{$userId}"] = true;
                foreach ($userData['departments'] as $deptId => $deptData) {
                    $this->expandedGroups["dept-{$userId}-{$deptId}"] = true;
                    foreach ($deptData['dates'] as $dateKey => $dateData) {
                        $groupKey = "date|creator|{$userId}|{$deptId}|{$dateKey}";
                        $this->expandedGroups[$groupKey] = true;
                        $this->loadInsightsForGroup($groupKey);
                    }
                }
            }
        } elseif ($this->groupingMode === 'branch') {
            // Branch mode: branch > dept > user > date
            foreach ($this->groups as $branch => $branchData) {
                $this->expandedGroups["branch-{$branch}"] = true;
                foreach ($branchData['departments'] as $deptId => $deptData) {
                    $this->expandedGroups["dept-{$branch}-{$deptId}"] = true;
                    foreach ($deptData['users'] as $userId => $userData) {
                        $this->expandedGroups["user-{$branch}-{$deptId}-{$userId}"] = true;
                        foreach ($userData['dates'] as $dateKey => $dateData) {
                            $groupKey = "date|branch|{$dateKey}|{$branch}|{$deptId}|{$userId}";
                            $this->expandedGroups[$groupKey] = true;
                            $this->loadInsightsForGroup($groupKey);
                        }
                    }
                }
            }
        }
    }

    public function collapseAll()
    {
        $this->expandedGroups = [];
        $this->insights = [];
    }

    public function toggleBulkSelection($mode, $id)
    {
        $keys = $this->getBulkKeys($id, $mode);
        $existing = array_intersect($this->selectedPackKeys, $keys);
        if (count($existing) === count($keys)) {
            // all selected, remove
            $this->selectedPackKeys = array_diff($this->selectedPackKeys, $keys);
        } else {
            // add
            $this->selectedPackKeys = array_unique(array_merge($this->selectedPackKeys, $keys));
        }
    }

    private function getBulkKeys($id, $mode)
    {
        $keys = [];
        if ($mode === 'department') {
            foreach ($this->groups[$id]['users'] as $userId => $userData) {
                foreach ($userData['dates'] as $dateKey => $dateData) {
                    $keys[] = "date|department|{$id}|{$userId}|{$dateKey}";
                }
            }
        } elseif ($mode === 'creator') {
            foreach ($this->groups[$id]['departments'] as $deptId => $deptData) {
                foreach ($deptData['dates'] as $dateKey => $dateData) {
                    $keys[] = "date|creator|{$id}|{$deptId}|{$dateKey}";
                }
            }
        } elseif ($mode === 'branch') {
            foreach ($this->groups[$id]['departments'] as $deptId => $deptData) {
                foreach ($deptData['users'] as $userId => $userData) {
                    foreach ($userData['dates'] as $dateKey => $dateData) {
                        $keys[] = "date|branch|{$dateKey}|{$id}|{$deptId}|{$userId}";
                    }
                }
            }
        }

        return $keys;
    }

    /**
     * Grouping algorithm:
     * Groups by selected mode: department (dept > user > date), creator (user > dept > date), branch (branch > dept > user > date).
     * Returns nested structure with summaries.
     */
    public function getGroupsProperty()
    {
        if (! $this->hasSelectedGroupingMode || empty($this->groupingMode)) {
            return collect();
        }

        $builder = new ApprovalHubQueryBuilder;
        $items = $builder->build(Auth::user())->get();

        // Apply filters
        if ($this->search) {
            $search = strtolower($this->search);
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->user->name), $search) ||
                       $item->details->contains(function ($detail) use ($search) {
                           return str_contains(strtolower($detail->name), $search) ||
                                  str_contains($detail->NIK, $search);
                       });
            });
        }

        // Compute overall totals from all items
        $this->totalForms = $items->count();
        $this->totalEmployees = $items->flatMap->details->pluck('NIK')->unique()->count();
        $this->totalHours = $items->sum('total_hours');
        $this->avgHours = $this->totalEmployees > 0 ? round($this->totalHours / $this->totalEmployees, 1) : 0.0;

        // Don't preload insights here - load them when groups are expanded to avoid conflicts

        $grouped = [];
        $totalGroups = 0;

        if ($this->groupingMode === 'department') {
            $deptGroups = $items->groupBy('dept_id');
            $totalGroups = $deptGroups->count();
            $perPage = 10; // Paginate departments
            $page = $this->getPage();
            $offset = ($page - 1) * $perPage;
            $paginatedDepts = $deptGroups->slice($offset, $perPage);

            foreach ($paginatedDepts as $deptId => $deptItems) {
                $dept = $deptItems->first()->department;
                $totalForms = $deptItems->count();
                $totalDetails = $deptItems->sum('total_details');
                $totalHours = $deptItems->sum('total_hours');
                $totalEmployees = $deptItems->flatMap->details->pluck('NIK')->unique()->count();

                $users = [];
                foreach ($deptItems->groupBy('user_id') as $userId => $userItems) {
                    $user = $userItems->first()->user;
                    $userTotalForms = $userItems->count();
                    $userTotalDetails = $userItems->sum('total_details');
                    $userTotalHours = $userItems->sum('total_hours');
                    $userTotalEmployees = $userItems->flatMap->details->pluck('NIK')->unique()->count();

                    $dates = $this->buildDateGroups($userItems);

                    $users[$userId] = [
                        'user' => $user,
                        'total_forms' => $userTotalForms,
                        'total_details' => $userTotalDetails,
                        'total_hours' => $userTotalHours,
                        'total_employees' => $userTotalEmployees,
                        'dates' => $dates,
                    ];
                }

                $grouped[$deptId] = [
                    'department' => $dept,
                    'total_forms' => $totalForms,
                    'total_details' => $totalDetails,
                    'total_hours' => $totalHours,
                    'total_employees' => $totalEmployees,
                    'users' => $users,
                ];
            }
        } elseif ($this->groupingMode === 'creator') {
            $userGroups = $items->groupBy('user_id');
            $totalGroups = $userGroups->count();
            $perPage = 10;
            $page = $this->getPage();
            $offset = ($page - 1) * $perPage;
            $paginatedUsers = $userGroups->slice($offset, $perPage);

            foreach ($paginatedUsers as $userId => $userItems) {
                $user = $userItems->first()->user;
                $totalForms = $userItems->count();
                $totalDetails = $userItems->sum('total_details');
                $totalHours = $userItems->sum('total_hours');
                $totalEmployees = $userItems->flatMap->details->pluck('NIK')->unique()->count();

                $depts = [];
                foreach ($userItems->groupBy('dept_id') as $deptId => $deptItems) {
                    $dept = $deptItems->first()->department;
                    $deptTotalForms = $deptItems->count();
                    $deptTotalDetails = $deptItems->sum('total_details');
                    $deptTotalHours = $deptItems->sum('total_hours');
                    $deptTotalEmployees = $deptItems->flatMap->details->pluck('NIK')->unique()->count();

                    $dates = $this->buildDateGroups($deptItems);

                    $depts[$deptId] = [
                        'department' => $dept,
                        'total_forms' => $deptTotalForms,
                        'total_details' => $deptTotalDetails,
                        'total_hours' => $deptTotalHours,
                        'total_employees' => $deptTotalEmployees,
                        'dates' => $dates,
                    ];
                }

                $grouped[$userId] = [
                    'user' => $user,
                    'total_forms' => $totalForms,
                    'total_details' => $totalDetails,
                    'total_hours' => $totalHours,
                    'total_employees' => $totalEmployees,
                    'departments' => $depts,
                ];
            }
        } elseif ($this->groupingMode === 'branch') {
            $branchGroups = $items->groupBy('branch');
            $totalGroups = $branchGroups->count();
            $perPage = 10;
            $page = $this->getPage();
            $offset = ($page - 1) * $perPage;
            $paginatedBranches = $branchGroups->slice($offset, $perPage);

            foreach ($paginatedBranches as $branch => $branchItems) {
                $totalForms = $branchItems->count();
                $totalDetails = $branchItems->sum('total_details');
                $totalHours = $branchItems->sum('total_hours');
                $totalEmployees = $branchItems->flatMap->details->pluck('NIK')->unique()->count();

                $depts = [];
                foreach ($branchItems->groupBy('dept_id') as $deptId => $deptItems) {
                    $dept = $deptItems->first()->department;
                    $deptTotalForms = $deptItems->count();
                    $deptTotalDetails = $deptItems->sum('total_details');
                    $deptTotalHours = $deptItems->sum('total_hours');
                    $deptTotalEmployees = $deptItems->flatMap->details->pluck('NIK')->unique()->count();

                    $users = [];
                    foreach ($deptItems->groupBy('user_id') as $userId => $userItems) {
                        $user = $userItems->first()->user;
                        $userTotalForms = $userItems->count();
                        $userTotalDetails = $userItems->sum('total_details');
                        $userTotalHours = $userItems->sum('total_hours');
                        $userTotalEmployees = $userItems->flatMap->details->pluck('NIK')->unique()->count();

                        $dates = $this->buildDateGroups($userItems);

                        $users[$userId] = [
                            'user' => $user,
                            'total_forms' => $userTotalForms,
                            'total_details' => $userTotalDetails,
                            'total_hours' => $userTotalHours,
                            'total_employees' => $userTotalEmployees,
                            'dates' => $dates,
                        ];
                    }

                    $depts[$deptId] = [
                        'department' => $dept,
                        'total_forms' => $deptTotalForms,
                        'total_details' => $deptTotalDetails,
                        'total_hours' => $deptTotalHours,
                        'total_employees' => $deptTotalEmployees,
                        'users' => $users,
                    ];
                }

                $grouped[$branch] = [
                    'branch' => $branch,
                    'total_forms' => $totalForms,
                    'total_details' => $totalDetails,
                    'total_hours' => $totalHours,
                    'total_employees' => $totalEmployees,
                    'departments' => $depts,
                ];
            }
        }

        // Create paginator
        $perPage = 10;
        $currentPage = $this->getPage();
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $grouped,
            $totalGroups,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        $paginated->appends(request()->query());

        return $paginated;
    }

    private function buildDateGroups($items)
    {
        $dates = [];
        foreach ($items->groupBy(function ($item) {
            $date = $item->first_overtime_date;
            if ($date instanceof \DateTimeInterface) {
                $date = $date->format('Y-m-d');
            }

            return $date ?: 'unknown';
        }) as $dateKey => $dateItems) {
            $dateTotalForms = $dateItems->count();
            $dateTotalDetails = $dateItems->sum('total_details');
            $dateTotalHours = $dateItems->sum('total_hours');
            $dateTotalEmployees = $dateItems->flatMap->details->pluck('NIK')->unique()->count();

            $dates[$dateKey] = [
                'forms' => $dateItems,
                'total_forms' => $dateTotalForms,
                'total_details' => $dateTotalDetails,
                'total_hours' => $dateTotalHours,
                'total_employees' => $dateTotalEmployees,
            ];
        }

        return $dates;
    }

    public function toggleGroup(string $groupKey): void
    {
        if (str_starts_with($groupKey, 'date|')) {
            $parts = explode('|', $groupKey);
            $mode = $parts[1];

            $groupData = null;
            if ($mode === 'department') {
                $deptId = $parts[2];
                $userId = $parts[3];
                $dateKey = $parts[4];
                $groupData = $this->groups[$deptId]['users'][$userId]['dates'][$dateKey] ?? null;
            } elseif ($mode === 'creator') {
                $userId = $parts[2];
                $deptId = $parts[3];
                $dateKey = $parts[4];
                $groupData = $this->groups[$userId]['departments'][$deptId]['dates'][$dateKey] ?? null;
            } elseif ($mode === 'branch') {
                $dateKey = $parts[2];
                $branch = $parts[3];
                $deptId = $parts[4];
                $userId = $parts[5];
                $groupData = $this->groups[$branch]['departments'][$deptId]['users'][$userId]['dates'][$dateKey] ?? null;
            }

            if (! $groupData) {
                return;
            }

            if (isset($this->expandedGroups[$groupKey])) {
                unset($this->expandedGroups[$groupKey]);
            } else {
                $this->expandedGroups[$groupKey] = true;
            }

            // Load insights if it's a date group
            if (isset($this->expandedGroups[$groupKey])) {
                $this->loadInsightsForGroup($groupKey);
            }
        } elseif (str_starts_with($groupKey, 'dept-') || str_starts_with($groupKey, 'user-') || str_starts_with($groupKey, 'branch-')) {
            if (isset($this->expandedGroups[$groupKey])) {
                unset($this->expandedGroups[$groupKey]);
            } else {
                $this->expandedGroups[$groupKey] = true;
                // Load insights for all child date groups when parent is expanded
                $this->loadInsightsForChildGroups($groupKey);
            }
        }
    }

    private function loadInsightsForChildGroups(string $groupKey): void
    {
        // Load insights for all child date groups when a parent group is expanded
        $childGroups = $this->getChildDateGroups($groupKey);

        foreach ($childGroups as $childGroupKey) {
            $this->loadInsightsForGroup($childGroupKey);
        }
    }

    private function getChildDateGroups(string $groupKey): array
    {
        $childGroups = [];

        if (str_starts_with($groupKey, 'dept-')) {
            $deptId = str_replace('dept-', '', $groupKey);
            if (isset($this->groups[$deptId])) {
                foreach ($this->groups[$deptId]['users'] as $userId => $userData) {
                    foreach ($userData['dates'] as $dateKey => $dateData) {
                        $childGroups[] = "date|department|{$deptId}|{$userId}|{$dateKey}";
                    }
                }
            }
        } elseif (str_starts_with($groupKey, 'user-')) {
            $userId = str_replace('user-', '', $groupKey);
            if ($this->groupingMode === 'creator') {
                if (isset($this->groups[$userId])) {
                    foreach ($this->groups[$userId]['departments'] as $deptId => $deptData) {
                        foreach ($deptData['dates'] as $dateKey => $dateData) {
                            $childGroups[] = "date|creator|{$userId}|{$deptId}|{$dateKey}";
                        }
                    }
                }
            } elseif ($this->groupingMode === 'department') {
                $parts = explode('-', $groupKey);
                if (count($parts) === 3) {
                    $deptId = $parts[1];
                    $userId = $parts[2];
                    if (isset($this->groups[$deptId]['users'][$userId])) {
                        foreach ($this->groups[$deptId]['users'][$userId]['dates'] as $dateKey => $dateData) {
                            $childGroups[] = "date|department|{$deptId}|{$userId}|{$dateKey}";
                        }
                    }
                }
            }
        } elseif (str_starts_with($groupKey, 'branch-')) {
            $branch = str_replace('branch-', '', $groupKey);
            if (isset($this->groups[$branch])) {
                foreach ($this->groups[$branch]['departments'] as $deptId => $deptData) {
                    foreach ($deptData['users'] as $userId => $userData) {
                        foreach ($userData['dates'] as $dateKey => $dateData) {
                            $childGroups[] = "date|branch|{$dateKey}|{$branch}|{$deptId}|{$userId}";
                        }
                    }
                }
            }
        }

        return $childGroups;
    }

    private function loadInsightsForGroup(string $groupKey): void
    {
        if (! str_starts_with($groupKey, 'date|')) {
            return;
        }

        $parts = explode('|', $groupKey);
        $mode = $parts[1];

        $groupData = null;
        $date = null;
        if ($mode === 'department') {
            $deptId = $parts[2];
            $userId = $parts[3];
            $dateKey = $parts[4];
            $groupData = $this->groups[$deptId]['users'][$userId]['dates'][$dateKey] ?? null;
            $date = \Carbon\Carbon::parse($dateKey);
        } elseif ($mode === 'creator') {
            $userId = $parts[2];
            $deptId = $parts[3];
            $dateKey = $parts[4];
            $groupData = $this->groups[$userId]['departments'][$deptId]['dates'][$dateKey] ?? null;
            $date = \Carbon\Carbon::parse($dateKey);
        } elseif ($mode === 'branch') {
            $dateKey = $parts[2];
            $branch = $parts[3];
            $deptId = $parts[4];
            $userId = $parts[5];
            $groupData = $this->groups[$branch]['departments'][$deptId]['users'][$userId]['dates'][$dateKey] ?? null;
            $date = \Carbon\Carbon::parse($dateKey);
        } else {
            return;
        }

        if (! $groupData) {
            return;
        }

        $niks = collect($groupData['forms'])->flatMap(fn ($h) => $h->details->pluck('NIK'))->unique()->toArray();
        $repo = app(EmployeeRepository::class);
        $newInsights = $repo->getApprovalInsights($niks, $date);

        $this->insights = array_merge($this->insights, $newInsights);
    }

    public function approvePack(string $groupKey): void
    {
        if (! str_starts_with($groupKey, 'date|')) {
            return;
        }

        $parts = explode('|', $groupKey);
        $mode = $parts[1];

        $user = Auth::user();
        $query = (new ApprovalHubQueryBuilder)->build($user);

        if ($mode === 'department') {
            $deptId = $parts[2];
            $userId = $parts[3];
            $dateKey = $parts[4];
            $query->where('header_form_overtime.dept_id', $deptId)
                ->where('header_form_overtime.user_id', $userId)
                ->whereRaw('(SELECT MIN(d.start_date) FROM detail_form_overtime d WHERE d.header_id = header_form_overtime.id) = ?', [$dateKey]);
        } elseif ($mode === 'creator') {
            $userId = $parts[2];
            $deptId = $parts[3];
            $dateKey = $parts[4];
            $query->where('header_form_overtime.user_id', $userId)
                ->where('header_form_overtime.dept_id', $deptId)
                ->whereRaw('(SELECT MIN(d.start_date) FROM detail_form_overtime d WHERE d.header_id = header_form_overtime.id) = ?', [$dateKey]);
        } elseif ($mode === 'branch') {
            $dateKey = $parts[2];
            $branch = $parts[3];
            $deptId = $parts[4];
            $userId = $parts[5];
            $query->where('header_form_overtime.branch', $branch)
                ->where('header_form_overtime.dept_id', $deptId)
                ->where('header_form_overtime.user_id', $userId)
                ->whereRaw('(SELECT MIN(d.start_date) FROM detail_form_overtime d WHERE d.header_id = header_form_overtime.id) = ?', [$dateKey]);
        }

        $forms = $query->get();
        $ids = $forms->pluck('id')->toArray();

        if (empty($ids)) {
            $this->dispatch('flash', type: 'error', message: 'Pack not found or already approved.');

            return;
        }

        $result = $this->processBulkApproval($ids);
        $this->resetPage();

        if ($result['success'] > 0) {
            $this->dispatch('flash', type: 'success', message: 'Pack approved successfully.');
            $this->js('$wire.$refresh()');
        } elseif ($result['fail'] > 0) {
            $this->dispatch('flash', type: 'error', message: 'Failed to approve pack.');
            $this->js('$wire.$refresh()');
        }
    }

    public function approveSelected(): void
    {
        $allIds = [];
        foreach ($this->selectedPackKeys as $key) {
            if (! str_starts_with($key, 'date|')) {
                continue;
            }
            $parts = explode('|', $key);
            if (count($parts) < 5) {
                continue;
            }

            $groupData = null;
            $mode = $parts[1];
            if ($mode === 'department') {
                $deptId = $parts[2];
                $userId = $parts[3];
                $dateKey = $parts[4];
                $groupData = $this->groups[$deptId]['users'][$userId]['dates'][$dateKey] ?? null;
            } elseif ($mode === 'creator') {
                $userId = $parts[2];
                $deptId = $parts[3];
                $dateKey = $parts[4];
                $groupData = $this->groups[$userId]['departments'][$deptId]['dates'][$dateKey] ?? null;
            } elseif ($mode === 'branch') {
                $dateKey = $parts[2];
                $branch = $parts[3];
                $deptId = $parts[4];
                $userId = $parts[5];
                $groupData = $this->groups[$branch]['departments'][$deptId]['users'][$userId]['dates'][$dateKey] ?? null;
            }

            if ($groupData) {
                $allIds = array_merge($allIds, collect($groupData['forms'])->pluck('id')->toArray());
            }
        }

        if (empty($allIds)) {
            $this->dispatch('flash', type: 'error', message: 'No items selected.');

            return;
        }

        $result = $this->processBulkApproval($allIds);
        $this->selectedPackKeys = [];
        $this->resetPage();

        $msg = "Successfully approved {$result['success']} requests.";
        if ($result['fail'] > 0) {
            $msg .= " Failed to approve {$result['fail']} items (likely permission/state mismatch).";
        }

        $this->dispatch('flash', type: $result['fail'] > 0 ? 'warning' : 'success', message: $msg);
        $this->js('$wire.$refresh()');
    }

    public function confirmReject(): void
    {
        $this->rejectSelected();
    }

    public function rejectSelected(): void
    {
        $allIds = [];
        foreach ($this->selectedPackKeys as $key) {
            if (! str_starts_with($key, 'date|')) {
                continue;
            }
            $parts = explode('|', $key);
            if (count($parts) < 5) {
                continue;
            }

            $groupData = null;
            $mode = $parts[1];
            if ($mode === 'department') {
                $deptId = $parts[2];
                $userId = $parts[3];
                $dateKey = $parts[4];
                $groupData = $this->groups[$deptId]['users'][$userId]['dates'][$dateKey] ?? null;
            } elseif ($mode === 'creator') {
                $userId = $parts[2];
                $deptId = $parts[3];
                $dateKey = $parts[4];
                $groupData = $this->groups[$userId]['departments'][$deptId]['dates'][$dateKey] ?? null;
            } elseif ($mode === 'branch') {
                $dateKey = $parts[2];
                $branch = $parts[3];
                $deptId = $parts[4];
                $userId = $parts[5];
                $groupData = $this->groups[$branch]['departments'][$deptId]['users'][$userId]['dates'][$dateKey] ?? null;
            }

            if ($groupData) {
                $allIds = array_merge($allIds, collect($groupData['forms'])->pluck('id')->toArray());
            }
        }

        if (empty($allIds)) {
            $this->dispatch('flash', type: 'error', message: 'No items selected.');

            return;
        }

        $result = $this->processBulkRejection($allIds, $this->rejectReason);
        $this->selectedPackKeys = [];
        $this->showRejectModal = false;
        $this->rejectReason = '';
        $this->resetPage();

        $msg = "Successfully rejected {$result['success']} requests.";
        if ($result['fail'] > 0) {
            $msg .= " Failed to reject {$result['fail']} items (likely permission/state mismatch).";
        }

        $this->dispatch('flash', type: $result['fail'] > 0 ? 'warning' : 'success', message: $msg);
        $this->js('$wire.$refresh()');
    }

    private function processBulkApproval(array $ids): array
    {
        $engine = app(ApprovalEngine::class);
        $user = Auth::user();
        $successCount = 0;
        $failCount = 0;

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $form = \App\Domain\Overtime\Models\OvertimeForm::find($id);
                if (! $form) {
                    continue;
                }

                try {
                    $engine->approve($form, $user->id, 'Bulk approved via Hub');
                    $successCount++;
                } catch (\Exception $e) {
                    $failCount++;
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return ['success' => $successCount, 'fail' => $failCount];
    }

    private function processBulkRejection(array $ids, string $reason): array
    {
        $engine = app(ApprovalEngine::class);
        $user = Auth::user();
        $successCount = 0;
        $failCount = 0;

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $form = \App\Domain\Overtime\Models\OvertimeForm::find($id);
                if (! $form) {
                    continue;
                }

                try {
                    $engine->reject($form, $user->id, $reason);
                    $successCount++;
                } catch (\Exception $e) {
                    $failCount++;
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return ['success' => $successCount, 'fail' => $failCount];
    }

    public function showInsightDetails(string $nik, string $name): void
    {
        $this->insightEmployeeNik = $nik;
        $this->insightEmployeeName = $name;
        $this->showInsightModal = true;

        // Load detailed insights
        $this->insightDetails = $this->getDetailedInsights($nik);
    }

    public function closeInsightModal(): void
    {
        $this->showInsightModal = false;
        $this->insightEmployeeNik = '';
        $this->insightEmployeeName = '';
        $this->insightDetails = [];
    }

    public function toggleGroupDepartment(string $identifier): void
    {
        $groupKey = "dept-{$identifier}";
        if (isset($this->expandedGroups[$groupKey])) {
            unset($this->expandedGroups[$groupKey]);
        } else {
            $this->expandedGroups[$groupKey] = true;
            $this->loadInsightsForChildGroups($groupKey);
        }
    }

    public function toggleGroupUser(string $identifier): void
    {
        $groupKey = "user-{$identifier}";
        if (isset($this->expandedGroups[$groupKey])) {
            unset($this->expandedGroups[$groupKey]);
        } else {
            $this->expandedGroups[$groupKey] = true;
            $this->loadInsightsForChildGroups($groupKey);
        }
    }

    public function toggleGroupBranch(string $branch): void
    {
        $groupKey = "branch-{$branch}";
        if (isset($this->expandedGroups[$groupKey])) {
            unset($this->expandedGroups[$groupKey]);
        } else {
            $this->expandedGroups[$groupKey] = true;
            $this->loadInsightsForChildGroups($groupKey);
        }
    }

    public function getBulkSelectionInfoProperty(): array
    {
        if (empty($this->selectedPackKeys)) {
            return ['message' => '', 'count' => 0, 'scope' => ''];
        }

        $count = count($this->selectedPackKeys);

        // Analyze all selected keys to understand the scope
        $entityCounts = [
            'departments' => [],
            'creators' => [],
            'branches' => [],
        ];

        $scope = 'items';
        $hasMixedSelection = false;

        foreach ($this->selectedPackKeys as $key) {
            $parts = explode('|', $key);

            if (str_starts_with($key, 'date|')) {
                $mode = $parts[1] ?? '';
                switch ($mode) {
                    case 'department':
                        $deptId = $parts[2] ?? null;
                        if ($deptId && isset($this->groups[$deptId])) {
                            $deptName = $this->groups[$deptId]['department']->name ?? 'Unknown Department';
                            $entityCounts['departments'][$deptId] = $deptName;
                        }
                        $scope = 'department overtime entries';
                        break;
                    case 'creator':
                        $userId = $parts[2] ?? null;
                        if ($userId && $this->groupingMode === 'creator' && isset($this->groups[$userId])) {
                            $userName = $this->groups[$userId]['user']->name ?? 'Unknown Creator';
                            $entityCounts['creators'][$userId] = $userName;
                        }
                        $scope = 'creator overtime entries';
                        $hasMixedSelection = count($entityCounts['departments']) > 0;
                        break;
                    case 'branch':
                        $branch = $parts[3] ?? null;
                        if ($branch) {
                            $entityCounts['branches'][$branch] = $branch;
                        }
                        $scope = 'branch overtime entries';
                        $hasMixedSelection = count($entityCounts['departments']) > 0 || count($entityCounts['creators']) > 0;
                        break;
                }
            } elseif (str_starts_with($key, 'dept-')) {
                $deptId = str_replace('dept-', '', $key);
                if (isset($this->groups[$deptId])) {
                    $deptName = $this->groups[$deptId]['department']->name ?? 'Unknown Department';
                    $entityCounts['departments'][$deptId] = $deptName;
                }
                $scope = 'entire departments';
            } elseif (str_starts_with($key, 'user-')) {
                $userPart = str_replace('user-', '', $key);
                $userParts = explode('-', $userPart);
                $userId = $userParts[0] ?? null;
                // Find the user name from the groups structure
                foreach ($this->groups as $group) {
                    if (isset($group['users']) && isset($group['users'][$userId])) {
                        $userName = $group['users'][$userId]['user']->name ?? 'Unknown Creator';
                        $entityCounts['creators'][$userId] = $userName;
                        break;
                    }
                }
                $scope = 'entire users/creators';
                $hasMixedSelection = count($entityCounts['departments']) > 0;
            } elseif (str_starts_with($key, 'branch-')) {
                $branch = str_replace('branch-', '', $key);
                $entityCounts['branches'][$branch] = $branch;
                $scope = 'entire branches';
                $hasMixedSelection = count($entityCounts['departments']) > 0 || count($entityCounts['creators']) > 0;
            }
        }

        // Generate contextual message
        $message = "You're selecting {$count} {$scope}";

        // Add entity context
        $entityParts = [];
        if (count($entityCounts['departments']) > 0) {
            $deptCount = count($entityCounts['departments']);
            if ($deptCount === 1) {
                $entityParts[] = '1 department (' . reset($entityCounts['departments']) . ')';
            } else {
                $entityParts[] = "{$deptCount} departments";
            }
        }
        if (count($entityCounts['creators']) > 0) {
            $creatorCount = count($entityCounts['creators']);
            if ($creatorCount === 1) {
                $entityParts[] = '1 creator (' . reset($entityCounts['creators']) . ')';
            } else {
                $entityParts[] = "{$creatorCount} creators";
            }
        }
        if (count($entityCounts['branches']) > 0) {
            $branchCount = count($entityCounts['branches']);
            if ($branchCount === 1) {
                $entityParts[] = '1 branch (' . reset($entityCounts['branches']) . ')';
            } else {
                $entityParts[] = "{$branchCount} branches";
            }
        }

        if (! empty($entityParts)) {
            if ($hasMixedSelection || count($entityParts) > 1) {
                $message .= ' across ' . implode(' and ', $entityParts);
            } else {
                $message .= ' from ' . reset($entityParts);
            }
        }

        // Determine primary entity info for backward compatibility
        $entityName = '';
        $entityType = '';
        if (count($entityCounts['departments']) === 1) {
            $entityName = reset($entityCounts['departments']);
            $entityType = 'department';
        } elseif (count($entityCounts['creators']) === 1) {
            $entityName = reset($entityCounts['creators']);
            $entityType = 'creator';
        } elseif (count($entityCounts['branches']) === 1) {
            $entityName = reset($entityCounts['branches']);
            $entityType = 'branch';
        }

        return [
            'message' => $message,
            'count' => $count,
            'scope' => $scope,
            'entity_name' => $entityName,
            'entity_type' => $entityType,
            'entity_counts' => [
                'departments' => count($entityCounts['departments']),
                'creators' => count($entityCounts['creators']),
                'branches' => count($entityCounts['branches']),
            ],
        ];
    }

    private function getDetailedInsights(string $nik): array
    {
        $repo = app(\App\Infrastructure\Persistence\Eloquent\Repositories\EloquentEmployeeRepository::class);

        // Current month data
        $currentMonth = \Carbon\Carbon::now();
        $currentMonthData = $repo->getApprovalInsights([$nik], $currentMonth);
        $currentMonthInsights = $currentMonthData[$nik] ?? ['monthly_hours' => 0, 'active_days' => 0];

        // Monthly breakdown for last 6 months
        $monthlyBreakdown = [];
        for ($i = 0; $i < 6; $i++) {
            $month = \Carbon\Carbon::now()->subMonths($i);
            $monthData = $repo->getApprovalInsights([$nik], $month);
            $insights = $monthData[$nik] ?? ['monthly_hours' => 0, 'active_days' => 0];

            $monthlyBreakdown[] = [
                'month' => $month->format('M Y'),
                'hours' => $insights['monthly_hours'],
                'days' => $insights['active_days'],
            ];
        }

        // Recent activity (last 10 overtime records)
        $recentActivity = \DB::table('detail_form_overtime as d')
            ->join('header_form_overtime as h', 'd.header_id', '=', 'h.id')
            ->where('d.NIK', $nik)
            ->where('h.status', '!=', 'Draft') // Include approved, rejected, etc.
            ->select(
                'd.overtime_date',
                \DB::raw('TIMESTAMPDIFF(MINUTE, CONCAT(d.start_date, " ", d.start_time), CONCAT(d.end_date, " ", d.end_time)) - IFNULL(d.break, 0) as minutes'),
                'h.status'
            )
            ->orderBy('d.overtime_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($record) {
                return [
                    'date' => $record->overtime_date,
                    'hours' => round($record->minutes / 60, 1),
                    'status' => $record->status,
                ];
            })
            ->toArray();

        return [
            'current_month' => [
                'hours' => $currentMonthInsights['monthly_hours'],
                'days' => $currentMonthInsights['active_days'],
            ],
            'monthly_breakdown' => $monthlyBreakdown,
            'recent_activity' => $recentActivity,
        ];
    }

    public function render()
    {
        return view('livewire.overtime.approval-hub', [
            'groups' => $this->groups,
        ]);
    }
}
