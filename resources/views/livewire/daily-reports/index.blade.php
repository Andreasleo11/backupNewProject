@section('title', 'Laporan Harian Karyawan')

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-8" x-data="{ showFilters: false }">
    {{-- Header Section (Premium Admin Dashboard Style) --}}
    <div class="relative z-50 rounded-3xl bg-slate-900 shadow-2xl">
        {{-- Background glow --}}
        <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
            <div class="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-16 -mb-16 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
        </div>

        <div class="relative px-8 py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            {{-- Left: title --}}
            <div class="flex items-center gap-6">
                 <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-500 text-white shadow-xl shadow-indigo-500/20 shrink-0">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                        Laporan Harian
                    </h1>
                    <p class="mt-2 max-w-2xl text-[15px] font-medium text-slate-400">
                        Pantau rekap aktivitas harian dari seluruh karyawan di departemen Anda.
                    </p>
                </div>
            </div>

            {{-- Right: Search & Toggle --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full lg:w-auto relative z-50">
                <div class="relative w-full sm:w-64 z-20">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        class="block w-full rounded-xl border-slate-700 bg-slate-800/80 py-2.5 pl-10 pr-4 text-sm font-semibold text-white placeholder-slate-400 shadow-lg focus:border-indigo-500 focus:ring-indigo-500 outline-none transition-colors"
                        placeholder="Cari NIK / Nama..."
                        wire:model.live.debounce.400ms="search"
                    >
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-slate-700 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 w-full sm:w-auto transition-colors z-10"
                    @click="showFilters = !showFilters"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                    </svg>
                    <span>Filter</span>
                </button>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-black text-white shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all w-full sm:w-auto z-10"
                    @click="$dispatch('openUpload')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Laporan Baru</span>
                </button>
            </div>
        </div>
    </div>

    @livewire('daily-reports.upload-overlay')

    {{-- Advanced Filters Panel --}}
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
            <div class="grid gap-5 md:grid-cols-12 md:items-end">
                {{-- Jabatan --}}
                <div class="md:col-span-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Jabatan
                    </label>
                    <select
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white outline-none transition-colors"
                        wire:model.live="jabatan"
                    >
                        <option value="">— Semua Jabatan —</option>
                        @foreach ($this->positions as $pos)
                            <option value="{{ $pos }}">{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dept No (optional) --}}
                @if ($canPickDept)
                    <div class="md:col-span-4">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Departemen
                        </label>
                        <select
                            class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white outline-none transition-colors"
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

                {{-- Dari Tanggal --}}
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Dari Tanggal
                    </label>
                    <input
                        type="date"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white outline-none transition-colors"
                        wire:model.live="dateFrom"
                    >
                </div>

                {{-- Sampai --}}
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Sampai
                    </label>
                    <input
                        type="date"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white outline-none transition-colors"
                        wire:model.live="dateTo"
                    >
                </div>

                {{-- Reset & Loading --}}
                <div class="md:col-span-12 flex items-center justify-between border-t border-slate-100 pt-4 mt-2">
                    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                        <span wire:loading.flex class="items-center gap-1.5 text-indigo-600">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            Memperbarui tabel...
                        </span>
                        <span wire:loading.remove>
                            Pilih kombinasi filter untuk mempersempit daftar.
                        </span>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-3 py-1.5 transition-colors"
                        wire:click="resetFilters"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M3 3a1 1 0 011-1h4a1 1 0 010 2H5v2.09A7.002 7.002 0 0117 11a1 1 0 11-2 0 5 5 0 10-8.9-2H8a1 1 0 110 2H4a1 1 0 01-1-1V3z"/>
                            <path d="M17 17a1 1 0 01-1 1h-4a1 1 0 110-2h3v-2.09A7.002 7.002 0 013 9a1 1 0 112 0 5 5 0 108.9 2H12a1 1 0 110-2h4a1 1 0 011 1v7z"/>
                        </svg>
                        <span>Reset Semua</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- LIST / TABLE --}}
    @if ($employees->isEmpty())
        <div class="mt-8 rounded-3xl border border-amber-200/60 bg-gradient-to-br from-amber-50 to-orange-50 px-6 py-8 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100/50 text-amber-500 mb-4">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wide">Data Tidak Ditemukan</h3>
            <p class="mt-1 text-sm text-amber-600/80">
                Tidak ada karyawan yang cocok dengan kriteria filter saat ini. Coba sesuaikan tanggal atau kata kunci.
            </p>
            <button type="button" class="mt-5 inline-flex items-center rounded-xl bg-white px-4 py-2 text-xs font-semibold text-amber-700 shadow-sm ring-1 ring-inset ring-amber-200 hover:bg-amber-50" wire:click="resetFilters">
                Bersihkan Filter
            </button>
        </div>
    @else
        <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-widest text-slate-400">
                        <tr>
                            <th class="px-6 py-4 text-left">NIK</th>
                            <th class="px-6 py-4 text-left">Dept No</th>
                            <th class="px-6 py-4 text-left">Karyawan</th>
                            <th class="px-6 py-4 text-left">Jabatan</th>
                            <th class="px-6 py-4 text-left">Terakhir Laporan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($employees as $employee)
                            @php
                                $latest = $employee->latest_dt ? \Carbon\Carbon::parse($employee->latest_dt) : null;
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/80 group">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                        {{ $employee->nik }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="text-sm font-semibold text-slate-700">
                                        {{ $employee->dept_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $employee->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if (!empty($employee->position))
                                        <span class="inline-flex items-center rounded-full border border-sky-100 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">
                                            {{ $employee->position }}
                                        </span>
                                    @else
                                        <span class="text-xs font-medium text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($latest)
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                                                <div class="h-2 w-2 rounded-full {{ $latest->isToday() ? 'bg-emerald-500' : 'bg-amber-400' }}"></div>
                                                {{ $latest->translatedFormat('d M Y') }} • {{ $latest->format('H:i') }}
                                            </span>
                                            <span class="text-[11px] font-medium text-slate-400 mt-0.5 ml-3.5">
                                                {{ $latest->diffForHumans() }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-500 bg-rose-50 px-3 py-1.5 rounded-xl border border-rose-100/50">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            BELUM PERNAH LAPORAN
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('daily-reports.show', $employee->nik) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-xs font-black text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 transition-all hover:bg-slate-50 hover:shadow-md group/btn">
                                            <span>Lihat Detail</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400 transition-transform group-hover/btn:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($employees->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
