@php
    $hoursDifference = Date::now()->diffInHours($report->rejected_at);
@endphp
@if ($report->is_approve === 1)
    <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
@elseif($report->is_approve === 0)
    <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>
@elseif($report->rejected_at != null && $hoursDifference < 24)
    @if ($report->autograph_3 != null)
        <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ON APPROVAL</span>
    @else
        <span class="badge text-bg-secondary px-3 py-2 fs-6">REVISION</span>
    @endif
@elseif(($report->autograph_1 || $report->autograph_2) && $report->autograph_3)
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ON APPROVAL</span>
@else
    <span class="badge text-bg-secondary px-3 py-2 fs-6">WAITING SIGNATURE</span>
@endif
