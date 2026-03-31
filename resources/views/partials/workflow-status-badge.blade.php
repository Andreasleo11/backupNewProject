@php
    $label = null;
    $cls = 'bg-slate-100 text-slate-700';
    $tooltip = null;

    $baseClasses = 'inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-widest border transition-colors';
    $icon = '';

    // Handle both 'record' and legacy 'pr' variable names for backward compatibility during transition if needed
    $model = $record ?? $pr ?? null;

    if ($model && !empty($model->workflow_status)) {
        $workflowStatus = strtoupper($model->workflow_status);
        
        // Unified Engine Lookup: Available for all status blocks below
        $approval = method_exists($model, 'approvalRequest') ? $model->approvalRequest : null;
        $actionStep = $approval && isset($approval->steps) ? $approval->steps->firstWhere('sequence', $approval->current_step) : null;
        
        if ($workflowStatus === 'IN_REVIEW') {
            $cls = 'bg-blue-50 text-blue-700 border-blue-200';
            $icon = 'bx-time-five';
            
            if (isset($model->approvalRequest->steps) && $model->approvalRequest->steps->isNotEmpty()) {
                $steps = $model->approvalRequest->steps->sortBy('sequence');
                $currentSeq = $model->approvalRequest->current_step;
                
                $dots = [];
                foreach ($steps as $step) {
                    $name = $step->role_name ?? 'Approver';
                    $name = str_replace(['pr-', 'depthead', 'mbr-'], ['', 'Dept Head', ''], $name);
                    $name = ucwords(str_replace('-', ' ', $name));
                    
                    if ($step->status === 'APPROVED') {
                        $dots[] = '<div class="h-1.5 w-1.5 rounded-full bg-emerald-500" title="'.$name.': Approved"></div>';
                    } elseif ($step->sequence === $currentSeq) {
                        $dots[] = '<div class="relative flex h-2 w-2 items-center justify-center" title="'.$name.': Pending"><div class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></div><div class="relative flex h-1.5 w-1.5 rounded-full bg-amber-500"></div></div>';
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
            
            $by = $actionStep->approver_snapshot_name ?? ($actionStep->approver_name ?? 'System');
            $at = $actionStep->acted_at ? \Carbon\Carbon::parse($actionStep->acted_at)->format('d M Y, H:i') : null;
            $reason = $actionStep->return_reason ?? 'Revision required';
            
            $tooltip = "Returned by {$by}" . ($at ? " on {$at}" : "") . ". Reason: \"{$reason}\"";

        } elseif ($workflowStatus === 'APPROVED') {
            $label = 'APPROVED';
            $cls = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $icon = 'bx-check-double';
            if ($model->approved_at) {
                $tooltip = 'Approved: ' . \Carbon\Carbon::parse($model->approved_at)->format('d M Y, H:i');
            }
        } elseif ($workflowStatus === 'REJECTED') {
            $label = 'REJECTED';
            $cls = 'bg-rose-50 text-rose-700 border-rose-200';
            $icon = 'bx-x';

            $by = $actionStep->approver_snapshot_name ?? ($actionStep->approver_name ?? 'System');
            $at = $actionStep->acted_at ? \Carbon\Carbon::parse($actionStep->acted_at)->format('d M Y, H:i') : null;
            $reason = $actionStep->remarks ?? ($model->description ?? '-');

            $tooltip = "Rejected by {$by}" . ($at ? " on {$at}" : "") . ". Reason: \"{$reason}\"";

        } elseif ($workflowStatus === 'CANCELED') {
            $label = 'CANCELED';
            $cls = 'bg-rose-50 text-rose-700 border-rose-200';
            $icon = 'bx-x-circle';
            
            // PRIORITY: 1. Engine Remark, 2. Unified Cancellation Column, 3. Legacy Fallbacks
            $reason = $actionStep->remarks ?? ($model->cancellation_reason ?? ($model->cancel_reason ?? ($model->description ?? 'No reason provided.')));
            
            $tooltip = "Document Canceled. Reason: \"{$reason}\"";

        } elseif ($workflowStatus === 'DRAFT') {
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
