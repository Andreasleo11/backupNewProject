<?php

namespace App\Livewire\Admin\Approvals\RuleTemplates;

use App\Infrastructure\Approval\Models\RuleTemplate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

final class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $createOpen = false;

    // quick-create fields
    public string $model_type = '';

    public string $name = '';

    public ?string $code = null;

    public int $priority = 100;

    public bool $active = true;

    public string $match_expr = ''; // JSON

    protected function rules(): array
    {
        return [
            'model_type' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:64'],
            'priority' => ['required', 'integer', 'min:1', 'max:9999'],
            'active' => ['boolean'],
            'match_expr' => ['nullable', 'string'],
        ];
    }

    public function create(): void
    {
        $this->validate();

        $expr = null;
        if (trim($this->match_expr) !== '') {
            $expr = json_decode($this->match_expr, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($expr)) {
                throw ValidationException::withMessages([
                    'match_expr' => 'Invalid JSON.',
                ]);
            }
        }

        RuleTemplate::create([
            'model_type' => $this->model_type,
            'name' => $this->name,
            'code' => $this->code ?: null,
            'priority' => $this->priority,
            'active' => $this->active,
            'match_expr' => $expr,
        ]);

        $this->reset(['createOpen', 'model_type', 'name', 'code', 'priority', 'active', 'match_expr']);
        $this->priority = 100;
        session()->flash('ok', 'Template created.');
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $tpl = RuleTemplate::find($id);
        if (! $tpl) {
            return;
        }
        if ($tpl->steps()->exists()) {
            // optional: cascade delete confirmation in Edit screen instead
            $tpl->steps()->delete();
        }
        $tpl->delete();
        session()->flash('ok', 'Template deleted.');
        $this->resetPage();
    }

    public function render()
    {
        $q = RuleTemplate::query()
            ->when($this->search, function ($qq) {
                $s = "%{$this->search}%";
                $qq->where('name', 'like', $s)
                    ->orWhere('code', 'like', $s)
                    ->orWhere('model_type', 'like', $s);
            })
            ->orderBy('priority')
            ->orderBy('name');

        return view('livewire.admin.approvals.rule-templates.index', [
            'templates' => $q->paginate(12),
        ]);
    }
}
