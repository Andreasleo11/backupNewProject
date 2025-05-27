@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 h4 fw-bold text-dark">Detail Laporan Harian: {{ $employee_name }} ({{ $employee_id }})</h2>

    @if($reports->isEmpty())
        <div class="alert alert-warning">
            Tidak ada laporan tersedia untuk karyawan ini.
        </div>
    @else
        <div class="table-responsive bg-white rounded shadow-sm">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Kerja</th>
                        <th>Deskripsi Pekerjaan</th>
                        <th>Bukti</th>
                        <th>Waktu Submit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($report->work_date)->format('d M Y') }}</td>
                            <td>{{ $report->work_time }}</td>
                            <td>{{ $report->work_description }}</td>
                            <td>
                                @if($report->proof_url)
                                    <a href="{{ $report->proof_url }}" target="_blank" class="btn btn-sm btn-info">
                                        Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-muted">Tidak ada</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($report->submitted_at)->format('d M Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <a href="{{ route('reports.depthead.index') }}" class="btn btn-secondary mt-4">Kembali</a>
</div>
@endsection
