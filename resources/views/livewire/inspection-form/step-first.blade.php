<div data-step-first>
    <!-- Status chip -->
    <div wire:ignore x-data="{ dirty: false, saved: @js($isSaved), ts: @js($savedAt) }" x-init="const root = $el.closest('[data-step-first]');
    const markDirty = () => { dirty = true;
        saved = false };
    
    if (root) {
        root.addEventListener('input', markDirty, { capture: true });
        root.addEventListener('change', markDirty, { capture: true });
    }
    
    Livewire.on('firstInspectionSaved', (e) => {
        dirty = false, saved = true;
        ts = e?.savedAt ?? new Date().toISOString();
    });
    
    Livewire.on('firstInspectionReset', () => {
        dirty = false, saved = false;
        ts = null;
    });" aria-live="polite" class="mb-2">
        <template x-if="dirty">
            <span class="badge rounded-pill bg-warning text-dark">
                <i class="bi bi-exclamation-triangle me-1"></i> Unsaved changes
            </span>
        </template>

        <template x-if="!dirty && saved">
            <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">
                <i class="bi bi-check-circle me-1"></i>
                Saved to session
                <small class="ms-1" x-text="ts ? new Date(ts).toLocaleString() : ''"></small>
            </span>
        </template>
    </div>
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
                    wire:model.blur="detail_inspection_report_document_number" class="form-control bg-secondary-subtle"
                    readonly>
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
                    <label for="appearance" class="form-label">Appearance <span class="text-danger">*</span></label>
                    <select id="appearance" wire:model.live="appearance"
                        class="form-select @error('appearance') is-invalid @enderror @if ($this->hasBaseline && $this->isFieldSaved('appearance') && !$errors->has('appearance')) is-valid @endif">
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
                        <label for="remarks" class="form-label">Remarks <span class="text-danger">*</span></label>
                        <input type="text" id="remarks" wire:model.blur="remarks"
                            class="form-control @error('remarks') is-invalid @enderror @if ($this->hasBaseline && $this->isFieldSaved('remarks') && !$errors->has('remarks')) is-valid @endif">
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
                                    class="form-control text-end @error('weight') is-invalid @enderror @if ($this->hasBaseline && $this->isGroupSaved(['weight', 'weight_uom']) && !$errors->has('weight')) is-valid @endif"
                                    wire:model.blur="weight" min="0">
                                <select
                                    class="form-select @error('weight_uom') is-invalid @enderror @if ($this->hasBaseline && $this->isGroupSaved(['weight', 'weight_uom']) && !$errors->has('weight_uom')) is-valid @endif"
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
                        class="form-control @error('fitting_test') is-invalid @enderror @if ($this->hasBaseline && $this->isFieldSaved('fitting_test') && !$errors->has('fitting_test')) is-valid @endif"
                        placeholder="Optional">
                    @error('fitting_test')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="text-end">
        <button type="button" class="btn btn-outline-primary" wire:click="saveStep" wire:loading.attr="disabled"
            x-data="{ justSaved: false }" x-init="Livewire.on('firstInspectionSaved', () => { justSaved = true;
                setTimeout(() => justSaved = false, 1500) })">
            <span wire:loading.remove>
                <span x-show="!justSaved">Save First Inspection</span>
                <span x-show="justSaved"><i class="bi bi-check2-circle me-1"></i> Saved</span>
            </span>
            <span wire:loading>Saving…</span>
        </button>
        <button class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
    </div>
</div>
