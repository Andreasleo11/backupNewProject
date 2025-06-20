<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-4 border-bottom text-primary fw-bold pb-1">General Information</h6>
                    <div class="row ">
                        <div class="mb-3 d-none">
                            <label class="form-label">Document Number <span class="text-danger">*</span></label>
                            <input type="text" wire:model.blur="document_number"
                                class="form-control bg-secondary-subtle @error('document_number') is-invalid @enderror"
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
                        @enderror">
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
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-4 border-bottom text-primary fw-bold pb-1">Part Details</h6>
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
                            <input type="number" class="form-control text-end @error('weight') is-invalid @enderror"
                                wire:model.blur="weight">
                            <select class="form-select @error('weight_uom') is-invalid @enderror"
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
                            class="form-control @error('material') is-invalid @enderror">
                        @error('material')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Color <span class="text-danger">*</span></label>
                        <input type="text" wire:model.blur="color"
                            class="form-control @error('color') is-invalid @enderror">
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
        <div class="col">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-4 border-bottom text-primary fw-bold pb-1">Machine Information</h6>
                            <div class="row">
                                <div class="mb-3">
                                    <label class="form-label">Tool/Cavity
                                        Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.blur="tool_number_or_cav_number"
                                        class="form-control @error('tool_number_or_cav_number') is-invalid @enderror">
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
                <div class="col">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="text-primary border-bottom pb-1 fw-bold mb-3">Shift & Operator</h6>
                            <div class="row">
                                <div class="mb-3">
                                    <label class="form-label">Operator <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('operator') is-invalid @enderror"
                                        wire:model.blur="operator" placeholder="Type Operator Name here..">
                                    <div class="form-text">
                                        <div class="small">e.g. Raymond</div>
                                    </div>
                                    @error('operator')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col">
                                    <label class="form-label">Shift <span class="text-danger">*</span></label>
                                    <select wire:model.live="shift"
                                        class="form-select @error('shift') is-invalid @enderror">
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
            <button type="button" class="btn btn-primary w-100" wire:click="saveStep">Save & Continue</button>
        </div>
    </div>
</div>
