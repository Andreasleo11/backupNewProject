<?php

namespace App\Livewire\Admin\Approvals;

use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class RuleIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = 'all';

    #[Url(history: true)]
    public ?string $modelTypeFilter = null;

    #[Url(history: true)]
    public bool $currentVersionOnly = true;

    #[Url(history: true)]
    public int $perPage = 10;

    // Bulk selection
    public array $selectedRows = [];
    public bool $selectAll = false;

    // Deletion Modal
    public bool $showDeleteModal = false;
    public ?int $ruleToDeleteId = null;
    public int $activeRequestsCount = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedModelTypeFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedCurrentVersionOnly(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPage(): void
    {
        $this->clearSelection();
    }

    private function clearSelection(): void
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    #[Computed]
    public function rules()
    {
        return RuleTemplate::query()
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
            ->when($this->currentVersionOnly, function ($q) {
                $q->where('is_current', true);
            })
            ->orderBy('priority')
            ->orderBy('code')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function availableModelTypes()
    {
        return RuleTemplate::distinct('model_type')->pluck('model_type')->sort();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = collect($this->rules->items())->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function bulkActivate(): void
    {
        $this->authorize('approval.manage-rules');
        RuleTemplate::whereIn('id', $this->selectedRows)->update(['active' => true]);
        $this->clearSelection();
        $this->dispatch('toast', message: 'Selected rules activated.', type: 'success');
    }

    public function bulkDeactivate(): void
    {
        $this->authorize('approval.manage-rules');
        RuleTemplate::whereIn('id', $this->selectedRows)->update(['active' => false]);
        $this->clearSelection();
        $this->dispatch('toast', message: 'Selected rules deactivated.', type: 'success');
    }

    public function duplicateRule(int $id): void
    {
        $this->authorize('approval.manage-rules');
        $source = RuleTemplate::with('steps')->findOrFail($id);

        $clone = RuleTemplate::create([
            'model_type' => $source->model_type,
            'code' => $source->code . '-COPY',
            'name' => $source->name . ' (Copy)',
            'active' => false,
            'priority' => $source->priority,
            'match_expr' => $source->match_expr,
            'created_by' => auth()->id(),
        ]);

        foreach ($source->steps as $step) {
            $clone->steps()->create([
                'sequence' => $step->sequence,
                'approver_type' => $step->approver_type,
                'approver_id' => $step->approver_id,
                'final' => $step->final,
                'parallel_group' => $step->parallel_group,
            ]);
        }

        $this->dispatch('toast', message: 'Rule duplicated. Edit the copy to customize.', type: 'success');
        $this->redirectRoute('admin.approval-rules.edit', ['id' => $clone->id]);
    }

    public function confirmDelete(int $id): void
    {
        $this->authorize('approval.manage-rules');
        $rule = RuleTemplate::findOrFail($id);
        
        $this->activeRequestsCount = \App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::where('rule_template_id', $rule->version_uuid)
            ->whereNotIn('status', ['APPROVED', 'CANCELLED', 'REJECTED'])
            ->count();

        $this->ruleToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        $this->authorize('approval.manage-rules');
        if (!$this->ruleToDeleteId) return;

        $rule = RuleTemplate::findOrFail($this->ruleToDeleteId);
        
        // Soft delete steps first (cascading)
        $rule->steps()->delete();
        // Soft delete the rule
        $rule->delete();

        $this->showDeleteModal = false;
        $this->ruleToDeleteId = null;
        
        $this->dispatch('toast', message: 'Rule deleted successfully.', type: 'success');
    }

    public function render()
    {
        $stats = [
            'total_rules' => RuleTemplate::count(),
            'active_rules' => RuleTemplate::where('active', true)->count(),
        ];

        return view('livewire.admin.approvals.rule-index', [
            'rules' => $this->rules,
            'stats' => $stats,
        ]);
    }
}
