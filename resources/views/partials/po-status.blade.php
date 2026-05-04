@php
    $statusEnum = $po->getStatusEnum();
@endphp

<span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full border shadow-sm transition-all duration-300 {{ $statusEnum->cssClass() }}">
    {{ $statusEnum->label() }}
</span>

@if ($po->latestDownloadLog)
    <button data-bs-toggle="tooltip" data-bs-html="true"
        data-bs-title="Last downloaded at : <br> {{ \Carbon\Carbon::parse($po->latestDownloadLog->created_at)->setTimezone('Asia/Jakarta')->format('d-m-Y (H:i)') ?? '-' }} by {{ $po->latestDownloadLog->user->name }}"
        class="btn btn-secondary btn-sm align-items-center my-1">
        <i class='bx bx-cloud-download'></i></button>
@endif
<script type="module">
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
        tooltipTriggerEl));
</script>
