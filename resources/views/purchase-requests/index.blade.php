@extends('new.layouts.app')

@section('title', 'Purchase Requisition List')

@section('content')
    <div class="mx-auto max-w-6xl px-3 py-6 sm:px-4 lg:px-0">
        {{-- TOP BAR: TITLE + ACTION BUTTONS --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
                    Purchase Requisition List
                </h1>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Lihat, filter, dan ekspor daftar Purchase Requisition yang tercatat di sistem.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Export button --}}
                <a href="{{ route('purchaserequest.export.excel') }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm hover:bg-emerald-100">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         viewBox="0 0 20 20"
                         fill="currentColor">
                        <path d="M3 4a1 1 0 011-1h3v2H5v10h10V5h-2V3h3a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V4z"/>
                        <path d="M9 3h2v2h2v2h-2v2H9V7H7V5h2V3z"/>
                    </svg>
                    <span>Export Excel</span>
                </a>

                {{-- Create PR (non Director) --}}
                @if (Auth::user()->specification->name !== 'DIRECTOR')
                    <a href="{{ route('purchase-requests.create') }}"
                       class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             viewBox="0 0 20 20"
                             fill="currentColor">
                            <path
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 010-2h3V6a1 1 0 011-1z"/>
                        </svg>
                        <span>Create PR</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- INFO STRIP / LEGEND (optional) --}}
        <div class="mb-4 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-[11px] text-slate-600 shadow-sm sm:text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold text-slate-700">Tips:</span>
                <span>Gunakan <span class="font-semibold">Search Panes</span> di atas tabel untuk filter cepat by status, dept, dll.</span>
            </div>
        </div>

        {{-- DATATABLE WRAPPER --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                {{-- Yajra DataTable (tetap pakai struktur aslinya) --}}
                <div class="p-2 sm:p-3">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Search Panes CSS --}}
    <link rel="stylesheet"
          href="https://cdn.datatables.net/searchpanes/2.1.1/css/searchPanes.bootstrap5.min.css">

    {{-- Search Panes JS --}}
    <script type="module"
            src="https://cdn.datatables.net/searchpanes/2.3.3/js/dataTables.searchPanes.min.js"></script>
    <script type="module"
            src="https://cdn.datatables.net/searchpanes/2.3.3/js/searchPanes.bootstrap5.min.js"></script>
@endsection

@push('scripts')
    {{-- Chart.js (kalau nanti mau dipakai untuk summary chart) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Yajra datatable scripts --}}
    {{ $dataTable->scripts() }}
@endpush
