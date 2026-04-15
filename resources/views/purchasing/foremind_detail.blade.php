@extends('new.layouts.app')

@push('head')
    <style>
        @keyframes rainbow {
            0% {
                color: #ef4444;
            }

            /* red */
            16% {
                color: #f97316;
            }

            /* orange */
            32% {
                color: #eab308;
            }

            /* yellow */
            48% {
                color: #22c55e;
            }

            /* green */
            64% {
                color: #3b82f6;
            }

            /* blue */
            80% {
                color: #6366f1;
            }

            /* indigo */
            100% {
                color: #a855f7;
            }

            /* violet */
        }

        .rainbow-text {
            animation: rainbow 3s linear infinite;
            font-weight: 600;
        }

        .table-forecast th,
        .table-forecast td {
            white-space: nowrap;
            font-size: 0.85rem;
        }

        .table-forecast thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #f8f9fa;
        }

        .table-forecast tbody td {
            vertical-align: middle;
        }

        .table-forecast tbody td strong {
            display: block;
            font-weight: 600;
        }

        .sub-row-separator td {
            border-top: 2px solid #dee2e6;
        }
    </style>
@endpush

@section('page-title', 'Forecast Reminder Detail')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Forecast Reminder Detail</h1>
                <p class="text-slate-600 mt-1">
                    Monitoring kebutuhan material per vendor berdasarkan forecast & quantity material.
                </p>
            </div>
            <div class="md:text-right">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-slate-100 text-slate-700 border border-slate-200">
                    Terakhir di update :
                    <span class="ml-1 font-medium text-slate-900">
                        -
                    </span>
                </span>
            </div>
        </div>

        {{-- FORM: INTERNAL VENDOR --}}
        <form method="GET" action="/foremind-detail/print" target="_blank"
            class="bg-white rounded-xl border border-slate-200 shadow-sm">
            @csrf
            <div class="p-6">
                <div class="grid md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="vendor_code_internal">
                            Vendor (Internal)
                        </label>
                        <p class="text-xs text-slate-500">
                            Pilih vendor untuk cetak form internal.
                        </p>
                    </div>
                    <div class="md:col-span-5">
                        <select
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            id="vendor_code_internal" name="vendor_code" required>
                            <option value="" selected disabled>Select Vendor Name</option>
                            @foreach ($contacts as $contact)
                                <option value="{{ $contact->vendor_code }}">
                                    {{ $contact->vendor_code }} - {{ $contact->vendor_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3 md:text-right">
                        <button
                            class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium"
                            type="submit">
                            Print Internal
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- FORM: CUSTOMER VENDOR --}}
        <form method="GET" action="/foremind-detail/printCustomer" target="_blank"
            class="bg-white rounded-xl border border-slate-200 shadow-sm">
            @csrf
            <div class="p-6">
                <div class="grid md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="vendor_code_customer">
                            Vendor (Customer)
                        </label>
                        <p class="text-xs text-slate-500">
                            Pilih vendor untuk form ke customer.
                        </p>
                    </div>
                    <div class="md:col-span-5">
                        <select
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            id="vendor_code_customer" name="vendor_code" required>
                            <option value="" selected disabled>Select Vendor Name</option>
                            @foreach ($contacts as $contact)
                                <option value="{{ $contact->vendor_code }}">
                                    {{ $contact->vendor_code }} - {{ $contact->vendor_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3 md:text-right">
                        <button
                            class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium border border-blue-600"
                            type="submit">
                            Print Customer
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="p-6">
                <div class="overflow-x-auto max-h-[70vh]">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr class="border-b border-slate-200">
                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Material
                                    Code</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Material
                                    Name</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Item No
                                </th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Vendor
                                    Code</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">UoM</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Qty
                                    Material</th>

                                @foreach ($mon as $month)
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                        {{ \Carbon\Carbon::parse($month)->format('Y-m') }}</th>
                                @endforeach

                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @php
                                $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
                                $currentMaterialCode = null;
                            @endphp

                            @foreach ($materials as $key => $material)
                                <tr class="hover:bg-slate-50">
                                    @if ($material->material_code != $currentMaterialCode)
                                        {{-- First row for material code --}}
                                        <td class="px-4 py-3 text-sm text-slate-900">{{ $material->material_code }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-900">{{ $material->material_name }}</td>
                                        @php $currentMaterialCode = $material->material_code; @endphp
                                    @else
                                        {{-- Subsequent rows: empty cells for code & name --}}
                                        <td class="px-4 py-3"></td>
                                        <td class="px-4 py-3"></td>
                                    @endif

                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $material->item_no }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $material->vendor_code }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $material->unit_of_measure }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $material->quantity_material }}</td>

                                    @php $total = 0; @endphp

                                    @foreach ($qforecast[$loop->index] as $index => $value)
                                        @php
                                            $calculation = $value * $material->quantity_material;
                                            $total += $calculation;
                                            $monthlyTotals[$index] += $calculation;
                                        @endphp

                                        <td class="px-4 py-3 text-sm">
                                            <div class="text-slate-600">{{ $value }}</div>
                                            <div class="font-semibold text-slate-900">{{ $calculation }}</div>
                                        </td>
                                    @endforeach

                                    <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $total }}</td>
                                </tr>

                                {{-- Ketika material_code berganti, tampilkan subtotal + separator --}}
                                @if (!$loop->last && $material->material_code != $materials[$loop->index + 1]->material_code)
                                    <tr class="bg-slate-50 font-semibold border-t-2 border-slate-300">
                                        <td colspan="5" class="px-4 py-3"></td>
                                        <td class="px-4 py-3 text-sm text-slate-700">Monthly Total</td>
                                        @foreach ($monthlyTotals as $monthlyTotal)
                                            <td class="px-4 py-3 text-sm text-slate-900 font-semibold">{{ $monthlyTotal }}
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 text-sm text-slate-900 font-semibold">
                                            {{ array_sum($monthlyTotals) }}</td>
                                    </tr>

                                    @php
                                        $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
                                    @endphp

                                    <tr>
                                        <td colspan="{{ 6 + count($qforecast[0]) + 1 }}"
                                            class="border-t-2 border-slate-300"></td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6 flex justify-end">
            {{ $materials->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#vendor_code_internal", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });

            new TomSelect("#vendor_code_customer", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    </script>
@endpush
