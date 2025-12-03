@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-8">
        <div class="max-w-3xl mx-auto px-4">
            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h1 class="text-lg font-semibold text-slate-900">
                        Pengaturan Parameter — Second Process
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Atur periode dan parameter produksi untuk skenario Second Process.
                    </p>
                </div>

                <form action="{{ route('processSecondForm') }}" method="POST" class="px-5 py-5 space-y-5">
                    @csrf

                    {{-- Tanggal --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="start_date"
                                   class="block text-sm font-medium text-slate-700 mb-1">
                                Tanggal Awal
                            </label>
                            <input type="date"
                                   name="start_date"
                                   id="start_date"
                                   value="{{ $datedata[15]->start_date }}"
                                   required
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                          text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label for="end_date"
                                   class="block text-sm font-medium text-slate-700 mb-1">
                                Tanggal Akhir
                            </label>
                            <input type="date"
                                   name="end_date"
                                   id="end_date"
                                   value="{{ $datedata[15]->end_date }}"
                                   required
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                          text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                    </div>

                    {{-- Jarak H-Min & gudang --}}
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="hm_fg"
                                   class="block text-sm font-medium text-slate-700 mb-1">
                                Jarak H-Min FG (hari)
                            </label>
                            <input type="number"
                                   name="hm_fg"
                                   id="hm_fg"
                                   value="{{ $data[0]->val_int_snd }}"
                                   required
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                          text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label for="hm_wip"
                                   class="block text-sm font-medium text-slate-700 mb-1">
                                Jarak H-Min WIP (hari)
                            </label>
                            <input type="number"
                                   name="hm_wip"
                                   id="hm_wip"
                                   value="{{ $data[1]->val_int_snd }}"
                                   required
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                          text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label for="jarak_gudang"
                                   class="block text-sm font-medium text-slate-700 mb-1">
                                Jarak Simpan Gudang (hari)
                            </label>
                            <input type="number"
                                   name="jarak_gudang"
                                   id="jarak_gudang"
                                   value="{{ $data[2]->val_int_snd }}"
                                   required
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                          text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                    </div>

                    {{-- Kapasitas --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="max_manpower"
                                   class="block text-sm font-medium text-slate-700 mb-1">
                                Batas Maksimal Man Power / Hari
                            </label>
                            <input type="number"
                                   name="max_manpower"
                                   id="max_manpower"
                                   value="{{ $data[3]->val_int_snd }}"
                                   required
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                          text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label for="max_mould_change"
                                   class="block text-sm font-medium text-slate-700 mb-1">
                                Maksimal Pergantian Mould / Mesin / Hari
                            </label>
                            <input type="number"
                                   name="max_mould_change"
                                   id="max_mould_change"
                                   value="{{ $data[4]->val_int_snd }}"
                                   required
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                          text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                    </div>

                    {{-- Forecast & WIP --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="forecast"
                                   class="block text-sm font-medium text-slate-700 mb-1">
                                Termasuk Forecast
                            </label>
                            <select name="forecast"
                                    id="forecast"
                                    required
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                           text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @php $valInt = $data[5]->val_int_snd; @endphp
                                <option value="1" {{ $valInt == 1 ? 'selected' : '' }}>Ya</option>
                                <option value="0" {{ $valInt == 0 ? 'selected' : '' }}>Tidak</option>
                            </select>
                        </div>

                        <div>
                            <label for="count_wip"
                                   class="block text-sm font-medium text-slate-700 mb-1">
                                Hitung WIP
                            </label>
                            <select name="count_wip"
                                    id="count_wip"
                                    required
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                           text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @php $valInt = $data[6]->val_int_snd; @endphp
                                <option value="1" {{ $valInt == 1 ? 'selected' : '' }}>Ya</option>
                                <option value="0" {{ $valInt == 0 ? 'selected' : '' }}>Tidak</option>
                            </select>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5
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
