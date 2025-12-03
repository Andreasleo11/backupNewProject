@extends('new.layouts.app')

@section('content')
    <div class="min-h-[50vh] bg-slate-50/60 py-6">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="p-5 md:p-6">
                    {{-- Stepper --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-4">
                            {{-- Step 1 - done --}}
                            <div class="flex items-center gap-2 flex-1">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white text-sm font-semibold">
                                    1
                                </div>
                                <div class="flex-1 h-1.5 rounded-full bg-indigo-100">
                                    <div class="h-1.5 rounded-full bg-indigo-600 w-full"></div>
                                </div>
                            </div>

                            {{-- Step 2 - done --}}
                            <div class="flex items-center gap-2 flex-1">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white text-sm font-semibold">
                                    2
                                </div>
                                <div class="flex-1 h-1.5 rounded-full bg-indigo-100">
                                    <div class="h-1.5 rounded-full bg-indigo-600 w-full"></div>
                                </div>
                            </div>

                            {{-- Step 3 - current --}}
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-indigo-500 bg-indigo-50 text-indigo-700 text-sm font-semibold">
                                    3
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 text-xs font-medium text-slate-500">
                            Step 3 dari 3 — proses terakhir pembentukan PPS.
                        </p>
                    </div>

                    {{-- Title / description --}}
                    <div class="mb-4">
                        <h1 class="text-lg md:text-xl font-semibold text-slate-900">
                            Proses 3 — Finalisasi PPS
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Klik tombol di bawah untuk menjalankan Proses 3 dan menyelesaikan rangkaian PPS Wizard.
                        </p>
                    </div>

                    {{-- Footer Actions --}}
                    <div class="pt-4 mt-2 border-t border-slate-200 flex justify-end">
                        <a href="{{ route('step3logic') }}"
                           class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold
                                  text-white shadow-sm hover:bg-indigo-700 focus:outline-none
                                  focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Mulai Proses 3
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
