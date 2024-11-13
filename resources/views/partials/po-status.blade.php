@if ($po->status === 1)
    <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting</span>
@elseif ($po->status === 2)
    <span class="badge text-bg-success px-3 py-2 fs-6">Approved</span>
@elseif ($po->status === 3)
    <span class="badge text-bg-danger px-3 py-2 fs-6">Rejected</span>
@endif

@if ($po->downloaded_at)
    <button data-bs-toggle="tooltip" data-bs-html="true"
        data-bs-title="Last time downloaded at : <br> {{ \Carbon\Carbon::parse($po->downloaded_at)->setTimezone('Asia/Jakarta')->format('d-m-y (h:i)') ?? '-' }}"
        class="btn btn-secondary btn-sm align-items-center my-1">
        <i class='bx bx-cloud-download'></i></button>
@endif
<script type="module">
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>
