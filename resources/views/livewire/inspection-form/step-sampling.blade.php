<div>
    @php
        $showDocumentInfo = false;
    @endphp
    @foreach ($samples as $index => $sample)
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
                    <input type="number" class="form-control @error("samples.$index.quantity") is-invalid @enderror"
                        wire:model.blur="samples.{{ $index }}.quantity">
                    @error("samples.$index.quantity")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">Box Label <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error("samples.$index.box_label") is-invalid @enderror"
                        wire:model.blur="samples.{{ $index }}.box_label">
                    @error("samples.$index.box_label")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">Appearance <span class="text-danger">*</span></label>
                    <select class="form-select @error("samples.$index.appearance") is-invalid @enderror"
                        wire:model.live="samples.{{ $index }}.appearance">
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
                        <input type="number"
                            class="form-control @error("samples.$index.ng_quantity") is-invalid @enderror"
                            wire:model.blur="samples.{{ $index }}.ng_quantity">
                        @error("samples.$index.ng_quantity")
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error("samples.$index.remarks") is-invalid @enderror"
                            wire:model.blur="samples.{{ $index }}.remarks">
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
        <button type="button" class="btn btn-outline-secondary" wire:click="addSample">+ Add Sampling</button>
        @php
            $showButtons = count($samples) > 0;
        @endphp
        <div @if (!$showButtons) class="d-none" @endif>
            <button type="button" class="btn btn-outline-primary" wire:click="saveStep">Save Sampling</button>
            <button type="button" class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
        </div>
    </div>
</div>
