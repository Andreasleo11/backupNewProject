@extends('new.layouts.app')

@section('title', 'Detail Laporan Harian')

@section('content')
    <div
        class="mx-auto max-w-6xl px-3 py-5 sm:px-4 lg:px-0"
        x-data="{ showFilters: true }"
    >
        {{-- Back + header --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a
                href="{{ route('daily-reports.index') }}"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-4 w-4"
                     viewBox="0 0 20 20"
                     fill="currentColor">
                    <path fill-rule="evenodd"
                          d="M9.707 14.707a1 1 0 01-1.414 0L3.586 10l4.707-4.707a1 1 0 011.414 1.414L6.414 9H16a1 1 0 110 2H6.414l3.293 3.293a1 1 0 010 1.414z"
                          clip-rule="evenodd" />
                </svg>
                <span>Kembali ke Daftar Karyawan</span>
            </a>

            <div class="flex flex-wrap items-center gap-3">
                <div class="text-right">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Detail Laporan Harian
                    </p>
                    <p class="text-base font-semibold text-slate-800">
                        Karyawan {{ $employee_id }}
                    </p>
                </div>
                <span
                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-800 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                    <span class="mr-1 inline-flex h-2 w-2 rounded-full bg-emerald-400 ring-2 ring-emerald-200"></span>
                    NIK: {{ $employee_id }}
                </span>
            </div>
        </div>

        {{-- FILTER KARTU --}}
        <div class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            {{-- Header filter --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Filter Periode</p>
                    <p class="text-[11px] text-slate-500">
                        Pilih rentang tanggal untuk melihat laporan harian karyawan ini.
                    </p>
                </div>

                {{-- Toggle mobile --}}
                <button
                    type="button"
                    class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 sm:hidden"
                    @click="showFilters = !showFilters"
                >
                    <span x-show="showFilters">Sembunyikan</span>
                    <span x-show="!showFilters">Tampilkan</span>
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-3 w-3"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            {{-- Body filter --}}
            <div
                class="px-4 pb-4 pt-3 sm:px-5 sm:pt-4"
                x-show="showFilters"
                x-transition
                x-cloak
            >
                <form method="GET" class="grid gap-3 md:grid-cols-12 md:items-end">
                    {{-- Tanggal mulai --}}
                    <div class="md:col-span-4">
                        <label
                            for="filter_start_date"
                            class="mb-1 block text-xs font-medium text-slate-700"
                        >
                            Tanggal Mulai
                        </label>
                        <input
                            type="date"
                            id="filter_start_date"
                            name="filter_start_date"
                            value="{{ $filter_start_date }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>

                    {{-- Tanggal selesai --}}
                    <div class="md:col-span-4">
                        <label
                            for="filter_end_date"
                            class="mb-1 block text-xs font-medium text-slate-700"
                        >
                            Tanggal Selesai
                        </label>
                        <input
                            type="date"
                            id="filter_end_date"
                            name="filter_end_date"
                            value="{{ $filter_end_date }}"
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                    </div>

                    {{-- Tombol --}}
                    <div class="flex gap-2 md:col-span-4 md:justify-end">
                        <button
                            type="submit"
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 md:mt-0 md:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4"
                                 viewBox="0 0 20 20"
                                 fill="currentColor">
                                <path
                                    d="M3 3a1 1 0 011-1h12a1 1 0 01.8 1.6l-3.1 4.65A2 2 0 0013 9.6V14l-3.724-1.862A1 1 0 008 13V9.6a2 2 0 00-.7-1.5L4.2 3.6A1 1 0 013 3z"/>
                            </svg>
                            <span>Terapkan</span>
                        </button>

                        <a
                            href="{{ route('daily-reports.depthead.show', $employee_id) }}"
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 md:mt-0 md:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4"
                                 viewBox="0 0 20 20"
                                 fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293A1 1 0 014.293 14.293L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                      clip-rule="evenodd" />
                            </svg>
                            <span>Reset</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- KONTEN --}}
        @if ($reports->isEmpty())
            <div
                class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800 shadow-sm">
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 text-lg">⚠️</span>
                    <div>
                        <p class="font-semibold">Tidak ada laporan untuk karyawan ini.</p>
                        <p class="text-xs text-amber-700">
                            Coba ubah rentang tanggal di atas untuk memastikan tidak ada laporan yang terlewat.
                        </p>
                    </div>
                </div>
            </div>
        @else
            {{-- FullCalendar CSS --}}
            <link
                href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css"
                rel="stylesheet"
            >

            {{-- FullCalendar JS --}}
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

            {{-- Calendar --}}
            <div
                id="calendar"
                class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4"
            ></div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const calendarEl = document.getElementById('calendar');
                    if (!calendarEl) return;

                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        height: 500,
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: ''
                        },
                        events: @json($calendarEvents)
                    });

                    calendar.render();
                });
            </script>

            {{-- Statistik kecil --}}
            <div class="mb-4 grid gap-3 sm:grid-cols-3">
                <div
                    class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">
                        Total Hari dengan Laporan
                    </p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700">
                        {{ $submittedDates->count() }} <span class="text-sm font-semibold">hari</span>
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-rose-600">
                        Hari Tanpa Laporan
                    </p>
                    <p class="mt-1 text-2xl font-bold text-rose-700">
                        {{ $missingDates->count() }} <span class="text-sm font-semibold">hari</span>
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-sky-600">
                        Periode Data
                    </p>
                    <p class="mt-1 text-sm font-semibold text-sky-800">
                        {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}
                    </p>
                </div>
            </div>

            {{-- Tabel laporan --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="whitespace-nowrap px-3 py-2 text-left">Tanggal</th>
                                <th class="whitespace-nowrap px-3 py-2 text-center">Jam Kerja</th>
                                <th class="px-3 py-2 text-left">Deskripsi Pekerjaan</th>
                                <th class="whitespace-nowrap px-3 py-2 text-center">Bukti</th>
                                <th class="whitespace-nowrap px-3 py-2 text-center">Waktu Submit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($reports as $report)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-3 py-2 text-sm text-slate-800">
                                        {{ \Carbon\Carbon::parse($report->work_date)->format('d M Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 text-center text-sm font-medium text-slate-800">
                                        {{ $report->work_time }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-slate-700">
                                        {{ $report->work_description }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 text-center text-sm">
                                        @if ($report->proof_url)
                                            <a
                                                href="{{ $report->proof_url }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 hover:bg-sky-100"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="h-3.5 w-3.5"
                                                     viewBox="0 0 20 20"
                                                     fill="currentColor">
                                                    <path
                                                        d="M4 3a2 2 0 00-2 2v7a2 2 0 002 2h3l1.293 1.293a1 1 0 001.414 0L11 14h5a2 2 0 002-2V5a2 2 0 00-2-2H4z"/>
                                                </svg>
                                                <span>Lihat</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 text-center text-sm text-slate-700">
                                        {{ \Carbon\Carbon::parse($report->submitted_at)->format('d M Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
