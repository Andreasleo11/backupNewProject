@if ($pr->status === 5)
    <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>
    {{-- After the maker signed --}}
@elseif($pr->status === 1)
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DEPT
        HEAD</span>
    {{-- After the dept head signed --}}
@elseif($pr->status === 6)
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR PURCHASER</span>
    {{-- After the purchaser signed --}}
@elseif ($pr->status === 7)
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR
        GM</span>
@elseif($pr->status === 2)
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR
        VERIFICATOR</span>
@elseif($pr->status === 3)
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR
        DIRECTOR</span>
@elseif($pr->status === 4)
    <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
@endif
