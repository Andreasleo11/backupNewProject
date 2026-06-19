<?php

namespace App\Livewire\Admin\Approvals;

use App\Infrastructure\Persistence\Eloquent\Models\RuleStepTemplate;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
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

    // Rule Form State
    public string $rule_model_type = '';
    public string $rule_code = '';
    public string $rule_name = '';
    public bool $rule_active = true;
    public int $rule_priority = 10;
    public string $rule_match_expr_raw = '{}';

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

    private function loadRule()
    {
        $rule = $this->rule;
        $this->rule_model_type = $rule->model_type;
        $this->rule_code = $rule->code;
        $this->rule_name = $rule->name;
        $this->rule_active = (bool) $rule->active;
        $this->rule_priority = (int) $rule->priority;
        $this->rule_match_expr_raw = json_encode($rule->match_expr ?? [], JSON_PRETTY_PRINT);
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
            'rule_match_expr_raw' => ['nullable', 'string'],
        ]);

        $matchExpr = [];
        if (trim($this->rule_match_expr_raw) !== '') {
            try {
                $matchExpr = json_decode($this->rule_match_expr_raw, true, 512, JSON_THROW_ON_ERROR);
                if (! is_array($matchExpr)) throw new \RuntimeException();
            } catch (\Throwable $e) {
                throw ValidationException::withMessages(['rule_match_expr_raw' => 'Invalid JSON']);
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
        ]);
        $this->step_approver_type = 'user';
    }

    public function render()
    {
        return view('livewire.admin.approvals.rule-builder');
    }
}
