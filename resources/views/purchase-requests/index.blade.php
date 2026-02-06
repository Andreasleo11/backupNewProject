@extends('new.layouts.app')

@section('title', 'Purchase Requisition List')

@section('content')
    <div class="mx-auto max-w-7xl px-3 py-6 sm:px-4 lg:px-0 space-y-6">
        {{-- HEADER CARD --}}
        <div class="glass-card flex flex-wrap items-center justify-between gap-4 p-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800">
                    Purchase Requisition
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Manage and track all procurement requests in one place.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Export button --}}
                <a href="{{ route('purchase-requests.export-excel') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50/50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition-all hover:bg-emerald-100 hover:shadow-md hover:-translate-y-0.5">
                    <i class="bi bi-file-earmark-excel text-lg"></i>
                    <span>Export</span>
                </a>

                {{-- Create PR (Role Check) --}}
                @if (Auth::user()->specification?->name !== 'DIRECTOR' && !Auth::user()->hasRole('director'))
                    <a href="{{ route('purchase-requests.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition-all hover:shadow-indigo-300 hover:-translate-y-0.5">
                        <i class="bi bi-plus-lg text-lg"></i>
                        <span>New Request</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- STATS DASHBOARD --}}
        @include('partials.pr-stats-cards', ['stats' => $stats])

        {{-- DATATABLE CARD --}}
        <div class="glass-card overflow-hidden p-1">
            <div class="rounded-xl bg-white/50 p-4">
                {{-- Tips/Info --}}
                <div class="mb-4 flex items-center gap-2 rounded-lg bg-blue-50/50 px-3 py-2 text-xs text-blue-700 border border-blue-100">
                    <i class="bi bi-info-circle-fill"></i>
                    <span><strong>Pro Tip:</strong> Use the <strong>Search Panes</strong> button above the table to filter by multiple criteria instantly.</span>
                </div>

                <div class="premium-datatable-wrapper">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Search Panes CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.1.1/css/searchPanes.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    {{-- Search Panes JS --}}
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/dataTables.searchPanes.min.js"></script>
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/searchPanes.bootstrap5.min.js"></script>

    <style>
        /* Custom DataTable Styling Overrides */
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.5rem;
            border-color: #e2e8f0;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.5rem;
            border-color: #e2e8f0;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }
        table.dataTable.table-striped > tbody > tr.odd > * {
            box-shadow: none !important; /* Remove bootstrap striped shadow */
            background-color: rgba(248, 250, 252, 0.5); /* Very light slate */
        }
        table.dataTable.table-striped > tbody > tr:hover > * {
            background-color: rgba(241, 245, 249, 0.8) !important; /* Slate-100 on hover */
        }
        table.dataTable {
            border-collapse: separate;
            border-spacing: 0;
            border-bottom: 1px solid #f1f5f9;
        }
        table.dataTable thead th {
            border-bottom: 1px solid #e2e8f0 !important;
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }
        .page-item.active .page-link {
            background-color: #4f46e5 !important;
            border-color: #4f46e5 !important;
            border-radius: 0.5rem;
        }
        .page-link {
            border-radius: 0.5rem;
            margin: 0 2px;
            color: #64748b;
            border: none;
        }
        div.dt-button-collection {
            border-radius: 1rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0.5rem !important;
        }
    </style>
@endsection

@push('scripts')
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{ $dataTable->scripts() }}
@endpush
