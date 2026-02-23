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
            if ($pr->workflow_step) {
                $label = 'PENDING: ' . strtoupper($pr->workflow_step);
            } else {
                $label = 'IN REVIEW';
            }
            $cls = 'bg-blue-50 text-blue-700 border-blue-200';
            $icon = 'bx-time-five';
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
        <span class="{{ $baseClasses }} {{ $cls }}" @if($tooltip) data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $tooltip }}" @endif>
            @if($icon)
                <i class='bx {{ $icon }} text-sm'></i>
            @endif
            {{ $label }}
        </span>
        
        @if($tooltip)
            <span class="text-slate-300 hover:text-indigo-500 transition-colors cursor-help" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $tooltip }}">
                <i class='bx bx-info-circle text-lg'></i>
            </span>
        @endif
    </div>
@endif

{{-- Initialize tooltips specifically for badges if drawn inside DT fragments --}}
<script>
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>
