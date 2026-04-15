@props(['model'])

@php
    $isCancelled = $model->is_cancel || $model->workflow_status === 'CANCELED';
    $isRejected = $model->workflow_status === 'REJECTED';
    $isReturned = $model->workflow_status === 'RETURNED';

    if (!($isCancelled || $isRejected || $isReturned)) {
        return; // Render nothing if the state is not terminal/insightful
    }

    $approval = method_exists($model, 'approvalRequest') ? $model->approvalRequest : null;
    $actionStep = $approval ? $approval->steps->firstWhere('sequence', $approval->current_step) : null;

    $reasonMessage = '';
    $actionLabel = '';
    $bgColor = '';
    $textColor = '';
    $borderColor = '';
    $iconColor = '';
    $icon = '';

    if ($isCancelled) {
        $reasonMessage =
            $model->cancellation_reason ?? ($model->description ?? ($actionStep->remarks ?? 'No reason provided.'));
        $actionLabel = 'Cancelled';
        $bgColor = 'bg-slate-50/50';
        $textColor = 'text-slate-800';
        $borderColor = 'border-slate-500';
        $iconColor = 'bg-slate-200 text-slate-600';
        $icon = 'bi-slash-circle-fill';
    } elseif ($isRejected) {
        $reasonMessage = $actionStep->remarks ?? 'No reason provided.';
        $actionLabel = 'Rejected';
        $bgColor = 'bg-rose-50/30';
        $textColor = 'text-rose-800';
        $borderColor = 'border-rose-500';
        $iconColor = 'bg-rose-100 text-rose-600';
        $icon = 'bi-x-octagon-fill';
    } elseif ($isReturned) {
        $reasonMessage = $actionStep->return_reason ?? 'No reason provided.';
        $actionLabel = 'Returned for Revision';
        $bgColor = 'bg-amber-50/30';
        $textColor = 'text-amber-800';
        $borderColor = 'border-amber-500';
        $iconColor = 'bg-amber-100 text-amber-600';
        $icon = 'bi-arrow-return-left';
    }
@endphp

@if ($reasonMessage)
    <div class="glass-card border-l-4 {{ $borderColor }} overflow-hidden {{ $bgColor }} shadow-sm">
        <div class="px-6 py-5 flex items-start gap-4">
            <div
                class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $iconColor }} shadow-sm">
                <i class="bi {{ $icon }} text-xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold {{ $textColor }}">
                    Document {{ $actionLabel }}
                    @if ($actionStep && !$isCancelled)
                        by {{ $actionStep->approver_snapshot_name ?? ($actionStep->approver_name ?? 'System') }}
                    @endif
                </h3>

                @if ($actionStep && $actionStep->acted_at)
                    <p
                        class="mt-1 flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest opacity-70 {{ $textColor }}">
                        <i class="bi bi-clock-history"></i>
                        {{ \Carbon\Carbon::parse($actionStep->acted_at)->format('d M Y, H:i') }}
                        <span class="opacity-50">•</span>
                        {{ $actionStep->approver_snapshot_label ?? ($actionStep->approver_label ?? 'Approver') }}
                    </p>
                @endif

                <div class="mt-4 rounded-xl border border-white/50 bg-white p-4 shadow-sm relative">
                    <div class="absolute -left-2 top-4 h-4 w-4 rotate-45 border-b border-l border-white/50 bg-white">
                    </div>
                    <p class="text-sm text-slate-700 italic relative z-10">"{!! nl2br(e($reasonMessage)) !!}"</p>
                </div>
            </div>
        </div>
    </div>
@endif
