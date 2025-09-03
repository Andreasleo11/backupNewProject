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

  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col">
          <label for="appearance" class="form-label">Appearance <span
              class="text-danger">*</span></label>
          <select id="appearance" wire:model.live="appearance"
            class="form-select @error('appearance') is-invalid @enderror">
            <option value="">-- Select Appearance --</option>
            <option value="OK">OK</option>
            <option value="NG">NG</option>
          </select>
          @error('appearance')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
        @if ($appearance === 'NG')
          <div class="col">
            <label for="remarks" class="form-label">Remarks <span
                class="text-danger">*</span></label>
            <input type="text" id="remarks" wire:model.blur="remarks"
              class="form-control @error('remarks') is-invalid @enderror">
            @error('remarks')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
        @endif
        <div class="col">
          <div class="row">
            <div class="col">
              <label class="form-label">Weight <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number"
                  class="form-control text-end @error('weight') is-invalid @enderror"
                  wire:model.blur="weight" min="0">
                <select class="form-select @error('weight_uom') is-invalid @enderror"
                  wire:model.blur="weight_uom">
                  <option value="" selected></option>
                  <option value="kg">KG</option>
                  <option value="g">Gram</option>
                </select>
                @error('weight')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                @error('weight_uom')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <div class="col">
          <label for="fitting_test" class="form-label">Fitting Test</label>
          <input type="text" id="fitting_test" wire:model.blur="fitting_test"
            class="form-control @error('fitting_test') is-invalid @enderror" placeholder="Optional">
          @error('fitting_test')
            <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <div class="text-end">
    <button type="button" class="btn btn-outline-primary" wire:click="saveStep">Save First
      Inspection</button>
    <button class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
  </div>
</div>
