@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-6">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-100">
                    <h1 class="text-lg font-semibold text-slate-900">
                        Karawang — PPS Parameter
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Atur periode dan parameter perhitungan PPS untuk plant Karawang.
                    </p>
                </div>

                {{-- Form --}}
                <form action="{{ route('processKarawangForm') }}" method="POST" class="px-6 py-5 space-y-5">
                    @csrf

                    {{-- Periode --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">
                                Tanggal Awal
                            </label>
                            <input type="date" id="start_date" name="start_date"
                                value="{{ old('start_date', $datedata[17]->start_date) }}" required
                                class="block w-full rounded-lg border-slate-300 text-sm px-3 py-2
                                          shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">
                                Tanggal Akhir
                            </label>
                            <input type="date" id="end_date" name="end_date"
                                value="{{ old('end_date', $datedata[17]->end_date) }}" required
                                class="block w-full rounded-lg border-slate-300 text-sm px-3 py-2
                                          shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Jarak H- & Gudang --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="hm_fg" class="block text-sm font-medium text-slate-700 mb-1">
                                Jarak H-Min FG (hari)
                            </label>
                            <input type="number" id="hm_fg" name="hm_fg"
                                value="{{ old('hm_fg', $data[0]->val_int_kri) }}" required
                                class="block w-full rounded-lg border-slate-300 text-sm px-3 py-2
                                          shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="hm_wip" class="block text-sm font-medium text-slate-700 mb-1">
                                Jarak H-Min WIP (hari)
                            </label>
                            <input type="number" id="hm_wip" name="hm_wip"
                                value="{{ old('hm_wip', $data[1]->val_int_kri) }}" required
                                class="block w-full rounded-lg border-slate-300 text-sm px-3 py-2
                                          shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="jarak_gudang" class="block text-sm font-medium text-slate-700 mb-1">
                                Jarak Simpan Gudang (hari)
                            </label>
                            <input type="number" id="jarak_gudang" name="jarak_gudang"
                                value="{{ old('jarak_gudang', $data[2]->val_int_kri) }}" required
                                class="block w-full rounded-lg border-slate-300 text-sm px-3 py-2
                                          shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Manpower & mould change --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="max_manpower" class="block text-sm font-medium text-slate-700 mb-1">
                                Batas Maksimal Man Power / Hari
                            </label>
                            <input type="number" id="max_manpower" name="max_manpower"
                                value="{{ old('max_manpower', $data[3]->val_int_kri) }}" required
                                class="block w-full rounded-lg border-slate-300 text-sm px-3 py-2
                                          shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="max_mould_change" class="block text-sm font-medium text-slate-700 mb-1">
                                Maks. Pergantian Mould / Mesin / Hari
                            </label>
                            <input type="number" id="max_mould_change" name="max_mould_change"
                                value="{{ old('max_mould_change', $data[4]->val_int_kri) }}" required
                                class="block w-full rounded-lg border-slate-300 text-sm px-3 py-2
                                          shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Forecast & WIP --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="forecast" class="block text-sm font-medium text-slate-700 mb-1">
                                Termasuk Forecast
                            </label>
                            @php $valInt = $data[5]->val_int_kri; @endphp
                            <select id="forecast" name="forecast" required
                                class="block w-full rounded-lg border-slate-300 text-sm px-3 py-2
                                           shadow-sm bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="1" {{ old('forecast', $valInt) == 1 ? 'selected' : '' }}>Ya</option>
                                <option value="0" {{ old('forecast', $valInt) == 0 ? 'selected' : '' }}>Tidak</option>
                            </select>
                        </div>

                        <div>
                            <label for="count_wip" class="block text-sm font-medium text-slate-700 mb-1">
                                Hitung WIP
                            </label>
                            @php $valIntWip = $data[6]->val_int_kri; @endphp
                            <select id="count_wip" name="count_wip" required
                                class="block w-full rounded-lg border-slate-300 text-sm px-3 py-2
                                           shadow-sm bg-white focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="1" {{ old('count_wip', $valIntWip) == 1 ? 'selected' : '' }}>Ya
                                </option>
                                <option value="0" {{ old('count_wip', $valIntWip) == 0 ? 'selected' : '' }}>Tidak
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2
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
