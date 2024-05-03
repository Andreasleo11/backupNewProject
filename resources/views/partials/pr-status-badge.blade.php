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
@elseif($pr->status === 2)
    @if ($pr->type === 'factory')
        <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR
            GM</span>
    @else
        <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR
            VERIFICATOR</span>
    @endif
@elseif($pr->files === null)
    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ATTACHMENT</span>
@elseif($pr->status === 3)
    @if ($pr->to_department === 'Computer' && $pr->type === 'factory')
        <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR
            VERIFICATOR</span>
    @else
        <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR
            DIRECTOR</span>
    @endif
@elseif($pr->status === 4)
    <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
@endif
