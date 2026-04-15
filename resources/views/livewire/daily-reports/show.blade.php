<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-8" x-data="{ showFilters: false }">

    {{-- Header Section (Premium Dashboard Style) --}}
    <div class="relative z-50 rounded-3xl bg-slate-900 shadow-2xl">
        {{-- Background glow --}}
        <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
            <div class="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-16 -mb-16 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
        </div>

        <div class="relative px-8 py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            {{-- Left: title & back button --}}
            <div class="flex items-start gap-6">
                <a href="{{ route('daily-reports.index') }}" wire:navigate
                    class="mt-1 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white shadow-sm ring-1 ring-white/20 transition-all hover:bg-white/20 hover:scale-105 shrink-0"
                    title="Kembali">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                            {{ $this->employee->name }}
                        </h1>
                        <span
                            class="inline-flex items-center rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-bold text-indigo-300 ring-1 ring-inset ring-indigo-500/30 backdrop-blur-md">
                            {{ $this->employee->nik }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium text-slate-400">
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-7h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>

                            {{ $this->employee->position ?? 'Position Not Set' }}
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $this->employee->department->name ?? $this->employee->dept_code }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right: Filter Toggle --}}
            <div class="flex items-center z-50">
                <button type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-slate-700 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors w-full sm:w-auto"
                    @click="showFilters = !showFilters">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Rentang Tanggal</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Advanced Filters Panel --}}
    <div x-show="showFilters" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4" x-cloak class="relative z-40 -mt-10 pt-10">
        <div class="rounded-b-3xl border border-t-0 border-slate-200 bg-white p-6 shadow-xl">
            <div class="grid gap-5 md:grid-cols-12 md:items-end">
                <div class="md:col-span-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal
                        Mulai</label>
                    <input type="date" wire:model.live="filter_start_date"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                </div>
                <div class="md:col-span-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal
                        Selesai</label>
                    <input type="date" wire:model.live="filter_end_date"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                </div>
                <div class="md:col-span-4 flex items-center justify-end gap-3">
                    <button type="button" wire:click="resetFilters"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50">Reset</button>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards row --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div
            class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl">
            <div
                class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-emerald-50 transition-all group-hover:scale-150">
            </div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Laporan Masuk</h3>
                    <p class="mt-3 text-3xl font-black text-slate-900 leading-none">{{ $submittedDates->count() }} <span
                            class="text-sm font-semibold text-slate-500">Hari</span></p>
                </div>
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 shadow-inner">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        <div
            class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl">
            <div
                class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-rose-50 transition-all group-hover:scale-150">
            </div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Belum Terisi</h3>
                    <p class="mt-3 text-3xl font-black text-rose-600 leading-none">{{ $missingDates->count() }} <span
                            class="text-sm font-semibold text-slate-500">Hari</span></p>
                </div>
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 shadow-inner">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        <div
            class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl">
            <div
                class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-sky-50 transition-all group-hover:scale-150">
            </div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Total Entri</h3>
                    <p class="mt-3 text-3xl font-black text-slate-900 leading-none">{{ $reports->count() }} <span
                            class="text-sm font-semibold text-slate-500">Task</span></p>
                </div>
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 shadow-inner">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Analysis Grid --}}
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 items-start">
        {{-- Left: Calendar --}}
        <div class="space-y-8 lg:col-span-4">
            <div class="rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40 p-6" wire:ignore>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600 mb-6 flex items-center gap-2">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Visualisasi Kehadiran
                </h2>
                <div id="calendar"></div>
            </div>
        </div>

        {{-- Right: Content --}}
        <div class="space-y-8 lg:col-span-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600">Riwayat Pekerjaan</h2>
                    <span
                        class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600">{{ $reports->count() }}
                        Entri</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr class="bg-slate-50/30">
                                <th
                                    class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500">
                                    Tanggal</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500">
                                    Jam / Sesi</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500">
                                    Aktivitas</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500">
                                    Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($reports as $report)
                                <tr class="transition-colors hover:bg-slate-50/50 group">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm font-bold text-slate-900">
                                            {{ \Carbon\Carbon::parse($report->work_date)->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="text-[10px] font-medium text-slate-400">
                                            {{ \Carbon\Carbon::parse($report->work_date)->diffForHumans() }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">
                                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $report->work_time }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium leading-relaxed text-slate-700 max-w-md">
                                            {{ $report->work_description }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($report->proof_url)
                                            <a href="{{ $report->proof_url }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-50 px-3 py-2 text-xs font-black text-indigo-700 transition-all hover:bg-indigo-100">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                <span>Lihat Bukti</span>
                                            </a>
                                        @else
                                            <span class="text-[10px] font-bold text-slate-300 uppercase italic">Tidak
                                                Ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                        Tidak ada data ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- FullCalendar Assets & Logic --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

    <style>
        .fc .fc-toolbar-title {
            font-size: 1rem !important;
            font-weight: 800 !important;
            color: #1e293b !important;
        }

        .fc .fc-button-primary {
            background-color: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
            color: #475569 !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            border-radius: 0.5rem !important;
            padding: 0.25rem 0.75rem !important;
            transition: all 0.2s !important;
        }

        .fc .fc-button-primary:hover {
            background-color: #e2e8f0 !important;
            color: #1e293b !important;
        }

        .fc .fc-button-active {
            background-color: #4f46e5 !important;
            color: white !important;
            border-color: #4f46e5 !important;
        }

        .fc-theme-standard th {
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 0.5rem 0 !important;
            font-size: 0.7rem !important;
            text-transform: uppercase !important;
            color: #94a3b8 !important;
            font-weight: 700 !important;
        }

        .fc-theme-standard td {
            border: 1px solid #f8fafc !important;
        }

        .fc-daygrid-day-number {
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: #475569 !important;
            padding: 4px 8px !important;
        }

        .fc-event {
            border-radius: 4px !important;
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            border: inset 1px rgba(0, 0, 0, 0.1) !important;
            padding: 1px 3px !important;
        }

        .fc-day-today {
            background-color: #f8fafc !important;
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', function() {
            initCalendar();
            Livewire.on('calendarUpdated', () => initCalendar());
        });

        function initCalendar() {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            calendarEl.innerHTML = '';
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 400,
                contentHeight: 'auto',
                aspectRatio: 1,
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                },
                events: @json($calendarEvents),
                eventDisplay: 'block'
            });
            calendar.render();
        }
    </script>
</div>
