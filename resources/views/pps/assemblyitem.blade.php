@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-8">
        <div class="max-w-6xl mx-auto px-4">
            {{-- Card utama --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                {{-- Header --}}
                <div
                    class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900">
                            Item Menu — Assembly
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Daftar item yang akan digunakan di proses Assembly.
                        </p>
                    </div>

                    <a href="{{ route('assemblyprocess5') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm
                              hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Lanjut
                    </a>
                </div>

                {{-- Tabel --}}
                <div class="px-4 pb-5 pt-4">
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="overflow-x-auto">
                            {{ $dataTable->table([
                                'class' => 'min-w-full text-sm text-slate-700',
                            ]) }}
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
