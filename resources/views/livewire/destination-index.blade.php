<div class="container">
    {{-- Card wrapper --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary"><i class="bi bi-geo-alt-fill me-2"></i> Destination Suggestions
            </h4>
            <a href="{{ route('destination.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
        </div>

        <div class="card-body bg-white">
            {{-- Flash success --}}
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Search input --}}
            <div class="input-group mb-4">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input wire:model.live="search" type="text" class="form-control"
                    placeholder="Search destinations by name or city...">
            </div>

            {{-- Livewire loading spinner --}}
            <div wire:loading wire:target="search" class="text-muted small mb-3">
                <i class="spinner-border spinner-border-sm text-secondary me-2"></i>Searching...
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover table-borderless align-middle">
                    <thead class="table-primary text-uppercase text-secondary small">
                        <tr>
                            <th class="fw-bold">Name</th>
                            <th class="fw-bold text-center">City</th>
                            <th class="fw-bold">Description</th>
                            <th class="fw-bold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($destinations as $destination)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $destination->name }}</div>
                                    <div><span class="badge bg-secondary">{{ $destination->code }}</span></div>
                                </td>
                                <td class="text-center">
                                    {{ $destination->city ?? '-' }}
                                </td>
                                <td class="text-muted">
                                    {{ $destination->description ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('destination.edit', $destination->id) }}"
                                        class="btn btn-warning btn-sm me-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <button wire:click="delete({{ $destination->id }})" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this destination?')"
                                        title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-geo-alt-slash fs-4 d-block mb-2"></i>
                                    No destination found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
