<?php

namespace App\Livewire\Admin\Approvals;

use App\Infrastructure\Persistence\Eloquent\Models\RuleStepTemplate;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class RuleManager extends Component
{
    use WithPagination;

    // ====== Filters & UI ======
    public string $search = '';

    public string $statusFilter = 'all'; // 'all', 'active', 'inactive'

    public ?string $modelTypeFilter = null; // null for all, or specific model type

    public bool $groupByModel = false;

    public array $selectedRules = [];

    public ?int $selectedRuleId = null;

    // ====== Rule form ======
    public ?int $editingRuleId = null;

    public string $rule_model_type = '';

    public ?string $rule_code = '';

    public string $rule_name = '';

    public bool $rule_active = true;

    public int $rule_priority = 10;

    public string $rule_match_expr_raw = '{}';

    public bool $showRuleModal = false;

    // ====== View settings ======
    public string $viewMode = 'visual'; // 'visual' | 'compact'

    public bool $showJsonViewer = false;

    public string $version_notes = '';

    public bool $forceNewVersion = false;

    // ====== Step form ======
    public ?int $editingStepId = null;

    public int $step_sequence = 1;

    public string $step_approver_type = 'user'; // 'user' | 'role'

    public ?int $step_approver_id = null;

    public bool $step_final = false;

    public bool $step_parallel_group = false;

    public bool $showStepModal = false;

    protected $rules = [
        'rule_model_type' => ['required', 'string', 'max:255'],
        'rule_code' => ['required', 'string', 'max:50'],
        'rule_name' => ['required', 'string', 'max:255'],
        'rule_active' => ['boolean'],
        'rule_priority' => ['integer', 'min:0'],
        'rule_match_expr_raw' => ['nullable', 'string'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedModelTypeFilter(): void
    {
        $this->resetPage();
    }

    public function toggleGroupByModel(): void
    {
        $this->groupByModel = ! $this->groupByModel;
    }

    public function setModelTypeFilter(?string $modelType): void
    {
        $this->modelTypeFilter = $modelType;
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function toggleRuleSelection(int $ruleId): void
    {
        if (in_array($ruleId, $this->selectedRules)) {
            $this->selectedRules = array_diff($this->selectedRules, [$ruleId]);
        } else {
            $this->selectedRules[] = $ruleId;
        }
    }

    public function selectAllRules(): void
    {
        $this->selectedRules = $this->getFilteredRules()->pluck('id')->toArray();
    }

    public function clearSelection(): void
    {
        $this->selectedRules = [];
    }

    public function bulkActivate(): void
    {
        RuleTemplate::whereIn('id', $this->selectedRules)->update(['active' => true]);
        $this->selectedRules = [];
        session()->flash('success', 'Selected rules activated.');
    }

    public function bulkDeactivate(): void
    {
        RuleTemplate::whereIn('id', $this->selectedRules)->update(['active' => false]);
        $this->selectedRules = [];
        session()->flash('success', 'Selected rules deactivated.');
    }

    public function updatedShowRuleModal($value): void
    {
        if (! $value) {
            $this->resetRuleForm();
        }
    }

    public function updatedShowStepModal($value): void
    {
        if (! $value) {
            $this->resetStepForm();
        }
    }

    // ====== Rule CRUD ======

    public function openCreateRule(): void
    {
        $this->resetRuleForm();
        $this->editingRuleId = null;
        $this->rule_active = true;
        $this->rule_match_expr_raw = '{}';
        $this->showRuleModal = true;
    }

    public function openEditRule(int $id): void
    {
        $rule = RuleTemplate::findOrFail($id);

        $this->editingRuleId = $rule->id;
        $this->rule_model_type = $rule->model_type;
        $this->rule_code = $rule->code;
        $this->rule_name = $rule->name;
        $this->rule_active = (bool) $rule->active;
        $this->rule_priority = (int) $rule->priority;
        $this->rule_match_expr_raw = json_encode($rule->match_expr ?? [], JSON_PRETTY_PRINT);

        $this->selectedRuleId ??= $rule->id;

        $this->showRuleModal = true;
    }

    public function saveRule(): void
    {
        $this->validate();

        // Validate JSON
        $matchExpr = [];
        if (trim($this->rule_match_expr_raw) !== '') {
            try {
                $matchExpr = json_decode($this->rule_match_expr_raw, true, 512, JSON_THROW_ON_ERROR);
                if (! is_array($matchExpr)) {
                    throw new \RuntimeException('match_expr must be a JSON object.');
                }
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'rule_match_expr_raw' => 'Invalid JSON: ' . $e->getMessage(),
                ]);
            }
        }

        $data = [
            'model_type' => $this->rule_model_type,
            'code' => $this->rule_code,
            'name' => $this->rule_name,
            'active' => $this->rule_active,
            'priority' => $this->rule_priority,
            'match_expr' => $matchExpr,
            'version_notes' => $this->version_notes ?? null,
        ];

        if ($this->editingRuleId) {
            // VERSIONING: Create new version instead of updating
            $currentRule = RuleTemplate::findOrFail($this->editingRuleId);

            // Check if there are active approval requests using any version of this rule
            $activeRequestsCount = \App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::where('rule_template_id', $currentRule->version_uuid)
                ->whereNotIn('status', ['APPROVED', 'CANCELLED', 'REJECTED'])
                ->count();

            if ($activeRequestsCount > 0 && ! $this->forceNewVersion) {
                // Warn user that active requests exist
                $this->dispatch('confirm-new-version', [
                    'ruleId' => $currentRule->id,
                    'ruleName' => $currentRule->name,
                    'activeRequestsCount' => $activeRequestsCount,
                    'data' => $data,
                ]);

                return;
            }

            // Create new version
            $rule = $currentRule->createNewVersion($data, auth()->id());
            session()->flash('success', "New version (v{$rule->version_number}) created successfully.");
        } else {
            // Creating new rule - version_uuid will be auto-generated by the trait
            $data['created_by'] = auth()->id();
            $rule = RuleTemplate::create($data);
            session()->flash('success', 'Rule created.');
        }

        $this->selectedRuleId = $rule->id;
        $this->showRuleModal = false;
        $this->resetRuleForm();
        $this->version_notes = '';
        $this->forceNewVersion = false;
    }

    /**
     * Force create new version even with active requests
     */
    public function forceCreateNewVersion(int $ruleId, array $data): void
    {
        $currentRule = RuleTemplate::findOrFail($ruleId);
        $rule = $currentRule->createNewVersion($data, auth()->id());

        $this->selectedRuleId = $rule->id;
        session()->flash('success', "New version (v{$rule->version_number}) created. Active requests still use old version.");
    }

    public function deleteRule(int $id): void
    {
        $rule = RuleTemplate::findOrFail($id);

        // Check if there are any active approval requests using any version of this rule
        $activeRequestsCount = \App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::where('rule_template_id', $rule->version_uuid)
            ->whereNotIn('status', ['APPROVED', 'CANCELLED', 'REJECTED'])
            ->count();

        if ($activeRequestsCount > 0) {
            // Show warning and ask for confirmation
            $this->dispatch('confirm-delete-rule', [
                'ruleId' => $id,
                'ruleName' => $rule->name,
                'activeRequestsCount' => $activeRequestsCount,
            ]);

            return;
        }

        // No active requests, proceed with soft deletion
        $this->performRuleSoftDeletion($rule);
    }

    public function forceDeleteRule(int $id): void
    {
        $rule = RuleTemplate::findOrFail($id);
        $this->performRuleSoftDeletion($rule);
    }

    private function performRuleSoftDeletion(RuleTemplate $rule): void
    {
        // Soft delete steps first (cascading)
        $rule->steps()->delete();

        // Soft delete the rule
        $rule->delete();

        if ($this->selectedRuleId === $rule->id) {
            $this->selectedRuleId = null;
        }

        session()->flash('success', 'Rule soft-deleted. It can be restored if needed.');
    }

    public function restoreRule(int $id): void
    {
        $rule = RuleTemplate::withTrashed()->findOrFail($id);

        // Restore steps first
        $rule->steps()->restore();

        // Restore the rule
        $rule->restore();

        session()->flash('success', 'Rule restored.');
    }

    public function forceDeleteRulePermanently(int $id): void
    {
        $rule = RuleTemplate::withTrashed()->findOrFail($id);

        // Permanently delete steps first
        $rule->steps()->forceDelete();

        // Permanently delete the rule
        $rule->forceDelete();

        session()->flash('success', 'Rule permanently deleted.');
    }

    private function resetRuleForm(): void
    {
        $this->reset([
            'editingRuleId',
            'rule_model_type',
            'rule_code',
            'rule_name',
            'rule_active',
            'rule_priority',
            'rule_match_expr_raw',
        ]);
    }

    public function selectRule(int $id): void
    {
        $this->selectedRuleId = $id;
        $this->resetStepForm();
    }

    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'visual' ? 'compact' : 'visual';
    }

    // ====== Steps CRUD ======

    public function openCreateStep(): void
    {
        if (! $this->selectedRuleId) {
            session()->flash('error', 'Select a rule first.');

            return;
        }

        $this->resetStepForm();
        $this->editingStepId = null;
        $this->showStepModal = true;
    }

    public function openEditStep(int $stepId): void
    {
        $step = RuleStepTemplate::findOrFail($stepId);

        $this->editingStepId = $step->id;
        $this->step_sequence = (int) $step->sequence;
        $this->step_approver_type = $step->approver_type;
        $this->step_approver_id = $step->approver_id;
        $this->step_final = (bool) $step->final;
        $this->step_parallel_group = (bool) $step->parallel_group;

        $this->showStepModal = true;
    }

    public function saveStep(): void
    {
        if (! $this->selectedRuleId) {
            session()->flash('error', 'Select a rule first.');

            return;
        }

        $this->validateStep();

        $data = [
            'rule_template_id' => $this->selectedRuleId,
            'sequence' => $this->step_sequence,
            'approver_type' => $this->step_approver_type,
            'approver_id' => $this->step_approver_id,
            'final' => $this->step_final,
            'parallel_group' => $this->step_parallel_group,
        ];

        if ($this->editingStepId) {
            $step = RuleStepTemplate::findOrFail($this->editingStepId);
            $step->update($data);
            session()->flash('success', 'Step updated.');
        } else {
            RuleStepTemplate::create($data);
            session()->flash('success', 'Step created.');
        }

        $this->showStepModal = false;
        $this->resetStepForm();
    }

    public function deleteStep(int $stepId): void
    {
        $step = RuleStepTemplate::findOrFail($stepId);
        $step->delete();

        session()->flash('success', 'Step deleted.');
    }

    private function validateStep(): void
    {
        $this->validate([
            'step_sequence' => ['required', 'integer', 'min:1'],
            'step_approver_type' => ['required', 'in:user,role'],
            'step_approver_id' => ['nullable', 'integer', 'min:1'],
            'step_final' => ['boolean'],
            'step_parallel_group' => ['boolean'],
        ]);
    }

    private function resetStepForm(): void
    {
        $this->reset([
            'editingStepId',
            'step_sequence',
            'step_approver_type',
            'step_approver_id',
            'step_final',
            'step_parallel_group',
        ]);
        $this->step_sequence = 1;
        $this->step_approver_type = 'user';
    }

    // ====== Render ======

    public function render()
    {
        // Get available model types for filtering
        $availableModelTypes = RuleTemplate::distinct('model_type')->pluck('model_type')->sort();

        // Build filtered query
        $rulesQuery = RuleTemplate::query()
            ->withCount('steps')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($query) {
                    $query->where('code', 'like', '%' . $this->search . '%')
                        ->orWhere('name', 'like', '%' . $this->search . '%')
                        ->orWhere('model_type', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('active', $this->statusFilter === 'active');
            })
            ->when($this->modelTypeFilter, function ($q) {
                $q->where('model_type', $this->modelTypeFilter);
            })
            ->orderBy('priority')
            ->orderBy('code');

        // Group rules by model type if requested
        $groupedRules = [];
        $rules = [];

        if ($this->groupByModel) {
            $allRules = $rulesQuery->get();
            $groupedRules = $allRules->groupBy('model_type')->sortKeys();
            $rules = $allRules; // For pagination compatibility
        } else {
            $rules = $rulesQuery->paginate(10);
        }

        $selectedRule = null;
        $steps = collect();

        if ($this->selectedRuleId) {
            $selectedRule = RuleTemplate::with(['steps' => function ($query) {
                $query->with(['user:id,name,email', 'role:id,name']); // Load related user/role data
            }])
                ->find($this->selectedRuleId);

            $steps = $selectedRule?->steps ?? collect();
        }

        // Calculate stats more efficiently
        $stats = [
            'total_rules' => RuleTemplate::count(),
            'active_rules' => RuleTemplate::where('active', true)->count(),
            'total_steps' => RuleStepTemplate::count(),
            'avg_steps_per_rule' => RuleTemplate::withCount('steps')->get()->avg('steps_count') ?? 0,
        ];

        // Stats per model type - optimized with fewer queries
        $modelTypeStats = [];
        $modelStatsQuery = RuleTemplate::withCount('steps')
            ->select('model_type', 'active')
            ->whereIn('model_type', $availableModelTypes)
            ->get()
            ->groupBy('model_type');

        foreach ($availableModelTypes as $modelType) {
            $modelRules = $modelStatsQuery->get($modelType, collect());
            $modelTypeStats[$modelType] = [
                'total' => $modelRules->count(),
                'active' => $modelRules->where('active', true)->count(),
                'steps' => $modelRules->sum('steps_count'),
            ];
        }

        return view('livewire.admin.approvals.rule-manager', [
            'rules' => $rules,
            'groupedRules' => $groupedRules,
            'selectedRule' => $selectedRule,
            'steps' => $steps,
            'viewMode' => $this->viewMode,
            'stats' => $stats,
            'modelTypeStats' => $modelTypeStats,
            'availableModelTypes' => $availableModelTypes,
        ])->layout('new.layouts.app');
    }

    private function getFilteredRules()
    {
        return RuleTemplate::query()
            ->when($this->search !== '', function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%')
                    ->orWhere('model_type', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('active', $this->statusFilter === 'active');
            })
            ->when($this->modelTypeFilter, function ($q) {
                $q->where('model_type', $this->modelTypeFilter);
            })
            ->get();
    }
}
