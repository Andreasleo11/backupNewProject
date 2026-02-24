@php
    $label = null;
    $cls = 'bg-slate-100 text-slate-700';
    $tooltip = null;

    $baseClasses = 'inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-widest border transition-colors';
    $icon = '';

    // Priority 1: Use new workflow_status if available
    if (!empty($pr->workflow_status)) {
        $workflowStatus = strtoupper($pr->workflow_status);
        
        if ($pr->is_cancel === 1) {
            $label = 'CANCELED';
            $cls = 'bg-rose-50 text-rose-700 border-rose-200';
            $icon = 'bx-x-circle';
            $tooltip = 'Cancel Reason: ' . ($pr->description ?? '-');
        } elseif ($workflowStatus === 'IN_REVIEW') {
            $cls = 'bg-blue-50 text-blue-700 border-blue-200';
            $icon = 'bx-time-five';
            
            if (isset($pr->approvalRequest->steps) && $pr->approvalRequest->steps->isNotEmpty()) {
                $steps = $pr->approvalRequest->steps->sortBy('sequence');
                $currentSeq = $pr->approvalRequest->current_step;
                
                $dots = [];
                foreach ($steps as $step) {
                    $name = $step->role_name ?? 'Approver';
                    $name = str_replace(['pr-', 'depthead'], ['', 'Dept Head'], $name);
                    $name = ucwords(str_replace('-', ' ', $name));
                    
                    if ($step->status === 'APPROVED') {
                        $dots[] = '<div class="h-1.5 w-1.5 rounded-full bg-emerald-500" title="'.$name.': Approved"></div>';
                    } elseif ($step->sequence === $currentSeq) {
                        $dots[] = '<div class="relative flex h-2 w-2 items-center justify-center" title="'.$name.': Pending"><div class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></div><div class="relative inline-flex h-1.5 w-1.5 rounded-full bg-amber-500"></div></div>';
                    } elseif ($step->status === 'REJECTED') {
                        $dots[] = '<div class="h-1.5 w-1.5 rounded-full bg-rose-500" title="'.$name.': Rejected"></div>';
                    } else {
                        $dots[] = '<div class="h-1.5 w-1.5 rounded-full bg-slate-300" title="'.$name.': Waiting"></div>';
                    }
                }
                $microProgress = '<div class="flex items-center gap-1.5 border-l border-blue-200/60 pl-2 ml-1 relative top-[0.5px]">'.implode('', $dots).'</div>';
                $label = 'IN REVIEW' . $microProgress;
            } else {
                $label = 'IN REVIEW';
            }
        } elseif ($workflowStatus === 'RETURNED') {
            $label = 'RETURNED';
            $cls = 'bg-orange-50 text-orange-700 border-orange-200';
            $icon = 'bx-revision';
            $tooltip = isset($pr->approvalRequest->steps) 
                ? 'Returned by: ' . ($pr->approvalRequest->steps->where('status', 'RETURNED')->last()?->actedUser?->name ?? 'Approver')
                : 'Returned for Revision';
        } elseif ($workflowStatus === 'APPROVED') {
            $label = 'APPROVED';
            $cls = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $icon = 'bx-check-double';
            if ($pr->approved_at) {
                $tooltip = 'Approved: ' . \Carbon\Carbon::parse($pr->approved_at)->format('d M Y, H:i');
            }
        } elseif ($workflowStatus === 'REJECTED') {
            $label = 'REJECTED';
            $cls = 'bg-rose-50 text-rose-700 border-rose-200';
            $icon = 'bx-x';
            $tooltip = 'Reject Reason: ' . ($pr->description ?? '-');
        } elseif ($workflowStatus === 'CANCELED') {
            $label = 'CANCELED';
            $cls = 'bg-rose-50 text-rose-700 border-rose-200';
            $icon = 'bx-x-circle';
            $tooltip = 'Cancel Reason: ' . ($pr->description ?? '-');
        } elseif ($workflowStatus === 'DRAFT') {
            $label = 'DRAFT';
            $cls = 'bg-slate-50 text-slate-600 border-slate-200';
            $icon = 'bx-edit-alt';
        }
    } 
    // Priority 2: Fallback to legacy status if workflow_status is null
    else {
        if ($pr->is_cancel === 1) {
            $label = 'CANCELED';
            $cls = 'bg-rose-50 text-rose-700 border-rose-200';
            $icon = 'bx-x-circle';
            $tooltip = 'Cancel Reason: ' . ($pr->description ?? '-');
        } elseif ($pr->status === 5) {
            $label = 'REJECTED';
            $cls = 'bg-rose-50 text-rose-700 border-rose-200';
            $icon = 'bx-x';
            $tooltip = 'Reject Reason: ' . ($pr->description ?? '-');
        } elseif ($pr->status === 1) {
            $label = 'WAITING FOR DEPT HEAD';
            $cls = 'bg-amber-50 text-amber-700 border-amber-200';
            $icon = 'bx-time-five';
        } elseif ($pr->status === 7) {
            $label = 'WAITING FOR GM';
            $cls = 'bg-amber-50 text-amber-700 border-amber-200';
            $icon = 'bx-time-five';
        } elseif ($pr->status === 6) {
            $label = 'WAITING FOR PURCHASER';
            $cls = 'bg-amber-50 text-amber-700 border-amber-200';
            $icon = 'bx-time-five';
        } elseif ($pr->status === 2) {
            $label = 'WAITING FOR VERIFICATOR';
            $cls = 'bg-amber-50 text-amber-700 border-amber-200';
            $icon = 'bx-time-five';
        } elseif ($pr->status === 3) {
            $label = 'WAITING FOR DIRECTOR';
            $cls = 'bg-amber-50 text-amber-700 border-amber-200';
            $icon = 'bx-time-five';
        } elseif ($pr->status === 4) {
            $label = 'APPROVED';
            $cls = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $icon = 'bx-check-double';
        } elseif ($pr->status === 8) {
            $label = 'DRAFT';
            $cls = 'bg-slate-50 text-slate-600 border-slate-200';
            $icon = 'bx-edit-alt';
        }
    }
@endphp

@if($label)
    <div class="flex items-center gap-2">
        <span class="{{ $baseClasses }} {{ $cls }}" @if($tooltip) title="{{ $tooltip }}" @endif>
            @if($icon)
                <i class='bx {{ $icon }} text-sm'></i>
            @endif
            {!! $label !!}
        </span>
        
        @if($tooltip)
            <span class="text-slate-300 hover:text-indigo-500 transition-colors cursor-help" title="{{ $tooltip }}">
                <i class='bx bx-info-circle text-lg'></i>
            </span>
        @endif
    </div>
@endif
