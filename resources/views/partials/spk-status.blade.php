<div class="pe-3 d-inline-flex align-items-center">
    @if ($status === 0)
        <span class="badge text-primary bg-primary-subtle px-3 py-2 fs-6">WAITING CREATOR</span>
    @elseif ($status === 1)
        <span class="badge text-warning-emphasis bg-warning-subtle px-3 py-2 fs-6">WAITING DEPT
            HEAD</span>
    @elseif ($status === 6)
        <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING PPIC</span>
    @elseif ($status === 2)
        <span class="badge text-primary bg-primary-subtle px-3 py-2 fs-6">WAITING ADMIN</span>
    @elseif($status === 3)
        <span class="badge bg-primary px-3 py-2 fs-6">IN PROGRESS</span>
    @elseif ($status === 4)
        <span class="badge bg-success px-3 py-2 fs-6">DONE</span>
    @elseif ($status === 5)
        <span class="badge text-success bg-success-subtle px-3 py-2 fs-6">FINISH</span>
    @endif
    @if ($is_urgent)
        <div class="d-inline-flex align-items-center ps-1" data-bs-toggle="tooltip" data-bs-title="Urgent">
            <span class="badge bg-danger-subtle"><i class='bx bx-alarm-exclamation bx-sm' style="color: red"></i></span>
        </div>
    @endif
</div>

<script type="module">
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
        tooltipTriggerEl))
</script>
