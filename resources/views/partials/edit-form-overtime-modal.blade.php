@php
    // Siapkan data department untuk Alpine (id + name saja)
    $departmentsForJs = $departements
        ->map(
            fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
            ],
        )
        ->values();
@endphp

<div x-data="editOvertimeModal(@js($header), @js($datas), @js($departmentsForJs))" x-init="// Buka modal ketika tombol di detail.blade mem-broadcast event ini
window.addEventListener('open-overtime-edit-modal-{{ $header->id }}', () => { open = true })" x-cloak>
    {{-- BACKDROP --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/60"></div>

    {{-- MODAL WRAPPER --}}
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6" role="dialog"
        aria-modal="true">
        <div class="flex h-full max-h-[90vh] w-full max-w-6xl flex-col rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200"
            @click.outside="close()">
            {{-- HEADER --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-800 sm:text-lg">
                        Edit Form Overtime
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Update header dan detail lembur untuk form ini.
                    </p>
                </div>

                <button type="button" @click="close()"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                    <span class="sr-only">Close</span>
                    ✕
                </button>
            </div>

            {{-- BODY --}}
            <div class="flex-1 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5">
                <form id="form-overtime-edit" action="{{ route('formovertime.update', $header->id) }}" method="POST"
                    class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- HEADER FIELDS --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        {{-- Department --}}
                        <div class="space-y-1.5">
                            <label for="from_department" class="text-xs font-medium text-slate-700">
                                From Department <span class="text-rose-500">*</span>
                            </label>
                            <select id="from_department" name="from_department" x-model="deptId"
                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                required>
                                <option value="">Select department…</option>
                                @foreach ($departements as $department)
                                    <option value="{{ $department->id }}" @selected($department->id === $header->dept_id)>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400">
                                Pilih department asal form lembur ini.
                            </p>
                        </div>

                        {{-- Date of Form Overtime --}}
                        <div class="space-y-1.5">
                            <label for="date_form_overtime" class="text-xs font-medium text-slate-700">
                                Date of Form Overtime Create <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="date_form_overtime" name="date_form_overtime" x-model="createDate"
                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                required>
                        </div>
                    </div>

                    {{-- DESIGN FIELD (ONLY FOR MOULDING) --}}
                    <div x-show="deptIsMoulding" x-transition class="space-y-1.5">
                        <label for="design" class="text-xs font-medium text-slate-700">
                            Design <span class="text-rose-500">*</span>
                        </label>
                        <select id="design" name="design" x-model="isDesign"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            :required="deptIsMoulding">
                            <option value="">Select…</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <p class="text-[11px] text-slate-400">
                            Hanya muncul jika department = MOULDING.
                        </p>
                    </div>

                    {{-- ITEMS / EMPLOYEES LIST --}}
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    List of Employees
                                </p>
                                <p class="text-[11px] text-slate-500">
                                    Edit detail lembur per karyawan. Bisa tambah/hapus baris.
                                </p>
                            </div>
                            <button type="button" @click="addItem()"
                                class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                                <span class="text-sm">＋</span>
                                <span>Add Employee</span>
                            </button>
                        </div>

                        {{-- Kalau kosong --}}
                        <template x-if="items.length === 0">
                            <p
                                class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-500">
                                Belum ada detail lembur. Klik <span class="font-semibold">Add Employee</span> untuk
                                menambah baris.
                            </p>
                        </template>

                        {{-- Item cards --}}
                        <div class="space-y-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 sm:p-4"
                                    x-on:click.outside="closeSearch(index)">
                                    <div class="mb-2 flex items-center justify-between gap-2">
                                        <p class="text-xs font-semibold text-slate-600">
                                            Employee #<span x-text="index + 1"></span>
                                        </p>
                                        <button type="button" @click="removeItem(index)"
                                            class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-100">
                                            ✕ Remove
                                        </button>
                                    </div>

                                    <div class="grid gap-3 md:grid-cols-2">
                                        {{-- NIK with search --}}
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-medium text-slate-600">
                                                NIK <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text"
                                                    class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                    placeholder="Search or type NIK…" x-model="item.nik"
                                                    @focus="startSearch(index, 'nik')"
                                                    @input="onSearchInput('nik', $event.target.value, index)" required>

                                                {{-- Dropdown hasil search NIK --}}
                                                <div x-show="searchOpenFor === 'nik' && searchIndex === index && searchResults.length"
                                                    x-transition
                                                    class="absolute z-50 mt-1 max-h-48 w-full overflow-y-auto rounded-md border border-slate-200 bg-white text-xs shadow-lg">
                                                    <template x-for="emp in searchResults" :key="emp.NIK">
                                                        <button type="button" @click="pickEmployee(emp)"
                                                            class="flex w-full items-center justify-between px-3 py-1.5 text-left hover:bg-slate-100">
                                                            <span class="font-medium text-slate-700"
                                                                x-text="emp.NIK + ' - ' + emp.nama"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Name with search --}}
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-medium text-slate-600">
                                                Name <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text"
                                                    class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                    placeholder="Search or type name…" x-model="item.nama"
                                                    @focus="startSearch(index, 'nama')"
                                                    @input="onSearchInput('nama', $event.target.value, index)"
                                                    required>

                                                {{-- Dropdown hasil search Nama --}}
                                                <div x-show="searchOpenFor === 'nama' && searchIndex === index && searchResults.length"
                                                    x-transition
                                                    class="absolute z-50 mt-1 max-h-48 w-full overflow-y-auto rounded-md border border-slate-200 bg-white text-xs shadow-lg">
                                                    <template x-for="emp in searchResults" :key="emp.NIK">
                                                        <button type="button" @click="pickEmployee(emp)"
                                                            class="flex w-full items-center justify-between px-3 py-1.5 text-left hover:bg-slate-100">
                                                            <span class="font-medium text-slate-700"
                                                                x-text="emp.nama + ' (' + emp.NIK + ')'"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Job desc --}}
                                        <div class="space-y-1.5 md:col-span-2">
                                            <label class="text-[11px] font-medium text-slate-600">
                                                Job Description <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" x-model="item.jobdesc" name=""
                                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                placeholder="Describe the overtime job…" required>
                                        </div>

                                        {{-- Start date & time --}}
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-medium text-slate-600">
                                                Start Date <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="date" x-model="item.startdate"
                                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                required>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-medium text-slate-600">
                                                Start Time <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="time" x-model="item.starttime"
                                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                required>
                                        </div>

                                        {{-- End date & time --}}
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-medium text-slate-600">
                                                End Date <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="date" x-model="item.enddate"
                                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                required>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-medium text-slate-600">
                                                End Time <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="time" x-model="item.endtime"
                                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                required>
                                        </div>

                                        {{-- Break --}}
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-medium text-slate-600">
                                                Break (minutes) <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="number" min="0" x-model="item.break"
                                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                placeholder="e.g. 45" required>
                                        </div>

                                        {{-- Remark --}}
                                        <div class="space-y-1.5 md:col-span-1">
                                            <label class="text-[11px] font-medium text-slate-600">
                                                Remark <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" x-model="item.remark"
                                                class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                placeholder="Keterangan singkat" required>
                                        </div>
                                    </div>

                                    {{-- Hidden inputs supaya format request sama seperti sebelumnya --}}
                                    <template x-for="(itemHidden, iHidden) in [item]" :key="'hidden-' + index">
                                        <div class="hidden">
                                            <input type="hidden" :name="`items[${index}][NIK]`" x-model="item.nik">
                                            <input type="hidden" :name="`items[${index}][nama]`" x-model="item.nama">
                                            <input type="hidden" :name="`items[${index}][jobdesc]`"
                                                x-model="item.jobdesc">
                                            <input type="hidden" :name="`items[${index}][startdate]`"
                                                x-model="item.startdate">
                                            <input type="hidden" :name="`items[${index}][starttime]`"
                                                x-model="item.starttime">
                                            <input type="hidden" :name="`items[${index}][enddate]`"
                                                x-model="item.enddate">
                                            <input type="hidden" :name="`items[${index}][endtime]`"
                                                x-model="item.endtime">
                                            <input type="hidden" :name="`items[${index}][break]`"
                                                x-model="item.break">
                                            <input type="hidden" :name="`items[${index}][remark]`"
                                                x-model="item.remark">
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </form>
            </div>

            {{-- FOOTER --}}
            <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-4 py-3 sm:px-6">
                <button type="button" @click="close()"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                    Close
                </button>
                <button type="submit" form="form-overtime-edit"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Save changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Alpine helper --}}
<script>
    function editOvertimeModal(header, datas, departments) {
        return {
            open: false,
            deptId: header.dept_id ?? '',
            isDesign: header.is_design ?? '',
            createDate: header.create_date ?? '',
            departments: departments ?? [],
            items: (datas ?? []).map(d => ({
                nik: d.NIK ?? '',
                nama: d.nama ?? d.name ?? '',
                jobdesc: d.job_desc ?? '',
                startdate: d.start_date ?? '',
                starttime: d.start_time ?? '',
                enddate: d.end_date ?? '',
                endtime: d.end_time ?? '',
                break: d.break ?? '',
                remark: d.remark ?? '',
            })),

            // Search state
            searchResults: [],
            searchIndex: null,
            searchOpenFor: null,

            get deptIsMoulding() {
                const dep = this.departments.find(d => String(d.id) === String(this.deptId));
                return dep && dep.name === 'MOULDING';
            },

            addItem() {
                this.items.push({
                    nik: '',
                    nama: '',
                    jobdesc: '',
                    startdate: '',
                    starttime: '',
                    enddate: '',
                    endtime: '',
                    break: '',
                    remark: '',
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
                // close dropdown kalau index yang dihapus sedang aktif
                if (this.searchIndex === index) {
                    this.closeSearch(index);
                }
            },

            startSearch(index, field) {
                this.searchIndex = index;
                this.searchOpenFor = field; // 'nik' atau 'nama'
                this.searchResults = [];
            },

            async onSearchInput(field, value, index) {
                this.searchIndex = index;
                this.searchOpenFor = field;

                if (!value) {
                    this.searchResults = [];
                    return;
                }

                try {
                    const param = field === 'nik' ? 'nik' : 'name';
                    const url = `/get-employees?${param}=${encodeURIComponent(value)}&deptid=${this.deptId || ''}`;
                    const res = await fetch(url);
                    const data = await res.json();
                    this.searchResults = Array.isArray(data) ? data : [];
                } catch (e) {
                    console.error('Error fetching employees:', e);
                    this.searchResults = [];
                }
            },

            pickEmployee(emp) {
                if (this.searchIndex === null) return;
                const item = this.items[this.searchIndex];
                item.nik = emp.NIK;
                item.nama = emp.nama;
                this.searchResults = [];
                this.searchIndex = null;
                this.searchOpenFor = null;
            },

            closeSearch(index) {
                if (this.searchIndex === index) {
                    this.searchIndex = null;
                    this.searchOpenFor = null;
                    this.searchResults = [];
                }
            },

            close() {
                this.open = false;
                this.searchIndex = null;
                this.searchOpenFor = null;
                this.searchResults = [];
            },
        }
    }
</script>
