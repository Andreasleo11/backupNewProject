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
                    'rule_match_expr_raw' => 'Invalid JSON: '.$e->getMessage(),
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
        ];

        if ($this->editingRuleId) {
            $rule = RuleTemplate::findOrFail($this->editingRuleId);
            $rule->update($data);
            session()->flash('success', 'Rule updated.');
        } else {
            $rule = RuleTemplate::create($data);
            session()->flash('success', 'Rule created.');
        }

        $this->selectedRuleId = $rule->id;
        $this->showRuleModal = false;
        $this->resetRuleForm();
    }

    public function deleteRule(int $id): void
    {
        $rule = RuleTemplate::with('steps')->findOrFail($id);

        // Simple guard: avoid deleting if used heavily (optional)
        $rule->steps()->delete();
        $rule->delete();

        if ($this->selectedRuleId === $id) {
            $this->selectedRuleId = null;
        }

        session()->flash('success', 'Rule deleted.');
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
        $rules = RuleTemplate::query()
            ->when($this->search !== '', function ($q) {
                $q->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhere('model_type', 'like', '%'.$this->search.'%');
            })
            ->orderBy('priority')
            ->orderBy('code')
            ->paginate(10);

        $selectedRule = null;
        $steps = collect();

        if ($this->selectedRuleId) {
            $selectedRule = RuleTemplate::with('steps')
                ->find($this->selectedRuleId);

            $steps = $selectedRule?->steps ?? collect();
        }

        return view('livewire.admin.approvals.rule-manager', [
            'rules' => $rules,
            'selectedRule' => $selectedRule,
            'steps' => $steps,
        ])->layout('new.layouts.app');
    }
}
