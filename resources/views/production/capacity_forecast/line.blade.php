@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-6">
        <div class="max-w-6xl mx-auto px-4">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                        Capacity By Forecast
                        <span class="text-indigo-600">(Line Section)</span>
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Kapasitas berdasarkan forecast per line / mesin.
                    </p>
                </div>

                <a href="{{ route('capacityforecastindex') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white
                          px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50
                          focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                    ← Back
                </a>
            </div>

            {{-- Tabel --}}
            <section class="mt-3">
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-3">
                        <div class="overflow-x-auto">
                            {{-- Kalau mau override class tabel DataTables bisa pakai opsi array --}}
                            {{-- {{ $dataTable->table(['class' => 'min-w-full text-sm']) }} --}}
                            {{ $dataTable->table() }}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Script DataTables --}}
    {{ $dataTable->scripts() }}
@endsection
