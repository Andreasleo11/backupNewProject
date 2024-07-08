<style>
    .head-bg-warning {
        background-color: rgba(255, 193, 7, 0.2);
        /* Adjust the alpha value (0.7) for opacity */
        color: #000;
        /* Set text color */
    }

    .purchaser-bg-warning {
        background-color: rgba(255, 193, 7, 0.45);
        color: #000;
    }

    .gm-bg-warning {
        background-color: rgba(255, 193, 7, 0.7);
        color: #000;
    }

    .verificator-bg-warning {
        background-color: rgba(255, 193, 7, 0.7);
        color: #000;
    }

    .director-bg {
        background-color: rgb(255, 191, 0);
        color: #000;
    }

    .approved-bg {
        background-color: rgb(20, 128, 72);
        color: #fff;
    }

    .rejected-bg {
        background-color: rgb(221, 47, 47);
        color: #fff;
    }
</style>

@if ($pr->is_cancel === 1)
    <span class="badge rejected-bg px-3 py-2 fs-6">CANCELED</span>
    <button data-bs-toggle="tooltip" data-bs-title="Cancel Reason: {{ $pr->description ?? '-' }}"
        class="btn btn-secondary btn-sm align-items-center">
        <i class='bx bx-info-circle'></i></button>
@else
    @if ($pr->status === 5)
        <span class="badge rejected-bg px-3 py-2 fs-6">REJECTED</span>
        <button data-bs-toggle="tooltip" data-bs-title="Reject Reason: {{ $pr->description ?? '-' }}"
            class="btn btn-secondary btn-sm align-items-center">
            <i class='bx bx-info-circle'></i></button>
        {{-- After the maker signed --}}
    @elseif($pr->status === 1)
        <span class="badge head-bg-warning px-3 py-2 fs-6">WAITING FOR DEPT
            HEAD</span>
        {{-- After the dept head signed --}}
    @elseif ($pr->status === 7)
        <span class="badge gm-bg-warning px-3 py-2 fs-6">WAITING FOR
            GM</span>
        {{-- After the GM signed --}}
    @elseif($pr->status === 6)
        <span class="badge purchaser-bg-warning px-3 py-2 fs-6">WAITING FOR PURCHASER</span>
        {{-- After the purchaser signed --}}
    @elseif($pr->status === 2)
        <span class="badge verificator-bg-warning px-3 py-2 fs-6">WAITING FOR
            VERIFICATOR</span>
        {{-- After the verificator signed --}}
    @elseif($pr->status === 3)
        <span class="badge director-bg px-3 py-2 fs-6">WAITING FOR
            DIRECTOR</span>
    @elseif($pr->status === 4)
        <span class="badge approved-bg px-3 py-2 fs-6">APPROVED</span>
    @endif
@endif
<script type="module">
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>
