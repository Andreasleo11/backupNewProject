<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0 text-primary">
        <i class="bi bi-truck me-2"></i>
        {{ $vehicleId ? 'Edit Vehicle' : 'Add New Vehicle' }}
      </h5>
      <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left-circle me-1"></i> Back to List
      </a>
    </div>

    <div class="card-body">
      {{-- Success Message --}}
      @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
          <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      {{-- Form --}}
      <form wire:submit.prevent="save">
        <div class="mb-3">
          <label class="form-label fw-semibold">Plate Number <span
              class="text-danger">*</span></label>
          <input type="text" wire:model.defer="plate_number"
            class="form-control @error('plate_number') is-invalid @enderror"
            placeholder="e.g. B 1234 ABC">
          @error('plate_number')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Driver Name <span
              class="text-danger">*</span></label>
          <input type="text" wire:model.defer="driver_name"
            class="form-control @error('driver_name') is-invalid @enderror"
            placeholder="e.g. John Doe">
          @error('driver_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="d-flex justify-content-end gap-2">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save me-1"></i> Save
          </button>
          <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
