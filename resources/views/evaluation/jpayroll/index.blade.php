@extends('new.layouts.app')

@section('title', 'Export JPayroll — Status Departemen')
@section('page-title', 'Export JPayroll')
@section('page-subtitle', \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'))

@section('content')

    {{-- Page header --}}
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div
                class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                <i class="bx bx-buildings text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Status Departemen — JPayroll</h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    Periode:
                    <span class="font-semibold text-indigo-600">
                        {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
                    </span>
                </p>
            </div>
        </div>
        <a href="{{ route('evaluation.jpayroll.select') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200 transition-colors">
            <i class="bx bx-arrow-back"></i> Ganti Periode
        </a>
    </div>

    <div class="space-y-5">

        {{-- Summary chips --}}
        @php $totalDepts = count($departmentStatus); @endphp
        <div class="grid grid-cols-3 gap-4">
            <div class="glass-card px-5 py-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Dept</p>
                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalDepts }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4">
                <p class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Ready</p>
                <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $readyCount }}</p>
            </div>
            <div class="rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4">
                <p class="text-xs font-medium text-rose-600 uppercase tracking-wide">Belum Ready</p>
                <p class="text-3xl font-bold text-rose-700 mt-1">{{ $totalDepts - $readyCount }}</p>
            </div>
        </div>

        {{-- Department status table --}}
        <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-700">Kesiapan per Departemen</h3>
                <span class="text-xs text-slate-400">{{ $totalDepts }} departemen</span>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse ($departmentStatus as $dept => $status)
                    <div class="flex items-center justify-between px-6 py-3.5 hover:bg-slate-50/70 transition-colors">
                        <span class="text-sm text-slate-700 font-medium">{{ $dept }}</span>
                        @if ($status === 'Ready')
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Ready
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Belum Ready
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <i class="bx bx-folder-open text-4xl text-slate-300"></i>
                        <p class="text-sm text-slate-400 mt-2">Tidak ada data departemen untuk periode ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Download section --}}
        <div class="glass-card px-6 py-5">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-700">Download File JPayroll</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        @if ($readyCount > 0)
                            {{ $readyCount }} departemen siap diproses. File Excel akan terunduh otomatis.
                        @else
                            Belum ada departemen yang Ready. Pastikan approver dept sudah menyetujui nilai.
                        @endif
                    </p>
                </div>

                <form action="{{ route('evaluation.jpayroll.download') }}" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" @if ($readyCount === 0) disabled @endif
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all
                           {{ $readyCount > 0
                               ? 'bg-indigo-600 hover:bg-indigo-700 hover:-translate-y-0.5 shadow-indigo-200'
                               : 'bg-slate-300 cursor-not-allowed opacity-60' }}">
                        <i class="bx bx-download text-base"></i>
                        Download Excel
                    </button>
                </form>
            </div>
        </div>

    </div>
@endsection
