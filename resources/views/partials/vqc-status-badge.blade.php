@php
    $hoursDifference = Date::now()->diffInHours($report->rejected_at);
@endphp
@if ($report->is_approve === 1)
    <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
@elseif($report->is_approve === 0)
    <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>
    @if ($report->is_locked)
        <span class="badge text-bg-dark py-2 fs-6">
            <i class='bx bxs-lock-alt'></i>
        </span>
    @endif
@elseif($report->rejected_at != null && $hoursDifference < 24)
    @if ($report->autograph_3 != null)
        <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ON APPROVAL</span>
    @else
        <span class="badge text-bg-secondary px-3 py-2 fs-6">REVISION</span>
        @if ($report->has_been_emailed)
            <span class="badge text-bg-secondary py-2 fs-6">
                <i class='bx bx-mail-send'></i>
            </span>
        @endif
    @endif
@elseif(($report->autograph_1 || $report->autograph_2) && $report->autograph_3)
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ON APPROVAL</span>
@else
    <span class="badge text-bg-secondary px-3 py-2 fs-6">WAITING SIGNATURE</span>
    @if ($report->has_been_emailed)
        <span class="badge text-bg-secondary py-2 fs-6">
            <i class='bx bx-mail-send'></i>
        </span>
    @endif
@endif
