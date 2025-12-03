@extends('new.layouts.app')

@section('content')
    <div class="px-4 py-4 md:px-6 md:py-6">
        {{-- Header --}}
        <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
            <div>
                <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-900">
                    DELIVERY SCHEDULE (WIP)
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Jadwal pengiriman untuk WIP (Work In Process).
                </p>
            </div>

            <div class="flex flex-wrap gap-2 justify-start md:justify-end">
                <a href="{{ route('delschedwip.step1') }}"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white
                          shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2
                          focus:ring-indigo-500 focus:ring-offset-1">
                    Update
                </a>
            </div>
        </header>

        {{-- Table --}}
        <section class="mt-4">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-3 md:p-4 overflow-x-auto">
                    {{ $dataTable->table() }}
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <a href="{{ route('indexds') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2
                          text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none
                          focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                    Delivery Schedule
                </a>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
