@extends('new.layouts.app')

@section('content')
    @php
        $currentUser = auth()->user();
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ openDeleteId: null, openLockId: null }">
        {{-- Header + actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Verification Reports</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Daftar laporan QA/QC harian. Gunakan filter bulan dan ekspor ke Excel bila diperlukan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('qaqc.summarymonth') }}"
                   class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    Summary Per Month
                </a>

                <a href="{{ route('export.formadjusts') }}"
                   class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    Export Form Adjust
                </a>

                <a href="{{ route('export.reports') }}"
                   class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    Export All
                </a>

                @if ($currentUser->department->name === 'QC' && $currentUser->specification->name === 'INSPECTOR')
                    <a href="{{ route('qaqc.report.create') }}"
                       class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        <i class='bx bx-plus mr-1 text-base'></i>
                        <span>Add Report</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="breadcrumb">
            <ol class="flex items-center gap-1 text-sm text-gray-500">
                <li>
                    <a href="{{ route('qaqc') }}"
                       class="font-medium text-gray-600 hover:text-indigo-600">
                        Home
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 mx-1 text-gray-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li class="font-medium text-gray-900">
                    Reports
                </li>
            </ol>
        </nav>

        {{-- Main card --}}
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <div class="px-4 py-4 sm:px-6 sm:py-5">
                {{-- Filter bar --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                        <span class="font-medium">Filter by month</span>
                        <span class="hidden sm:inline text-gray-400">|</span>
                        <span class="text-xs sm:text-sm text-gray-400">
                            Format: <span class="font-mono">mm-yyyy</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 
                                             2v9a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 
                                             00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 
                                             00-1-1zM4 8h12v7H4V8z" />
                                </svg>
                            </div>
                            <input type="text"
                                   id="monthPicker"
                                   class="block w-40 sm:w-48 rounded-md border-gray-300 bg-slate-50 pl-9 pr-3 py-2 text-xs sm:text-sm shadow-sm
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Select month">
                        </div>
                        <button type="button"
                                id="clearMonth"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-300">
                            Reset
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    {{ $dataTable->table(['class' => 'table table-striped table-bordered w-full text-sm align-middle']) }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}

    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
            const $monthPicker = $('#monthPicker');
            const tableId = 'vqcreports-table';

            if ($monthPicker.length) {
                // Bootstrap Datepicker month picker
                $monthPicker.datepicker({
                    format: 'mm-yyyy',
                    startView: 'months',
                    minViewMode: 'months',
                    autoclose: true
                });

                // Trigger reload on change
                $monthPicker.on('change', function() {
                    if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                        window.LaravelDataTables[tableId].draw();
                    }
                });
            }

            // Clear month filter
            $('#clearMonth').on('click', function() {
                $monthPicker.val('');
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].draw();
                }
            });

            // DataTables custom month filter
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                // Pastikan hanya apply ke table ini
                if (settings.sTableId !== tableId) {
                    return true;
                }

                const selectedMonth = $monthPicker.val();
                if (!selectedMonth) return true; // jika kosong, skip filter

                // Sesuaikan index ini dengan kolom rec_date di DataTable-mu
                const recDate = data[3]; // example: "2025-11-29"
                if (!recDate) return true;

                const d = new Date(recDate);
                if (isNaN(d.getTime())) return true; // kalau parse gagal, jangan di-drop

                const month = ('0' + (d.getMonth() + 1)).slice(-2) + '-' + d.getFullYear();

                return month === selectedMonth;
            });
        });
    </script>
@endpush
