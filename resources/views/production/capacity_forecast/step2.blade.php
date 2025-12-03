@extends('new.layouts.app')

@section('content')
    <div class="min-h-[50vh] bg-slate-50/60 py-6">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="p-5 md:p-6">
                    {{-- Stepper --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-4">
                            {{-- Step 1 - completed --}}
                            <div class="flex items-center gap-2 flex-1">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white text-sm font-semibold">
                                    1
                                </div>
                                <div class="flex-1 h-1.5 rounded-full bg-indigo-100">
                                    <div class="h-1.5 rounded-full bg-indigo-600 w-full"></div>
                                </div>
                            </div>

                            {{-- Step 2 - current --}}
                            <div class="flex items-center gap-2 flex-1">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-indigo-500 bg-indigo-50 text-indigo-700 text-sm font-semibold">
                                    2
                                </div>
                                <div class="flex-1 h-1.5 rounded-full bg-slate-200">
                                    <div class="h-1.5 rounded-full bg-indigo-400 w-1/4"></div>
                                </div>
                            </div>

                            {{-- Step 3 - upcoming --}}
                            <div>
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-300 text-sm font-semibold">
                                    3
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 text-xs font-medium text-slate-500">
                            Step 2 dari 3 — lanjutkan proses berikutnya.
                        </p>
                    </div>

                    {{-- Title / description (optional, biar nggak kosong banget) --}}
                    <div class="mb-4">
                        <h1 class="text-lg md:text-xl font-semibold text-slate-900">
                            Proses 2 — Lanjutkan pembentukan PPS
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Klik tombol di bawah untuk menjalankan Proses 2 sesuai konfigurasi yang sudah dipilih pada langkah sebelumnya.
                        </p>
                    </div>

                    {{-- Footer Actions --}}
                    <div class="pt-4 mt-2 border-t border-slate-200 flex justify-end">
                        <a href="{{ route('step2logic') }}"
                           class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold
                                  text-white shadow-sm hover:bg-indigo-700 focus:outline-none
                                  focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Mulai Proses 2
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
