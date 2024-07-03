@if ($pr->is_cancel)
    <a href="{{ route('purchaserequest.detail', ['id' => $pr->id]) }}" class="btn btn-secondary">
        <i class='bx bx-info-circle'></i> Detail
    </a>
    <a href="{{ route('purchaserequest.exportToPdf', $pr->id) }}" class="btn btn-outline-success my-1 ">
        <i class='bx bxs-file-pdf'></i> <span class="d-none d-sm-inline">Export
            PDF</span>
    </a>
@else
    <a href="{{ route('purchaserequest.detail', ['id' => $pr->id]) }}" class="btn btn-secondary">
        <i class='bx bx-info-circle'></i> Detail
    </a>

    {{-- Edit Feature --}}
    {{-- @if (($pr->status == 1 && $user->specification->name == 'PURCHASER') || ($pr->status == 6 && $user->is_head == 1) || ($pr->status == 2 && $user->department->name == 'HRD'))
    <a href="{{ route('purchaserequest.edit', $pr->id) }}" class="btn btn-primary">
        <i class='bx bx-edit'></i> Edit
    </a>
    @endif --}}

    {{-- Delete Feature --}}
    @if ($pr->user_id_create === Auth::user()->id)
        @include('partials.delete-pr-modal', [
            'id' => $pr->id,
            'doc_num' => $pr->doc_num,
        ])
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete-pr-modal-{{ $pr->id }}">
            <i class='bx bx-trash-alt'></i> <span class="d-none d-sm-inline">Delete</span>
        </button>
    @endif

    @include('partials.cancel-pr-confirmation-modal')
    <div class="btn-group" role="group">

        <button type="button" class="btn text-success border border-success dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false">
            More
        </button>

        <ul class="dropdown-menu">
            <li>
                <a href="{{ route('purchaserequest.exportToPdf', $pr->id) }}"
                    class="btn btn-success my-1 dropdown-item">
                    <i class='bx bxs-file-pdf'></i> <span class="d-none d-sm-inline">Export PDF</span>
                </a>
                @if (
                    ($user->id === $pr->user_id_create && $pr->status === 1) ||
                        ($user->department->name === 'COMPUTER' && $user->is_head && $pr->status === 4))
                    <button data-bs-target="#cancel-confirmation-modal-{{ $pr->id }}" data-bs-toggle="modal"
                        class="btn btn-danger my-1 dropdown-item"><i class='bx bxs-file-pdf'></i> <span
                            class="d-none d-sm-inline">Cancel</span></button>
                @endif
            </li>
        </ul>
    </div>
@endif
