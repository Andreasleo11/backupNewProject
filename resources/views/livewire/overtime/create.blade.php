@section('title', 'Create Overtime Form - ' . env('APP_NAME'))

<div class="container">
    <h2 class="h4 fw-bold text-primary mb-4">Create Overtime Form</h2>
    <form wire:submit.prevent="submit" enctype="multipart/form-data">
        <div class="bg-white p-4 rounded shadow-sm border">
            <h5 class="fw-semibold text-secondary mb-3">General Information</h5>
            <!-- Department & Branch -->
            <div class="row g-4 mb-4">
                <div class="col">
                    <label for="dept_id" class="form-label">From Department <span class="text-danger">*</span></label>
                    <select wire:model.live="dept_id" id="dept_id"
                        class="form-select shadow-sm @error('dept_id') is-invalid @enderror">
                        <option value="">-- Select Department --</option>
                        @foreach ($departements as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('dept_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col">
                    <label for="branch" class="form-label">Branch <span class="text-danger">*</span></label>
                    <select wire:model="branch" id="branch"
                        class="form-select shadow-sm @error('branch') is-invalid @enderror">
                        <option value="">-- Select Branch --</option>
                        <option value="Jakarta">Jakarta</option>
                        <option value="Karawang">Karawang</option>
                    </select>
                    @error('branch')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col">
                    <label for="is_after_hour" class="form-label">After Hour OT? <span
                            class="text-danger">*</span></label>
                    <select wire:model="is_after_hour" id="is_after_hour"
                        class="form-select shadow-sm @error('is_after_hour') is-invalid @enderror">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                    @error('is_after_hour')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                @if ($dept_id && optional($departements->firstWhere('id', $dept_id))->name === 'MOULDING')
                    <div class="col">
                        <label for="design" class="form-label">Design <span class="text-danger">*</span></label>
                        <select wire:model="design" id="design" class="form-select shadow-sm">
                            <option value="">-- Select Design --</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        @error('design')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                @endif
            </div>
        </div>
        <div class="bg-white p-4 rounded shadow-sm border mt-4">
            <h5 class="fw-semibold text-secondary mb-3">Input Mode</h5>
            <!-- Excel / Manual toggle -->
            <div class="mb-4">
                @php
                    $cardBase = 'card shadow-sm border bg-transparent';
                @endphp
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="{{ $cardBase }} {{ !$isExcelMode ? 'border-primary bg-primary-subtle ' : '' }}"
                            wire:click="$set('isExcelMode', false)" style="cursor: pointer;">
                            <div class="card-body d-flex align-items-center gap-2">
                                <i class="bi bi-pencil-square fs-4 text-primary"></i>
                                <div>
                                    <h6 class="mb-0 {{ !$isExcelMode ? 'text-primary' : '' }}">Manual Entry</h6>
                                    <small class="text-muted">Fill in the form manually.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="{{ $cardBase }} {{ $isExcelMode ? 'border-success bg-success-subtle' : '' }}"
                            wire:click="$set('isExcelMode', true)" style="cursor: pointer;">
                            <div class="card-body d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-excel fs-4 text-success"></i>
                                <div>
                                    <h6 class="mb-0 {{ $isExcelMode ? 'text-success' : '' }}">Import from Excel</h6>
                                    <small class="text-muted">Upload Excel file with overtime data.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <small class="text-muted">Choose how you'd like to enter the data.</small>
                </div>
            </div>
            <h5 class="fw-semibold text-secondary mt-4 mb-3">Overtime Entries</h5>
            @if ($isExcelMode)
                <div class="mb-4" wire:key="excel-mode">
                    <a href="{{ route('formovertime.template.download') }}"
                        class="btn btn-outline-primary btn-sm mb-2">
                        📄 Download Excel Template
                    </a>
                    <input wire:model="excel_file" type="file" class="form-control shadow-sm" accept=".xlsx,.xls">
                    @error('excel_file')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @else
                <div class="card border shadow-sm mb-4" wire:key="manual-mode">
                    <div class="card-body">
                        @foreach ($items as $index => $item)
                            <div class="card mb-4 border-0 shadow" wire:key="employee-item-{{ $index }}">
                                <div class="card-body bg-white rounded">
                                    <div class="row g-3">
                                        <!-- NIK Dropdown -->
                                        <div class="col-md-3">
                                            <div x-data="{ open: false, search: @entangle('items.' . $index . '.nik') }" x-init="$watch('search', value => open = true)"
                                                class="position-relative">
                                                <label class="form-label">NIK <span class="text-danger">*</span></label>
                                                <input
                                                    class='form-control form-control-sm shadow-sm @error("items.$index.nik") is-invalid @enderror'
                                                    placeholder="Search NIK..." type="text" x-model="search"
                                                    @click="open = true" @keydown.escape.window="open = false"
                                                    @blur="setTimeout(() => open = false, 200)">

                                                <ul x-show="open && search.length > 0" x-transition
                                                    class="list-group position-absolute bg-white border rounded shadow-sm w-100 z-10"
                                                    style="max-height: 200px; overflow-y: auto;">
                                                    @foreach ($employees as $emp)
                                                        <li class="list-group-item list-group-item-action"
                                                            x-show="'{{ $emp->NIK }}'.toLowerCase().includes(search.toLowerCase())"
                                                            @click="search = '{{ $emp->NIK }}'; open = false; $wire.set('items.{{ $index }}.nik', '{{ $emp->NIK }}')">
                                                            {{ $emp->NIK }} - {{ $emp->Nama }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @error("items.$index.nik")
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Name Dropdown -->
                                        <div class="col-md-3">
                                            <div x-data="{ open: false, search: @entangle('items.' . $index . '.name') }" x-init="$watch('search', value => open = true)"
                                                class="position-relative">
                                                <label class="form-label">Name <span
                                                        class="text-danger">*</span></label>
                                                <input
                                                    class='form-control form-control-sm shadow-sm @error("items.$index.name") is-invalid @enderror'
                                                    placeholder="Search Name..." type="text" x-model="search"
                                                    @click="open = true" @keydown.escape.window="open = false"
                                                    @blur="setTimeout(() => open = false, 200)">

                                                <ul x-show="open && search.length > 0" x-transition
                                                    class="list-group position-absolute bg-white border rounded shadow-sm w-100 z-10"
                                                    style="max-height: 200px; overflow-y: auto;">
                                                    @foreach ($employees as $emp)
                                                        <li class="list-group-item list-group-item-action"
                                                            x-show="'{{ strtolower($emp->Nama) }}'.includes(search.toLowerCase())"
                                                            @click="search = '{{ $emp->Nama }}'; open = false; $wire.set('items.{{ $index }}.name', '{{ $emp->Nama }}')">
                                                            {{ $emp->Nama }} ({{ $emp->NIK }})
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @error("items.$index.name")
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>

                                        @foreach ([['key' => 'overtime_date', 'label' => 'Overtime Date'], ['key' => 'job_desc', 'label' => 'Job Desc'], ['key' => 'start_date', 'label' => 'Start Date'], ['key' => 'start_time', 'label' => 'Start Time'], ['key' => 'end_date', 'label' => 'End Date'], ['key' => 'end_time', 'label' => 'End Time'], ['key' => 'break', 'label' => 'Break (min)'], ['key' => 'remarks', 'label' => 'Remark']] as $field)
                                            <div class="col-md-3">
                                                <label class="form-label">{{ $field['label'] }} <span
                                                        class="text-danger">*</span></label>
                                                <input wire:model="items.{{ $index }}.{{ $field['key'] }}"
                                                    type="{{ in_array($field['key'], ['start_date', 'end_date', 'overtime_date']) ? 'date' : (in_array($field['key'], ['start_time', 'end_time']) ? 'time' : ($field['key'] === 'break' ? 'number' : 'text')) }}"
                                                    class="form-control form-control-sm shadow-sm @error("items.$index.{$field['key']}") is-invalid @enderror">
                                                @error("items.$index.{$field['key']}")
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        @endforeach
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button wire:click.prevent="removeItem({{ $index }})"
                                                class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <button wire:click.prevent="addItem" type="button" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Employee
                        </button>
                    </div>
                </div>
            @endif
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-success px-4">Submit</button>
            </div>
        </div>
    </form>
</div>
