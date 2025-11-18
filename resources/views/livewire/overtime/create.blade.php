@section('title', 'Create Overtime Form - ' . env('APP_NAME'))

<div class="container mt-5" x-data="overtimeForm($wire)">
    @include('partials.alert-success-error')

    <h2 class="h4 fw-bold text-primary mb-4">Create Overtime Form</h2>

    <form wire:submit.prevent="submit" enctype="multipart/form-data">
        {{-- ====================== GENERAL INFORMATION ====================== --}}
        <div class="bg-white p-4 rounded shadow-sm border mb-4">
            <h5 class="fw-semibold text-secondary mb-3">General Information</h5>
            <div class="row g-2">
                {{-- Department --}}
                <div class="col">
                    <label for="dept_id" class="form-label">From Department <span class="text-danger">*</span></label>
                    <select wire:model.lazy="dept_id" id="dept_id"
                        class="form-select shadow-sm @error('dept_id') is-invalid @enderror">
                        <option value="">-- Select Department --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('dept_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Branch --}}
                <div class="col">
                    <label for="branch" class="form-label">Branch <span class="text-danger">*</span></label>
                    <select wire:model.lazy="branch" id="branch"
                        class="form-select shadow-sm @error('branch') is-invalid @enderror">
                        <option value="">-- Select Branch --</option>
                        <option value="Jakarta">Jakarta</option>
                        <option value="Karawang">Karawang</option>
                    </select>
                    @error('branch')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- After Hour --}}
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

                {{-- Design (only for MOULDING) --}}
                @if ($dept_id && optional($departments->firstWhere('id', $dept_id))->name === 'MOULDING')
                    <div class="col">
                        <label for="design" class="form-label">Design <span class="text-danger">*</span></label>
                        <select wire:model.lazy="design" id="design" class="form-select shadow-sm">
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

        {{-- ====================== INPUT MODE ====================== --}}
        <div class="bg-white rounded shadow-sm border" x-data="{ excel: @entangle('isExcelMode') }">
            <h5 class="fw-semibold text-secondary m-4 mb-2">Input Mode</h5>

            <div class="m-4 mt-0 row g-2">
                <div class="col">
                    <div class="card shadow-sm border cursor-pointer"
                        :class="!excel ? 'border-primary bg-primary-subtle' : ''" @click="excel = false">
                        <div class="card-body d-flex align-items-center gap-2">
                            <i class="bi bi-pencil-square fs-4 text-primary"></i>
                            <div>
                                <h6 :class="!excel ? 'text-primary mb-0' : 'mb-0'">Manual Entry</h6>
                                <small class="text-muted">Fill in the form manually.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow-sm border cursor-pointer"
                        :class="excel ? 'border-success bg-success-subtle' : ''" @click="excel = true">
                        <div class="card-body d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-excel fs-4 text-success"></i>
                            <div>
                                <h6 :class="excel ? 'text-success mb-0' : 'mb-0'">Import from Excel</h6>
                                <small class="text-muted">Upload Excel file with overtime data.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Excel Upload Mode --}}
            <template x-if="excel">
                <div class="mb-4 py-4 px-3 border-top" wire:key="excel-mode">
                    <a href="{{ route('formovertime.template.download') }}" class="btn btn-outline-primary btn-sm mb-2">
                        📄 Download Excel Template
                    </a>
                    <input wire:model.defer="excel_file" type="file" class="form-control shadow-sm"
                        accept=".xlsx,.xls">
                    @error('excel_file')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </template>

            {{-- Manual Entry Mode --}}
            <template x-if="!excel">
                <div class="card border-0 bg-secondary-subtle rounded-0" wire:key="manual-mode">
                    <div class="card-body">
                        <template x-for="(row, index) in items" :key="index">
                            <div class="card border-0 mb-3 shadow-sm">
                                <div class="card-body">
                                    <div class="row g-2 align-items-end">

                                        {{-- NIK --}}
                                        <div class="col" x-data="{ open: false, q: '' }">
                                            <label class="form-label">NIK <span class="text-danger">*</span></label>
                                            <div>
                                                <input type="text" class='form-control form-control-sm shadow-sm'
                                                    :class="{ 'is-invalid': hasError(index, 'nik') }"
                                                    placeholder="Search NIK..." x-model="items[index].nik"
                                                    @focus="open = true" @input="q = $event.target.value; open = !!q"
                                                    @keydown.escape.window="open = false">

                                                <ul x-cloak x-show="open && filteredBy('nik', q).length" x-transition
                                                    class="list-group position-absolute bg-white border rounded shadow-sm w-auto mt-1"
                                                    style="max-height: 180px; overflow-y: auto; z-index: 10;">
                                                    <template x-for="emp in filteredBy('nik', q)"
                                                        :key="emp.nik">
                                                        <li class="list-group-item list-group-item-action"
                                                            @click="pick(index, emp); open=false; q=''">
                                                            <span x-text="emp.nik + ' - ' + emp.name"></span>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>

                                            <small x-text="getError(index, 'nik')" x-show="hasError(index, 'nik')"
                                                class="text-danger"></small>
                                        </div>

                                        {{-- Name --}}
                                        <div class="col" x-data="{ open: false, q: '' }">
                                            <label class="form-label mt-2">Name <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <input type="text" class='form-control form-control-sm shadow-sm'
                                                    :class="{ 'is-invalid': hasError(index, 'name') }"
                                                    placeholder="Search Name..." x-model="items[index].name"
                                                    @focus="open = true" @input="q = $event.target.value; open = !!q"
                                                    @keydown.escape.window="open = false">

                                                <ul x-cloak x-show="open && filteredBy('name', q).length" x-transition
                                                    class="list-group position-absolute bg-white border rounded shadow-sm w-auto mt-1"
                                                    style="max-height: 180px; overflow-y: auto; z-index: 10;">
                                                    <template x-for="emp in filteredBy('name', q)"
                                                        :key="emp.nik">
                                                        <li class="list-group-item list-group-item-action"
                                                            @click="pick(index, emp); open=false; q=''">
                                                            <span x-text="emp.name + ' (' + emp.nik + ')'"></span>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                            <small x-text="getError(index, 'name')" x-show="hasError(index, 'name')"
                                                class="text-danger"></small>
                                        </div>

                                        @foreach ([['overtime_date', 'Overtime Date', 'date'], ['job_desc', 'Job Desc', 'text'], ['start_date', 'Start Date', 'date'], ['start_time', 'Start Time', 'time'], ['end_date', 'End Date', 'date'], ['end_time', 'End Time', 'time'], ['break', 'Break (min)', 'number'], ['remarks', 'Remark', 'text']] as [$key, $label, $type])
                                            <div class="col">
                                                <label class="form-label">{{ $label }} <span
                                                        class="text-danger">*</span></label>
                                                <input x-model="items[index].{{ $key }}"
                                                    type="{{ $type }}"
                                                    class='form-control form-control-sm shadow-sm'
                                                    :class="{ 'is-invalid': hasError(index, '{{ $key }}') }">
                                                <small x-text="getError(index, '{{ $key }}')"
                                                    x-show="hasError(index, '{{ $key }}')"
                                                    class="text-danger"></small>
                                            </div>
                                        @endforeach

                                        {{-- Buttons (pure Alpine) --}}
                                        <div class="col-auto">
                                            <button @click.prevent="removeRow(index)" title="Remove"
                                                class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <button @click.prevent="addRow()" type="button" title="Add"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-plus-circle"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button @click.prevent="addRow()" type="button" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="bi bi-plus-circle me-1"></i> Add Employee
                        </button>
                    </div>
                </div>
            </template>

        </div>

        {{-- ====================== SUBMIT BUTTON ====================== --}}
        <div class="text-end mt-3">
            <button type="submit" class="btn btn-lg btn-success" wire:loading.attr="disabled">
                <span wire:loading.remove><i class="bi bi-check-circle me-1"></i> Submit</span>
                <span wire:loading><i class="spinner-border spinner-border-sm me-1"></i> Processing...</span>
            </button>
        </div>
    </form>

    @push('extraJs')
        <script>
            function overtimeForm($wire) {
                return {
                    items: $wire.entangle('items'),
                    excel: $wire.entangle('isExcelMode'),
                    employees: $wire.entangle('employees'),
                    errors: $wire.entangle('validationErrors'),

                    // Helper to check if a field has an error
                    hasError(index, field) {
                        return !!this.errors[`items.${index}.${field}`];
                    },
                    getError(index, field) {
                        return this.errors[`items.${index}.${field}`] ?? '';
                    },

                    // Row operations on the FRONTEND (then sync once)
                    addRow() {
                        this.items.push({
                            nik: '',
                            name: '',
                            overtime_date: '',
                            job_desc: '',
                            start_date: '',
                            start_time: '',
                            end_date: '',
                            end_time: '',
                            break: '',
                            remarks: '',
                        });
                        // ensure server catches the array mutation
                        $wire.set('items', this.items);
                    },
                    removeRow(i) {
                        this.items.splice(i, 1);
                        $wire.set('items', this.items);
                    },

                    // Employee searching fully on the frontend
                    filteredBy(field, q) {
                        q = (q || '').toLowerCase();
                        if (!q) return [];
                        return this.employees
                            .filter(e =>
                                String(e[field]).toLowerCase().includes(q) ||
                                String(e.name).toLowerCase().includes(q)
                            )
                            .slice(0, 10);
                    },

                    // Pick employee into a specific row
                    pick(index, emp) {
                        this.items[index].nik = emp.nik;
                        this.items[index].name = emp.name;
                        $wire.set('items', this.items);
                    },
                }
            }
        </script>
    @endpush
</div>
