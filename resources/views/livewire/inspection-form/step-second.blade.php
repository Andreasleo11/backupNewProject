<div>
    @php
        $showDocumentInfo = false;
    @endphp
    <div class="card mb-4">
        <div class="card-body">
            {{-- Status chip (JS island) --}}
            <div wire:ignore x-data="{ dirty: false, saved: @js($isSaved), ts: @js($savedAt) }" x-init="const root = $el.parentElement.querySelector('[data-step-second]'); // sibling lookup
            const markDirty = () => { dirty = true;
                saved = false };
            root.addEventListener('input', markDirty, { capture: true });
            root.addEventListener('change', markDirty, { capture: true });
            
            Livewire.on('secondInspectionSaved', e => {
                dirty = false;
                saved = true;
                ts = e?.savedAt ?? new Date().toISOString();
            });
            Livewire.on('secondInspectionReset', () => {
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
                    <span
                        class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">
                        <i class="bi bi-check-circle me-1"></i> Saved to session
                        <small class="ms-1" x-text="ts ? new Date(ts).toLocaleString() : ''"></small>
                    </span>
                </template>
            </div>

            <div data-step-second>
                <div class="@if (!$showDocumentInfo) d-none @endif">
                    <div class="mb-3">
                        <label class="form-label">Detail Inspection Report Document Number <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-secondary-subtle"
                            wire:model.blur="detail_inspection_report_document_number" readonly>
                        @error('detail_inspection_report_document_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Document Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-secondary-subtle" wire:model.blur="document_number"
                            readonly>
                        @error('document_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div x-data="{ skipLot: @entangle('skipLotSize') }" class="mb-3">
                    <label class="form-label d-block">Skip Lot Size?</label>

                    <div class="form-check form-check-inline">
                        <input type="radio" id="skip-no" value="false" x-model.boolean="skipLot">
                        <label class="form-check-label" for="skip-no">No</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input type="radio" id="skip-yes" value="true" x-model.boolean="skipLot">
                        <label class="form-check-label" for="skip-yes">Yes</label>
                    </div>

                    <div x-show="!skipLot" x-transition.opacity class="mt-3">
                        <label class="form-label">
                            Lot Size Quantity <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                            class="form-control @error('lot_size_quantity') is-invalid @enderror {{ $this->hasBaseline && $this->isFieldSaved('lot_size_quantity') && !$errors->has('lot_size_quantity') ? 'is-valid' : '' }}"
                            wire:model.defer="lot_size_quantity">
                        @error('lot_size_quantity')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="text-end mb-2">
                    <button class="btn btn-outline-primary" wire:click="saveStep">Save Second
                        Inspection</button>
                    <button class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
                </div>
            </div>

            @if ($secondInspectionSaved)
                <div>
                    <div class="fw-bold text-primary mb-2 @if ($savedSamples) text-success @endif">
                        Sampling</div>
                    @livewire('inspection-form.step-sampling', ['second_inspection_document_number' => $document_number], key('step-sampling'))

                    <div class="mt-4">
                        <div class="fw-bold text-primary mb-2 @if ($savedPackagings) text-success @endif">
                            Packaging</div>
                        @livewire('inspection-form.step-packaging', ['second_inspection_document_number' => $document_number], key('step-packaging'))
                    </div>
                </div>
            @else
                <div>
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        <div>
                            <strong>Almost there!</strong><br>
                            Save the <em>Second Inspection</em> section to unlock
                            the Sampling and Packaging steps.
                        </div>
                        <a class="btn btn-sm btn-primary ms-auto" href="#secondInspection">
                            Go to Second Inspection
                        </a>
                    </div>

                    {{-- Optional blurred placeholders so the page layout stays visible --}}
                    <div class="position-relative">
                        <div class="card opacity-25" style="filter: blur(2px);">
                            <div class="card-body text-center py-5">
                                <h5 class="text-muted">Sampling (locked)</h5>
                            </div>
                        </div>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <i class="bi bi-lock-fill fs-1 text-secondary"></i>
                        </div>
                    </div>

                    <div class="position-relative mt-3">
                        <div class="card opacity-25" style="filter: blur(2px);">
                            <div class="card-body text-center py-5">
                                <h5 class="text-muted">Packaging (locked)</h5>
                            </div>
                        </div>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <i class="bi bi-lock-fill fs-1 text-secondary"></i>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
