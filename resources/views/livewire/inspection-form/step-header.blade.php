<div data-header>
    <!-- Status chip -->
    <div wire:ignore x-data="{ dirty: false, saved: @js($isSaved), ts: @js($savedAt) }" x-init="const root = $el.closest('[data-header]');
    const markDirty = () => { dirty = true;
        saved = false };
    
    if (root) {
        root.addEventListener('input', markDirty, { capture: true });
        root.addEventListener('change', markDirty, { capture: true });
    }
    
    Livewire.on('stepHeaderSaved', (e) => {
        dirty = false, saved = true;
        ts = e?.savedAt ?? new Date().toISOString();
    });
    
    Livewire.on('stepHeaderReset', () => {
        dirty = false, saved = false;
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
    <div class="row">
        @php
            $generalInformationSaved = $this->isGroupSaved(['inspection_date', 'customer']);
        @endphp
        <div class="col-md-12">
            <div class="card mb-4 shadow @if ($generalInformationSaved) border-success @endif">
                <div class="card-body">
                    <h6
                        class="mb-4 border-bottom text-primary fw-bold pb-1 @if ($generalInformationSaved) border-success text-success @endif">
                        General Information</h6>
                    <div class="row ">
                        <div class="mb-3 d-none">
                            <label class="form-label">Document Number <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="document_number"
                                class="form-control bg-secondary-subtle @error('document_number') is-invalid @enderror @if ($this->isFieldSaved('document_number')) is-valid @endif"
                                readonly>
                            @error('document_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col">
                            <label class="form-label">Inspection Date <span class="text-danger">*</span></label>
                            <input type="date" wire:model.blur="inspection_date"
                                class="form-control @error('inspection_date')
                            is-invalid
                        @enderror @if ($this->isFieldSaved('inspection_date')) is-valid @endif">
                            <div class="form-text">
                                <small>Format: DD/MM/YYYY</small>
                            </div>
                            @error('inspection_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col">
                            @livewire(
                                'components.searchable-dropdown',
                                [
                                    'name' => 'customer',
                                    'labelHtml' => "Customer <span class='text-danger'>*</span>",
                                    'model' => \App\Models\MasterDataRogCustomerName::class,
                                    'column' => 'name',
                                    'hasError' => $errors->has('customer'),
                                    'value' => old('customer') ?? ($this->customer ?? ''),
                                    'isSaved' => $this->isFieldSaved('customer'),
                                    'options' => [
                                        'distinct' => true,
                                    ],
                                ],
                                key('customer-dropdown-' . ($errors->has('customer') ? 'invalid' : 'valid'))
                            )

                            <div class="form-text">
                                <small>Customer Name</small>
                            </div>

                            @error('customer')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @php
            $partDetailsGroupSaved = $this->isGroupSaved([
                'part_number',
                'part_name',
                'weight',
                'weight_uom',
                'material',
                'color',
            ]);
        @endphp
        <div class="col-md-6">
            <div class="card mb-4 shadow @if ($partDetailsGroupSaved) border-success @endif">
                <div class="card-body">
                    <h6
                        class="mb-4 border-bottom text-primary fw-bold pb-1 @if ($partDetailsGroupSaved) border-success text-success @endif">
                        Part Details</h6>
                    <div class="mb-3">
                        @livewire(
                            'components.searchable-dropdown',
                            [
                                'name' => 'part_number',
                                'labelHtml' => "Part Number <span class='text-danger'>*</span>",
                                'model' => \App\Models\MasterDataPart::class,
                                'column' => 'item_no',
                                'hasError' => $errors->has('part_number'),
                                'value' => $this->part_number ?? (old('part_number') ?? ''),
                                'isSaved' => $this->isFieldSaved('part_number'),
                                'options' => [
                                    'distinct' => true,
                                ],
                            ],
                            key('part_number' . $this->part_number . ($errors->has('part_number') ? 'invalid' : 'valid'))
                        )

                        @error('part_number')
                            <span class="text-danger small mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        @livewire(
                            'components.searchable-dropdown',
                            [
                                'name' => 'part_name',
                                'labelHtml' => "Part Name <span class='text-danger'>*</span>",
                                'model' => \App\Models\MasterDataPart::class,
                                'column' => 'description',
                                'hasError' => $errors->has('part_name'),
                                'value' => $this->part_name ?? (old('part_name') ?? ''),
                                'isSaved' => $this->isFieldSaved('part_name'),
                                'options' => [
                                    'distinct' => true,
                                ],
                            ],
                            key('part_name_' . $this->part_name . ($errors->has('part_name') ? 'invalid' : 'valid'))
                        )

                        @error('part_name')
                            <span class="text-danger small mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Weight <span class="text-danger">*</span></label>
                        <div class="input-group mb-3">
                            <input type="number"
                                class="form-control text-end @error('weight') is-invalid @enderror @if ($this->isFieldSaved('weight') && $this->isGroupSaved(['weight', 'weight_uom'])) is-valid @endif"
                                wire:model.blur="weight">
                            <select
                                class="form-select @error('weight_uom') is-invalid @enderror @if ($this->isFieldSaved('weight_uom') && $this->isGroupSaved(['weight', 'weight_uom'])) is-valid @endif"
                                wire:model.blur="weight_uom">
                                <option value="" selected></option>
                                <option value="kg">KG</option>
                                <option value="g">Gram</option>
                            </select>
                            @error('weight')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            @error('weight_uom')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Material <span class="text-danger">*</span></label>
                        <input type="text" id="material" wire:model.blur="material"
                            class="form-control @error('material') is-invalid @enderror @if ($this->isFieldSaved('material')) is-valid @endif">
                        @error('material')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Color <span class="text-danger">*</span></label>
                        <input type="text" wire:model.blur="color"
                            class="form-control @error('color') is-invalid @enderror @if ($this->isFieldSaved('color')) is-valid @endif">
                        <div class="form-text">
                            <small>e.g. WHITE, BLACK, BLUE</small>
                        </div>
                        @error('color')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        @php
            $machineGroupSaved = $this->isGroupSaved(['tool_number_or_cav_number', 'machine_number']);
        @endphp
        <div class="col">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4 shadow @if ($machineGroupSaved) border-success @endif">
                        <div class="card-body">
                            <h6
                                class="mb-4 border-bottom text-primary fw-bold pb-1 @if ($machineGroupSaved) border-success text-success @endif">
                                Machine Information</h6>
                            <div class="row">
                                <div class="mb-3">
                                    <label class="form-label">Tool/Cavity
                                        Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.blur="tool_number_or_cav_number"
                                        class="form-control @error('tool_number_or_cav_number') is-invalid @enderror @if ($this->isFieldSaved('tool_number_or_cav_number')) is-valid @endif">
                                    @error('tool_number_or_cav_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    @livewire('components.searchable-dropdown', [
                                        'name' => 'machine_number',
                                        'labelHtml' => "Machine Number <span class='text-danger'>*</span>",
                                        'model' => \App\Models\sapLineProduction::class,
                                        'column' => 'line_production',
                                        'hasError' => $errors->has('machine_number'),
                                        'value' => $this->machine_number ?? (old('machine_number') ?? ''),
                                        'isSaved' => $this->isFieldSaved('machine_number'),
                                        'options' => [
                                            'distinct' => true,
                                        ],
                                    ])
                                    @error('machine_number')
                                        <span class="text-danger small mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @php
                    $shiftInspectorOperatorGroupSaved = $this->isGroupSaved(['inspector', 'operator', 'shift']);
                @endphp
                <div class="col">
                    <div class="card mb-4 shadow @if ($shiftInspectorOperatorGroupSaved) border-success @endif">
                        <div class="card-body">
                            <h6
                                class="text-primary border-bottom pb-1 fw-bold mb-3 @if ($shiftInspectorOperatorGroupSaved) border-success text-success @endif">
                                Shift & Operator</h6>
                            <div class="row">
                                <div class="mb-3">
                                    <label class="form-label">Operator <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('operator') is-invalid @enderror @if ($this->isFieldSaved('operator')) is-valid @endif"
                                        wire:model.blur="operator" placeholder="Type Operator Name here..">
                                    <div class="form-text">
                                        <div class="small">e.g. Raymond</div>
                                    </div>
                                    @error('operator')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Inspector <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('inspector') is-invalid @enderror @if ($this->isFieldSaved('inspector')) is-valid @endif"
                                        wire:model.blur="inspector" placeholder="Type Inspector Name here..">
                                    <div class="form-text">
                                        <div class="small">e.g. Raymond</div>
                                    </div>
                                    @error('inspector')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col">
                                    <label class="form-label">Shift <span class="text-danger">*</span></label>
                                    <select wire:model.live="shift"
                                        class="form-select @error('shift') is-invalid @enderror @if ($this->isFieldSaved('shift')) is-valid @endif">
                                        <option value="">-- Select Shift --</option>
                                        <option value="1">Shift 1</option>
                                        <option value="2">Shift 2</option>
                                        <option value="3">Shift 3</option>
                                    </select>
                                    @error('shift')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <button type="button" class="btn btn-primary w-100" wire:click="saveStep">Save &
                Continue</button>
        </div>
    </div>
</div>
