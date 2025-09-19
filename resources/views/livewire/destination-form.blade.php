<div class="container py-4">
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white">
      <h4 class="mb-0 text-primary">
        <i class="bi bi-geo-alt me-2"></i>
        {{ $destinationId ? 'Edit' : 'Add' }} Destination
      </h4>
    </div>

    <div class="card-body bg-white">
      {{-- Flash success (optional) --}}
      @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <form wire:submit.prevent="save">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
            <input wire:model.defer="name" type="text"
              class="form-control @error('name') is-invalid @enderror"
              placeholder="e.g. Surabaya Port">
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">City</label>
            <input wire:model.defer="city" type="text" class="form-control"
              placeholder="e.g. Surabaya">
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Description</label>
          <textarea wire:model.defer="description" class="form-control" rows="3"
            placeholder="Optional notes, location, access info, etc."></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <a href="{{ route('destination.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle me-1"></i> Cancel
          </a>
          <button class="btn btn-success">
            <i class="bi bi-save2 me-1"></i> Save Destination
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
