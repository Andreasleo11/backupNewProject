@if ($report->status_laporan === 1)
    <span class="badge bg-primary px-3 py-2 fs-6">IN PROGRESS</span>
@elseif ($report->status_laporan === 2)
    <span class="badge bg-success px-3 py-2 fs-6">DONE</span>
@else
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING</span>
@endif
