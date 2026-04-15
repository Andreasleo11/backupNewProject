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

    /**
     * Grouping algorithm:
     * We fetch the raw pending items and group them by (user_id, dept_id, first_overtime_date).
     */
    public function getGroupsProperty()
    {
        $builder = new ApprovalHubQueryBuilder;
        $items = $builder->build(Auth::user())->get();

        return $items->groupBy(function ($item) {
            $date = $item->first_overtime_date;
            if ($date instanceof \DateTimeInterface) {
                $date = $date->format('Y-m-d');
            }

            return $item->user_id . '-' . $item->dept_id . '-' . ($date ?: 'unknown');
        });
    }

    public function toggleGroup(string $groupKey): void
    {
        $this->expandedGroups[$groupKey] = ! ($this->expandedGroups[$groupKey] ?? false);

        if ($this->expandedGroups[$groupKey]) {
            $this->loadInsightsForGroup($groupKey);
        }
    }

    private function loadInsightsForGroup(string $groupKey): void
    {
        $group = $this->groups->get($groupKey);
        if (! $group) {
            return;
        }

        $niks = $group->flatMap(fn ($h) => $h->details->pluck('NIK'))->unique()->toArray();
        $date = \Carbon\Carbon::parse($group[0]->first_overtime_date);

        $repo = app(EmployeeRepository::class);
        $newInsights = $repo->getApprovalInsights($niks, $date);

        $this->insights = array_merge($this->insights, $newInsights);
    }

    public function approvePack(string $groupKey): void
    {
        $group = $this->groups->get($groupKey);
        if (! $group) {
            return;
        }

        $ids = $group->pluck('id')->toArray();
        $this->processBulkApproval($ids);
    }

    public function approveSelected(): void
    {
        $allIds = [];
        foreach ($this->selectedPackKeys as $key) {
            $group = $this->groups->get($key);
            if ($group) {
                $allIds = array_merge($allIds, $group->pluck('id')->toArray());
            }
        }

        if (empty($allIds)) {
            $this->dispatch('flash', type: 'error', message: 'No items selected.');

            return;
        }

        $this->processBulkApproval($allIds);
        $this->selectedPackKeys = [];
    }

    private function processBulkApproval(array $ids): void
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

            $msg = "Successfully approved {$successCount} requests.";
            if ($failCount > 0) {
                $msg .= " Failed to approve {$failCount} items (likely permission/state mismatch).";
            }

            $this->dispatch('flash', type: $failCount > 0 ? 'warning' : 'success', message: $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('flash', type: 'error', message: 'Core approval process failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.overtime.approval-hub', [
            'groups' => $this->groups,
        ]);
    }
}
