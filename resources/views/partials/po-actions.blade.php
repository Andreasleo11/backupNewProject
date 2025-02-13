<a href="{{ route('po.view', $po->id) }}" class="btn btn-outline-primary">
    <i class="bi bi-eye"></i></i> View
</a>
@if ($po->status === 1 && auth()->user()->department->name !== 'DIRECTOR')
    <a href="{{ route('po.edit', $po->id) }}" class="btn btn-outline-secondary my-1">
        <i class="bi bi-pencil"></i></i> Edit
    </a>
@endif
@if (auth()->user()->role->name === 'SUPERADMIN')
    @include('partials.delete-confirmation-modal', [
        'id' => $po->id,
        'route' => 'po.destroy',
        'title' => 'Delete PO confirmation',
        'body' => "Are you sure want to delete this PO with id <strong>$po->id</strong>?",
    ])
    <button class="btn btn-outline-danger my-1" data-bs-toggle="modal"
        data-bs-target="#delete-confirmation-modal-{{ $po->id }}">
        <i class="bi bi-trash"></i>
        <span class="d-none d-sm-inline">Delete</span>
    </button>
@endif
@if (
    ($po->status === 2 && auth()->user()->department->name === 'ACCOUNTING') ||
        auth()->user()->role->name === 'SUPERADMIN')
    @include('partials.cancel-confirmation-modal', [
        'id' => $po->id,
        'route' => route('po.cancel', $po->id),
    ])
    <button class="btn btn-outline-danger my-1" data-bs-target="#cancel-confirmation-modal-{{ $po->id }}"
        data-bs-toggle="modal">
        <i class='bx bx-x-circle'></i>
        <span class="d-none d-sm-inline">Cancel</span> </button>
@endif
