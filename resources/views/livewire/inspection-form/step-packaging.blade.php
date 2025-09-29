<div data-step-packaging>
    {{-- Status chip (JS island) --}}
    <div wire:ignore x-data="{ dirty: false, saved: @js($isSaved), ts: @js($savedAt) }" x-init="const root = $el.closest('[data-step-packaging]');
    const markDirty = () => { dirty = true;
        saved = false };
    root.addEventListener('input', markDirty, { capture: true });
    root.addEventListener('change', markDirty, { capture: true });
    
    Livewire.on('packagingSaved', e => {
        dirty = false;
        saved = true;
        ts = e?.savedAt ?? new Date().toISOString();
    });
    Livewire.on('packagingReset', () => {
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
    @foreach ($packagings as $index => $item)
        @php $rk = $item['row_key'] ?? null; @endphp
        <div class="card p-3 mb-3">
            <div class="row g-3">
                <div class="col @if (!$showDocumentInfo) d-none @endif">
                    <label class="form-label">Second Inspection Report Document Number <span
                            class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control bg-secondary-subtle @error("packagings.$index.second_inspection_document_number") is-invalid @enderror "
                        wire:model.blur="packagings.{{ $index }}.second_inspection_document_number" readonly>
                    @error("packagings.$index.second_inspection_document_number")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col">
                    <label class="form-label">SNP <span class="text-danger">*</span></label>
                    <input type="number" wire:model.blur="packagings.{{ $index }}.snp"
                        @class([
                            'form-control',
                            'is-invalid' => $errors->has("packagings.$index.snp"),
                            'is-valid' =>
                                $rk &&
                                $this->isRowFieldSaved($rk, 'snp') &&
                                !$errors->has("packagings.$index.snp"),
                        ])>
                    @error("packagings.$index.snp")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col">
                    <label class="form-label">Box Label <span class="text-danger">*</span></label>
                    <input type="text" wire:model.blur="packagings.{{ $index }}.box_label"
                        @class([
                            'form-control',
                            'is-invalid' => $errors->has("packagings.$index.box_label"),
                            'is-valid' =>
                                $rk &&
                                $this->isRowFieldSaved($rk, 'box_label') &&
                                !$errors->has("packagings.$index.box_label"),
                        ])>
                    @error("packagings.$index.box_label")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col">
                    <label class="form-label">Judgement <span class="text-danger">*</span></label>
                    <select wire:model.live="packagings.{{ $index }}.judgement" @class([
                        'form-select',
                        'is-invalid' => $errors->has("packagings.$index.judgement"),
                        'is-valid' =>
                            $rk &&
                            $this->isRowFieldSaved($rk, 'judgement') &&
                            !$errors->has("packagings.$index.judgement"),
                    ])>
                        <option value="">-- Select Judgement --</option>
                        <option value="OK">OK</option>
                        <option value="NG">NG</option>
                    </select>
                    @error("packagings.$index.judgement")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                @if (($item['judgement'] ?? '') === 'NG')
                    <div class="col">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <input wire:model.blur="packagings.{{ $index }}.remarks"
                            @class([
                                'form-control',
                                'is-invalid' => $errors->has("packagings.$index.remarks"),
                                'is-valid' =>
                                    $rk &&
                                    $this->isRowFieldSaved($rk, 'remarks') &&
                                    !$errors->has("packagings.$index.remarks"),
                            ])></input>
                        @error("packagings.$index.remarks")
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                <div class="col-auto align-self-end mb-1">
                    <button class="btn btn-link text-danger btn-sm" type="button"
                        wire:click="removePackaging({{ $index }})">Remove</button>
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary" wire:click="addPackaging">+ Add
            Packaging</button>
        <div>
            <button type="button" class="btn btn-outline-primary" wire:click="saveStep">Save
                Packaging</button>
            <button type="button" class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
        </div>
    </div>
</div>
