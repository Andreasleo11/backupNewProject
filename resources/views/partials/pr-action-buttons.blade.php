@if ($pr->is_cancel)
    <a href="{{ route('purchaserequest.detail', ['id' => $pr->id]) }}" class="my-1 btn btn-secondary">
        <i class='bx bx-info-circle'></i> Detail
    </a>
    <a href="{{ route('purchaserequest.exportToPdf', $pr->id) }}" class="my-1 btn btn-outline-success my-1 ">
        <i class='bx bxs-file-pdf'></i> <span class="d-none d-sm-inline">Export
            PDF</span>
    </a>
@else
    <a href="{{ route('purchaserequest.detail', ['id' => $pr->id]) }}" class="my-1 btn btn-secondary">
        <i class='bx bx-info-circle'></i> Detail
    </a>

    {{-- Edit Feature --}}
    {{-- @if (($pr->status == 1 && $user->specification->name == 'PURCHASER') || ($pr->status == 6 && $user->is_head == 1) || ($pr->status == 2 && $user->department->name == 'PERSONALIA' && $user->is_head === 1))
    <a href="{{ route('purchaserequest.edit', $pr->id) }}" class="my-1 btn btn-primary">
        <i class='bx bx-edit'></i> Edit
    </a>
    @endif --}}

    {{-- Delete Feature --}}
    @if (auth()->user()->role->name === 'SUPERADMIN')
        @include('partials.delete-pr-modal', [
            'id' => $pr->id,
            'doc_num' => $pr->doc_num,
        ])
        <button class="my-1 btn btn-danger" data-bs-toggle="modal"
            data-bs-target="#delete-pr-modal-{{ $pr->id }}">
            <i class='bx bx-trash-alt'></i> <span class="d-none d-sm-inline">Delete</span>
        </button>
    @endif

    @if (
        ($user->id === $pr->user_id_create && $pr->status === 1) ||
            ($user->department->name === 'COMPUTER' && $user->is_head && $pr->status === 4) ||
            auth()->user()->role->name === 'SUPERADMIN')
        <button data-bs-target="#cancel-confirmation-modal-{{ $pr->id }}" data-bs-toggle="modal"
            class="my-1 btn btn-danger my-1"><i class='bx bx-x-circle'></i> <span
                class="d-none d-sm-inline">Cancel</span></button>
    @endif

    @include('partials.cancel-pr-confirmation-modal')
    @include('partials.edit-purchase-request-po-number-modal', [
        'pr' => $pr,
    ])

    <div class="btn-group" role="group">
        <button type="button" class="my-1 btn text-success border border-success dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
            More
        </button>
        <ul class="dropdown-menu">
            <li>
                <a href="{{ route('purchaserequest.exportToPdf', $pr->id) }}"
                    class="my-1 btn btn-success my-1 dropdown-item">
                    <i class='bx bxs-file-pdf'></i> <span class="d-none d-sm-inline">Export PDF</span>
                </a>
            </li>
            <li>

            </li>
            <li>
                @if ($pr->status === 4 && $user->specification->name === 'PURCHASER')
                    <button data-bs-target="#edit-purchase-request-po-number-{{ $pr->id }}" data-bs-toggle="modal"
                        class="my-1 btn btn-primary dropdown-item"><i class='bx bx-edit'></i> Edit PO
                        Number</button>
                @endif
            </li>
        </ul>
    </div>
@endif
