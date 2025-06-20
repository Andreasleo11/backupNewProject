@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📊 Ringkasan Lembur Karyawan</h4>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('overtime.summary') }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                        class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                        class="form-control" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">🔍 Tampilkan</button>
                </div>
            </form>

            <div class="mb-3 text-end">
                <a href="{{ route('overtime.summary.export', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                class="btn btn-success">
                    📥 Export ke Excel
                </a>
            </div>

             @if($summary->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">NIK</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Tanggal Awal</th>
                                <th scope="col">Tanggal Akhir</th>
                                <th scope="col">Total Jam Lembur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary as $row)
                            <tr>
                                <td>{{ $row['NIK'] }}</td>
                                <td>{{ $row['nama'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($row['start_date'])->translatedFormat('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($row['end_date'])->translatedFormat('d M Y') }}</td>
                                <td><span class="fw-bold text-success">{{ number_format($row['total_ot'], 2) }} jam</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning text-center mt-4" role="alert">
                    ⚠️ Tidak ada data lembur ditemukan untuk rentang tanggal yang dipilih.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection