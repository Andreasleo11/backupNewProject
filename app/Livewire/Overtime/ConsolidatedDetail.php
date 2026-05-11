<?php

namespace App\Livewire\Overtime;

use App\Application\Overtime\Queries\OvertimeQueryBuilder;
use App\Domain\Overtime\Models\OvertimeForm;
use App\Infrastructure\Approval\Contracts\Approvals;;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('new.layouts.app')]
class ConsolidatedDetail extends Component
{
    public string $date;
    public ?int $dept = null;
    public ?string $branch = null;

    public function mount(string $date)
    {
        $this->date = $date;
        $this->dept = request('dept');
        $this->branch = request('branch');
    }

    public function render()
    {
        $user = Auth::user();
        $builder = new OvertimeQueryBuilder;

        // Determine default filter based on user permissions and pending approvals
        $defaultFilter = [];

        // Check if user has pending approvals (they are in current approval request steps)
        $hasPendingApprovals = $builder->build($user, ['infoStatus' => 'my_approval'])->count() > 0;

        if ($hasPendingApprovals) {
            // User has pending approvals, show their approvals by default
            $defaultFilter = ['infoStatus' => 'my_approval'];
        } elseif ($user->can('overtime.review')) {
            // User has review permission, we need to apply custom filtering
            // Don't use default filter, we'll apply custom logic below
        } else {
            // Default fallback
            $defaultFilter = ['infoStatus' => 'my_approval'];
        }

        $query = $builder->build($user, $defaultFilter);

        // Apply custom filtering for review users
        if (!$hasPendingApprovals && $user->can('overtime.review')) {
            $query->workflowApproved()
                  ->whereHas('details', fn ($q) => $q->whereNull('status'))
                  ->where('is_push', '!=', 1);
        }

        // Filter by the specific date - we need forms where the earliest start_date matches
        $query->whereRaw('
            (SELECT MIN(start_date) FROM detail_form_overtime WHERE header_id = header_form_overtime.id) = ?
        ', [$this->date]);

        // Apply department filter if provided
        if ($this->dept) {
            $query->where('dept_id', $this->dept);
        }

        // Apply branch filter if provided (handle multiple branches separated by comma)
        if ($this->branch) {
            $branches = explode(',', $this->branch);
            $branches = array_map('trim', $branches);
            $query->whereIn('branch', $branches);
        }

        $headers = $query->with([
            'user:id,name',
            'department:id,name',
            'failedDetails',
            'approvalRequest.steps' => fn ($q) => $q
                ->select(['id', 'approval_request_id', 'sequence', 'status', 'approver_snapshot_label', 'acted_by'])
                ->orderBy('sequence'),
        ])->get();

        $totalForms = $headers->count();
        $totalDetails = $headers->sum('details_count');
        $approvedDetails = $headers->sum('approved_count');
        $rejectedDetails = $headers->sum('rejected_count');
        $pendingDetails = $headers->sum('pending_count');

        return view('livewire.overtime.consolidated-detail', [
            'headers' => $headers,
            'date' => $this->date,
            'totalForms' => $totalForms,
            'totalDetails' => $totalDetails,
            'approvedDetails' => $approvedDetails,
            'rejectedDetails' => $rejectedDetails,
            'pendingDetails' => $pendingDetails,
            'user' => Auth::user(),
            'canApprove' => Auth::user()->can('overtime.approve'),
        ]);
    }
}