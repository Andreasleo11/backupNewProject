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
</style>

@if ($pr->status === 5)
    <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>
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
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR
        DIRECTOR</span>
@elseif($pr->status === 4)
    <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
@endif
