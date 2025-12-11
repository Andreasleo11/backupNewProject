@extends('new.layouts.app')

@section('content')
    
    <div
        x-data="{ }"
        class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
    >
        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h1 class="text-lg sm:text-xl font-semibold text-slate-900">
                    Form Keluar List
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">
                    Monitoring izin keluar karyawan dan status persetujuannya.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('formkeluar.create') }}"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                    </svg>
                    New Request
                </a>
            </div>
        </div>

        {{-- Table card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse text-xs sm:text-sm">
                    <thead class="bg-slate-50/80 border-b border-slate-200">
                        <tr class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2 text-center w-12">No</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Nama</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Doc Num</th>
                            <th class="px-3 py-2 text-center whitespace-nowrap">Action</th>
                            <th class="px-3 py-2 text-center whitespace-nowrap">Status</th>
                            <th class="px-3 py-2 text-left">Description</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($formkeluar as $fk)
                            <tr class="hover:bg-slate-50/60">
                                {{-- No --}}
                                <td class="px-3 py-2 text-center align-middle text-slate-700">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- Nama --}}
                                <td class="px-3 py-2 align-middle">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-900">
                                            {{ $fk->name }}
                                        </span>
                                        <span class="text-[11px] text-slate-500">
                                            {{-- bisa diisi NIK / dept kalau ada --}}
                                        </span>
                                    </div>
                                </td>

                                {{-- Doc Num --}}
                                <td class="px-3 py-2 align-middle text-slate-700 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[11px] font-medium text-slate-700">
                                        {{ $fk->doc_num }}
                                    </span>
                                </td>

                                {{-- Action --}}
                                <td class="px-3 py-2 align-middle text-center">
                                    <a href="{{ route('formkeluar.detail', ['id' => $fk->id]) }}"
                                       class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20"
                                             fill="currentColor">
                                            <path d="M10 3C5 3 1.73 7.11.46 9.53a1.86 1.86 0 000 1.94C1.73 13.89 5 18 10 18s8.27-4.11 9.54-6.53a1.86 1.86 0 000-1.94C18.27 7.11 15 3 10 3zm0 3a3 3 0 11-3 3 3 3 0 013-3z" />
                                        </svg>
                                        Detail
                                    </a>
                                </td>

                                {{-- Status --}}
                                <td class="px-3 py-2 align-middle text-center">
                                    @php
                                        // Sesuaikan mapping ini dengan field status di FormKeluar
                                        // Contoh: jika kolomnya 'status' dengan nilai: pending, approved, rejected
                                        $status = $fk->status ?? null;
                                        $statusLabel = 'Pending';
                                        $statusStyles = 'bg-amber-50 text-amber-700 border-amber-200';

                                        if ($status === 'approved' || $status === 1) {
                                            $statusLabel = 'Approved';
                                            $statusStyles = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        } elseif ($status === 'rejected' || $status === 2) {
                                            $statusLabel = 'Rejected';
                                            $statusStyles = 'bg-rose-50 text-rose-700 border-rose-200';
                                        } elseif ($status === 'pending' || $status === 0 || is_null($status)) {
                                            $statusLabel = 'Pending';
                                            $statusStyles = 'bg-amber-50 text-amber-700 border-amber-200';
                                        }
                                    @endphp

                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $statusStyles }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                {{-- Description --}}
                                <td class="px-3 py-2 align-middle text-left text-slate-600">
                                    {{ \Illuminate\Support\Str::limit($fk->alasan_izin_keluar, 90) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-sm text-slate-500">
                                    Belum ada Form Keluar yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($formkeluar->count())
                <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[11px] text-slate-500">
                        Total <span class="font-medium text-slate-700">{{ $formkeluar->count() }}</span> request.
                    </p>
                    {{-- Kalau nanti pakai pagination: {{ $formkeluar->links() }} di sini --}}
                </div>
            @endif
        </div>
    </div>
@endsection
