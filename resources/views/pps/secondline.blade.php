@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-8">
        <div class="max-w-6xl mx-auto px-4">
            {{-- Card wrapper --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                {{-- Header --}}
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900">
                            Line Menu — Second Process
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Daftar line produksi yang digunakan untuk proses Second. Silakan review sebelum finalisasi PPS.
                        </p>
                    </div>
                </div>

                {{-- Table --}}
                <div class="px-5 py-4">
                    <div class="overflow-x-auto rounded-xl border border-slate-100">
                        {{ $dataTable->table([
                            'class' => 'min-w-full text-sm text-slate-700'
                        ]) }}
                    </div>
                </div>

                {{-- Footer actions --}}
                <div class="px-5 pb-5 pt-2 flex justify-end">
                    <a href="{{ route('finalsecondpps') }}"
                       class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5
                              text-sm font-semibold text-white shadow-sm hover:bg-indigo-700
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Lanjut
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
