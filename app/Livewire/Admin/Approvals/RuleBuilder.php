<?php

namespace App\Livewire\Admin\Approvals;

use App\Infrastructure\Approval\Services\ApprovableModuleScanner;
use App\Infrastructure\Persistence\Eloquent\Models\RuleStepTemplate;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RuleBuilder extends Component
{
    public int $ruleId;
    public ?string $version_notes = '';
    public bool $forceNewVersion = false;
    public bool $showVersionWarningModal = false;
    public int $activeRequestsCount = 0;

    public array $availableModules = [];
    public string $approverSearch = '';
    public array $approverResults = [];
    public bool $showVersionHistory = false;

    // Rule Form State
    public string $rule_model_type = '';
    public string $rule_code = '';
    public string $rule_name = '';
    public bool $rule_active = true;
    public int $rule_priority = 10;
    public array $ruleConditions = [];

    // Step Form State
    public ?int $editingStepId = null;
    public int $step_sequence = 1;
    public string $step_approver_type = 'user'; // 'user' | 'role'
    public ?int $step_approver_id = null;
    public bool $step_final = false;
    public bool $step_parallel_group = false;
    public bool $showStepModal = false;

    public function mount(int $ruleId)
    {
        $this->ruleId = $ruleId;
        $this->availableModules = (new ApprovableModuleScanner)->scan();
        $this->loadRule();
    }

    #[Computed]
    public function rule()
    {
        return RuleTemplate::findOrFail($this->ruleId);
    }

    #[Computed]
    public function steps()
    {
        return RuleStepTemplate::where('rule_template_id', $this->ruleId)
            ->with(['user:id,name,email', 'role:id,name'])
            ->orderBy('sequence')
            ->get();
    }

    #[Computed]
    public function versionHistory()
    {
        return RuleTemplate::withTrashed()
            ->where('version_uuid', $this->rule->version_uuid)
            ->withCount('steps')
            ->orderByDesc('version_number')
            ->get();
    }

    public function switchToVersion(int $ruleId): void
    {
        $this->redirectRoute('admin.approval-rules.edit', ['id' => $ruleId]);
    }

    private function loadRule()
    {
        $rule = $this->rule;
        $this->rule_model_type = $rule->model_type;
        $this->rule_code = $rule->code;
        $this->rule_name = $rule->name;
        $this->rule_active = (bool) $rule->active;
        $this->rule_priority = (int) $rule->priority;
        $this->parseMatchExpr($rule->match_expr ?? []);
    }

    private function parseMatchExpr(array $expr): void
    {
        $this->ruleConditions = [];
        foreach ($expr as $k => $v) {
            if ($k === 'amount_gt') {
                $this->ruleConditions[] = ['field' => 'amount', 'operator' => '>', 'value' => $v];
            } elseif ($k === 'amount_gte') {
                $this->ruleConditions[] = ['field' => 'amount', 'operator' => '>=', 'value' => $v];
            } elseif ($k === 'amount_lte') {
                $this->ruleConditions[] = ['field' => 'amount', 'operator' => '<=', 'value' => $v];
            } elseif ($k === 'any_tags') {
                $this->ruleConditions[] = ['field' => 'tags', 'operator' => 'any', 'value' => is_array($v) ? implode(', ', $v) : $v];
            } elseif (str_ends_with($k, '_in')) {
                $this->ruleConditions[] = ['field' => substr($k, 0, -3), 'operator' => 'in', 'value' => is_array($v) ? implode(', ', $v) : $v];
            } elseif (str_ends_with($k, '_not_in')) {
                $this->ruleConditions[] = ['field' => substr($k, 0, -7), 'operator' => 'not_in', 'value' => is_array($v) ? implode(', ', $v) : $v];
            } else {
                $this->ruleConditions[] = ['field' => $k, 'operator' => '==', 'value' => is_array($v) ? implode(', ', $v) : $v];
            }
        }
    }

    private function compileMatchExpr(): array
    {
        $expr = [];
        foreach ($this->ruleConditions as $condition) {
            $field = trim($condition['field'] ?? '');
            $operator = $condition['operator'] ?? '==';
            $value = $condition['value'] ?? '';

            if (empty($field)) continue;

            if (in_array($operator, ['in', 'not_in', 'any'])) {
                $valueArray = array_map('trim', explode(',', (string)$value));
                $value = array_filter($valueArray, fn($v) => $v !== '');
                if (empty($value)) continue;
            } else {
                if (is_numeric($value)) {
                    $value = $value + 0;
                }
            }

            if ($field === 'amount' && $operator === '>') {
                $expr['amount_gt'] = $value;
            } elseif ($field === 'amount' && $operator === '>=') {
                $expr['amount_gte'] = $value;
            } elseif ($field === 'amount' && $operator === '<=') {
                $expr['amount_lte'] = $value;
            } elseif ($field === 'tags' && $operator === 'any') {
                $expr['any_tags'] = $value;
            } elseif ($operator === 'in') {
                $expr[$field . '_in'] = $value;
            } elseif ($operator === 'not_in') {
                $expr[$field . '_not_in'] = $value;
            } else {
                $expr[$field] = $value;
            }
        }
        return $expr;
    }

    public function addCondition(): void
    {
        $this->ruleConditions[] = ['field' => '', 'operator' => '==', 'value' => ''];
    }

    public function removeCondition(int $index): void
    {
        unset($this->ruleConditions[$index]);
        $this->ruleConditions = array_values($this->ruleConditions);
    }

    public function updateRule()
    {
        $this->authorize('approval.manage-rules');
        
        $this->validate([
            'rule_model_type' => ['required', 'string', 'max:255'],
            'rule_code' => ['required', 'string', 'max:50'],
            'rule_name' => ['required', 'string', 'max:255'],
            'rule_active' => ['boolean'],
            'rule_priority' => ['integer', 'min:0'],
            'ruleConditions.*.field' => ['required', 'string'],
        ], [
            'ruleConditions.*.field.required' => 'All condition fields must have a name.',
        ]);

        $matchExpr = $this->compileMatchExpr();

        $data = [
            'model_type' => $this->rule_model_type,
            'code' => $this->rule_code,
            'name' => $this->rule_name,
            'active' => $this->rule_active,
            'priority' => $this->rule_priority,
            'match_expr' => $matchExpr,
            'version_notes' => $this->version_notes ?? null,
        ];

        $currentRule = $this->rule;

        $this->activeRequestsCount = \App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::where('rule_template_id', $currentRule->version_uuid)
            ->whereNotIn('status', ['APPROVED', 'CANCELLED', 'REJECTED'])
            ->count();

        if ($this->activeRequestsCount > 0 && ! $this->forceNewVersion) {
            $this->showVersionWarningModal = true;
            return;
        }

        $newRule = $currentRule->createNewVersion($data, auth()->id());
        $this->dispatch('toast', message: "New version (v{$newRule->version_number}) created successfully.", type: 'success');
        
        // Redirect to new rule version edit page
        $this->redirectRoute('admin.approval-rules.edit', ['id' => $newRule->id]);
    }

    public function confirmForceNewVersion()
    {
        $this->forceNewVersion = true;
        $this->showVersionWarningModal = false;
        $this->updateRule();
    }

    // ====== Steps ======
    public function openCreateStep()
    {
        $this->resetStepForm();
        $this->step_sequence = $this->steps->count() + 1;
        $this->showStepModal = true;
    }

    public function openEditStep(int $stepId)
    {
        $step = RuleStepTemplate::findOrFail($stepId);
        $this->editingStepId = $step->id;
        $this->step_sequence = (int) $step->sequence;
        $this->step_approver_type = $step->approver_type;
        $this->step_approver_id = $step->approver_id;
        $this->step_final = (bool) $step->final;
        $this->step_parallel_group = (bool) $step->parallel_group;

        if ($this->step_approver_type === 'user' && $step->user) {
            $this->approverSearch = $step->user->name;
        } elseif ($this->step_approver_type === 'role' && $step->role) {
            $this->approverSearch = $step->role->name;
        } else {
            $this->approverSearch = '';
        }

        $this->showStepModal = true;
    }

    public function saveStep()
    {
        $this->authorize('approval.manage-rules');
        
        $this->validate([
            'step_sequence' => ['required', 'integer', 'min:1'],
            'step_approver_type' => ['required', 'in:user,role'],
            'step_approver_id' => ['required', 'integer', 'min:1'],
            'step_final' => ['boolean'],
            'step_parallel_group' => ['boolean'],
        ]);

        $data = [
            'rule_template_id' => $this->ruleId,
            'sequence' => $this->step_sequence,
            'approver_type' => $this->step_approver_type,
            'approver_id' => $this->step_approver_id,
            'final' => $this->step_final,
            'parallel_group' => $this->step_parallel_group,
        ];

        if ($this->editingStepId) {
            RuleStepTemplate::findOrFail($this->editingStepId)->update($data);
            $this->dispatch('toast', message: 'Step updated.', type: 'success');
        } else {
            RuleStepTemplate::create($data);
            $this->dispatch('toast', message: 'Step created.', type: 'success');
        }

        $this->showStepModal = false;
        $this->resetStepForm();
        unset($this->steps); // Clear cache
    }

    public function deleteStep(int $stepId)
    {
        $this->authorize('approval.manage-rules');
        RuleStepTemplate::findOrFail($stepId)->delete();
        $this->dispatch('toast', message: 'Step deleted.', type: 'success');
        unset($this->steps);
    }

    private function resetStepForm()
    {
        $this->reset([
            'editingStepId',
            'step_sequence',
            'step_approver_type',
            'step_approver_id',
            'step_final',
            'step_parallel_group',
            'approverSearch',
            'approverResults',
        ]);
        $this->step_approver_type = 'user';
    }

    public function updatedStepApproverType(): void
    {
        $this->approverSearch = '';
        $this->approverResults = [];
        $this->step_approver_id = null;
    }

    public function updatedApproverSearch(): void
    {
        if (strlen($this->approverSearch) < 2) {
            $this->approverResults = [];
            return;
        }

        if ($this->step_approver_type === 'user') {
            $this->approverResults = User::where('name', 'like', "%{$this->approverSearch}%")
                ->orWhere('email', 'like', "%{$this->approverSearch}%")
                ->limit(10)
                ->get(['id', 'name', 'email'])
                ->toArray();
        } else {
            $this->approverResults = Role::where('name', 'like', "%{$this->approverSearch}%")
                ->limit(10)
                ->get(['id', 'name'])
                ->toArray();
        }
    }

    public function selectApprover(int $id, string $name): void
    {
        $this->step_approver_id = $id;
        $this->approverSearch = $name;
        $this->approverResults = [];
    }

    public function render()
    {
        return view('livewire.admin.approvals.rule-builder');
    }
}
