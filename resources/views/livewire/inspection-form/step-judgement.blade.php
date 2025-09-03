<div>
  @php
    $showDocumentInfo = false;
  @endphp
  <div class="card mb-4 @if (!$showDocumentInfo) d-none @endif">
    <div class="card-body">
      <h6 class="text-primary fw-bold mb-3">Document Information</h6>
      <div class="mb-3">
        <label for="detail_inspection_report_document_number" class="form-label">Detail Inspection
          Report Document
          Number <span class="text-danger">*</span></label>
        <input type="text" id="detail_inspection_report_document_number"
          wire:model.blur="detail_inspection_report_document_number"
          class="form-control bg-secondary-subtle" readonly>
        @error('detail_inspection_report_document_number')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <label class="form-label">Pass Quantity <span class="text-danger">*</span></label>
          <input type="number" min="0"
            class="form-control @error('pass_quantity') is-invalid @enderror"
            wire:model.blur="pass_quantity" required>
          @error('pass_quantity')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Reject Quantity <span class="text-danger">*</span></label>
          <input type="number" min="0"
            class="form-control @error('reject_quantity') is-invalid @enderror"
            wire:model.blur="reject_quantity" required>
          @error('reject_quantity')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
      </div>
    </div>
  </div>
  <div class="text-end">
    <button type="button" class="btn btn-outline-primary" wire:click="saveStep">Save
      Judgements</button>
    <button type="button" class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
  </div>
</div>
