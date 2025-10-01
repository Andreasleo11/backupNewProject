<div data-step-sampling>
    {{-- Status chip (JS island) --}}
    <div wire:ignore x-data="{ dirty: false, saved: @js($isSaved), ts: @js($savedAt) }" x-init="const root = $el.closest('[data-step-sampling]');
    const markDirty = () => { dirty = true;
        saved = false };
    root.addEventListener('input', markDirty, { capture: true });
    root.addEventListener('change', markDirty, { capture: true });
    
    Livewire.on('samplingSaved', e => {
        dirty = false;
        saved = true;
        ts = e?.savedAt ?? new Date().toISOString();
    });
    Livewire.on('samplingReset', () => {
        dirty = false;
        saved = false;
        ts = null;
    });" class="mb-2" aria-live="polite">
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
    @php
        $showDocumentInfo = false;
    @endphp
    @foreach ($samples as $index => $sample)
        @php $rk = $sample['row_key'] ?? null; @endphp
        <div class="card p-3 mb-3 border">
            <div class="row g-3">
                <div class="col-12 @if (!$showDocumentInfo) d-none @endif">
                    <label class="form-label">Second Inspection Report Document Number <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control bg-secondary-subtle"
                        wire:model.blur="samples.{{ $index }}.second_inspection_document_number" readonly>
                    @error("samples.$index.second_inspection_document_number")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" wire:model.blur="samples.{{ $index }}.quantity"
                        @class([
                            'form-control',
                            'is-invalid' => $errors->has("samples.$index.quantity"),
                            'is-valid' =>
                                $rk &&
                                $this->isRowFieldSaved($rk, 'quantity') &&
                                !$errors->has("samples.$index.quantity"),
                        ])>
                    @error("samples.$index.quantity")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">Box Label <span class="text-danger">*</span></label>
                    <input type="text" wire:model.blur="samples.{{ $index }}.box_label"
                        @class([
                            'form-control',
                            'is-invalid' => $errors->has("samples.$index.box_label"),
                            'is-valid' =>
                                $rk &&
                                $this->isRowFieldSaved($rk, 'box_label') &&
                                !$errors->has("samples.$index.box_label"),
                        ])>
                    @error("samples.$index.box_label")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">Appearance <span class="text-danger">*</span></label>
                    <select wire:model.live="samples.{{ $index }}.appearance" @class([
                        'form-select',
                        'is-invalid' => $errors->has("samples.$index.appearance"),
                        'is-valid' =>
                            $rk &&
                            $this->isRowFieldSaved($rk, 'appearance') &&
                            !$errors->has("samples.$index.appearance"),
                    ])>
                        <option value="">-- Select Appearance --</option>
                        <option value="OK">OK</option>
                        <option value="NG">NG</option>
                    </select>
                    @error("samples.$index.appearance")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                @if (($sample['appearance'] ?? '') === 'NG')
                    <div class="col">
                        <label class="form-label">NG Quantity <span class="text-danger">*</span></label>
                        <input type="number" wire:model.blur="samples.{{ $index }}.ng_quantity"
                            @class([
                                'form-control',
                                'is-invalid' => $errors->has("samples.$index.ng_quantity"),
                                'is-valid' =>
                                    $rk &&
                                    $this->isRowFieldSaved($rk, 'ng_quantity') &&
                                    !$errors->has("samples.$index.ng_quantity"),
                            ])>
                        @error("samples.$index.ng_quantity")
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <input type="text" wire:model.blur="samples.{{ $index }}.remarks"
                            @class([
                                'form-control',
                                'is-invalid' => $errors->has("samples.$index.remarks"),
                                'is-valid' =>
                                    $rk &&
                                    $this->isRowFieldSaved($rk, 'remarks') &&
                                    !$errors->has("samples.$index.remarks"),
                            ])>
                        @error("samples.$index.remarks")
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <div class="col-auto align-self-end mb-1">
                    <div class="btn btn-link text-danger btn-sm" type="button"
                        wire:click="removeSample({{ $index }})">
                        Remove</div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary" wire:click="addSample">+ Add
            Sampling</button>
        @php
            $showButtons = count($samples) > 0;
        @endphp
        <div @if (!$showButtons) class="d-none" @endif>
            <button type="button" class="btn btn-outline-primary" wire:click="saveStep">Save
                Sampling</button>
            <button type="button" class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
        </div>
    </div>
</div>
