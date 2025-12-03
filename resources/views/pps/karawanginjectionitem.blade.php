@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-6">
        <div class="max-w-6xl mx-auto px-4">
            {{-- Header --}}
            <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900">
                        Item Menu — Karawang Injection
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Daftar item injection yang akan digunakan untuk pembentukan PPS Karawang.
                    </p>
                </div>

                <a href="{{ route('karawangprocess5') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2
                          text-sm font-semibold text-white shadow-sm hover:bg-indigo-700
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Lanjut
                </a>
            </div>

            {{-- Card tabel --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-800">
                        Item list Karawang injection
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Pastikan master item sudah sesuai sebelum melanjutkan proses.
                    </p>
                </div>

                <div class="px-4 py-4">
                    <div class="overflow-x-auto">
                        {!! $dataTable->table([
                            'class' => 'min-w-full text-sm text-left border border-slate-200 rounded-lg',
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
