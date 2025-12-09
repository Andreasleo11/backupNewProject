@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl px-4 py-6 mx-auto space-y-6">
        @php
            $totalLogs = count($logs);
            $successCount = collect($logs)->where('status', 'Berhasil')->count();
            $duplicateCount = collect($logs)->filter(fn ($l) => str_contains($l['status'], 'Duplikat'))->count();
            $failedCount = $totalLogs - $successCount - $duplicateCount;
        @endphp

        {{-- Page header --}}
        <div class="flex flex-col gap-1">
            <h1 class="text-xl font-semibold text-slate-900">
                Hasil Upload Laporan Kerja
            </h1>
            <p class="text-sm text-slate-500">
                Ringkasan hasil pemrosesan file laporan kerja yang baru diunggah.
            </p>
        </div>

        @if ($totalLogs)
            {{-- Small summary badges --}}
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-3 py-1 text-slate-700 ring-1 ring-slate-200">
                    <span class="font-medium">Total baris:</span>
                    <span>{{ $totalLogs }}</span>
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-emerald-700 ring-1 ring-emerald-200">
                    <span class="font-medium">Berhasil:</span>
                    <span>{{ $successCount }}</span>
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-amber-700 ring-1 ring-amber-200">
                    <span class="font-medium">Duplikat:</span>
                    <span>{{ $duplicateCount }}</span>
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-3 py-1 text-rose-700 ring-1 ring-rose-200">
                    <span class="font-medium">Gagal:</span>
                    <span>{{ $failedCount }}</span>
                </span>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full text-sm text-left text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2 text-center w-12">#</th>
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2">Jam</th>
                            <th class="px-3 py-2">Nama</th>
                            <th class="px-3 py-2">Departemen</th>
                            <th class="px-3 py-2">Deskripsi</th>
                            <th class="px-3 py-2 text-center">Status</th>
                            <th class="px-3 py-2">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($logs as $index => $log)
                            @php
                                $rowBg = match ($log['status']) {
                                    'Berhasil' => 'bg-emerald-50/40',
                                    'Duplikat', 'Duplikat - dilewati' => 'bg-amber-50/40',
                                    default => 'bg-rose-50/40',
                                };

                                $badgeClass = match ($log['status']) {
                                    'Berhasil' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                    'Duplikat', 'Duplikat - dilewati' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                    default => 'bg-rose-50 text-rose-700 ring-rose-200',
                                };
                            @endphp
                            <tr class="{{ $rowBg }}">
                                <td class="px-3 py-2 text-center text-xs text-slate-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($log['work_date'])->format('d M Y') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ $log['work_time'] }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ $log['employee_name'] }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ $log['departement_id'] }}
                                </td>
                                <td class="px-3 py-2">
                                    <div class="max-w-xs truncate" title="{{ $log['work_description'] }}">
                                        {{ $log['work_description'] }}
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span
                                        class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $badgeClass }}">
                                        {{ $log['status'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="max-w-xs truncate" title="{{ $log['message'] ?? '-' }}">
                                        {{ $log['message'] ?? '-' }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Back button --}}
            <div class="flex justify-end mt-6">
                <a href="{{ route('daily-report.form') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <span>Kembali ke Form</span>
                </a>
            </div>
        @else
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Tidak ada log upload ditemukan.
            </div>

            <div class="flex justify-end mt-4">
                <a href="{{ route('daily-report.form') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <span>Kembali ke Form</span>
                </a>
            </div>
        @endif
    </div>
@endsection
