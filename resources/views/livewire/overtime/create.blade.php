@section('title', 'Create Overtime Form')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="overtimeForm($wire)">
    <h2 class="text-lg sm:text-xl font-semibold text-slate-900 mb-4">
        Create Overtime Form
    </h2>

    <form wire:submit.prevent="submit" enctype="multipart/form-data" class="space-y-5">
        {{-- ====================== GENERAL INFORMATION ====================== --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 space-y-3">
            <h5 class="text-sm font-semibold text-slate-700">
                General Information
            </h5>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {{-- Department --}}
                <div>
                    <label for="dept_id" class="block text-xs font-medium text-slate-700">
                        From Department <span class="text-rose-600">*</span>
                    </label>
                    <select id="dept_id" wire:model.lazy="dept_id"
                        class="mt-1 block w-full rounded-lg border text-sm border-slate-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 @error('dept_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                        <option value="">-- Select Department --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('dept_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Branch --}}
                <div>
                    <label for="branch" class="block text-xs font-medium text-slate-700">
                        Branch <span class="text-rose-600">*</span>
                    </label>
                    <select id="branch" wire:model.lazy="branch"
                        class="mt-1 block w-full rounded-lg border text-sm border-slate-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 @error('branch') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                        <option value="">-- Select Branch --</option>
                        <option value="Jakarta">Jakarta</option>
                        <option value="Karawang">Karawang</option>
                    </select>
                    @error('branch')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- After Hour --}}
                <div>
                    <label for="is_after_hour" class="block text-xs font-medium text-slate-700">
                        After Hour OT? <span class="text-rose-600">*</span>
                    </label>
                    <select id="is_after_hour" wire:model="is_after_hour"
                        class="mt-1 block w-full rounded-lg border text-sm border-slate-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 @error('is_after_hour') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                    @error('is_after_hour')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Design (only for MOULDING) --}}
                @if ($dept_id && optional($departments->firstWhere('id', $dept_id))->name === 'MOULDING')
                    <div>
                        <label for="design" class="block text-xs font-medium text-slate-700">
                            Design <span class="text-rose-600">*</span>
                        </label>
                        <select id="design" wire:model.lazy="design"
                            class="mt-1 block w-full rounded-lg border text-sm border-slate-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 @error('design') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror">
                            <option value="">-- Select Design --</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        @error('design')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
        </div>

        {{-- ====================== INPUT MODE ====================== --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
            <div class="px-4 pt-4 sm:px-5 sm:pt-5">
                <h5 class="text-sm font-semibold text-slate-700 mb-3">
                    Input Mode
                </h5>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Manual Entry card --}}
                    <button type="button"
                        class="flex items-center gap-3 rounded-xl border px-3.5 py-3 text-left shadow-sm transition
                               hover:border-indigo-400 hover:bg-indigo-50/60"
                        :class="!excel ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-slate-200 bg-white'"
                        @click="excel = false">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                            ✏️
                        </div>
                        <div>
                            <div class="text-sm font-semibold" :class="!excel ? 'text-indigo-700' : 'text-slate-800'">
                                Manual Entry
                            </div>
                            <p class="text-xs text-slate-500">
                                Fill in the overtime details manually per employee.
                            </p>
                        </div>
                    </button>

                    {{-- Excel Import card --}}
                    <button type="button"
                        class="flex items-center gap-3 rounded-xl border px-3.5 py-3 text-left shadow-sm transition
                               hover:border-emerald-400 hover:bg-emerald-50/60"
                        :class="excel ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500' : 'border-slate-200 bg-white'"
                        @click="excel = true">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            📄
                        </div>
                        <div>
                            <div class="text-sm font-semibold" :class="excel ? 'text-emerald-700' : 'text-slate-800'">
                                Import from Excel
                            </div>
                            <p class="text-xs text-slate-500">
                                Upload an Excel file with overtime data.
                            </p>
                        </div>
                    </button>
                </div>
            </div>

            {{-- Excel Upload Mode --}}
            <template x-if="excel">
                <div class="border-t border-slate-200 px-4 py-4 sm:px-5" wire:key="excel-mode">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                        <p class="text-xs text-slate-500">
                            Download the latest template before upload to avoid format issues.
                        </p>
                        <a href="{{ route('formovertime.template.download') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-indigo-500 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 shadow-sm hover:bg-indigo-100">
                            📄 Download Excel Template
                        </a>
                    </div>

                    <input wire:model.defer="excel_file" type="file" accept=".xlsx,.xls"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs sm:text-sm text-slate-900 shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    @error('excel_file')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </template>

            {{-- Manual Entry Mode --}}
            <template x-if="!excel">
                <div class="mt-4 border-t border-slate-100 bg-slate-50/80 rounded-b-2xl" wire:key="manual-mode">
                    <div class="p-4 sm:p-5 space-y-3">
                        <template x-for="(row, index) in items" :key="index">
                            <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-3 sm:p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h6 class="text-xs font-semibold text-slate-700">
                                        Employee <span x-text="index + 1"></span>
                                    </h6>
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" @click.prevent="removeRow(index)"
                                            class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-medium text-rose-700 hover:bg-rose-100"
                                            title="Remove">
                                            🗑 Remove
                                        </button>
                                        <button type="button" @click.prevent="addRow()"
                                            class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2 py-1 text-[11px] font-medium text-indigo-700 hover:bg-indigo-100"
                                            title="Add">
                                            ＋ Add
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 items-start">
                                    {{-- NIK --}}
                                    <div class="relative" x-data="{ open: false, q: '' }">
                                        <label class="block text-xs font-medium text-slate-700">
                                            NIK <span class="text-rose-600">*</span>
                                        </label>
                                        <input type="text"
                                            class="mt-1 block w-full rounded-lg border text-xs sm:text-sm border-slate-300 bg-white px-3 py-1.5 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                            :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500': hasError(
                                                    index, 'nik') }"
                                            placeholder="Search NIK..." x-model="items[index].nik"
                                            @focus="open = true" @input="q = $event.target.value; open = !!q"
                                            @keydown.escape.window="open = false">

                                        {{-- Suggestion list --}}
                                        <ul x-cloak x-show="open && filteredBy('nik', q).length" x-transition
                                            class="absolute left-0 right-0 z-20 mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg text-xs">
                                            <template x-for="emp in filteredBy('nik', q)" :key="emp.nik">
                                                <li class="cursor-pointer px-3 py-1.5 hover:bg-slate-50"
                                                    @click="pick(index, emp); open=false; q=''">
                                                    <span x-text="emp.nik + ' - ' + emp.name"></span>
                                                </li>
                                            </template>
                                        </ul>

                                        <p x-text="getError(index, 'nik')" x-show="hasError(index, 'nik')"
                                            class="mt-1 text-[11px] text-rose-600"></p>
                                    </div>

                                    {{-- Name --}}
                                    <div class="relative" x-data="{ open: false, q: '' }">
                                        <label class="block text-xs font-medium text-slate-700">
                                            Name <span class="text-rose-600">*</span>
                                        </label>
                                        <input type="text"
                                            class="mt-1 block w-full rounded-lg border text-xs sm:text-sm border-slate-300 bg-white px-3 py-1.5 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                            :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500': hasError(
                                                    index, 'name') }"
                                            placeholder="Search Name..." x-model="items[index].name"
                                            @focus="open = true" @input="q = $event.target.value; open = !!q"
                                            @keydown.escape.window="open = false">

                                        {{-- Suggestion list --}}
                                        <ul x-cloak x-show="open && filteredBy('name', q).length" x-transition
                                            class="absolute left-0 right-0 z-20 mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg text-xs">
                                            <template x-for="emp in filteredBy('name', q)" :key="emp.nik">
                                                <li class="cursor-pointer px-3 py-1.5 hover:bg-slate-50"
                                                    @click="pick(index, emp); open=false; q=''">
                                                    <span x-text="emp.name + ' (' + emp.nik + ')'"></span>
                                                </li>
                                            </template>
                                        </ul>

                                        <p x-text="getError(index, 'name')" x-show="hasError(index, 'name')"
                                            class="mt-1 text-[11px] text-rose-600"></p>
                                    </div>

                                    {{-- Other fields --}}
                                    @foreach ([['overtime_date', 'Overtime Date', 'date'], ['job_desc', 'Job Desc', 'text'], ['start_date', 'Start Date', 'date'], ['start_time', 'Start Time', 'time'], ['end_date', 'End Date', 'date'], ['end_time', 'End Time', 'time'], ['break', 'Break (min)', 'number'], ['remarks', 'Remark', 'text']] as [$key, $label, $type])
                                        <div>
                                            <label class="block text-xs font-medium text-slate-700">
                                                {{ $label }} <span class="text-rose-600">*</span>
                                            </label>
                                            <input type="{{ $type }}"
                                                x-model="items[index].{{ $key }}"
                                                class="mt-1 block w-full rounded-lg border text-xs sm:text-sm border-slate-300 bg-white px-3 py-1.5 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                                :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500': hasError(
                                                        index, '{{ $key }}') }">
                                            <p x-text="getError(index, '{{ $key }}')"
                                                x-show="hasError(index, '{{ $key }}')"
                                                class="mt-1 text-[11px] text-rose-600"></p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </template>

                        <button type="button" @click.prevent="addRow()"
                            class="inline-flex items-center gap-1 rounded-full border border-indigo-300 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 shadow-sm hover:bg-indigo-100">
                            ＋ Add Employee
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- ====================== SUBMIT BUTTON ====================== --}}
        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                wire:loading.attr="disabled">
                <span wire:loading.remove>
                    ✅ Submit
                </span>
                <span class="inline-flex items-center gap-2" wire:loading>
                    <svg class="h-4 w-4 animate-spin text-white" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" fill="none" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    Processing...
                </span>
            </button>
        </div>
    </form>

    @push('scripts')
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
