@extends('new.layouts.app')

@section('content')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Header --}}
        <section class="mb-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-lg sm:text-xl font-semibold text-slate-900">
                        Form Cuti
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm text-slate-500">
                        Daftar pengajuan cuti karyawan.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('formcuti.create') }}"
                       class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        + Create
                    </a>
                </div>
            </div>
        </section>

        {{-- Table card --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="p-3 sm:p-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2 text-center">No</th>
                                <th class="px-3 py-2 text-left">Doc Num</th>
                                <th class="px-3 py-2 text-left">No Karyawan</th>
                                <th class="px-3 py-2 text-left">Tanggal Permohonan</th>
                                <th class="px-3 py-2 text-center">Action</th>
                                <th class="px-3 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($formcuti as $fc)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-2 text-center align-middle">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="px-3 py-2 align-middle">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-800 text-xs sm:text-sm">
                                                {{ $fc->doc_num }}
                                            </span>
                                            @if (!empty($fc->jenis_cuti))
                                                <span class="mt-0.5 text-[11px] text-slate-500">
                                                    Jenis: {{ ucfirst($fc->jenis_cuti) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 align-middle">
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 text-xs sm:text-sm">
                                                {{ $fc->no_karyawan }}
                                            </span>
                                            @if (!empty($fc->name))
                                                <span class="mt-0.5 text-[11px] text-slate-500">
                                                    {{ $fc->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 align-middle text-xs sm:text-sm text-slate-700">
                                        {{ $fc->tanggal_permohonan }}
                                    </td>

                                    <td class="px-3 py-2 text-center align-middle">
                                        <a href="{{ route('formcuti.detail', ['id' => $fc->id]) }}"
                                           class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                            <i class='bx bx-info-circle mr-1 text-xs'></i>
                                            Detail
                                        </a>
                                    </td>

                                    <td class="px-3 py-2 text-center align-middle">
                                        @if ($fc->is_accept == 1)
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                                APPROVED
                                            </span>
                                        @elseif (is_null($fc->is_accept))
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-100">
                                                WAITING
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-[11px] font-semibold text-rose-700 ring-1 ring-rose-100">
                                                REJECTED
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-xs sm:text-sm text-slate-500">
                                        Belum ada pengajuan cuti.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- (Optional) pagination di sini kalau nanti pakai ->links() --}}
                {{-- <div class="mt-3">
                    {{ $formcuti->links() }}
                </div> --}}
            </div>
        </section>
    </div>
@endsection
