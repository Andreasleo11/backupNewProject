@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-4 space-y-4" x-data="monthlyBudgetForm()" x-cloak>
        {{-- Breadcrumb --}}
        <nav class="text-xs text-slate-500 mb-1" aria-label="breadcrumb">
            <ol class="flex items-center gap-1">
                <li>
                    <a href="{{ route('monthly-budget-reports.index') }}" class="hover:text-slate-700 hover:underline">
                        Monthly Budget Reports
                    </a>
                </li>
                <li>/</li>
                <li class="text-slate-700 font-medium">Create</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="flex items-center justify-between gap-2">
            <div>
                <h1 class="text-lg font-semibold text-slate-900">
                    Create Monthly Budget Report
                </h1>
                <p class="text-xs text-slate-500">
                    Fill in the period and items or upload from Excel template.
                </p>
            </div>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="p-4 sm:p-6">
                <form action="{{ route('monthly-budget-reports.store') }}" method="post" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf

                    <input type="hidden" name="creator_id" value="{{ auth()->user()->id }}">

                    {{-- Top row: Dept + Report date + Input method --}}
                    <div class="grid gap-4 md:grid-cols-3">
                        {{-- Dept No --}}
                        <div>
                            <label for="dept_no" class="block text-xs font-semibold text-slate-700 mb-1">
                                Dept No <span class="text-rose-500">*</span>
                            </label>
                            <select name="dept_no" id="dept_no" x-model="deptNo" required
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                                       focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                @foreach ($departments as $department)
                                    @if ($department->name !== 'MANAGEMENT')
                                        <option value="{{ $department->dept_no }}"
                                            {{ auth()->user()->department->id === $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('dept_no')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Report date --}}
                        <div>
                            <label for="report_date" class="block text-xs font-semibold text-slate-700 mb-1">
                                Report Date <span class="text-rose-500">*</span>
                            </label>
                            <input id="report_date"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                                       focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                type="date" name="report_date" x-model="reportDate" required>
                            @error('report_date')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Input method --}}
                        <div class="flex flex-col justify-center">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Input Method
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                                    <input type="checkbox" id="inputToggle" name="input_method" value="excel"
                                        x-model="useExcel"
                                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>Use Excel Input</span>
                                </label>

                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-slate-300 bg-white
                                           px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm
                                           hover:bg-slate-50"
                                    x-show="useExcel" @click.prevent="$refs.excelForm.submit()">
                                    Download Excel Template
                                </button>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-500">
                                Check to upload Excel instead of manual input.
                            </p>
                        </div>
                    </div>

                    {{-- Manual input section --}}
                    <div class="mt-4 space-y-3" x-show="!useExcel">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    List of Items
                                </label>
                                <p class="text-[11px] text-slate-500">
                                    Add items and quantities for this month.
                                </p>
                            </div>
                            <button
                                class="inline-flex items-center rounded-md border border-slate-300 bg-white
                                       px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                                type="button" @click="addItem()">
                                <span class="mr-1 text-base leading-none">+</span> Add Item
                            </button>
                        </div>

                        <div id="items" class="border border-slate-200 rounded-lg p-3 space-y-2 bg-slate-50/50">
                            {{-- Header row --}}
                            <div class="header-row items-center pb-1 mb-1 border-b border-slate-200"
                                :class="isDept363 ? 'grid grid-cols-12 gap-2' : 'grid grid-cols-10 gap-2'"
                                x-show="items.length > 0">
                                {{-- # --}}
                                <div class="text-[11px] font-semibold text-slate-600 text-center"
                                    :class="isDept363 ? 'col-span-1' : 'col-span-1'">
                                    #
                                </div>

                                {{-- Name --}}
                                <div class="text-[11px] font-semibold text-slate-600 text-center"
                                    :class="isDept363 ? 'col-span-2' : 'col-span-3'">
                                    Name
                                </div>

                                {{-- Spec (hanya dept 363) --}}
                                <div class="text-[11px] font-semibold text-slate-600 text-center" x-show="isDept363"
                                    :class="isDept363 ? 'col-span-2' : 'hidden'">
                                    Spec
                                </div>

                                {{-- UoM --}}
                                <div class="text-[11px] font-semibold text-slate-600 text-center"
                                    :class="isDept363 ? 'col-span-1' : 'col-span-1'">
                                    UoM
                                </div>

                                {{-- Last Recorded Stock (363) --}}
                                <div class="text-[11px] font-semibold text-slate-600 text-center" x-show="isDept363"
                                    :class="isDept363 ? 'col-span-1' : 'hidden'">
                                    Last Recorded Stock
                                </div>

                                {{-- Usage Per Month (363) --}}
                                <div class="text-[11px] font-semibold text-slate-600 text-center" x-show="isDept363"
                                    :class="isDept363 ? 'col-span-1' : 'hidden'">
                                    Usage Per Month
                                </div>

                                {{-- Quantity Request --}}
                                <div class="text-[11px] font-semibold text-slate-600 text-center"
                                    :class="isDept363 ? 'col-span-1' : 'col-span-1'">
                                    Quantity Request
                                </div>

                                {{-- Remark --}}
                                <div class="text-[11px] font-semibold text-slate-600 text-center"
                                    :class="isDept363 ? 'col-span-2' : 'col-span-3'">
                                    Remark
                                </div>

                                {{-- Action --}}
                                <div class="text-[11px] font-semibold text-slate-600 text-center"
                                    :class="isDept363 ? 'col-span-1' : 'col-span-1'">
                                    Action
                                </div>
                            </div>


                            {{-- Item rows --}}
                            <template x-for="(item, index) in items" :key="index">
                                <div class="added-item items-center mt-1"
                                    :class="isDept363 ? 'grid grid-cols-12 gap-2' : 'grid grid-cols-10 gap-2'">
                                    {{-- # --}}
                                    <div class="text-center text-xs text-slate-500"
                                        :class="isDept363 ? 'col-span-1' : 'col-span-1'">
                                        <span x-text="index + 1"></span>
                                    </div>

                                    {{-- Name --}}
                                    <div :class="isDept363 ? 'col-span-2' : 'col-span-3'">
                                        <input type="text"
                                            class="block w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm
                       focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            :name="`items[${index}][name]`" placeholder="Name" x-model="item.name"
                                            :required="!useExcel">
                                    </div>

                                    {{-- Spec (363 only) --}}
                                    <div x-show="isDept363" :class="isDept363 ? 'col-span-2' : 'hidden'">
                                        <input type="text"
                                            class="block w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm
                       focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            :name="`items[${index}][spec]`" placeholder="Spec" x-model="item.spec">
                                    </div>

                                    {{-- UoM --}}
                                    <div :class="isDept363 ? 'col-span-1' : 'col-span-1'">
                                        <input type="text"
                                            class="block w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm
                       focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            :name="`items[${index}][uom]`" placeholder="UoM" x-model="item.uom"
                                            :required="!useExcel">
                                    </div>

                                    {{-- Last Recorded Stock (363) --}}
                                    <div x-show="isDept363" :class="isDept363 ? 'col-span-1' : 'hidden'">
                                        <input type="number"
                                            class="block w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm
                       focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            :name="`items[${index}][last_recorded_stock]`"
                                            placeholder="Last Recorded Stock" x-model="item.last_recorded_stock">
                                    </div>

                                    {{-- Usage Per Month (363) --}}
                                    <div x-show="isDept363" :class="isDept363 ? 'col-span-1' : 'hidden'">
                                        <input type="text"
                                            class="block w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm
                       focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            :name="`items[${index}][usage_per_month]`" placeholder="Usage Per Month"
                                            x-model="item.usage_per_month">
                                    </div>

                                    {{-- Quantity --}}
                                    <div :class="isDept363 ? 'col-span-1' : 'col-span-1'">
                                        <input type="number"
                                            class="block w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm
                       focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            :name="`items[${index}][quantity]`" placeholder="Qty" min="0"
                                            x-model="item.quantity" :required="!useExcel">
                                    </div>

                                    {{-- Remark --}}
                                    <div :class="isDept363 ? 'col-span-2' : 'col-span-3'">
                                        <input type="text"
                                            class="block w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs shadow-sm
                       focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            :name="`items[${index}][remark]`" placeholder="Remark" x-model="item.remark">
                                    </div>

                                    {{-- Action --}}
                                    <div :class="isDept363 ? 'col-span-1 text-center' : 'col-span-1 text-center'">
                                        <button type="button"
                                            class="inline-flex items-center rounded-md bg-rose-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700"
                                            @click="removeItem(index)">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </template>

                            {{-- Empty state --}}
                            <div class="text-center text-xs text-slate-400 py-4" x-show="items.length === 0">
                                No items yet. Click “Add Item” to start.
                            </div>
                        </div>
                    </div>

                    {{-- Excel file input section --}}
                    <div class="mt-4" x-show="useExcel">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Upload Excel File
                        </label>
                        <input
                            class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                                   file:mr-3 file:rounded-md file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5
                                   file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-700
                                   focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            type="file" name="excel_file" accept=".xlsx,.xls" :required="useExcel">
                        <p class="mt-1 text-[11px] text-slate-500">
                            Use the downloaded template so the columns match.
                        </p>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2">
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600
                                   px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Submit Report
                        </button>
                    </div>
                </form>

                {{-- Hidden form: download Excel template (uses current deptNo) --}}
                <form action="{{ route('monthly.budget.download.excel.template') }}" method="post" x-ref="excelForm"
                    class="hidden">
                    @csrf
                    <input type="hidden" name="dept_no" :value="deptNo">
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function monthlyBudgetForm() {
            return {
                // initial values from backend
                deptNo: @json(old('dept_no', optional(auth()->user()->department)->dept_no)),
                reportDate: @json(old('report_date')),
                useExcel: false,
                items: [],

                get isDept363() {
                    return this.deptNo === '363';
                },

                init() {
                    // fallback deptNo jika kosong -> ambil option pertama
                    if (!this.deptNo) {
                        const el = document.getElementById('dept_no');
                        if (el && el.options.length) {
                            this.deptNo = el.value;
                        }
                    }

                    // Kalau belum ada items, minimal 1 row
                    if (this.items.length === 0) {
                        this.addItem();
                    }
                },

                addItem() {
                    this.items.push({
                        name: '',
                        spec: '',
                        uom: 'PCS',
                        last_recorded_stock: '',
                        usage_per_month: '',
                        quantity: '',
                        remark: '',
                    });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },
            };
        }
    </script>
@endpush
