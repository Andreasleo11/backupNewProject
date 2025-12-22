@extends('new.layouts.app')

@section('content')
    <div class="container py-3" x-data="{ openModal: false }">
        {{-- Header --}}
        <section class="mb-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h1 class="h4 mb-1">Forecast Customer Master Data</h1>
                    <p class="text-muted mb-0 small">
                        Kelola daftar master data customer yang digunakan pada perhitungan forecast dan PPS.
                    </p>
                </div>

                <div class="text-md-end">
                    <button type="button" @click="openModal = true"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        + Add Forecast Customer
                    </button>
                </div>
            </div>
        </section>

        {{-- Content --}}
        <section class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Customer List</span>
                    <span class="small text-muted">Data terhubung dengan sistem SAP / modul forecast</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-sm table-hover align-middle mb-0']) }}
                    </div>
                </div>
            </div>

            {{-- Modal Add New (Alpine, no Bootstrap JS) --}}
            <div x-cloak x-show="openModal" x-transition.opacity
                class="fixed inset-0 z-40 flex items-center justify-center">
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-slate-900/40" @click="openModal = false"></div>

                {{-- Modal panel --}}
                <div class="relative z-50 w-full max-w-lg bg-white rounded-xl shadow-lg border border-slate-200"
                    @click.stop>
                    <div class="border-b border-slate-100 px-4 py-3 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-800">
                            Add Forecast Master Data
                        </h2>
                        <button type="button" @click="openModal = false"
                            class="inline-flex items-center justify-center rounded-full p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            <span class="sr-only">Close</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('addnewforecastmaster') }}" class="px-4 py-4 space-y-4">
                        @csrf

                        {{-- Forecast Code --}}
                        <div class="space-y-1">
                            <label for="forecast_code" class="block text-xs font-medium text-slate-700 mb-1">
                                Forecast Code
                            </label>
                            <input type="text" name="forecast_code" id="forecast_code"
                                class="block py-2.5 px-3 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Forecast Name --}}
                        <div class="space-y-1">
                            <label for="forecast_name" class="block text-xs font-medium text-slate-700 mb-1">
                                Forecast Name
                            </label>
                            <input type="text" name="forecast_name" id="forecast_name"
                                class="block py-2.5 px-3 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Customer --}}
                        <div class="space-y-1">
                            <label for="customer" class="block text-xs font-medium text-slate-700 mb-1">
                                Customer
                            </label>
                            <input type="text" name="customer" id="customer"
                                class="block py-2.5 px-3 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="mt-3 border-t border-slate-100 pt-3 flex items-center justify-end gap-2">
                            <button type="button" @click="openModal = false"
                                class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                                Close
                            </button>
                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                Add
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
