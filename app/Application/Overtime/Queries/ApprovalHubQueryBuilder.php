<?php

namespace App\Application\Overtime\Queries;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ApprovalHubQueryBuilder
{
    /**
     * Build the query for Approval Hub "Packs".
     * Groups forms by (Maker, Dept, Date) to reduce fragmented review.
     */
    public function build(User $user): Builder
    {
        // 1. Base query: Forms that are IN_REVIEW and where the current user can act.
        // We use the same permission scoping as the standard Index.
        $query = OvertimeForm::query()
            ->select([
                'header_form_overtime.id', 
                'header_form_overtime.user_id', 
                'header_form_overtime.dept_id', 
                'header_form_overtime.branch', 
                'header_form_overtime.status',
                'header_form_overtime.is_planned',
            ])
            ->with([
                'user:id,name',
                'department:id,name',
                'details:id,header_id,NIK,name,overtime_date,start_time,end_time,break,job_desc',
            ])
            ->whereHas('approvalRequest', function ($q) use ($user) {
                $q->where('status', 'IN_REVIEW')
                  ->forUser($user);
            });

        // 2. Precompute first overtime date for grouping
        $query->selectSub(function ($sub) {
            $sub->from('detail_form_overtime as d')
                ->selectRaw('MIN(d.start_date)')
                ->whereColumn('d.header_id', 'header_form_overtime.id');
        }, 'first_overtime_date');
        
        // 3. Filtering: Only items where the user is the current active approver
        // This logic is critical to ensure the "Approve All" button doesn't hit authorization errors.
        $query->where(function($q) use ($user) {
            $q->whereHas('approvalRequest.steps', function($sq) use ($user) {
                $sq->whereColumn('sequence', 'approval_requests.current_step')
                   ->where('status', 'PENDING');
                
                // Permission-based (User or Role)
                $sq->where(function($per) use ($user) {
                    $per->where(function($u) use ($user) {
                        $u->where('approver_type', 'user')->where('approver_id', $user->id);
                    })->orWhere(function($r) use ($user) {
                        $r->where('approver_type', 'role')->whereIn('approver_id', $user->roles->pluck('id'));
                    });
                });
            });
        });

        return $query;
    }
}
