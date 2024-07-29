@if ($report->is_reject === 1)
    <span class="badge text-bg-danger px-3 py-2 fs-6">Rejected</span>
    <button data-bs-toggle="tooltip" data-bs-title="Reject Reason: {{ $report->reject_reason ?? '-' }}"
        class="btn btn-secondary btn-sm align-items-center">
        <i class='bx bx-info-circle'></i></button>
@else
    @if ($report->approved_autograph)
        <span class="badge text-bg-success px-3 py-2 fs-6">Approved</span>
    @elseif($report->is_known_autograph)
        @if ($report->department->name === 'QA' || $report->department->name === 'QC')
            <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting Director</span>
        @else
            <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting GM</span>
        @endif
    @elseif($report->created_autograph)
        <span class="badge text-bg-secondary px-3 py-2 fs-6">Waiting Dept Head</span>
    @elseif(!$report->created_autograph)
        <span class="badge text-black-50 bg-primary-subtle px-3 py-2 fs-6">Waiting Creator</span>
    @endif
@endif

<script type="module">
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>
