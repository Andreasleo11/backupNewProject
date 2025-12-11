@extends('new.layouts.app')

@section('content')
    @php
        // Simple average calculation (better if moved to controller later)
        $totalPresentase = 0;
        foreach ($monthlyReport as $row) {
            $totalPresentase += $row['presentase'];
        }
        $averagePresentase = count($monthlyReport) > 0
            ? $totalPresentase / count($monthlyReport)
            : null;

        $selectedMonthName = \Carbon\Carbon::createFromDate(null, $month, 1)->translatedFormat('F');
    @endphp

    <div
        x-data="{ submitting: false }"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
    >
        {{-- Page header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5">
            <div>
                <h1 class="text-lg sm:text-xl font-semibold text-slate-900">
                    Monthly SPK Report
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    Monitoring SLA & handling performance for
                    <span class="font-semibold text-slate-700">
                        {{ $selectedMonthName }} {{ $year }}
                    </span>
                </p>
            </div>

            {{-- Quick summary card --}}
            <div class="flex items-center gap-3">
                @if (!empty($monthlyReport))
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-4 py-2.5 shadow-sm">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-emerald-700">
                            Average SLA Met
                        </p>
                        <p class="mt-0.5 text-lg font-semibold text-emerald-800">
                            {{ number_format($averagePresentase, 2) }}%
                        </p>
                        <p class="mt-0.5 text-[11px] text-emerald-700/80">
                            Based on {{ count($monthlyReport) }} tickets
                        </p>
                    </div>
                @else
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 shadow-sm">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">
                            No data
                        </p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            No SPK records for this period.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Filter panel --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 sm:p-5 mb-6">
            <form
                method="GET"
                action="{{ route('spk.monthlyreport') }}"
                class="space-y-4"
                @submit="submitting = true"
            >
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Month --}}
                    <div>
                        <label for="month" class="block text-xs font-medium text-slate-700 mb-1.5">
                            Select month
                        </label>
                        <select
                            name="month"
                            id="month"
                            class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Year --}}
                    <div>
                        <label for="year" class="block text-xs font-medium text-slate-700 mb-1.5">
                            Select year
                        </label>
                        <select
                            name="year"
                            id="year"
                            class="block w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            @foreach (range(date('Y') - 5, date('Y') + 5) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Quick actions --}}
                    <div class="flex flex-col justify-end gap-2">
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 w-full sm:w-auto"
                            >
                                <span x-show="!submitting">
                                    Apply filter
                                </span>
                                <span x-show="submitting" class="inline-flex items-center gap-1">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    Filtering...
                                </span>
                            </button>

                            <a
                                href="{{ route('spk.monthlyreport') }}"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 w-full sm:w-auto"
                            >
                                Reset
                            </a>
                        </div>

                        <p class="text-[11px] text-slate-500">
                            Filter will refresh the table below. Use browser print / export tools if you need a PDF.
                        </p>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse text-xs sm:text-sm">
                    <thead class="bg-slate-50/80 border-b border-slate-200">
                        <tr class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2 text-left whitespace-nowrap">No Dokumen</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Pelapor</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">From Dept</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Judul</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Keterangan Laporan</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">PIC</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Keterangan PIC</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Tanggal Lapor</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Tanggal Terima</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Tanggal Selesai</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Durasi</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Estimasi Kesepakatan</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">Menit Estimasi</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">Menit Durasi</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">Presentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($monthlyReport as $report)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-3 py-2 align-top font-medium text-slate-800">
                                    {{ $report['no_dokumen'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-slate-700">
                                    {{ $report['pelapor'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-slate-700 whitespace-nowrap">
                                    {{ $report['from_department'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-slate-800">
                                    {{ $report['judul'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-slate-600 max-w-xs">
                                    <div class="line-clamp-3">
                                        {{ $report['keterangan_laporan'] }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top text-slate-700 whitespace-nowrap">
                                    {{ $report['pic'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-slate-600 max-w-xs">
                                    <div class="line-clamp-3">
                                        {{ $report['tindakan'] }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top text-slate-700 whitespace-nowrap">
                                    {{ $report['tanggal_lapor'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-slate-700 whitespace-nowrap">
                                    {{ $report['tanggal_mulai'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-slate-700 whitespace-nowrap">
                                    {{ $report['tanggal_selesai'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-slate-700 whitespace-nowrap">
                                    {{ $report['durasi'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-slate-700 whitespace-nowrap">
                                    {{ $report['estimasi_kesepakatan'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-right text-slate-800">
                                    {{ $report['menit_estimasi'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-right text-slate-800">
                                    {{ $report['menit_durasi'] }}
                                </td>
                                <td class="px-3 py-2 align-top text-right">
                                    <span class="inline-flex items-center justify-end rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        {{ $report['presentase'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="px-3 py-6 text-center text-sm text-slate-500">
                                    No SPK data found for this period.
                                </td>
                            </tr>
                        @endforelse

                        @if (!empty($monthlyReport))
                            <tr class="bg-slate-50/80 border-t border-slate-200">
                                <td colspan="14" class="px-3 py-3 text-right text-xs font-semibold text-slate-700">
                                    Average Presentase:
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <span class="inline-flex items-center justify-end rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800">
                                        {{ number_format($averagePresentase, 2) }}%
                                    </span>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if (!empty($monthlyReport))
                <div class="px-4 py-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <p class="text-[11px] text-slate-500">
                        Showing <span class="font-medium text-slate-700">{{ count($monthlyReport) }}</span> records for
                        {{ $selectedMonthName }} {{ $year }}.
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Tip: Use <span class="font-mono">Ctrl + P</span> to print or save as PDF.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection
