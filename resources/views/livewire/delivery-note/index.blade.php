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
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label>Status</label>
                    <select class="form-select" wire:model.defer="inputStatus">
                        <option value="all">All</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Branch</label>
                    <select class="form-select" wire:model.defer="inputBranch">
                        <option value="all">All</option>
                        <option value="JAKARTA">JAKARTA</option>
                        <option value="KARAWANG">KARAWANG</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Ritasi</label>
                    <select class="form-select" wire:model.defer="inputRitasi">
                        <option value="all">All</option>
                        <option value="1">1 (Pagi)</option>
                        <option value="2">2 (Siang)</option>
                        <option value="3">3 (Sore)</option>
                        <option value="4">4 (Malam)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" class="form-control" wire:model.defer="inputFromDate">
                </div>
                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" class="form-control" wire:model.defer="inputToDate">
                </div>

                <div class="col-md-2 mt-2">
                    <button wire:click="applyFilters" class="btn btn-primary w-100">
                        🔍 Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-end mb-3">
        <div class="col-md-2">
            <input type="text" class="form-control" wire:model.live="searchAll" placeholder="Search...">
        </div>
    </div>

    @if ($filterStatus !== 'all' || $filterBranch !== 'all' || $filterRitasi !== 'all' || $fromDate || $toDate || $searchAll)
        <div class="alert alert-info mb-3">
            <strong>Active Filters:</strong>
            <ul class="mb-0 small">
                @if ($filterStatus !== 'all')
                    <li>Status: <strong>{{ ucfirst($filterStatus) }}</strong></li>
                @endif
                @if ($filterBranch !== 'all')
                    <li>Branch: <strong>{{ $filterBranch }}</strong></li>
                @endif
                @if ($filterRitasi !== 'all')
                    <li>Ritasi: <strong>{{ $filterRitasi }}</strong></li>
                @endif
                @if ($fromDate)
                    <li>From: <strong>{{ $fromDate }}</strong></li>
                @endif
                @if ($toDate)
                    <li>To: <strong>{{ $toDate }}</strong></li>
                @endif
                @if ($searchAll)
                    <li>Search: <strong>{{ $searchAll }}</strong></li>
                @endif

            </ul>
        </div>
    @endif

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle table-hover">
            <thead class="table-light">
                <tr>
                    <th wire:click="sortBy('id')" style="cursor:pointer;">#
                        @if ($sortField === 'id')
                            @if ($sortDirection === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('branch')" style="cursor: pointer;">Branch
                        @if ($sortField === 'branch')
                            @if ($sortDirection === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('ritasi')" style="cursor: pointer;">
                        Ritasi
                        @if ($sortField === 'ritasi')
                            @if ($sortDirection === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('delivery_note_date')" style="cursor: pointer;">
                        Date
                        @if ($sortField === 'delivery_note_date')
                            @if ($sortDirection === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('plate_number')" style="cursor: pointer;">
                        Vehicle
                        @if ($sortField === 'plate_number')
                            @if ($sortDirection === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('driver_name')" style="cursor: pointer;">
                        Driver
                        @if ($sortField === 'driver_name')
                            @if ($sortDirection === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        @endif
                    </th>
                    <th>Status</th>
                    <th class="text-center" style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveryNotes as $note)
                    <tr>
                        <td>{{ $note->id }}</td>
                        <td>{{ $note->branch }}</td>
                        <td>{{ $note->ritasi_label }}</td>
                        <td>{{ $note->formatted_delivery_note_date }}</td>
                        <td>{{ $note->vehicle->plate_number ?? '-' }}</td>
                        <td>{{ $note->vehicle->driver_name ?? '-' }}</td>
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
    {{ $deliveryNotes->links() }}

</div>
