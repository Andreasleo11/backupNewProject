<div class="container py-4">

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-primary">
      <i class="bi bi-truck-front me-2"></i> Vehicle Management
    </h4>
    <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-circle me-1"></i> Add Vehicle
    </a>
  </div>

  {{-- Success Flash --}}
  @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Search --}}
  <div class="input-group mb-3">
    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
    <input type="text" class="form-control" wire:model.live="search"
      placeholder="Search vehicle number or driver...">
  </div>

  {{-- Loading Indicator --}}
  <div wire:loading wire:target="search" class="text-muted small mb-2">
    <i class="spinner-border spinner-border-sm me-1"></i> Searching...
  </div>

  {{-- Table --}}
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-uppercase small text-secondary">
          <tr>
            <th>Plate Number</th>
            <th>Driver Name</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($vehicles as $v)
            <tr>
              <td class="fw-semibold">{{ $v->plate_number }}</td>
              <td>{{ $v->driver_name }}</td>
              <td class="text-center">
                <a href="{{ route('vehicles.edit', $v->id) }}" class="btn btn-sm btn-warning me-1"
                  title="Edit">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <button wire:click="delete({{ $v->id }})"
                  onclick="return confirm('Delete this vehicle?')"
                  class="btn btn-sm btn-outline-danger" title="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted py-4">
                <i class="bi bi-truck-flatbed fs-4 mb-2 d-block"></i>
                No vehicles found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
