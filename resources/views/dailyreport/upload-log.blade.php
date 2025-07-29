@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4 text-primary">📄 Hasil Upload Laporan Kerja</h2>

        @if (count($logs))
            <div class="table-responsive shadow-sm border rounded">
                <table class="table table-bordered table-hover table-striped table-sm align-middle mb-0">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $index => $log)
                            @php
                                $rowClass = match ($log['status']) {
                                    'Berhasil' => 'table-success',
                                    'Duplikat', 'Duplikat - dilewati' => 'table-warning',
                                    default => 'table-danger',
                                };
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($log['work_date'])->format('d M Y') }}</td>
                                <td>{{ $log['work_time'] }}</td>
                                <td>{{ $log['employee_name'] }}</td>
                                <td>{{ $log['departement_id'] }}</td>
                                <td>{{ $log['work_description'] }}</td>
                                <td class="text-center fw-bold">
                                    @if ($log['status'] === 'Berhasil')
                                        ✅
                                    @elseif (str_contains($log['status'], 'Duplikat'))
                                        ⚠️
                                    @else
                                        ❌
                                    @endif
                                    {{ $log['status'] }}
                                </td>
                                <td>{{ $log['message'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <a href="{{ route('daily-report.form') }}" class="btn btn-secondary">
                    ⬅️ Kembali ke Form
                </a>
            </div>
        @else
            <div class="alert alert-warning mt-4">
                Tidak ada log upload ditemukan.
            </div>
        @endif
    </div>
@endsection
