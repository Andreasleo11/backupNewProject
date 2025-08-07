<div>
    @php
        $showDocumentInfo = false;
    @endphp
    @foreach ($packagings as $index => $item)
        <div class="card p-3 mb-3 @error("packagings.$index") is-invalid @enderror">
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
                    <input type="number" class="form-control @error("packagings.$index.snp") is-invalid @enderror"
                        wire:model.blur="packagings.{{ $index }}.snp">
                    @error("packagings.$index.snp")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col">
                    <label class="form-label">Box Label <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error("packagings.$index.box_label") is-invalid @enderror"
                        wire:model.blur="packagings.{{ $index }}.box_label">
                    @error("packagings.$index.box_label")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col">
                    <label class="form-label">Judgement <span class="text-danger">*</span></label>
                    <select class="form-select @error("packagings.$index.judgement") is-invalid @enderror"
                        wire:model.live="packagings.{{ $index }}.judgement">
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
                        <input class="form-control @error('packagings.' . $index . '.remarks') is-invalid @enderror"
                            wire:model.blur="packagings.{{ $index }}.remarks"></input>
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
        <button type="button" class="btn btn-outline-secondary" wire:click="addPackaging">+ Add Packaging</button>
        <div>
            <button type="button" class="btn btn-outline-primary" wire:click="saveStep">Save Packaging</button>
            <button type="button" class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
        </div>
    </div>
</div>
