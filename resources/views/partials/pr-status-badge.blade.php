@php
    $label = null;
    $cls = 'bg-slate-100 text-slate-700';
    $tooltip = null;

    // Priority 1: Use new workflow_status if available
    if (!empty($pr->workflow_status)) {
        $workflowStatus = strtoupper($pr->workflow_status);
        
        if ($pr->is_cancel === 1) {
            $label = 'CANCELED';
            $cls = 'bg-rose-600 text-white';
            $tooltip = 'Cancel Reason: ' . ($pr->description ?? '-');
        } elseif ($workflowStatus === 'IN_REVIEW') {
            if ($pr->workflow_step) {
                $label = 'PENDING: ' . strtoupper($pr->workflow_step);
            } else {
                $label = 'IN REVIEW';
            }
            $cls = 'bg-amber-100 text-amber-900';
        } elseif ($workflowStatus === 'APPROVED') {
            $label = 'APPROVED';
            $cls = 'bg-emerald-600 text-white';
            if ($pr->approved_at) {
                $tooltip = 'Approved: ' . \Carbon\Carbon::parse($pr->approved_at)->format('d M Y, H:i');
            }
        } elseif ($workflowStatus === 'REJECTED') {
            $label = 'REJECTED';
            $cls = 'bg-rose-600 text-white';
            $tooltip = 'Reject Reason: ' . ($pr->description ?? '-');
        } elseif ($workflowStatus === 'CANCELED') {
            $label = 'CANCELED';
            $cls = 'bg-rose-600 text-white';
            $tooltip = 'Cancel Reason: ' . ($pr->description ?? '-');
        } elseif ($workflowStatus === 'DRAFT') {
            $label = 'DRAFT';
            $cls = 'bg-slate-200 text-slate-700';
        }
    } 
    // Priority 2: Fallback to legacy status if workflow_status is null
    else {
        if ($pr->is_cancel === 1) {
            $label = 'CANCELED';
            $cls = 'bg-rose-600 text-white';
            $tooltip = 'Cancel Reason: ' . ($pr->description ?? '-');
        } elseif ($pr->status === 5) {
            $label = 'REJECTED';
            $cls = 'bg-rose-600 text-white';
            $tooltip = 'Reject Reason: ' . ($pr->description ?? '-');
        } elseif ($pr->status === 1) {
            $label = 'WAITING FOR DEPT HEAD';
            $cls = 'bg-amber-100 text-amber-900';
        } elseif ($pr->status === 7) {
            $label = 'WAITING FOR GM';
            $cls = 'bg-amber-200 text-amber-900';
        } elseif ($pr->status === 6) {
            $label = 'WAITING FOR PURCHASER';
            $cls = 'bg-amber-200 text-amber-900';
        } elseif ($pr->status === 2) {
            $label = 'WAITING FOR VERIFICATOR';
            $cls = 'bg-amber-200 text-amber-900';
        } elseif ($pr->status === 3) {
            $label = 'WAITING FOR DIRECTOR';
            $cls = 'bg-amber-300 text-amber-950';
        } elseif ($pr->status === 4) {
            $label = 'APPROVED';
            $cls = 'bg-emerald-600 text-white';
        } elseif ($pr->status === 8) {
            $label = 'DRAFT';
            $cls = 'bg-slate-200 text-slate-700';
        }
    }
@endphp

@if($label)
    <span
        class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold {{ $cls }}"
        title="{{ $tooltip }}">
        {{ $label }}
    </span>

    @if($tooltip)
        <span class="ml-2 inline-flex items-center text-slate-400" title="{{ $tooltip }}">
            <i class='bx bx-info-circle text-base'></i>
        </span>
    @endif
@endif
