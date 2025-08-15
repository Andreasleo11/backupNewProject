<div>
    @php
        $showDocumentInfo = false;
    @endphp
    <div>
        @if ($dimensions)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <div x-data="{ value: @entangle('start_time').live, fp: null }" x-init="fp = flatpickr($refs.tf, {
                                enableTime: true,
                                noCalendar: true,
                                time_24hr: true,
                                minuteIncrement: 15,
                                defaultDate: value, // ← real string like '11:30'
                                allowInput: true,
                            
                                onChange(selectedDates, dateStr) {
                                    value = dateStr; // pushes to Livewire
                                }
                            });
                            
                            /* if Livewire changes the value later, update Flatpickr */
                            $watch('value', v => fp.setDate(v, false));">
                                <input type="text" x-ref="tf"
                                    class="form-control @error('start_time') is-invalid @enderror" readonly>
                            </div>
                            @error('start_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>`
                        <div class="col">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <div x-data="{ value: @entangle('end_time').live, fp: null }" x-init="fp = flatpickr($refs.tf, {
                                enableTime: true,
                                noCalendar: true,
                                time_24hr: true,
                                minuteIncrement: 15,
                                defaultDate: value, // ← real string like '11:30'
                                allowInput: true,
                            
                                onChange(selectedDates, dateStr) {
                                    value = dateStr; // pushes to Livewire
                                }
                            });
                            
                            /* if Livewire changes the value later, update Flatpickr */
                            $watch('value', v => fp.setDate(v, false));">
                                <input type="text" x-ref="tf"
                                    class="form-control @error('end_time') is-invalid @enderror" readonly>
                            </div>
                            @error('end_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @foreach ($dimensions as $index => $row)
            <div class="card mb-3 p-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-12 @if (!$showDocumentInfo) d-none @endif">
                        <label class="form-label">Inspection Report Document Number <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-secondary-subtle"
                            wire:model.blur="dimensions.{{ $index }}.inspection_report_document_number" readonly>
                        @error("dimensions.$index.inspection_report_document_number")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model.live="dimensions.{{ $index }}.limit_uom">
                            <option value="" selected>-- Select Unit --</option>
                            <option value="cm">cm</option>
                            <option value="mm">mm</option>
                        </select>
                    </div>

                    <div class="col">
                        <label class="form-label">Lower Limit <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control"
                                wire:model.blur="dimensions.{{ $index }}.lower_limit">
                            <span class="input-group-text">{{ data_get($dimensions, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("dimensions.$index.lower_limit")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Upper Limit <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control"
                                wire:model.blur="dimensions.{{ $index }}.upper_limit">
                            <span class="input-group-text">{{ data_get($dimensions, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("dimensions.$index.upper_limit")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Actual Value <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control"
                                wire:model.blur="dimensions.{{ $index }}.actual_value">
                            <span class="input-group-text">{{ data_get($dimensions, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("dimensions.$index.actual_value")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Area/Section <span class="text-danger">*</span></label>
                        <input type="text" class="form-control"
                            wire:model.blur="dimensions.{{ $index }}.area">
                        @error("dimensions.$index.area")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Judgement <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model.live="dimensions.{{ $index }}.judgement">
                            <option value="" disabled>--Select Judgement--</option>
                            <option value="OK">OK</option>
                            <option value="NG">NG</option>
                        </select>
                        @error("dimensions.$index.judgement")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (($row['judgement'] ?? '') === 'NG')
                        <div class="col">
                            <label class="form-label">Remarks <span class="text-danger">*</span></label>
                            <input type="text" class="form-control"
                                wire:model.blur="dimensions.{{ $index }}.remarks">
                            @error("dimensions.$index.remarks")
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="col-auto align-self-end mb-3">
                        <button type="button" class="btn btn-link text-danger btn-sm"
                            wire:click="removeDimension({{ $index }})">Remove</button>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="justify-content-between d-flex">
            <button type="button" class="btn btn-outline-secondary" wire:click="addDimension">
                + Add Dimension
            </button>
            @if (count($dimensions) > 0)
                @php
                    $buttonDisabled = true;

                    if (
                        $dimensions[0]['limit_uom'] !== '' &&
                        $dimensions[0]['upper_limit'] !== '' &&
                        $dimensions[0]['lower_limit'] !== '' &&
                        $dimensions[0]['area'] !== ''
                    ) {
                        $buttonDisabled = false;
                    }
                @endphp
                <div>
                    <button type="button" class="btn btn-outline-primary" wire:click="saveStep"
                        @disabled($buttonDisabled)>
                        Save Dimension
                    </button>
                    <button type="button" class="btn btn-outline-danger" wire:click="resetStep"
                        @disabled($buttonDisabled)>Reset</button>
                </div>
            @endif
        </div>
    </div>
</div>
