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

            @if(isset($summary) && $summary->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Tanggal Awal</th>
                                <th>Tanggal Akhir</th>
                                <th>Total Jam Lembur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary as $row)
                            <tr>
                                <td>{{ $row->NIK }}</td>
                                <td>{{ $row->nama }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->start_date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->end_date)->format('d M Y') }}</td>
                                <td><strong>{{ number_format($row->total_ot, 2) }} jam</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning mt-3">
                    Tidak ada data lembur untuk rentang tanggal tersebut.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
