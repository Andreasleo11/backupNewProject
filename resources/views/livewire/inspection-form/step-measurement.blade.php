<div>
    @php
        $showDocumentInfo = false;
    @endphp
    <div>
        @if ($measurements)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col">
                            <label class="form-label">Start Time</label>
                            <input type="time" step="900"
                                class="form-control @error('start_time') is-invalid @enderror"
                                wire:model.blur="start_time">
                            @error('start_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>`
                        <div class="col">
                            <label class="form-label">End Time</label>
                            <input type="time" step="900"
                                class="form-control @error('end_time') is-invalid @enderror" wire:model.blur="end_time">
                            @error('end_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @foreach ($measurements as $index => $row)
            <div class="card mb-3 p-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-12 @if (!$showDocumentInfo) d-none @endif">
                        <label class="form-label">Inspection Report Document Number <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-secondary-subtle"
                            wire:model.blur="measurements.{{ $index }}.inspection_report_document_number"
                            readonly>
                        @error("measurements.$index.inspection_report_document_number")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Unit</label>
                        <select class="form-select" wire:model.live="measurements.{{ $index }}.limit_uom">
                            <option value="" selected></option>
                            <option value="cm">cm</option>
                            <option value="mm">mm</option>
                        </select>
                    </div>

                    <div class="col">
                        <label class="form-label">Lower Limit</label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control"
                                wire:model.blur="measurements.{{ $index }}.lower_limit">
                            <span class="input-group-text">{{ data_get($measurements, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("measurements.$index.lower_limit")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Upper Limit</label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control"
                                wire:model.blur="measurements.{{ $index }}.upper_limit">
                            <span class="input-group-text">{{ data_get($measurements, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("measurements.$index.upper_limit")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Actual Value</label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control"
                                wire:model.blur="measurements.{{ $index }}.actual_value">
                            <span class="input-group-text">{{ data_get($measurements, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("measurements.$index.actual_value")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Area/Section</label>
                        <input type="text" class="form-control"
                            wire:model.blur="measurements.{{ $index }}.area">
                        @error("measurements.$index.area")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Judgement</label>
                        <select class="form-select" wire:model.live="measurements.{{ $index }}.judgement">
                            <option value="" disabled>--Select Judgement--</option>
                            <option value="OK">OK</option>
                            <option value="NG">NG</option>
                        </select>
                        @error("measurements.$index.judgement")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (($row['judgement'] ?? '') === 'NG')
                        <div class="col">
                            <label class="form-label">Remarks</label>
                            <input type="text" class="form-control"
                                wire:model.blur="measurements.{{ $index }}.remarks">
                            @error("measurements.$index.remarks")
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="col-auto align-self-end mb-3">
                        <button type="button" class="btn btn-link text-danger btn-sm"
                            wire:click="removeMeasurement({{ $index }})">Remove</button>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="justify-content-between d-flex">
            <button type="button" class="btn btn-outline-secondary" wire:click="addMeasurement">
                + Add Measurement
            </button>
            @if (count($measurements) > 0)
                @php
                    $buttonDisabled = true;

                    if (
                        $measurements[0]['limit_uom'] !== '' &&
                        $measurements[0]['upper_limit'] !== '' &&
                        $measurements[0]['lower_limit'] !== '' &&
                        $measurements[0]['area'] !== ''
                    ) {
                        $buttonDisabled = false;
                    }
                @endphp
                <div>
                    <button type="button" class="btn btn-outline-primary" wire:click="saveStep"
                        @disabled($buttonDisabled)>
                        Save Measurement
                    </button>
                    <button type="button" class="btn btn-outline-danger" wire:click="resetStep"
                        @disabled($buttonDisabled)>Reset</button>
                </div>
            @endif
        </div>
    </div>
</div>
