@extends('new.layouts.app')

@section('content')
    @php
        $start = request('start_date');
        $end = request('end_date');
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Header --}}
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-semibold text-slate-900 flex items-center gap-2">
                    <span>📊 Ringkasan Lembur Karyawan</span>
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    Lihat total jam lembur karyawan berdasarkan periode tanggal yang dipilih.
                </p>

                @if ($start && $end)
                    <p class="mt-1 text-[11px] sm:text-xs text-slate-400">
                        Periode:
                        <span class="font-medium text-slate-600">
                            {{ \Carbon\Carbon::parse($start)->translatedFormat('d M Y') }}
                            &mdash;
                            {{ \Carbon\Carbon::parse($end)->translatedFormat('d M Y') }}
                        </span>
                    </p>
                @endif
            </div>

            @if ($summary->count())
                <div class="flex items-center gap-2">
                    <a href="{{ route('overtime.summary.export', ['start_date' => $start, 'end_date' => $end]) }}"
                       class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                        📥 Export ke Excel
                    </a>
                </div>
            @endif
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
                {{-- Filter form --}}
                <form method="GET"
                      action="{{ route('overtime.summary') }}"
                      class="grid grid-cols-1 gap-3 sm:grid-cols-[1.2fr,1.2fr,auto] items-end">
                    <div>
                        <label for="start_date" class="block text-xs font-medium text-slate-700">
                            Tanggal Mulai
                        </label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ $start }}"
                            required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                        >
                    </div>

                    <div>
                        <label for="end_date" class="block text-xs font-medium text-slate-700">
                            Tanggal Selesai
                        </label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="{{ $end }}"
                            required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/60"
                        >
                    </div>

                    <div class="flex sm:justify-end">
                        <button
                            type="submit"
                            class="inline-flex w-full sm:w-auto items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        >
                            🔍 Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-4 py-4 sm:px-6 sm:py-5">
                @if ($summary->count())
                    @php
                        $totalJam = collect($summary)->sum('total_ot');
                        $karyawanCount = $summary->count();
                    @endphp

                    {{-- Small stats --}}
                    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3 text-xs sm:text-sm">
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] uppercase tracking-wide text-slate-400">Total karyawan</p>
                            <p class="mt-1 text-base font-semibold text-slate-800">
                                {{ $karyawanCount }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] uppercase tracking-wide text-slate-400">Total jam lembur</p>
                            <p class="mt-1 text-base font-semibold text-emerald-700">
                                {{ number_format($totalJam, 2) }} jam
                            </p>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] uppercase tracking-wide text-slate-400">Rata-rata / karyawan</p>
                            <p class="mt-1 text-base font-semibold text-slate-800">
                                {{ number_format($karyawanCount ? $totalJam / $karyawanCount : 0, 2) }} jam
                            </p>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto text-xs sm:text-sm border border-slate-100">
                            <thead>
                                <tr class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="px-3 py-2 border-b border-slate-100 text-left">NIK</th>
                                    <th class="px-3 py-2 border-b border-slate-100 text-left">Nama</th>
                                    <th class="px-3 py-2 border-b border-slate-100 text-left">Tanggal Awal</th>
                                    <th class="px-3 py-2 border-b border-slate-100 text-left">Tanggal Akhir</th>
                                    <th class="px-3 py-2 border-b border-slate-100 text-right">Total Jam Lembur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($summary as $row)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-3 py-2 text-slate-800">
                                            {{ $row['NIK'] }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-col">
                                                <span class="text-slate-800">{{ $row['nama'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ \Carbon\Carbon::parse($row['start_date'])->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ \Carbon\Carbon::parse($row['end_date'])->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <span class="font-semibold text-emerald-700">
                                                {{ number_format($row['total_ot'], 2) }} jam
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                        <div class="text-3xl">⚠️</div>
                        <p class="text-sm font-medium text-slate-700">
                            Tidak ada data lembur ditemukan
                        </p>
                        <p class="text-xs text-slate-500">
                            Pilih rentang tanggal di atas lalu klik <span class="font-semibold">Tampilkan</span> untuk melihat ringkasan lembur.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
