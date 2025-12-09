@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl px-4 py-6 mx-auto space-y-6">
        {{-- Page header --}}
        <div class="flex flex-col gap-1">
            <h1 class="text-xl font-semibold text-slate-900">
                Preview Laporan Kerja
            </h1>
            <p class="text-sm text-slate-500">
                Tinjau kembali data berikut sebelum dikonfirmasi dan disimpan ke sistem.
            </p>
        </div>

        @if (count($previewData))
            {{-- Info box --}}
            <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                <span class="font-semibold">Periksa kembali</span>
                <span>data di bawah ini sebelum dikonfirmasi.</span>
            </div>

            <form method="POST" action="{{ route('daily-report.confirm-upload') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="data" value="{{ base64_encode(serialize($previewData)) }}">

                {{-- Table preview --}}
                <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full text-sm text-left text-slate-700">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2 text-center w-10">#</th>
                                <th class="px-3 py-2 whitespace-nowrap">Tanggal</th>
                                <th class="px-3 py-2 whitespace-nowrap">Jam</th>
                                <th class="px-3 py-2 whitespace-nowrap">Nama</th>
                                <th class="px-3 py-2 whitespace-nowrap">Departemen</th>
                                <th class="px-3 py-2">Deskripsi Pekerjaan</th>
                                <th class="px-3 py-2 text-center whitespace-nowrap">Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($previewData as $index => $row)
                                <tr>
                                    <td class="px-3 py-2 text-center text-xs text-slate-500">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($row['work_date'])->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        {{ $row['work_time'] }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        {{ $row['employee_name'] }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        {{ $row['departement_id'] }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="max-w-md truncate" title="{{ $row['work_description'] }}">
                                            {{ $row['work_description'] }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if (!empty($row['proof_url']))
                                            <a href="{{ $row['proof_url'] }}" target="_blank"
                                               class="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100">
                                                Lihat bukti
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('daily-report.form') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Kembali
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Konfirmasi &amp; Simpan
                    </button>
                </div>
            </form>
        @else
            <div class="space-y-4">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <span class="font-semibold">Tidak ada data valid</span>
                    <span>ditemukan dari file yang diunggah.</span>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('daily-report.form') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Kembali ke Form Upload
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
