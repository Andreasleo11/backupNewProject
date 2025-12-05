<?php

namespace App\Livewire\Admin\Approvals\RuleTemplates;

use App\Infrastructure\Approval\Models\RuleStepTemplate;
use App\Infrastructure\Approval\Models\RuleTemplate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class Edit extends Component
{
    public ?RuleTemplate $template = null;

    public string $model_type = '';

    public string $name = '';

    public ?string $code = null;

    public int $priority = 100;

    public bool $active = true;

    public string $match_expr = ''; // JSON text area

    /** @var array<int, array{sequence:int,approver_type:string,approver_id:int|null,final:bool}> */
    public array $steps = [];

    public function mount(?int $templateId = null): void
    {
        if ($templateId) {
            $this->template = RuleTemplate::with('steps')->findOrFail($templateId);
            $this->model_type = $this->template->model_type;
            $this->name = $this->template->name;
            $this->code = $this->template->code;
            $this->priority = (int) $this->template->priority;
            $this->active = (bool) $this->template->active;
            $this->match_expr = $this->template->match_expr ? json_encode($this->template->match_expr, JSON_PRETTY_PRINT) : '';

            $this->steps = $this->template->steps->map(fn ($s) => [
                'sequence' => (int) $s->sequence,
                'approver_type' => $s->approver_type,
                'approver_id' => $s->approver_id ? (int) $s->approver_id : null,
                'final' => (bool) $s->final,
            ])->values()->toArray();
        }
    }

    protected function rules(): array
    {
        return [
            'model_type' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:64'],
            'priority' => ['required', 'integer', 'min:1', 'max:9999'],
            'active' => ['boolean'],
            'match_expr' => ['nullable', 'string'],

            'steps' => ['array', 'min:1'],
            'steps.*.sequence' => ['required', 'integer', 'min:1', 'max:999'],
            'steps.*.approver_type' => ['required', 'in:user,role'],
            'steps.*.approver_id' => ['nullable', 'integer', 'min:1'],
            'steps.*.final' => ['boolean'],
        ];
    }

    public function addStep(): void
    {
        $next = empty($this->steps) ? 1 : (max(array_column($this->steps, 'sequence')) + 1);
        $this->steps[] = ['sequence' => $next, 'approver_type' => 'role', 'approver_id' => null, 'final' => false];
    }

    public function removeStep(int $idx): void
    {
        unset($this->steps[$idx]);
        $this->steps = array_values($this->steps);
        $this->resequence();
    }

    public function moveUp(int $idx): void
    {
        if ($idx <= 0) {
            return;
        }
        [$this->steps[$idx - 1], $this->steps[$idx]] = [$this->steps[$idx], $this->steps[$idx - 1]];
        $this->resequence();
    }

    public function moveDown(int $idx): void
    {
        if ($idx >= count($this->steps) - 1) {
            return;
        }
        [$this->steps[$idx + 1], $this->steps[$idx]] = [$this->steps[$idx], $this->steps[$idx + 1]];
        $this->resequence();
    }

    private function resequence(): void
    {
        foreach ($this->steps as $i => &$s) {
            $s['sequence'] = $i + 1;
        }
        unset($s);
    }

    public function save()
    {
        $this->validate();

        // validate JSON
        $expr = null;
        if (trim($this->match_expr) !== '') {
            $expr = json_decode($this->match_expr, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($expr)) {
                throw ValidationException::withMessages([
                    'match_expr' => 'Invalid JSON.',
                ]);
            }
        }

        // ensure only the last step (or designated) is 'final'
        $finalCount = collect($this->steps)->where('final', true)->count();
        if ($finalCount > 1) {
            throw ValidationException::withMessages([
                'steps' => 'Only one step should be marked as final.',
            ]);
        }
        if ($finalCount === 0 && count($this->steps) > 0) {
            // set last one as final for convenience
            $this->steps[array_key_last($this->steps)]['final'] = true;
        }

        if ($this->template) {
            $tpl = $this->template;
            $tpl->update([
                'model_type' => $this->model_type,
                'name' => $this->name,
                'code' => $this->code ?: null,
                'priority' => $this->priority,
                'active' => $this->active,
                'match_expr' => $expr,
            ]);

            // Replace step set (simple & safe)
            $tpl->steps()->delete();
            foreach ($this->steps as $s) {
                RuleStepTemplate::create([
                    'rule_template_id' => $tpl->id,
                    'sequence' => $s['sequence'],
                    'approver_type' => $s['approver_type'],
                    'approver_id' => $s['approver_id'],
                    'final' => (bool) $s['final'],
                ]);
            }

            session()->flash('ok', 'Template updated.');
        } else {
            $tpl = RuleTemplate::create([
                'model_type' => $this->model_type,
                'name' => $this->name,
                'code' => $this->code ?: null,
                'priority' => $this->priority,
                'active' => $this->active,
                'match_expr' => $expr,
            ]);

            foreach ($this->steps as $s) {
                RuleStepTemplate::create([
                    'rule_template_id' => $tpl->id,
                    'sequence' => $s['sequence'],
                    'approver_type' => $s['approver_type'],
                    'approver_id' => $s['approver_id'],
                    'final' => (bool) $s['final'],
                ]);
            }

            session()->flash('ok', 'Template created.');

            return redirect()->route('admin.approvals.rules.edit', $tpl->id);
        }

        $this->template = $tpl->fresh('steps');
    }

    public function render()
    {
        return view('livewire.admin.approvals.rule-templates.edit');
    }
}
