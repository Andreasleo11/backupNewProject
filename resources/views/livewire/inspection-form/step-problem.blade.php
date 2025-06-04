<div>
    @php
        $showDocumentInfo = false;
    @endphp
    @foreach ($problems as $index => $row)
        <div class="card mb-3 @if (!$showDocumentInfo) d-none @endif">
            <div class="card-body">
                <h6 class="fw-bold text-primary mb-3">Document Information</h6>
                <label class="form-label">Inspection Report Document
                    Number <span class="text-danger">*</span></label>
                <input type="text" wire:model.blur="problems.{{ $index }}.inspection_report_document_number"
                    class="form-control bg-secondary-subtle @error("problems.$index.inspection_report_document_number")
                            is-invalid
                        @enderror"
                    readonly>
                @error("problems.$index.inspection_report_document_number")
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="card p-3 mb-3 border">
            <div class="row g-3">
                <div class="col">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select wire:model.blur="problems.{{ $index }}.type"
                        class="form-control @error("problems.$index.type") is-invalid @enderror">
                        <option value="">-- Select Type --</option>
                        <option value="NO PROBLEM">NO PROBLEM</option>
                        <option value="QUALITY PROBLEM">QUALITY PROBLEM</option>
                        <option value="MOLD PROBLEM">MOLD PROBLEM</option>
                        <option value="MACHINE PROBLEM">MACHINE PROBLEM</option>
                        <option value="4M PROBLEM">4M PROBLEM</option>
                    </select>
                    @error("problems.$index.type")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">Time <span class="text-danger">*</span></label>
                    <input type="time" wire:model.blur="problems.{{ $index }}.time"
                        class="form-control @error("problems.$index.time") is-invalid @enderror" step="1800">
                    @error("problems.$index.time")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">C/T <span class="text-danger">*</span></label>
                    <input type="number" wire:model.blur="problems.{{ $index }}.cycle_time"
                        class="form-control @error("problems.$index.cycle_time") is-invalid @enderror" min="1">
                    @error("problems.$index.cycle_time")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">Remark</label>
                    <input type="text" wire:model.blur="problems.{{ $index }}.remark"
                        class="form-control @error("problems.$index.remark") is-invalid @enderror" rows="2"
                        placeholder="Optional" />
                    @error("problems.$index.remark")
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-auto align-self-end mb-2">
                    <button type="button" class="btn btn-link text-danger btn-sm"
                        wire:click="removeProblem({{ $index }})">Remove</button>
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary" wire:click="addProblem">+ Add Problem</button>
        @if (count($problems) > 0)
            <div>
                <button type="button" class="btn btn-outline-primary" wire:click="saveStep">Save Problems</button>
                <button type="button" class="btn btn-outline-danger" wire:click="resetStep">Reset</button>
            </div>
        @endif
    </div>
</div>
