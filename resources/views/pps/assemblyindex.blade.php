@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-8">
        <div class="max-w-xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-100">
                    <h1 class="text-lg font-semibold text-slate-900">
                        Pengaturan Parameter — Assembly
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Atur periode perhitungan dan parameter H-Min, WIP, gudang, serta batas kapasitas untuk PPS Assembly.
                    </p>
                </div>

                {{-- Form --}}
                <form action="{{ route('processAssemblyForm') }}" method="POST" class="px-6 py-5 space-y-4">
                    @csrf

                    {{-- Tanggal Awal --}}
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">
                            Tanggal Awal
                        </label>
                        <input
                            type="date"
                            name="start_date"
                            id="start_date"
                            value="{{ $datedata[14]->start_date }}"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm px-3 py-2 text-sm
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">
                            Tanggal Akhir
                        </label>
                        <input
                            type="date"
                            name="end_date"
                            id="end_date"
                            value="{{ $datedata[14]->end_date }}"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm px-3 py-2 text-sm
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    {{-- Jarak H-Min FG --}}
                    <div>
                        <label for="hm_fg" class="block text-sm font-medium text-slate-700 mb-1">
                            Jarak H-Min FG (hari)
                        </label>
                        <input
                            type="number"
                            name="hm_fg"
                            id="hm_fg"
                            value="{{ $data[0]->val_int_asm }}"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm px-3 py-2 text-sm
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    {{-- Jarak H-Min WIP --}}
                    <div>
                        <label for="hm_wip" class="block text-sm font-medium text-slate-700 mb-1">
                            Jarak H-Min WIP (hari)
                        </label>
                        <input
                            type="number"
                            name="hm_wip"
                            id="hm_wip"
                            value="{{ $data[1]->val_int_asm }}"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm px-3 py-2 text-sm
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    {{-- Jarak simpan Gudang --}}
                    <div>
                        <label for="jarak_gudang" class="block text-sm font-medium text-slate-700 mb-1">
                            Jarak simpan Gudang (hari)
                        </label>
                        <input
                            type="number"
                            name="jarak_gudang"
                            id="jarak_gudang"
                            value="{{ $data[2]->val_int_asm }}"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm px-3 py-2 text-sm
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    {{-- Batas Maksimal Man Power --}}
                    <div>
                        <label for="max_manpower" class="block text-sm font-medium text-slate-700 mb-1">
                            Batas Maksimal Man Power per Hari
                        </label>
                        <input
                            type="number"
                            name="max_manpower"
                            id="max_manpower"
                            value="{{ $data[3]->val_int_asm }}"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm px-3 py-2 text-sm
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    {{-- Batas Maksimal Pergantian Mould --}}
                    <div>
                        <label for="max_mould_change" class="block text-sm font-medium text-slate-700 mb-1">
                            Batas Maksimal Pergantian Mould per Mesin per Hari
                        </label>
                        <input
                            type="number"
                            name="max_mould_change"
                            id="max_mould_change"
                            value="{{ $data[4]->val_int_asm }}"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm px-3 py-2 text-sm
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    {{-- Termasuk forecast --}}
                    <div>
                        <label for="forecast" class="block text-sm font-medium text-slate-700 mb-1">
                            Termasuk forecast
                        </label>
                        @php
                            $valInt = $data[5]->val_int_asm;
                        @endphp
                        <select
                            name="forecast"
                            id="forecast"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm px-3 py-2 text-sm bg-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="1" {{ $valInt == 1 ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ $valInt == 0 ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    {{-- Hitung WIP --}}
                    <div>
                        <label for="count_wip" class="block text-sm font-medium text-slate-700 mb-1">
                            Hitung WIP
                        </label>
                        @php
                            $valInt = $data[6]->val_int_asm;
                        @endphp
                        <select
                            name="count_wip"
                            id="count_wip"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm px-3 py-2 text-sm bg-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="1" {{ $valInt == 1 ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ $valInt == 0 ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5
                                   text-sm font-semibold text-white shadow-sm hover:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
