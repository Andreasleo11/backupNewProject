@php
    $label = null;
    $cls = 'bg-slate-100 text-slate-700';
    $tooltip = null;
    $progress = 0;

    $baseClasses =
        'relative inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest border transition-all overflow-hidden';
    $icon = '';

    $model = $record ?? ($pr ?? null);

    if ($model && !empty($model->workflow_status)) {
        $workflowStatus = strtoupper($model->workflow_status);
        $approval = method_exists($model, 'approvalRequest') ? $model->approvalRequest : null;
        $totalSteps = $approval && isset($approval->steps) ? $approval->steps->count() : 1;
        $currentStepSeq = $approval->current_step ?? 1;
        $actionStep =
            $approval && isset($approval->steps) ? $approval->steps->firstWhere('sequence', $currentStepSeq) : null;

        if ($workflowStatus === 'IN_REVIEW') {
            $cls = 'bg-blue-50 text-blue-700 border-blue-200';
            $icon = 'bx-time-five';

            $stepName = $model->workflow_step ?? 'Review';
            $stepName = str_replace(['pr-', 'depthead', 'mbr-'], ['', 'Dept Head', ''], $stepName);
            $stepName = ucwords(str_replace('-', ' ', $stepName));

            $label = "PENDING: {$stepName}";
            $progress = $totalSteps > 0 ? ($currentStepSeq / ($totalSteps + 1)) * 100 : 50;
        } elseif ($workflowStatus === 'RETURNED') {
            $label = 'RETURNED';
            $cls = 'bg-orange-50 text-orange-700 border-orange-200';
            $icon = 'bx-revision';
            $progress = 100;

            $by = $actionStep->approver_snapshot_name ?? ($actionStep->approver_name ?? 'System');
            $reason = $actionStep->return_reason ?? 'Revision required';
            $tooltip = "Returned by {$by}. Reason: \"{$reason}\"";
        } elseif ($workflowStatus === 'APPROVED') {
            $label = 'APPROVED';
            $cls = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $icon = 'bx-check-double';
            $progress = 100;
            if ($model->approved_at) {
                $tooltip = 'Approved: ' . \Carbon\Carbon::parse($model->approved_at)->format('d M Y, H:i');
            }
        } elseif ($workflowStatus === 'REJECTED') {
            $label = 'REJECTED';
            $cls = 'bg-rose-50 text-rose-700 border-rose-200';
            $icon = 'bx-x';
            $progress = 100;

            $by = $actionStep->approver_snapshot_name ?? ($actionStep->approver_name ?? 'System');
            $reason = $actionStep->remarks ?? ($model->description ?? '-');
            $tooltip = "Rejected by {$by}. Reason: \"{$reason}\"";
        } elseif ($workflowStatus === 'CANCELED') {
            $label = 'CANCELED';
            $cls = 'bg-rose-50 text-rose-700 border-rose-200';
            $icon = 'bx-x-circle';
            $progress = 100;

            $reason =
                $actionStep->remarks ??
                ($model->cancellation_reason ??
                    ($model->cancel_reason ?? ($model->description ?? 'No reason provided.')));
            $tooltip = "Document Canceled. Reason: \"{$reason}\"";
        } elseif ($workflowStatus === 'DRAFT') {
            $label = 'DRAFT';
            $cls = 'bg-slate-50 text-slate-600 border-slate-200';
            $icon = 'bx-edit-alt';
            $progress = 0;
        }
    }
@endphp

@if ($label)
    <div class="inline-flex items-center gap-2">
        <span class="{{ $baseClasses }} {{ $cls }}"
            @if ($tooltip) title="{{ $tooltip }}" @endif>
            @if ($icon)
                <i class='bx {{ $icon }} text-xs opacity-80'></i>
            @endif

            <span class="relative z-10">{{ $label }}</span>

            {{-- Stealth Progress Bar --}}
            @if ($progress > 0)
                <div class="absolute bottom-0 left-0 h-0.5 bg-current opacity-20" style="width: 100%"></div>
                <div class="absolute bottom-0 left-0 h-0.5 bg-current opacity-60 transition-all duration-1000"
                    style="width: {{ $progress }}%"></div>
            @endif
        </span>
    </div>
@endif
