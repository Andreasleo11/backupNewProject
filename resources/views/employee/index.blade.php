@extends('layouts.guest')

@section('content')
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="min-h-screen bg-gray-50 py-10">
        <div class="max-w-6xl mx-auto px-4">
            {{-- Top bar --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                        Employee Daily Reports
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Ringkasan laporan harian karyawan beserta bukti pekerjaan.
                    </p>
                </div>

                <form action="{{ route('employee.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold
                           bg-red-500 text-white shadow-md shadow-red-500/20 hover:bg-red-600
                           focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-gray-50
                           transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>

            {{-- Card --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                {{-- Header + Filters --}}
                <div class="px-4 md:px-6 py-4 border-b border-gray-100 space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-800">
                                Daftar Laporan
                            </h2>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Total: <span class="font-semibold text-gray-600">{{ $reports->total() }}</span> laporan
                            </p>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        {{-- Search --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Cari (nama / deskripsi)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                    </svg>
                                </span>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    class="block w-full rounded-full border border-gray-200 pl-9 pr-3 py-2 text-xs
                                       text-gray-700 placeholder:text-gray-400
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Masukkan nama karyawan atau kata kunci deskripsi...">
                            </div>
                        </div>

                        {{-- From date --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Dari tanggal kerja
                            </label>
                            <input type="date" name="from" value="{{ request('from') }}"
                                class="block w-full rounded-xl border border-gray-200 px-3 py-2 text-xs
                                   text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- To date + button --}}
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Sampai tanggal
                                </label>
                                <input type="date" name="to" value="{{ request('to') }}"
                                    class="block w-full rounded-xl border border-gray-200 px-3 py-2 text-xs
                                       text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="flex items-end pb-0.5">
                                <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-indigo-500 px-3 py-2 text-xs font-semibold
                                       text-white shadow-sm hover:bg-indigo-600 focus:outline-none focus:ring-2
                                       focus:ring-indigo-500 focus:ring-offset-2 transition">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                @if ($reports->count() > 0)
                    {{-- Desktop table (md and up) --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th
                                        class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                        #</th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                        Timestamp</th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                        Nama</th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                        Tanggal Kerja</th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                        Jam Kerja</th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                        Deskripsi</th>
                                    <th
                                        class="px-4 md:px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">
                                        Bukti</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($reports as $index => $report)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 md:px-6 py-3 align-top text-gray-400 text-xs">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-4 md:px-6 py-3 align-top">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-medium text-gray-800">
                                                    {{ \Carbon\Carbon::parse($report->submitted_at)->format('d/m/Y H:i') }}
                                                </span>
                                                <span class="text-[11px] text-gray-400">
                                                    {{ \Carbon\Carbon::parse($report->submitted_at)->diffForHumans() }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 md:px-6 py-3 align-top">
                                            <span class="text-sm font-semibold text-gray-900">
                                                {{ $report->employee_name }}
                                            </span>
                                        </td>
                                        <td class="px-4 md:px-6 py-3 align-top">
                                            <span
                                                class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 text-xs font-medium px-3 py-1">
                                                {{ \Carbon\Carbon::parse($report->work_date)->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="px-4 md:px-6 py-3 align-top">
                                            <span
                                                class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1">
                                                {{ $report->work_time }}
                                            </span>
                                        </td>
                                        <td class="px-4 md:px-6 py-3 align-top">
                                            <div class="max-w-xs md:max-w-sm">
                                                <p class="text-sm text-gray-700 truncate"
                                                    title="{{ $report->work_description }}">
                                                    {{ $report->work_description }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-4 md:px-6 py-3 align-top text-center">
                                            @if ($report->proof_url)
                                                <a href="{{ $report->proof_url }}" target="_blank"
                                                    class="inline-flex items-center justify-center gap-1 rounded-full border border-indigo-200
                                                       bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700
                                                       hover:bg-indigo-100 hover:border-indigo-300
                                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                                       transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.7"
                                                            d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5M13.5 6L18 10.5M13.5 6H18M9 12.75h3.75" />
                                                    </svg>
                                                    Lihat Bukti
                                                </a>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-[11px] font-medium text-gray-400">
                                                    Tidak ada bukti
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile card view (below md) --}}
                    <div class="block md:hidden divide-y divide-gray-100">
                        @foreach ($reports as $index => $report)
                            <div class="px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs text-gray-400">#{{ $index + 1 }}</p>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $report->employee_name }}
                                        </p>
                                        <p class="mt-0.5 text-[11px] text-gray-500">
                                            {{ \Carbon\Carbon::parse($report->submitted_at)->format('d/m/Y H:i') }}
                                            · {{ \Carbon\Carbon::parse($report->submitted_at)->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="text-right space-y-1">
                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-50 text-blue-700 text-[11px] font-medium px-2.5 py-1">
                                            {{ \Carbon\Carbon::parse($report->work_date)->format('d/m/Y') }}
                                        </span>
                                        <span class="block">
                                            <span
                                                class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 text-[11px] font-medium px-2.5 py-1">
                                                {{ $report->work_time }}
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <p class="text-xs font-medium text-gray-500 mb-1">Deskripsi</p>
                                    <p class="text-sm text-gray-700">
                                        {{ $report->work_description }}
                                    </p>
                                </div>

                                <div class="mt-3 flex justify-between items-center">
                                    <p class="text-[11px] text-gray-400">
                                        Bukti pekerjaan:
                                    </p>
                                    @if ($report->proof_url)
                                        <a href="{{ $report->proof_url }}" target="_blank"
                                            class="inline-flex items-center gap-1 rounded-full border border-indigo-200
                                               bg-indigo-50 px-3 py-1.5 text-[11px] font-semibold text-indigo-700
                                               hover:bg-indigo-100 hover:border-indigo-300 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                    d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5M13.5 6L18 10.5M13.5 6H18M9 12.75h3.75" />
                                            </svg>
                                            Lihat Bukti
                                        </a>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-[11px] font-medium text-gray-400">
                                            Tidak ada bukti
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Empty state --}}
                    <div class="px-6 py-10 flex flex-col items-center justify-center text-center">
                        <div class="mb-3 flex items-center justify-center">
                            <div class="h-14 w-14 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18A2.25 2.25 0 0020.25 16.5V6.75A2.25 2.25 0 0018 4.5H9l-4.5 4.5v7.5A2.25 2.25 0 006.75 18.75H9" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            Belum ada laporan harian.
                        </h3>
                        <p class="mt-1 text-xs text-gray-500 max-w-sm">
                            Laporan yang dikirim karyawan akan muncul di halaman ini.
                            Gunakan filter di atas untuk mempermudah pencarian saat data sudah banyak.
                        </p>
                    </div>
                @endif
                @if ($reports->hasPages())
                    <div class="px-4 md:px-6 py-4 border-t border-gray-100 bg-gray-50">
                        <div class="flex justify-between items-center">
                            <p class="text-xs text-gray-500">
                                Menampilkan
                                <span class="font-semibold">
                                    {{ $reports->firstItem() }}–{{ $reports->lastItem() }}
                                </span>
                                dari
                                <span class="font-semibold">
                                    {{ $reports->total() }}
                                </span>
                                laporan
                            </p>
                            <div class="text-xs">
                                {{ $reports->onEachSide(1)->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
