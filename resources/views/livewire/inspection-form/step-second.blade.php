<div>
    @php
        $showDocumentInfo = false;
    @endphp
    <div class="card mb-4">
        <div class="card-body">
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

            <div class="mb-3">
                <label class="form-label">Lot Size Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('lot_size_quantity') is-invalid @enderror"
                    wire:model.blur="lot_size_quantity">
                @error('lot_size_quantity')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-end">
                <button class="btn btn-outline-primary" wire:click="saveStep">Save Second Inspection</button>
                <button class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
            </div>

            <div class="mt-3">
                @if (session("stepDetailSaved.second_inspections.$quarterKey"))
                    <div class="fw-bold text-primary mb-2">Sampling</div>
                    @livewire('inspection-form.step-sampling', ['second_inspection_document_number' => $document_number], key('step-sampling'))


                    <div class="mt-4">
                        <div class="fw-bold text-primary mb-2">Packaging</div>
                        @livewire('inspection-form.step-packaging', ['second_inspection_document_number' => $document_number], key('step-packaging'))
                    </div>
                @else
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
                @endif

            </div>
        </div>
    </div>
</div>
