@section('title', 'Laporan Harian Karyawan')
<div class="mx-auto max-w-6xl px-3 py-4 sm:px-4 lg:px-0" x-data="{ showFilters: true }">
    {{-- HEADER --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-slate-800 sm:text-xl">
                Laporan Harian Karyawan
            </h1>
            <p class="text-xs text-slate-500">
                Rekap laporan harian karyawan di departemen Anda.
            </p>
        </div>

        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700">
            <span
                class="mr-1 inline-flex h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-emerald-100"></span>
            Departemen Anda
        </span>
    </div>

    {{-- FILTER CARD --}}
    <div class="mb-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        {{-- Filter header (mobile bisa collapse) --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5">
            <div>
                <p class="text-sm font-semibold text-slate-800">Filter Laporan</p>
                <p class="text-[11px] text-slate-500">
                    Gunakan filter di bawah untuk mempersempit daftar karyawan.
                </p>
            </div>

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

        {{-- Filter body --}}
        <div
            class="px-4 pb-4 pt-3 sm:px-5 sm:pt-4"
            x-show="showFilters"
            x-transition
            x-cloak
        >
            <div class="grid gap-3 md:grid-cols-12 md:items-end">
                {{-- Search --}}
                <div class="md:col-span-4">
                    <label class="mb-1 block text-xs font-medium text-slate-700">
                        Cari (NIK / Nama)
                    </label>
                    <input
                        type="text"
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        placeholder="Ketik untuk mencari…"
                        wire:model.live.debounce.400ms="search"
                    >
                </div>

                {{-- Karyawan --}}
                <div class="md:col-span-3">
                    <label class="mb-1 block text-xs font-medium text-slate-700">
                        Karyawan
                    </label>
                    <select
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        wire:model.live="employeeId"
                    >
                        <option value="">— Semua —</option>
                        @foreach ($this->employeesDropdown as $emp)
                            <option value="{{ $emp['employee_id'] }}">
                                {{ $emp['employee_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Dept No (optional) --}}
                @if ($canPickDept)
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            Dept No
                        </label>
                        <select
                            class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            wire:model.live="departmentNo"
                        >
                            <option value="">— Semua —</option>
                            @foreach ($this->departmentNos as $d)
                                <option value="{{ $d['dept_no'] }}">
                                    {{ $d['dept_no'] }} — {{ $d['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Jabatan --}}
                <div class="md:col-span-3">
                    <label class="mb-1 block text-xs font-medium text-slate-700">
                        Jabatan
                    </label>
                    <select
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        wire:model.live="jabatan"
                    >
                        <option value="">— Semua —</option>
                        @foreach ($this->positions as $pos)
                            <option value="{{ $pos }}">{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dari Tanggal --}}
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-700">
                        Dari Tanggal
                    </label>
                    <input
                        type="date"
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        wire:model.live="dateFrom"
                    >
                </div>

                {{-- Sampai --}}
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-700">
                        Sampai
                    </label>
                    <input
                        type="date"
                        class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        wire:model.live="dateTo"
                    >
                </div>

                {{-- Reset button --}}
                <div class="md:col-span-2 flex items-end justify-end">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm hover:bg-slate-50"
                        wire:click="resetFilters"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-3.5 w-3.5"
                             viewBox="0 0 20 20"
                             fill="currentColor">
                            <path
                                d="M3 3a1 1 0 011-1h4a1 1 0 010 2H5v2.09A7.002 7.002 0 0117 11a1 1 0 11-2 0 5 5 0 10-8.9-2H8a1 1 0 110 2H4a1 1 0 01-1-1V3z"/>
                            <path
                                d="M17 17a1 1 0 01-1 1h-4a1 1 0 110-2h3v-2.09A7.002 7.002 0 013 9a1 1 0 112 0 5 5 0 108.9 2H12a1 1 0 110-2h4a1 1 0 011 1v7z"/>
                        </svg>
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Footer loading --}}
        <div class="border-t border-slate-100 bg-slate-50 px-4 py-2 text-xs text-slate-500 sm:px-5">
            <span wire:loading.flex class="inline-flex items-center gap-1">
                <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                Memuat data…
            </span>
            <span wire:loading.remove>
                Filter siap digunakan.
            </span>
        </div>
    </div>

    {{-- LIST / TABLE --}}
    @if ($employees->isEmpty())
        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800 shadow-sm">
            <div class="flex items-start gap-2">
                <span class="mt-0.5 text-lg">⚠️</span>
                <div>
                    <p class="font-semibold">Tidak ada laporan ditemukan</p>
                    <p class="text-xs text-amber-700">
                        Coba ubah kombinasi filter atau rentang tanggal yang digunakan.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="sticky top-0 z-10 whitespace-nowrap px-3 py-2 text-left font-semibold">NIK</th>
                            <th class="sticky top-0 z-10 whitespace-nowrap px-3 py-2 text-left font-semibold">Dept No</th>
                            <th class="sticky top-0 z-10 px-3 py-2 text-left font-semibold">Nama Karyawan</th>
                            <th class="sticky top-0 z-10 whitespace-nowrap px-3 py-2 text-left font-semibold">Jabatan</th>
                            <th class="sticky top-0 z-10 whitespace-nowrap px-3 py-2 text-left font-semibold">Terakhir Update</th>
                            <th class="sticky top-0 z-10 whitespace-nowrap px-3 py-2 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($employees as $employee)
                            @php
                                $latest = $employee->latest_dt ? \Carbon\Carbon::parse($employee->latest_dt) : null;
                            @endphp
                            <tr class="hover:bg-slate-50/60">
                                <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-800">
                                    {{ $employee->employee_id }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-700">
                                    {{ $employee->departement_id }}
                                </td>
                                <td class="px-3 py-2 text-sm font-semibold text-slate-800">
                                    {{ $employee->employee_name }}
                                </td>
                                <td class="px-3 py-2">
                                    @if (!empty($employee->jabatan))
                                        <span
                                            class="inline-flex items-center rounded-full border border-sky-100 bg-sky-50 px-2.5 py-0.5 text-[11px] font-medium text-sky-700">
                                            {{ $employee->jabatan }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-slate-700">
                                    @if ($latest)
                                        <div class="flex flex-col">
                                            <span class="text-xs font-medium text-slate-800">
                                                {{ $latest->translatedFormat('d M Y') }} • {{ $latest->format('H:i') }}
                                            </span>
                                            <span class="text-[11px] text-slate-500">
                                                {{ $latest->diffForHumans() }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <a
                                        href="{{ route('daily-reports.depthead.show', $employee->employee_id) }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700"
                                    >
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-slate-100 bg-slate-50 px-4 py-2">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span>
                        Menampilkan
                        <span class="font-semibold text-slate-700">{{ $employees->firstItem() }}</span>
                        –
                        <span class="font-semibold text-slate-700">{{ $employees->lastItem() }}</span>
                        dari
                        <span class="font-semibold text-slate-700">{{ $employees->total() }}</span>
                        karyawan
                    </span>

                    <div class="text-right">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
