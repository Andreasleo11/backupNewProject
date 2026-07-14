<div data-step-dimensions>
    <!-- Status chip -->
    <div wire:ignore x-data="{ dirty: false, saved: @js($this->isSaved), ts: @js($savedAt) }" x-init="const root = $el.closest('[data-step-dimensions]');
    const markDirty = () => {
        dirty = true;
        saved = false
    };
    
    if (root) {
        root.addEventListener('input', markDirty, { capture: true });
        root.addEventListener('change', markDirty, { capture: true });
    }
    
    Livewire.on('dimensionsSaved', e => {
        dirty = false;
        saved = true;
        ts = e?.savedAt ?? ts ?? new Date().toISOString();
    });
    
    Livewire.on('dimensionsReset', () => {
        dirty = false;
        saved = false;
        ts = null;
    });" aria-live="polite" class="mb-2">
        <template x-if="dirty">
            <span class="badge rounded-pill bg-warning text-dark">
                <i class="bi bi-exclamation-triangle me-1"></i> Unsaved changes
            </span>
        </template>

        <template x-if="!dirty && saved">
            <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">
                <i class="bi bi-check-circle me-1"></i>
                Saved to session
                <small class="ms-1" x-text="ts ? new Date(ts).toLocaleString() : ''"></small>
            </span>
        </template>
    </div>
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <strong>Inspection Reference Documents</strong>
                <span class="text-muted">({{ count($files) }})</span>
            </div>
            
            <div class="d-flex gap-2 align-items-center">
                <div wire:loading wire:target="newAttachments" class="text-muted small me-2">Processing files...</div>
                <input type="file" wire:model="newAttachments" class="form-control form-control-sm" multiple style="max-width: 250px;">
                <button wire:click="uploadDocuments" class="btn btn-sm btn-primary" wire:loading.attr="disabled" wire:target="newAttachments,uploadDocuments">
                    <span wire:loading.remove wire:target="uploadDocuments">Upload</span>
                    <span wire:loading wire:target="uploadDocuments">Uploading...</span>
                </button>
            </div>
        </div>
        @error('newAttachments.*') <span class="text-danger d-block mb-2">{{ $message }}</span> @enderror

        @if (count($files) > 0)
            <div class="row g-3">
                @foreach ($files as $file)
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    @php
                                        $ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                                        $icon = 'bi-file-earmark-text text-secondary';
                                        if (in_array($ext, ['pdf'])) $icon = 'bi-file-earmark-pdf text-danger';
                                        elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) $icon = 'bi-file-earmark-spreadsheet text-success';
                                        elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'bi-file-earmark-image text-primary';
                                    @endphp
                                    <i class="bi {{ $icon }} fs-4"></i>
                                    <div class="text-truncate fw-semibold flex-fill" title="{{ $file->name }}">
                                        {{ $file->name }}
                                    </div>
                                </div>
                                <div class="text-muted small mt-auto">
                                    {{ number_format($file->size / 1024, 2) }} KB
                                </div>
                            </div>
                            <div class="card-footer p-2 d-flex gap-2">
                                <a class="btn btn-sm btn-outline-secondary flex-fill"
                                    href="{{ asset('storage/files/' . $file->name) }}" target="_blank">
                                    Open
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    wire:click="deleteDocument({{ $file->id }})"
                                    wire:confirm="Are you sure you want to delete this document?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-secondary d-flex align-items-center gap-2">
                <i class="bi bi-info-circle"></i>
                No reference documents attached to this inspection report.
            </div>
        @endif
    </div>
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
                                    class="form-control @error('start_time') is-invalid @enderror @if ($this->isTimeSaved('start')) is-valid @endif"
                                    readonly>
                            </div>
                            @error('start_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
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
                                    class="form-control @error('end_time') is-invalid @enderror @if ($this->isTimeSaved('end')) is-valid @endif"
                                    readonly>
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
            @php $rk = $row['row_key'] ?? null; @endphp
            <div class="card mb-3 p-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-12 @if (!$showDocumentInfo) d-none @endif">
                        <label class="form-label">Inspection Report Document Number <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-secondary-subtle"
                            wire:model.blur="dimensions.{{ $index }}.inspection_report_document_number"
                            readonly>
                        @error("dimensions.$index.inspection_report_document_number")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select @class([
                            'form-select',
                            'is-valid' =>
                                $rk &&
                                $this->isRowFieldSaved($rk, 'limit_uom') &&
                                !$errors->has("dimensions.${index}.limit_uom"),
                        ])
                            wire:model.live="dimensions.{{ $index }}.limit_uom">
                            <option value="" selected>-- Select Unit --</option>
                            <option value="cm">cm</option>
                            <option value="mm">mm</option>
                        </select>
                    </div>

                    <div class="col">
                        <label class="form-label">Lower Limit <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="any" @class([
                                'form-control',
                                'is-valid' =>
                                    $rk &&
                                    $this->isRowFieldSaved($rk, 'lower_limit') &&
                                    !$errors->has("dimensions.{$index}.lower_limit"),
                            ])
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
                            <input type="number" step="any" @class([
                                'form-control',
                                'is-valid' =>
                                    $rk &&
                                    $this->isRowFieldSaved($rk, 'upper_limit') &&
                                    !$errors->has("dimensions.${index}.upper_limit"),
                            ])
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
                            <input type="number" step="any" @class([
                                'form-control',
                                'is-valid' =>
                                    $rk &&
                                    $this->isRowFieldSaved($rk, 'actual_value') &&
                                    !$errors->has("dimensions.${index}.actual_value"),
                            ])
                                wire:model.blur="dimensions.{{ $index }}.actual_value">
                            <span class="input-group-text">{{ data_get($dimensions, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("dimensions.$index.actual_value")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Area/Section <span class="text-danger">*</span></label>
                        <input type="text" @class([
                            'form-control',
                            'is-valid' =>
                                $rk &&
                                $this->isRowFieldSaved($rk, 'area') &&
                                !$errors->has("dimensions.${index}.area"),
                        ])
                            wire:model.blur="dimensions.{{ $index }}.area">
                        @error("dimensions.$index.area")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Judgement <span class="text-danger">*</span></label>
                        <select @class([
                            'form-select',
                            'is-valid' =>
                                $rk &&
                                $this->isRowFieldSaved($rk, 'judgement') &&
                                !$errors->has("dimensions.${index}.judgement"),
                        ])
                            wire:model.live="dimensions.{{ $index }}.judgement">
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
                            <input type="text" @class([
                                'form-control',
                                'is-valid' =>
                                    $rk &&
                                    $this->isRowFieldSaved($rk, 'remarks') &&
                                    !$errors->has("dimensions.${index}.remarks"),
                            ])
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
