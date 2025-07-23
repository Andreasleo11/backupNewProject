<div class="container py-4">

    {{-- Success Message --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">📋 Delivery Notes</h3>
        <a href="{{ route('delivery-notes.create') }}" class="btn btn-primary">
            + Create Delivery Note
        </a>
    </div>

    {{-- Filters --}}
    <div class="btn-group mb-3" role="group" aria-label="Filter by Status">
        <button class="btn btn-outline-secondary @if ($filterStatus === 'all') active @endif"
            wire:click="setFilter('all')">All</button>
        <button class="btn btn-outline-warning @if ($filterStatus === 'draft') active @endif"
            wire:click="setFilter('draft')">Draft</button>
        <button class="btn btn-outline-success @if ($filterStatus === 'submitted') active @endif"
            wire:click="setFilter('submitted')">Submitted</button>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Branch</th>
                    <th>Ritasi</th>
                    <th>Date</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Approval Flow</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveryNotes as $note)
                    <tr>
                        <td>{{ $note->id }}</td>
                        <td>{{ $note->branch }}</td>
                        <td>{{ $note->ritasi }}</td>
                        <td>{{ \Carbon\Carbon::parse($note->delivery_note_date)->format('d M Y') }}</td>
                        <td>{{ $note->vehicle_number }}</td>
                        <td>{{ $note->driver_name }}</td>
                        <td>{{ $note->approvalFlow->name ?? '-' }}</td>
                        <td>
                            <span
                                class="badge 
                                @if ($note->status === 'draft') bg-warning text-dark 
                                @else bg-success @endif">
                                {{ ucfirst($note->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('delivery-notes.show', $note->id) }}"
                                    class="btn btn-sm btn-outline-info" title="View">
                                    🔍
                                </a>
                                <a href="{{ route('delivery-notes.edit', $note->id) }}"
                                    class="btn btn-sm btn-outline-warning" title="Edit">
                                    ✏️
                                </a>
                                <button x-data
                                    @click.prevent="if (confirm('Are you sure you want to delete this delivery note?')) { $wire.delete({{ $note->id }}) }"
                                    class="btn btn-sm btn-outline-danger" title="Delete">
                                    🗑
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">No delivery notes found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $deliveryNotes->links() }}
    </div>

</div>
