@extends('new.layouts.app')

@section('title', 'Export JPayroll — Pilih Periode')
@section('page-title', 'Export JPayroll')
@section('page-subtitle', 'Pilih periode untuk mengunduh data nilai ke format JPayroll Yayasan')

@section('content')

    {{-- Page header card --}}
    <div class="flex items-center gap-4 mb-6">
        <div
            class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
            <i class="bx bx-export text-white text-2xl"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800">Export JPayroll — Yayasan</h1>
            <p class="text-sm text-slate-500 mt-0.5">Pilih periode untuk mengunduh data nilai ke format JPayroll</p>
        </div>
    </div>

    {{-- Card --}}
    <div class="max-w-lg bg-white rounded-2xl shadow-sm premium-shadow border border-slate-100 overflow-hidden">

        {{-- Card header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i class="bx bx-calendar"></i> Pilih Bulan & Tahun
            </h2>
            <p class="text-xs text-white/70 mt-1">
                Data yang sudah <strong class="text-white">dept_approved</strong> akan diproses dan dikategorisasi ke nilai
                A atau B.
            </p>
        </div>

        {{-- Form --}}
        <form action="{{ route('evaluation.jpayroll.index') }}" method="GET" class="px-6 py-6 space-y-5">

            <div class="grid grid-cols-2 gap-4">
                {{-- Month --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Bulan</label>
                    <select name="month"
                        class="form-select rounded-xl border-slate-200 text-sm w-full focus:ring-indigo-400 focus:border-indigo-400">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ now()->month === $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Year --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tahun</label>
                    <select name="year"
                        class="form-select rounded-xl border-slate-200 text-sm w-full focus:ring-indigo-400 focus:border-indigo-400">
                        @foreach (range(now()->year, now()->year - 4) as $y)
                            <option value="{{ $y }}" {{ now()->year === $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Info note --}}
            <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 flex gap-3 items-start">
                <i class="bx bx-info-circle text-amber-500 text-lg mt-0.5 shrink-0"></i>
                <p class="text-xs text-amber-700 m-0">
                    Hanya karyawan Yayasan dengan masa kerja <strong>≥ 6 bulan</strong> pada periode ini yang akan diproses.
                </p>
            </div>

            <div class="flex justify-end pt-1">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                    <i class="bx bx-search-alt"></i>
                    Lihat Status
                </button>
            </div>
        </form>
    </div>

@endsection
