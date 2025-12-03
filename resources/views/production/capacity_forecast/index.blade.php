@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-6">
        <div class="max-w-6xl mx-auto px-4">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
                        Capacity By Forecast Periode
                        <span class="text-indigo-600">
                            {{ $time->start_date }}
                        </span>
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Rekap kapasitas berdasarkan periode forecast yang aktif.
                    </p>
                </div>

                <a href="{{ route('viewstep1') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2
                          text-sm font-semibold text-white shadow-sm hover:bg-indigo-700
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Mulai Proses
                </a>
            </div>

            {{-- Tabel --}}
            <section class="mt-4">
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-3">
                        <div class="overflow-x-auto">
                            {{ $dataTable->table() }}
                            {{-- kalau mau, bisa kasih class sendiri:
                             {{ $dataTable->table(['class' => 'min-w-full text-sm']) }} --}}
                        </div>
                    </div>
                </div>
            </section>

            {{-- Actions bawah --}}
            <section class="mt-4">
                <div class="flex flex-wrap justify-end gap-2">
                    <a href="{{ route('capacityforecastdetail') }}"
                       class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5
                              text-xs md:text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50
                              focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                        Detail
                    </a>

                    <a href="{{ route('capacityforecastdistribution') }}"
                       class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5
                              text-xs md:text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50
                              focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                        Distribution
                    </a>

                    <a href="{{ route('capacityforecastline') }}"
                       class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5
                              text-xs md:text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50
                              focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                        Line
                    </a>
                </div>
            </section>
        </div>
    </div>

    {{-- Tetap perlu untuk Yajra DataTables --}}
    {{ $dataTable->scripts() }}
@endsection
