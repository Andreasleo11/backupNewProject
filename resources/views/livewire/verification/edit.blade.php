<div class="container py-4">
  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h5 mb-0">
        {{ $report?->id ? 'Edit Verification Report' : 'New Verification Report' }}
      </h1>
      @if($report?->document_number)
        <div class="text-muted small">Doc#: {{ $report->document_number }}</div>
      @endif
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('verification.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
      </a>
      <button class="btn btn-primary" wire:click="save">
        <i class="bi bi-save"></i> Save
      </button>
    </div>
  </div>

  {{-- Errors --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-1">Please fix the following:</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row g-3">
    {{-- Left column: header fields --}}
    <div class="col-12 col-lg-5">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('form.title') is-invalid @enderror"
                   placeholder="Enter a concise report title"
                   wire:model.live.defer="form.title">
            @error('form.title') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea rows="4" class="form-control @error('form.description') is-invalid @enderror"
                      placeholder="Optional notes / context"
                      wire:model.live.defer="form.description"></textarea>
            @error('form.description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Optional: simple meta.department input (matches resolver context) --}}
          <div class="mb-0">
            <label class="form-label">Department (optional)</label>
            <input type="text" class="form-control"
                   placeholder="e.g. FIN, OPS"
                   wire:model.live.defer="form.meta.department">
            <div class="form-text">Used by approval resolver when submitting.</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Right column: items repeater --}}
    <div class="col-12 col-lg-7">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">Items</div>
            <button class="btn btn-sm btn-outline-primary" wire:click="addItem" type="button">
              <i class="bi bi-plus-lg"></i> Add item
            </button>
          </div>

          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
              <tr>
                <th style="width: 30%">Name <span class="text-danger">*</span></th>
                <th style="width: 40%">Notes</th>
                <th style="width: 20%">Amount</th>
                <th style="width: 10%"></th>
              </tr>
              </thead>
              <tbody>
              @forelse($items as $i => $row)
                <tr>
                  <td>
                    <input type="text" class="form-control @error('items.'.$i.'.name') is-invalid @enderror"
                           placeholder="Item name"
                           wire:model.live.defer="items.{{ $i }}.name">
                    @error('items.'.$i.'.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </td>
                  <td>
                    <input type="text" class="form-control"
                           placeholder="Notes (optional)"
                           wire:model.live.defer="items.{{ $i }}.notes">
                  </td>
                  <td style="max-width: 160px">
                    <input type="number" step="0.01" class="form-control @error('items.'.$i.'.amount') is-invalid @enderror"
                           placeholder="0"
                           wire:model.live.defer="items.{{ $i }}.amount">
                    @error('items.'.$i.'.amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            wire:click="removeItem({{ $i }})">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-muted text-center py-4">No items yet. Add one to get started.</td>
                </tr>
              @endforelse
              </tbody>
              @if(count($items))
                <tfoot>
                  <tr>
                    <th colspan="2" class="text-end">Total</th>
                    <th>
                      {{ number_format(collect($items)->sum(fn($r)=> (float)($r['amount'] ?? 0)), 2) }}
                    </th>
                    <th></th>
                  </tr>
                </tfoot>
              @endif
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
