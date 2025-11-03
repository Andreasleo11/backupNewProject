<div data-step-judgement>
    @php
        $showDocumentInfo = false;
    @endphp

    {{-- Status chip (kept stable with wire:ignore; listens for events) --}}
    <div wire:ignore x-data="{ dirty: false, saved: @js($isSaved), ts: @js($savedAt) }" x-init="const root = $el.closest('[data-step-judgement]');;
    const markDirty = () => { dirty = true;
        saved = false };
    
    root.addEventListener('input', markDirty, { capture: true });
    root.addEventListener('change', markDirty, { capture: true });
    
    Livewire.on('judgementSaved', e => {
        dirty = false;
        saved = true;
        ts = e?.savedAt ?? new Date().toISOString();
    });
    Livewire.on('judgementReset', () => { dirty = false;
        saved = false;
        ts = null });" class="mb-2" aria-live="polite">
        <template x-if="dirty">
            <span class="badge rounded-pill bg-warning text-dark">
                <i class="bi bi-exclamation-triangle me-1"></i> Unsaved changes
            </span>
        </template>
        <template x-if="!dirty && saved">
            <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">
                <i class="bi bi-check-circle me-1"></i> Saved to session
                <small class="ms-1" x-text="ts ? new Date(ts).toLocaleString() : ''"></small>
            </span>
        </template>
    </div>

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
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Pass Quantity <span class="text-danger">*</span></label>
                    <input type="number" min="0" wire:model.blur="pass_quantity" required
                        @class([
                            'form-control',
                            'is-invalid' => $errors->has('pass_quantity'),
                            'is-valid' =>
                                $this->hasBaseline &&
                                $this->isFieldSaved('pass_quantity') &&
                                !$errors->has('pass_quantity'),
                        ])>
                    @error('pass_quantity')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Reject Quantity <span class="text-danger">*</span></label>
                    <input type="number" min="0" wire:model.blur="reject_quantity" required
                        @class([
                            'form-control',
                            'is-invalid' => $errors->has('reject_quantity'),
                            'is-valid' =>
                                $this->hasBaseline &&
                                $this->isFieldSaved('reject_quantity') &&
                                !$errors->has('reject_quantity'),
                        ])>
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
