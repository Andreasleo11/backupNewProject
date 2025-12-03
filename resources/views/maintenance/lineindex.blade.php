@extends('new.layouts.app')

@section('content')
    <div x-data="{ openAddModal: false }" class="max-w-7xl mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">
                    Line Down
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Index untuk Line Down – pantau dan tambahkan data line yang sedang down.
                </p>
            </div>

            <button type="button" @click="openAddModal = true"
                class="inline-flex items-center gap-1 rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm
                       hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1">
                <span class="text-base leading-none">+</span>
                <span>Add Line Down</span>
            </button>
        </div>

        {{-- Tabel utama --}}
        <section class="space-y-4">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="p-3 md:p-4">
                    <div class="overflow-x-auto">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </section>

        {{-- === MODAL ADD NEW LINE DOWN (Tailwind + Alpine) === --}}
        <div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @keydown.escape.window="openAddModal = false">
            {{-- Overlay click-close --}}
            <div class="absolute inset-0" @click="openAddModal = false"></div>

            {{-- Modal card --}}
            <div
                class="relative z-10 w-full max-w-md mx-4 rounded-2xl bg-white shadow-xl border border-slate-200
                       transform transition-all">
                <form method="POST" action="{{ route('addlinedown') }}" class="flex flex-col">
                    @csrf

                    {{-- Header --}}
                    <div class="px-5 pt-5 pb-3 flex items-start justify-between gap-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">
                                Add Line Down
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Pilih line code dan isi tanggal line mulai down serta prediksi normal kembali.
                            </p>
                        </div>

                        <button type="button"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400
                                   hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2
                                   focus:ring-slate-500 focus:ring-offset-1"
                            @click="openAddModal = false">
                            <span class="sr-only">Close</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-5 pt-4 pb-2 space-y-4">
                        {{-- Line Code --}}
                        <div>
                            <label for="line_code" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Line Code
                            </label>
                            <select name="line_code" id="line_code"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                       text-slate-900 shadow-sm focus:border-slate-700 focus:ring-slate-700">
                                <option value="">Select Line Code</option>
                                @foreach ($data as $line_code)
                                    <option value="{{ $line_code }}">
                                        {{ $line_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date Down --}}
                        <div>
                            <label for="date_down" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Date Down
                            </label>
                            <input type="date" name="date_down" id="date_down"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                       text-slate-900 shadow-sm focus:border-slate-700 focus:ring-slate-700">
                        </div>

                        {{-- Date Prediction --}}
                        <div>
                            <label for="date_prediction" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Date Prediction
                            </label>
                            <input type="date" name="date_prediction" id="date_prediction"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                       text-slate-900 shadow-sm focus:border-slate-700 focus:ring-slate-700">
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 pb-5 pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button type="button"
                            class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5
                                   text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50
                                   focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1"
                            @click="openAddModal = false">
                            Cancel
                        </button>

                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-slate-700 px-3 py-1.5
                                   text-xs font-semibold text-white shadow-sm hover:bg-slate-800
                                   focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-1">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- === END MODAL === --}}
    </div>

    {{-- DataTables scripts tetap dipakai --}}
    {{ $dataTable->scripts() }}
@endsection
