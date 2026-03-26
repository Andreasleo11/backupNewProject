@extends('new.layouts.app')

@section('content')
    @include('partials.alert-success-error')

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Header + breadcrumb --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">
                    Create Purchase Order
                </h1>

                <nav class="mt-2" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1 text-sm text-slate-500">
                        <li>
                            <a href="{{ route('po.index') }}" class="hover:text-slate-700">
                                Purchase Orders
                            </a>
                        </li>
                        <li class="px-1 text-slate-400">/</li>
                        <li class="text-slate-700 font-medium">Create</li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Card --}}
        <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl">
            <form
                action="{{ route('po.store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="p-5 space-y-5"
            >
                @csrf

                @if (!empty($parentPONumber))
                    <div>
                        <label for="parent_po_number" class="block text-sm font-medium text-slate-700 mb-1">
                            Parent PO Number
                        </label>
                        <input
                            id="parent_po_number"
                            name="parent_po_number"
                            type="text"
                            readonly
                            value="{{ old('parent_po_number', $parentPONumber) }}"
                            class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                        >
                        <p class="mt-1 text-xs text-slate-500">
                            This PO will be treated as a revision of the parent PO.
                        </p>
                    </div>
                @endif

                {{-- PO Number --}}
                <div>
                    <label for="po_number" class="block text-sm font-medium text-slate-700 mb-1">
                        PO Number
                    </label>
                    <input
                        id="po_number"
                        name="po_number"
                        type="number"
                        required
                        placeholder="2556622"
                        value="{{ old('po_number') }}"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('po_number')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Vendor Name --}}
                <div>
                    <label for="vendor_name" class="block text-sm font-medium text-slate-700 mb-1">
                        Vendor Name
                    </label>
                    <input
                        id="vendor_name"
                        name="vendor_name"
                        type="text"
                        required
                        placeholder="PT. MAJU TERUS"
                        value="{{ old('vendor_name') }}"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('vendor_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Invoice Date (dd.mm.yy) --}}
                <div>
                    <label for="invoice_date" class="block text-sm font-medium text-slate-700 mb-1">
                        Invoice Date
                    </label>
                    <input
                        id="invoice_date"
                        name="invoice_date"
                        type="text"
                        required
                        placeholder="18.11.24"
                        value="{{ old('invoice_date') }}"
                        aria-describedby="poDateHelp"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <p id="poDateHelp" class="mt-1 text-xs text-slate-500">
                        Use <span class="font-mono">dd.mm.yy</span> format.
                    </p>
                    @error('invoice_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Invoice Number --}}
                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-slate-700 mb-1">
                        Invoice Number
                    </label>
                    <input
                        id="invoice_number"
                        name="invoice_number"
                        type="text"
                        required
                        placeholder="98/MT/223/03"
                        value="{{ old('invoice_number') }}"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('invoice_number')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Payment Date --}}
                <div>
                    <label for="tanggal_pembayaran" class="block text-sm font-medium text-slate-700 mb-1">
                        Payment Date
                    </label>
                    <input
                        id="tanggal_pembayaran"
                        name="tanggal_pembayaran"
                        type="date"
                        required
                        value="{{ old('tanggal_pembayaran') }}"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('tanggal_pembayaran')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="purchase_order_category_id" class="block text-sm font-medium text-slate-700 mb-1">
                        Category
                    </label>
                    <select
                        id="purchase_order_category_id"
                        name="purchase_order_category_id"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="" {{ old('purchase_order_category_id') == '' ? 'selected' : '' }}>
                            — Select Category —
                        </option>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                {{ old('purchase_order_category_id') == $category->id ? 'selected' : '' }}
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('purchase_order_category_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Total + Currency --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Total
                    </label>
                    <div class="flex gap-2">
                        <select
                            id="currency"
                            name="currency"
                            required
                            class="w-28 rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="IDR" {{ old('currency') == 'IDR' ? 'selected' : '' }}>IDR</option>
                            <option value="YUAN" {{ old('currency') == 'YUAN' ? 'selected' : '' }}>CNY</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                        </select>

                        <div class="flex-1">
                            <input
                                id="total"
                                name="total"
                                type="text"
                                required
                                placeholder="1,498,000"
                                value="{{ old('total') }}"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>
                    </div>
                    @error('total')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PDF Upload with Alpine filename preview --}}
                <div x-data="{ fileName: '' }">
                    <label for="pdf_file" class="block text-sm font-medium text-slate-700 mb-1">
                        Invoice PDF
                    </label>
                    <input
                        id="pdf_file"
                        name="pdf_file"
                        type="file"
                        accept="application/pdf"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                        aria-describedby="pdfFileHelp"
                        @change="fileName = $event.target.files[0]?.name || ''"
                    >
                    <p id="pdfFileHelp" class="mt-1 text-xs text-slate-500">
                        Maximum file size 2 MB.
                    </p>
                    <p x-show="fileName" class="mt-1 text-xs text-slate-600">
                        Selected file:
                        <span class="font-medium" x-text="fileName"></span>
                    </p>
                    @error('pdf_file')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <a
                        href="{{ route('po.index') }}"
                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                    >
                        Save Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- amount formatting --}}
    <script>
        document.getElementById('total').addEventListener('input', function (e) {
            let value = e.target.value.replace(/,/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                parts.splice(2);
            }
            parts[0] = parts[0].replace(/\D/g, '');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            e.target.value = parts.join('.');
        });
    </script>
@endsection
