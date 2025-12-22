@extends('new.layouts.app')

@section('content')
    @php
        $initialItems = $report->details
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'spec' => $d->spec,
                    'uom' => $d->uom,
                    'last_recorded_stock' => $d->last_recorded_stock,
                    'usage_per_month' => $d->usage_per_month,
                    'quantity' => $d->quantity,
                    'remark' => $d->remark,
                ];
            })
            ->values();
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-4"
         x-data='monthlyBudgetEdit(@json([
            "deptNo"       => $report->dept_no,
            "initialItems" => $initialItems,
         ]))'
         x-init="init()"
    >
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="flex items-center text-xs text-slate-500 gap-1">
            <a href="{{ route('monthly-budget-reports.index') }}"
               class="hover:text-slate-700 hover:underline">
                Monthly Budget Reports
            </a>
            <span>/</span>
            <span class="text-slate-700 font-medium">Edit</span>
        </nav>

        {{-- Page header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-slate-900">
                    Edit Monthly Budget Report
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Update report data & requested items for this month.
                </p>
            </div>

            <a href="{{ route('monthly-budget-reports.index') }}"
               class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5
                      text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                <i class="bx bx-arrow-back mr-1 text-[0.9rem]"></i>
                Back to list
            </a>
        </div>

        {{-- FORM --}}
        <form action="{{ route('monthly-budget-reports.update', $report->id) }}"
              method="POST"
              id="form-monthly-budget-report"
              class="space-y-5"
        >
            @csrf
            @method('PUT')

            {{-- Header card --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 sm:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Dept no (readonly) --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">
                            Dept No
                        </label>
                        <input type="text"
                               name="dept_no"
                               readonly
                               x-model="deptNo"
                               class="block w-full rounded-md border border-slate-200 bg-slate-50
                                      px-3 py-2 text-sm text-slate-700 shadow-sm
                                      focus:outline-none focus:ring-1 focus:ring-slate-300"
                        >
                        <p class="text-[11px] text-slate-400">
                            Department is fixed for this report.
                        </p>
                    </div>

                    {{-- Report date --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">
                            Report Date
                        </label>
                        <input type="date"
                               name="report_date"
                               value="{{ $report->report_date }}"
                               required
                               class="block w-full rounded-md border border-slate-200
                                      px-3 py-2 text-sm text-slate-800 shadow-sm
                                      focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        <p class="text-[11px] text-slate-400">
                            Choose the effective month for this budget.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Items card --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                {{-- Card header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            List of Items
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Add or adjust requested items for this report.
                        </p>
                    </div>

                    <button type="button"
                            @click="addItem()"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5
                                   text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                        <i class="bx bx-plus mr-1 text-[0.9rem]"></i>
                        Add Item
                    </button>
                </div>

                {{-- Table-ish body --}}
                <div class="px-4 pb-4 pt-3">
                    <div class="overflow-x-auto">
                        {{-- Header row --}}
                        <div class="hidden md:grid grid-cols-12 gap-2 text-[11px] font-semibold
                                    text-slate-600 border-b border-slate-200 pb-2">
                            <div class="col-span-1 text-center">#</div>

                            {{-- Name --}}
                            <div :class="isMoulding ? 'col-span-2' : 'col-span-3'">
                                Name
                            </div>

                            {{-- Spec (moulding only) --}}
                            <template x-if="isMoulding">
                                <div class="col-span-2">
                                    Spec
                                </div>
                            </template>

                            {{-- UoM --}}
                            <div class="col-span-1">
                                UoM
                            </div>

                            {{-- Last recorded stock (moulding only) --}}
                            <template x-if="isMoulding">
                                <div class="col-span-1">
                                    Last Recorded Stock
                                </div>
                            </template>

                            {{-- Usage per month (moulding only) --}}
                            <template x-if="isMoulding">
                                <div class="col-span-1">
                                    Usage Per Month
                                </div>
                            </template>

                            {{-- Quantity --}}
                            <div :class="isMoulding ? 'col-span-1' : 'col-span-2'">
                                Quantity Request
                            </div>

                            {{-- Remark --}}
                            <div :class="isMoulding ? 'col-span-2' : 'col-span-4'">
                                Remark
                            </div>

                            <div class="col-span-1 text-center">
                                Action
                            </div>
                        </div>

                        {{-- Empty state --}}
                        <template x-if="items.length === 0">
                            <div class="py-6 text-center text-xs text-slate-500">
                                No items yet. Click
                                <span class="font-semibold text-slate-700">“Add Item”</span>
                                to start.
                            </div>
                        </template>

                        {{-- Rows --}}
                        <div class="space-y-2 mt-2">
                            <template x-for="(item, index) in items" :key="item.key">
                                <div
                                    class="grid grid-cols-1 md:grid-cols-12 gap-2 items-center bg-slate-50/40
                                           rounded-lg px-3 py-2"
                                >
                                    {{-- # --}}
                                    <div class="md:col-span-1 text-xs text-slate-500 md:text-center">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full
                                                     bg-slate-100 text-[11px] font-semibold text-slate-700">
                                            <span x-text="index + 1"></span>
                                        </span>
                                    </div>

                                    {{-- Name --}}
                                    <div :class="isMoulding ? 'md:col-span-2' : 'md:col-span-3'">
                                        <label class="md:hidden block text-[11px] font-medium text-slate-600 mb-0.5">
                                            Name
                                        </label>
                                        <input type="text"
                                               class="block w-full rounded-md border border-slate-200 bg-white
                                                      px-2.5 py-1.5 text-xs text-slate-800 shadow-sm
                                                      focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                               :name="`items[${index}][name]`"
                                               x-model="item.name"
                                               placeholder="Item name"
                                               required
                                        >
                                    </div>

                                    {{-- Spec (MOULDING only) --}}
                                    <template x-if="isMoulding">
                                        <div class="md:col-span-2">
                                            <label class="md:hidden block text-[11px] font-medium text-slate-600 mb-0.5">
                                                Spec
                                            </label>
                                            <input type="text"
                                                   class="block w-full rounded-md border border-slate-200 bg-white
                                                          px-2.5 py-1.5 text-xs text-slate-800 shadow-sm
                                                          focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                                   :name="`items[${index}][spec]`"
                                                   x-model="item.spec"
                                                   placeholder="Spec"
                                            >
                                        </div>
                                    </template>

                                    {{-- UoM --}}
                                    <div class="md:col-span-1">
                                        <label class="md:hidden block text-[11px] font-medium text-slate-600 mb-0.5">
                                            UoM
                                        </label>
                                        <input type="text"
                                               class="block w-full rounded-md border border-slate-200 bg-white
                                                      px-2.5 py-1.5 text-xs text-slate-800 shadow-sm
                                                      focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                               :name="`items[${index}][uom]`"
                                               x-model="item.uom"
                                               placeholder="PCS"
                                        >
                                    </div>

                                    {{-- Last recorded stock (MOULDING only) --}}
                                    <template x-if="isMoulding">
                                        <div class="md:col-span-1">
                                            <label class="md:hidden block text-[11px] font-medium text-slate-600 mb-0.5">
                                                Last Recorded Stock
                                            </label>
                                            <input type="number"
                                                   class="block w-full rounded-md border border-slate-200 bg-white
                                                          px-2.5 py-1.5 text-xs text-slate-800 shadow-sm
                                                          focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                                   :name="`items[${index}][last_recorded_stock]`"
                                                   x-model="item.last_recorded_stock"
                                                   placeholder="0"
                                                   min="0"
                                                   step="1"
                                            >
                                        </div>
                                    </template>

                                    {{-- Usage per month (MOULDING only) --}}
                                    <template x-if="isMoulding">
                                        <div class="md:col-span-1">
                                            <label class="md:hidden block text-[11px] font-medium text-slate-600 mb-0.5">
                                                Usage Per Month
                                            </label>
                                            <input type="text"
                                                   class="block w-full rounded-md border border-slate-200 bg-white
                                                          px-2.5 py-1.5 text-xs text-slate-800 shadow-sm
                                                          focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                                   :name="`items[${index}][usage_per_month]`"
                                                   x-model="item.usage_per_month"
                                                   placeholder="Usage / month"
                                            >
                                        </div>
                                    </template>

                                    {{-- Quantity --}}
                                    <div :class="isMoulding ? 'md:col-span-1' : 'md:col-span-2'">
                                        <label class="md:hidden block text-[11px] font-medium text-slate-600 mb-0.5">
                                            Quantity Request
                                        </label>
                                        <input type="number"
                                               class="block w-full rounded-md border border-slate-200 bg-white
                                                      px-2.5 py-1.5 text-xs text-slate-800 shadow-sm
                                                      focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                               :name="`items[${index}][quantity]`"
                                               x-model="item.quantity"
                                               placeholder="0"
                                               min="0"
                                               step="1"
                                        >
                                    </div>

                                    {{-- Remark --}}
                                    <div :class="isMoulding ? 'md:col-span-2' : 'md:col-span-4'">
                                        <label class="md:hidden block text-[11px] font-medium text-slate-600 mb-0.5">
                                            Remark
                                        </label>
                                        <input type="text"
                                               class="block w-full rounded-md border border-slate-200 bg-white
                                                      px-2.5 py-1.5 text-xs text-slate-800 shadow-sm
                                                      focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                               :name="`items[${index}][remark]`"
                                               x-model="item.remark"
                                               placeholder="Remark"
                                        >
                                    </div>

                                    {{-- Hidden ID (existing rows) --}}
                                    <template x-if="item.id">
                                        <input type="hidden"
                                               :name="`items[${index}][id]`"
                                               x-model="item.id">
                                    </template>

                                    {{-- Action --}}
                                    <div class="md:col-span-1 flex justify-end md:justify-center mt-1 md:mt-0">
                                        <button type="button"
                                                @click="removeItem(index)"
                                                class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1
                                                       text-[11px] font-medium text-rose-600 hover:bg-rose-100
                                                       border border-rose-100">
                                            <i class="bx bx-trash mr-1 text-[0.9rem]"></i>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 pt-2">
                <a href="{{ route('monthly-budget-reports.index') }}"
                   class="inline-flex items-center justify-center rounded-md border border-slate-300
                          bg-white px-4 py-2 text-xs font-medium text-slate-700 shadow-sm
                          hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2
                               text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <i class="bx bx-save mr-1 text-[0.95rem]"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('monthlyBudgetEdit', (config) => ({
                deptNo: config.deptNo ?? '',
                items: [],
                nextKey: 0,

                get isMoulding() {
                    // Bisa pakai dept no atau nama dept, disesuaikan
                    return String(this.deptNo) === '363' || '{{ $report->department->name }}' === 'MOULDING';
                },

                init() {
                    const initial = config.initialItems || [];

                    this.items = initial.map((d, idx) => ({
                        key: d.id ?? `existing-${idx}`,
                        id: d.id ?? null,
                        name: d.name ?? '',
                        spec: d.spec ?? '',
                        uom: d.uom ?? 'PCS',
                        last_recorded_stock: d.last_recorded_stock ?? '',
                        usage_per_month: d.usage_per_month ?? '',
                        quantity: d.quantity ?? '',
                        remark: d.remark ?? '',
                    }));

                    this.nextKey = this.items.length;

                    if (this.items.length === 0) {
                        this.addItem();
                    }
                },

                addItem() {
                    this.items.push({
                        key: `new-${this.nextKey++}`,
                        id: null,
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

                    if (this.items.length === 0) {
                        this.addItem();
                    }
                },
            }));
        });
    </script>
@endpush
