<?php

namespace App\Livewire\Admin\Approvals;

use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class RuleCreate extends Component
{
    public string $rule_model_type = '';
    public string $rule_code = '';
    public string $rule_name = '';
    public bool $rule_active = true;
    public int $rule_priority = 10;
    public string $rule_match_expr_raw = '{}';

    protected $rules = [
        'rule_model_type' => ['required', 'string', 'max:255'],
        'rule_code' => ['required', 'string', 'max:50'],
        'rule_name' => ['required', 'string', 'max:255'],
        'rule_active' => ['boolean'],
        'rule_priority' => ['integer', 'min:0'],
        'rule_match_expr_raw' => ['nullable', 'string'],
    ];

    public function saveRule()
    {
        $this->authorize('approval.manage-rules');
        $this->validate();

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

        $rule = RuleTemplate::create([
            'model_type' => $this->rule_model_type,
            'code' => $this->rule_code,
            'name' => $this->rule_name,
            'active' => $this->rule_active,
            'priority' => $this->rule_priority,
            'match_expr' => $matchExpr,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', 'Rule created successfully. You can now add approval steps.');
        $this->redirectRoute('admin.approval-rules.edit', ['id' => $rule->id]);
    }

    public function render()
    {
        return view('livewire.admin.approvals.rule-create');
    }
}
