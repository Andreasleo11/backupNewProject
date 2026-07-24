<?php

namespace App\Livewire\Admin\Approvals;

use App\Infrastructure\Approval\Services\ApprovableModuleScanner;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class RuleCreate extends Component
{
    public array $availableModules = [];

    public function mount()
    {
        $this->availableModules = (new ApprovableModuleScanner)->scan();
    }

    public string $rule_model_type = '';
    public string $rule_code = '';
    public string $rule_name = '';
    public bool $rule_active = true;
    public int $rule_priority = 10;
    public array $ruleConditions = [];

    protected function rules()
    {
        return [
            'rule_model_type' => ['required', 'string', 'max:255'],
            'rule_code' => ['required', 'string', 'max:50'],
            'rule_name' => ['required', 'string', 'max:255'],
            'rule_active' => ['boolean'],
            'rule_priority' => ['integer', 'min:0'],
            'ruleConditions.*.field' => ['required', 'string'],
        ];
    }

    protected $messages = [
        'ruleConditions.*.field.required' => 'All condition fields must have a name.',
    ];

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

    public function saveRule()
    {
        $this->authorize('approval.manage-rules');
        $this->validate();

        $matchExpr = $this->compileMatchExpr();

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
