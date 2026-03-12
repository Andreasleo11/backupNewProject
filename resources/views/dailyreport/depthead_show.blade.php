@extends('new.layouts.app')

@section('title', 'Detail Laporan Harian')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-8" x-data="{ showFilters: false }">
    
    {{-- Header Section (Premium Dashboard Style) --}}
    <div class="relative z-50 rounded-3xl bg-slate-900 shadow-2xl">
        {{-- Background glow with overflow hidden to prevent spilling --}}
        <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
            <div class="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-16 -mb-16 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
        </div>

        <div class="relative px-8 py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            {{-- Left: title & back button --}}
            <div class="flex items-start gap-6">
                <a href="{{ route('daily-reports.index') }}" class="mt-1 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white shadow-sm ring-1 ring-white/20 transition-all hover:bg-white/20 hover:scale-105 shrink-0" title="Kembali">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl flex items-center gap-3">
                        Detail Analisis
                        <span class="inline-flex items-center rounded-full bg-emerald-400/10 px-3 py-1 text-sm font-semibold text-emerald-400 ring-1 ring-inset ring-emerald-400/20">
                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            NIK: {{ $employee_id }}
                        </span>
                    </h1>
                    <p class="mt-2 max-w-2xl text-[15px] font-medium text-slate-400">
                        Memantau performa kehadiran dan aktivitas khusus untuk Karyawan <strong class="text-slate-300">{{ $employee_id }}</strong>.
                    </p>
                </div>
            </div>

            {{-- Right: Filter Toggle --}}
            <div class="flex items-center z-50">
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-slate-700 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors w-full sm:w-auto"
                    @click="showFilters = !showFilters"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Rentang Tanggal</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Advanced Filters Panel (Date Range) --}}
    <div
        x-show="showFilters"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        x-cloak
        class="relative z-40 -mt-10 pt-10"
    >
        <div class="rounded-b-3xl border border-t-0 border-slate-200 bg-white p-6 shadow-xl">
            <form method="GET" class="grid gap-5 md:grid-cols-12 md:items-end">
                {{-- Tanggal Mulai --}}
                <div class="md:col-span-4">
                    <label for="filter_start_date" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Tanggal Mulai
                    </label>
                    <input
                        type="date"
                        id="filter_start_date"
                        name="filter_start_date"
                        value="{{ $filter_start_date }}"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white outline-none transition-colors"
                    >
                </div>

                {{-- Tanggal Selesai --}}
                <div class="md:col-span-4">
                    <label for="filter_end_date" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Tanggal Selesai
                    </label>
                    <input
                        type="date"
                        id="filter_end_date"
                        name="filter_end_date"
                        value="{{ $filter_end_date }}"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white outline-none transition-colors"
                    >
                </div>

                {{-- Buttons --}}
                <div class="md:col-span-4 flex items-center justify-end gap-3">
                    <a
                        href="{{ route('daily-reports.depthead.show', $employee_id) }}"
                        class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 transition-all hover:bg-slate-50"
                    >
                        Reset
                    </a>
                    <button
                        type="submit"
                        class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-indigo-600 transition-all hover:bg-indigo-700"
                    >
                        Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI Cards Row (Moved to top as per analysis) --}}
    <div class="relative z-0 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Days Logged --}}
        <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-emerald-50 transition-all group-hover:scale-150"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">Total Kehadiran</h3>
                    <p class="mt-3 text-3xl font-black text-slate-900 leading-none">
                        {{ $submittedDates->count() }} <span class="text-base font-semibold text-slate-500">Hari</span>
                    </p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 shadow-inner">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Days Missing --}}
        <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-rose-50 transition-all group-hover:scale-150"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">Hari Tanpa Laporan</h3>
                    <p class="mt-3 text-3xl font-black text-rose-600 leading-none">
                        {{ $missingDates->count() }} <span class="text-base font-semibold text-slate-500">Hari</span>
                    </p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 shadow-inner">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Period --}}
        <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-sky-50 transition-all group-hover:scale-150"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">Periode Data</h3>
                    <p class="mt-3 text-lg font-black text-slate-900 leading-tight">
                        {{ $startDate->format('d M') }} <span class="text-slate-400 px-1">&rarr;</span> {{ $endDate->format('d M Y') }}
                    </p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 shadow-inner">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @if ($reports->isEmpty())
        <div class="rounded-3xl border border-amber-200/60 bg-gradient-to-br from-amber-50 to-orange-50 px-6 py-8 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100/50 text-amber-500 mb-4">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wide">Tidak Ada Rekaman</h3>
            <p class="mt-1 text-sm text-amber-600/80">
                Karyawan ini tidak memiliki laporan di rentang tanggal yang dipilih.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Main Content: Table --}}
            <div class="lg:col-span-8 space-y-8">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-800">Daftar Laporan Harian</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-widest text-slate-400">
                                <tr>
                                    <th class="px-6 py-4 text-left">Tanggal</th>
                                    <th class="px-6 py-4 text-left">Deskripsi</th>
                                    <th class="px-6 py-4 text-center">Durasi</th>
                                    <th class="px-6 py-4 text-center">Status File</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($reports as $report)
                                    <tr class="transition-colors hover:bg-slate-50/80 group">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($report->work_date)->format('d M Y') }}</div>
                                            <div class="text-[11px] font-medium text-slate-400 mt-0.5">Disubmit: {{ \Carbon\Carbon::parse($report->submitted_at)->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm text-slate-700 leading-relaxed">{{ $report->work_description }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-center">
                                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                                {{ $report->work_time }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-center">
                                            @if ($report->proof_url)
                                                <a
                                                    href="{{ $report->proof_url }}"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-sky-600 shadow-sm ring-1 ring-inset ring-sky-200 transition-all hover:bg-sky-50 group-hover:shadow-md"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z" />
                                                        <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                                                    </svg>
                                                    Lampiran
                                                </a>
                                            @else
                                                <span class="text-[11px] font-medium text-slate-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Sidebar: Calendar --}}
            <div class="lg:col-span-4">
                <div class="sticky top-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/40">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-4">Visualisasi Kehadiran</h2>
                    
                    {{-- FullCalendar Assets (Kept as minimal impact change per instructions, styled elegantly) --}}
                    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
                    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

                    <style>
                        /* Minimal overrides to make FullCalendar look premium */
                        .fc .fc-toolbar-title { font-size: 1rem !important; font-weight: 800 !important; color: #1e293b !important; }
                        .fc .fc-button-primary { background-color: #f1f5f9 !important; border-color: #e2e8f0 !important; color: #475569 !important; font-size: 0.75rem !important; font-weight: 600 !important; text-transform: uppercase !important; border-radius: 0.5rem !important; padding: 0.25rem 0.75rem !important; transition: all 0.2s !important; }
                        .fc .fc-button-primary:hover { background-color: #e2e8f0 !important; color: #1e293b !important; }
                        .fc .fc-button-active { background-color: #4f46e5 !important; color: white !important; border-color: #4f46e5 !important; }
                        .fc-theme-standard th { border: none !important; border-bottom: 1px solid #f1f5f9 !important; padding: 0.5rem 0 !important; font-size: 0.7rem !important; text-transform: uppercase !important; color: #94a3b8 !important; font-weight: 700 !important;}
                        .fc-theme-standard td { border: 1px solid #f8fafc !important; }
                        .fc-daygrid-day-number { font-size: 0.8rem !important; font-weight: 600 !important; color: #475569 !important; padding: 4px 8px !important; }
                        .fc-event { border-radius: 4px !important; font-size: 0.65rem !important; font-weight: 700 !important; border: inset 1px rgba(0,0,0,0.1) !important; padding: 1px 3px !important; }
                        .fc-day-today { background-color: #f8fafc !important; }
                        .fc-scroller::-webkit-scrollbar { width: 4px; height: 4px; }
                        .fc-scroller::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
                    </style>

                    <div id="calendar" class="fc-custom-theme"></div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const calendarEl = document.getElementById('calendar');
                            if (!calendarEl) return;

                            const calendar = new FullCalendar.Calendar(calendarEl, {
                                initialView: 'dayGridMonth',
                                height: 400,
                                contentHeight: 'auto',
                                aspectRatio: 1.2,
                                headerToolbar: {
                                    left: 'prev,next',
                                    center: 'title',
                                    right: 'today'
                                },
                                events: @json($calendarEvents),
                                eventDisplay: 'block'
                            });

                            calendar.render();
                        });
                    </script>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
