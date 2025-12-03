@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-6">
        <div class="max-w-6xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                {{-- Header --}}
                <div
                    class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900">
                            Item Menu — Injection
                        </h1>
                        <p class="mt-1 text-xs text-slate-500">
                            Pilih item yang akan digunakan dalam perhitungan PPS Injection.
                        </p>
                    </div>

                    <a href="{{ route('injectionprocess5') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2
                              text-sm font-semibold text-white shadow-sm hover:bg-indigo-700
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Lanjut
                    </a>
                </div>

                {{-- Table section --}}
                <div class="px-6 py-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/40">
                        <div class="overflow-x-auto">
                            {{-- Tetap pakai Yajra DataTable, hanya dibungkus Tailwind --}}
                            {{ $dataTable->table(['class' => 'min-w-full text-sm align-middle']) }}
                        </div>
                        <div class="px-4 py-2 border-t border-slate-100 text-[11px] text-slate-400">
                            Tips: gunakan search dan sorting untuk memfilter item sebelum lanjut.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
