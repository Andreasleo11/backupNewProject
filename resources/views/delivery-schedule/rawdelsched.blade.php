@extends('new.layouts.app')

@section('content')
    <div class="px-4 py-4 md:px-6 md:py-6">
        {{-- Header --}}
        <header class="flex flex-col gap-2 mb-4">
            <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-900">
                DELIVERY SCHEDULE (RAW)
            </h1>
            <p class="text-sm text-slate-500">
                Jadwal pengiriman untuk material RAW.
            </p>
        </header>

        {{-- Table --}}
        <section class="mt-4">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-3 md:p-4 overflow-x-auto">
                    {{ $dataTable->table() }}
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('indexds') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2
                          text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none
                          focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                    Back
                </a>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
