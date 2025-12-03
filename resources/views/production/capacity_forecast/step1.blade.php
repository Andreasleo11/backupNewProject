@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] bg-slate-50/60 py-6">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="p-5 md:p-6">
                    {{-- Stepper --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-4">
                            {{-- Step 1 --}}
                            <div class="flex items-center gap-2 flex-1">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white text-sm font-semibold">
                                    1
                                </div>
                                <div class="flex-1 h-1.5 rounded-full bg-indigo-100">
                                    <div class="h-1.5 rounded-full bg-indigo-600 w-1/2"></div>
                                </div>
                            </div>

                            {{-- Step 2 --}}
                            <div class="flex items-center gap-2 flex-1">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-indigo-400 bg-white text-indigo-500 text-sm font-semibold">
                                    2
                                </div>
                                <div class="flex-1 h-1.5 rounded-full bg-slate-200"></div>
                            </div>

                            {{-- Step 3 --}}
                            <div>
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-indigo-200 bg-white text-indigo-300 text-sm font-semibold">
                                    3
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 text-xs font-medium text-slate-500">
                            Step 1 dari 3 — pilih tanggal awal proses.
                        </p>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-lg md:text-xl font-semibold text-slate-900 mb-3">
                        PILIH TANGGAL 1 DI BULAN YANG INGIN DIPILIH
                    </h1>
                    <p class="text-sm text-slate-500 mb-5">
                        Gunakan tanggal 1 di bulan yang ingin diproses sebagai dasar perhitungan kapasitas.
                    </p>

                    {{-- Form --}}
                    <form action="{{ route('step1') }}" method="GET" class="space-y-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">
                                Pilih Tanggal
                            </label>
                            <input type="date" id="start_date" name="start_date" required
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm
                                       shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="pt-4 mt-2 border-t border-slate-200 flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold
                                       text-white shadow-sm hover:bg-indigo-700 focus:outline-none
                                       focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                Mulai Proses 1
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
