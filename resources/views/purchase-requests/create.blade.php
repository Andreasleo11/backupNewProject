@extends('new.layouts.app')

@section('content')
    @php
        $authUser = auth()->user();
    @endphp

    <div class="mx-auto max-w-5xl px-4 py-6 lg:py-8" x-data="purchaseRequestForm(
        @js(old('items', [])),
        '{{ old('from_department', $authUser->department->name) }}',
        '{{ old('to_department') }}'
    )" x-init="init()">
        {{-- TOP BAR --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <a href="{{ route('purchase-requests.index') }}"
                    class="inline-flex items-center text-xs font-medium text-slate-400 hover:text-slate-600">
                    ‹ Back to list
                </a>
                <h1 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
                    Create Purchase Request
                </h1>
                <p class="text-xs text-slate-500 sm:text-sm">
                    Masukkan detail PR dengan jelas untuk mempercepat proses approval.
                </p>
            </div>

            <div class="text-right text-xs sm:text-sm">
                <p class="font-medium text-slate-700">{{ $authUser->name }}</p>
                <p class="text-slate-400">{{ $authUser->department->name ?? 'Unknown Department' }}</p>
            </div>
        </div>

        {{-- CARD --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
                <p class="text-xs text-slate-500 sm:text-sm">
                    Gunakan <span class="font-semibold text-slate-800">Draft</span> jika PR belum final dan masih akan
                    direvisi.
                </p>
            </div>

            <div class="px-4 pb-5 sm:px-6">
                <form action="{{ route('purchase-requests.store') }}" method="POST" class="space-y-8" id="pr-form">
                    @csrf

                    {{-- SECTION 1: GENERAL --}}
                    <section class="space-y-4">
                        <h2 class="text-sm font-semibold text-slate-900">
                            General
                        </h2>
                        <div class="grid gap-4 md:grid-cols-2">
                            {{-- Draft --}}
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Draft
                                </label>
                                <div class="flex gap-3 rounded-xl bg-slate-50 p-2">
                                    <label
                                        class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border px-2 py-1.5 text-xs font-medium
                                        {{ old('is_draft', '0') == '1'
                                            ? 'border-indigo-500 bg-white text-indigo-600 shadow-sm'
                                            : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                        <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            type="radio" name="is_draft" value="1"
                                            {{ old('is_draft') == '1' ? 'checked' : '' }}>
                                        <span>Yes</span>
                                    </label>

                                    <label
                                        class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border px-2 py-1.5 text-xs font-medium
                                        {{ old('is_draft', '0') == '0'
                                            ? 'border-indigo-500 bg-white text-indigo-600 shadow-sm'
                                            : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                        <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            type="radio" name="is_draft" value="0"
                                            {{ old('is_draft', '0') == '0' ? 'checked' : '' }}>
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Branch --}}
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Branch
                                </label>
                                <div class="flex gap-3 rounded-xl bg-slate-50 p-2">
                                    <label
                                        class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border px-2 py-1.5 text-xs font-medium
                                        {{ old('branch', 'JAKARTA') === 'JAKARTA'
                                            ? 'border-indigo-500 bg-white text-indigo-600 shadow-sm'
                                            : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                        <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            type="radio" name="branch" value="JAKARTA"
                                            {{ old('branch', 'JAKARTA') === 'JAKARTA' ? 'checked' : '' }}>
                                        <span>Jakarta</span>
                                    </label>

                                    <label
                                        class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border px-2 py-1.5 text-xs font-medium
                                        {{ old('branch') === 'KARAWANG'
                                            ? 'border-indigo-500 bg-white text-indigo-600 shadow-sm'
                                            : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                        <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            type="radio" name="branch" value="KARAWANG"
                                            {{ old('branch') === 'KARAWANG' ? 'checked' : '' }}>
                                        <span>Karawang</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- SECTION 2: ROUTING --}}
                    <section class="space-y-4">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Routing
                        </h2>
                        <div class="grid gap-4 md:grid-cols-3">
                            {{-- From Department --}}
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    From Department
                                </label>
                                <select
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    name="from_department" x-model="from_department" x-init="initSimpleTomSelect($el, 'from')" required>
                                    <option value="" disabled>Select from department…</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->name }}"
                                            {{ old('from_department', $authUser->department->name) === $department->name ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- To Department --}}
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    To Department
                                </label>
                                <select
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    name="to_department" x-model="to_department" x-init="initSimpleTomSelect($el, 'to')" required>
                                    <option value="" disabled>Select to department…</option>
                                    <option value="Maintenance"
                                        {{ old('to_department') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="Purchasing"
                                        {{ old('to_department') == 'Purchasing' ? 'selected' : '' }}>Purchasing</option>
                                    <option value="Personnel" {{ old('to_department') == 'Personnel' ? 'selected' : '' }}>
                                        Personnel</option>
                                    <option value="Computer" {{ old('to_department') == 'Computer' ? 'selected' : '' }}>
                                        Computer</option>
                                </select>
                            </div>

                            {{-- Local / Import (selalu tampil, tapi bisa disabled) --}}
                            <div class="space-y-2">
                                <label
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500 flex items-center gap-2">
                                    <span>Local / Import</span>
                                </label>
                                {{-- Badge info kalau tidak relevan --}}
                                <span x-show="!showLocalImport"
                                    class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                                    Tidak diperlukan untuk routing ini
                                </span>

                                <div class="flex gap-3 rounded-xl bg-slate-50 p-2 transition-opacity"
                                    :class="showLocalImport ? 'opacity-100' : 'opacity-60'">
                                    <label
                                        class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border px-2 py-1.5 text-xs font-medium
                                                border-slate-200 text-slate-600 hover:border-slate-300">
                                        <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            type="radio" name="is_import" value="false" :disabled="!showLocalImport"
                                            @checked(old('is_import') === 'false')>
                                        <span>Local</span>
                                    </label>

                                    <label
                                        class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border px-2 py-1.5 text-xs font-medium
                                                border-slate-200 text-slate-600 hover:border-slate-300">
                                        <input class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            type="radio" name="is_import" value="true" :disabled="!showLocalImport"
                                            @checked(old('is_import') === 'true')>
                                        <span>Import</span>
                                    </label>
                                </div>

                                <p class="text-[11px] text-slate-400">
                                    Berlaku hanya untuk kombinasi <span class="font-semibold">MOULDING → Purchasing</span>.
                                </p>
                            </div>

                        </div>
                    </section>

                    {{-- SECTION 3: ITEMS --}}
                    <section class="space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-sm font-semibold text-slate-900">
                                Items
                            </h2>
                            <button type="button"
                                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-black"
                                @click="addItem()">
                                <span class="text-base leading-none">+</span>
                                <span>Add Item</span>
                            </button>
                        </div>

                        <div id="items"
                            class="mt-1 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm">
                            {{-- Header (desktop) --}}
                            <div
                                class="hidden md:grid md:grid-cols-12 items-center gap-3 border-b border-slate-200 bg-slate-100 px-2 py-2 text-[11px] font-semibold text-slate-600">
                                <div class="col-span-1 text-center">No</div>
                                <div class="col-span-2">Item Name</div>
                                <div class="col-span-1 text-center">Qty</div>
                                <div class="col-span-1 text-center">UoM</div>
                                <div class="col-span-1 text-center">Currency</div>
                                <div class="col-span-2">Unit Price</div>
                                <div class="col-span-1 text-right">Subtotal</div>
                                <div class="col-span-2">Purpose</div>
                                <div class="col-span-1 text-center">Action</div>
                            </div>

                            {{-- Rows --}}
                            <template x-for="(item, index) in items" :key="index">
                                <div
                                    class="grid grid-cols-1 gap-3 border-b border-slate-100 px-2 py-3 sm:grid-cols-2 md:grid-cols-12 md:items-center">

                                    {{-- No --}}
                                    <div class="md:text-center md:col-span-1 md:block">
                                        <span class="md:hidden text-[11px] font-medium text-slate-500">No</span>
                                        <span class="text-xs font-semibold text-slate-800" x-text="index + 1"></span>
                                    </div>

                                    {{-- Item Name --}}
                                    <div class="md:col-span-2">
                                        <label class="mb-1 block text-[11px] font-medium text-slate-500 md:hidden">
                                            Item Name
                                        </label>
                                        <input type="text"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="Item Name" x-model="item.item_name"
                                            :name="'items[' + index + '][item_name]'" x-init="initItemTomSelect($el, index)" required>
                                    </div>

                                    {{-- Qty --}}
                                    <div class="md:col-span-1">
                                        <label class="mb-1 block text-[11px] font-medium text-slate-500 md:hidden">
                                            Qty
                                        </label>
                                        <input type="text"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1 text-center text-xs text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="0" x-model="item.quantity"
                                            @input="sanitizeNumber(index, 'quantity')"
                                            :name="'items[' + index + '][quantity]'" required>
                                    </div>

                                    {{-- UoM --}}
                                    <div class="md:col-span-1">
                                        <label class="mb-1 block text-[11px] font-medium text-slate-500 md:hidden">
                                            UoM
                                        </label>
                                        <input type="text"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1 text-center text-xs text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                            x-model="item.uom" :name="'items[' + index + '][uom]'" required>
                                    </div>

                                    {{-- Currency --}}
                                    <div class="md:col-span-1">
                                        <label class="mb-1 block text-[11px] font-medium text-slate-500 md:hidden">
                                            Currency
                                        </label>
                                        <select
                                            class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-center text-xs text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                            x-model="item.currency" :name="'items[' + index + '][currency]'" required>
                                            <template x-for="cur in currencies" :key="cur">
                                                <option :value="cur" x-text="cur"></option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Unit Price --}}
                                    <div class="md:col-span-2">
                                        <label class="mb-1 block text-[11px] font-medium text-slate-500 md:hidden">
                                            Unit Price
                                        </label>
                                        <input type="text"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1 text-right text-xs text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="0" x-model="item.price" @input="sanitizeNumber(index, 'price')"
                                            :name="'items[' + index + '][price]'" required>
                                    </div>

                                    {{-- Subtotal --}}
                                    <div class="md:col-span-1 md:text-right">
                                        <label class="mb-1 block text-[11px] font-medium text-slate-500 md:hidden">
                                            Subtotal
                                        </label>
                                        <span class="text-xs font-semibold text-slate-800 md:text-sm"
                                            x-text="formatMoney(itemSubtotal(item), item.currency)"></span>
                                    </div>

                                    {{-- Purpose --}}
                                    <div class="sm:col-span-2 md:col-span-2">
                                        <label class="mb-1 block text-[11px] font-medium text-slate-500 md:hidden">
                                            Purpose
                                        </label>
                                        <input type="text"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="Tujuan pembelian" x-model="item.purpose"
                                            :name="'items[' + index + '][purpose]'" required>
                                    </div>

                                    {{-- Action --}}
                                    <div class="flex items-center justify-end md:col-span-1 md:block">
                                        <button type="button"
                                            class="inline-flex items-center justify-center rounded-md bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 disabled:opacity-40"
                                            @click="removeItem(index)" :disabled="items.length === 1"
                                            title="Remove item">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Totals by currency --}}
                        <div class="mt-3 flex flex-wrap justify-end gap-2 text-xs sm:text-sm">
                            <template x-for="([cur, total], i) in Object.entries(totalsByCurrency())"
                                :key="cur">
                                <div class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-3 py-1 text-white">
                                    <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-semibold"
                                        x-text="cur"></span>
                                    <span class="font-semibold" x-text="formatMoney(total, cur)"></span>
                                </div>
                            </template>
                        </div>
                    </section>

                    {{-- SECTION 4: DATES & SUPPLIER --}}
                    <section class="space-y-4">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Dates & Supplier
                        </h2>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Date of PR
                                </label>
                                <input
                                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    type="date" name="date_of_pr" value="{{ old('date_of_pr') }}" required>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Date of Required
                                </label>
                                <input
                                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    type="date" name="date_of_required" value="{{ old('date_of_required') }}"
                                    required>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Supplier
                                </label>
                                <input
                                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    type="text" name="supplier" value="{{ old('supplier') }}" required>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    PIC
                                </label>
                                <input
                                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    type="text" name="pic" value="{{ old('pic') }}" required>
                            </div>
                        </div>
                    </section>

                    {{-- SECTION 5: REMARK --}}
                    <section class="space-y-2">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Remark
                        </h2>
                        <textarea
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            name="remark" rows="3" required>{{ old('remark') }}</textarea>
                    </section>

                    {{-- ACTIONS --}}
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <a href="{{ route('purchase-requests.index') }}"
                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Cancel
                        </a>
                        <button
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700"
                            type="submit">
                            <span>Submit PR</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('purchaseRequestForm', (oldItems = [], initialFromDept = '', initialToDept = '') => ({
                from_department: initialFromDept || '',
                to_department: initialToDept || '',
                items: [],
                currencies: ['IDR', 'CNY', 'USD'],

                init() {
                    if (Array.isArray(oldItems) && oldItems.length) {
                        this.items = oldItems.map(i => ({
                            item_name: i.item_name || '',
                            quantity: i.quantity || '',
                            uom: i.uom || 'PCS',
                            currency: i.currency || 'IDR',
                            price: i.price || '',
                            purpose: i.purpose || '',
                        }));
                    }

                    if (this.items.length === 0) {
                        this.addItem();
                    }
                },

                initSimpleTomSelect(el, type) {
                    if (!window.TomSelect || el._ts) return;

                    const ts = new TomSelect(el, {
                        plugins: ['dropdown_input'],
                        sortField: {
                            field: 'text',
                            direction: 'asc'
                        },
                        dropdownParent: 'body',
                    });

                    el._ts = ts;

                    ts.on('change', (value) => {
                        if (type === 'from') this.from_department = value;
                        if (type === 'to') this.to_department = value;
                    });
                },

                initItemTomSelect(el, index) {
                    if (!window.TomSelect || el._ts) return;

                    const ts = new TomSelect(el, {
                        valueField: 'name',
                        labelField: 'name',
                        searchField: 'name',
                        maxItems: 1,
                        closeAfterSelect: true,
                        create: true,
                        dropdownParent: 'body',
                        load(query, callback) {
                            if (!query.length) return callback();
                            fetch(`/get-item-names?itemName=${encodeURIComponent(query)}`)
                                .then(res => res.json())
                                .then(data => callback(data))
                                .catch(() => callback());
                        },
                        render: {
                            option(item, escape) {
                                return `<div class="text-xs px-2 py-1">
                                    <span>${escape(item.name)}</span>
                                </div>`;
                            },
                        },
                    });

                    el._ts = ts;

                    ts.on('change', (value) => {
                        if (!this.items[index]) return;
                        this.items[index].item_name = value;

                        const opt = ts.options[value];
                        if (opt) {
                            if (opt.currency && this.items[index].currency === 'IDR') {
                                this.items[index].currency = opt.currency;
                            }
                            if (opt.latest_price || opt.price) {
                                this.items[index].price = (opt.latest_price ?? opt.price)
                                    .toString();
                            }
                        }
                    });
                },

                get showLocalImport() {
                    return this.from_department === 'MOULDING' && this.to_department ===
                        'Purchasing';
                },

                addItem() {
                    this.items.push({
                        item_name: '',
                        quantity: '',
                        uom: 'PCS',
                        currency: 'IDR',
                        price: '',
                        purpose: '',
                    });
                },

                removeItem(index) {
                    if (this.items.length === 1) return;
                    this.items.splice(index, 1);
                },

                sanitizeNumber(index, field) {
                    if (!this.items[index]) return;
                    const raw = this.items[index][field] ?? '';
                    this.items[index][field] = String(raw).replace(/[^0-9.]/g, '');
                },

                itemSubtotal(item) {
                    const qty = parseFloat(String(item.quantity || '').replace(/[^0-9.]/g, '')) || 0;
                    const price = parseFloat(String(item.price || '').replace(/[^0-9.]/g, '')) || 0;
                    return qty * price;
                },

                totalsByCurrency() {
                    const totals = {};
                    this.items.forEach((i) => {
                        const cur = i.currency || 'IDR';
                        const sub = this.itemSubtotal(i);
                        if (!totals[cur]) totals[cur] = 0;
                        totals[cur] += sub;
                    });
                    return totals;
                },

                formatMoney(amount, currency) {
                    amount = Number(amount || 0);
                    const symbol =
                        currency === 'IDR' ? 'Rp ' :
                        currency === 'USD' ? '$ ' :
                        currency === 'CNY' ? '¥ ' : '';

                    return symbol + amount.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });
                },
            }));
        });
    </script>
@endpush
