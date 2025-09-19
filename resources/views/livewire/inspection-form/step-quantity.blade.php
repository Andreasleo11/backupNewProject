<div>
  @php
    $showDocumentInfo = false;
  @endphp
  <div class="card mb-4 @if (!$showDocumentInfo) d-none @endif">
    <div class="card-body">
      <h6 class="text-primary fw-bold mb-3">Document Information</h6>
      <div class="mb-3">
        <label for="inspection_report_document_number" class="form-label">Inspection Report Document
          Number <span class="text-danger">*</span></label>
        <input type="text" id="inspection_report_document_number"
          wire:model.blur="inspection_report_document_number"
          class="form-control bg-secondary-subtle" readonly>
        @error('inspection_report_document_number')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col">
          <label class="form-label">
            Output Qty <span class="text-danger">*</span>
          </label>
          <input type="number" min="0" wire:model.live="output_quantity"
            class="form-control @error('output_quantity') is-invalid @enderror">
          @error('output_quantity')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <div class="col">
          <label class="form-label">
            Pass Qty <span class="text-danger">*</span>
          </label>
          <input type="number" min="0" wire:model.live="pass_quantity"
            class="form-control @error('pass_quantity') is-invalid @enderror">
          @error('pass_quantity')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <div class="col">
          <label class="form-label">
            Reject Qty <span class="text-danger">*</span>
          </label>
          <input type="number" min="0" wire:model.live="reject_quantity"
            class="form-control @error('reject_quantity') is-invalid @enderror">
          @error('reject_quantity')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <div class="col">
          <label class="form-label">
            Sampling Qty <span class="text-danger">*</span>
          </label>
          <input type="number" min="1" wire:model.live="sampling_quantity"
            class="form-control @error('sampling_quantity') is-invalid @enderror">
          @error('sampling_quantity')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
        <div class="col">
          <label class="form-label">Reject %</label>
          <input type="text" class="form-control bg-secondary-subtle" wire:model="reject_rate"
            readonly>
        </div>
      </div>

      <div class="text-end mt-3">
        <button type="button" class="btn btn-outline-primary" wire:click="saveStep">
          Save Quantities
        </button>
        <button type="button" class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
      </div>
    </div>
  </div>
</div>
