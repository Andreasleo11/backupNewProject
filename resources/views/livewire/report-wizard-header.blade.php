<div>
  <div class="mb-4">
    <h4 class="fw-bold">
      {{ $reportId ? 'Edit' : 'Create' }} Verification Header {{ $reportId ? "#{$reportId}" : '' }}
    </h4>
    <p class="text-muted">
      You need to {{ $reportId ? 'update the existing' : 'fill the' }} verification report header.
    </p>
  </div>
  <form wire:submit.prevent="saveReport" class="needs-validation" novalidate>
    {{-- Header Card --}}
    <div class="card shadow-sm">
      <div class="card-body">
        {{-- Rec Date --}}
        <div class="mb-3">
          <label class="form-label">📅 Received Date <span class="text-danger">*</span></label>
          <input type="date" class="form-control @error('rec_date') is-invalid @enderror"
            wire:model="rec_date">
          @error('rec_date')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Verify Date --}}
        <div class="mb-3">
          <label class="form-label">🔍 Verified Date <span class="text-danger">*</span></label>
          <input type="date" class="form-control @error('verify_date') is-invalid @enderror"
            wire:model="verify_date">
          @error('verify_date')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Customer with suggestions --}}
        <div class="mb-3 position-relative">
          <label class="form-label">👤 Customer <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('customer') is-invalid @enderror"
            wire:model.live="customer" autocomplete="off" placeholder="Start typing...">

          @if (!empty($customerSuggestions))
            <ul class="list-group position-absolute w-100 shadow-sm"
              style="max-height: 200px; overflow-y: auto; z-index: 1050;">
              @foreach ($customerSuggestions as $suggestion)
                <li class="list-group-item list-group-item-action"
                  wire:click="selectCustomer('{{ $suggestion }}')">
                  {{ $suggestion }}
                </li>
              @endforeach
            </ul>
          @endif

          @error('customer')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        {{-- Invoice No --}}
        <div class="mb-3">
          <label class="form-label">🧾 Invoice No <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('invoice_no') is-invalid @enderror"
            wire:model="invoice_no" placeholder="Enter invoice number">
          @error('invoice_no')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
    <div class="d-flex justify-content-between mt-3">
      <button type="button" class="btn btn-outline-danger"
        x-on:click="if (confirm('Are you sure you want to cancel this action?')) { $wire.confirmCancel() }">
        <i class="bi bi-x-circle"></i>
        Cancel</button>
      <button type="submit" class="btn btn-primary">
        Next <i class="bi bi-arrow-right"></i>
      </button>
    </div>
  </form>
</div>
