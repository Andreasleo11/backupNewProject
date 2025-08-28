@extends('layouts.app')

@section('content')
  <div class="container py-4">
    <h2 class="mb-4 text-primary">📝 Preview Laporan Kerja</h2>

    @if (count($previewData))
      <div class="alert alert-info">
        <strong>Periksa kembali</strong> data di bawah ini sebelum dikonfirmasi.
      </div>

      <form method="POST" action="{{ route('daily-report.confirm-upload') }}">
        @csrf
        <input type="hidden" name="data" value="{{ base64_encode(serialize($previewData)) }}">

        <div class="table-responsive mb-3 shadow-sm border rounded">
          <table class="table table-hover table-striped table-bordered table-sm align-middle mb-0">
            <thead class="table-primary text-center">
              <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Nama</th>
                <th>Departemen</th>
                <th>Deskripsi Pekerjaan</th>
                <th>Bukti</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($previewData as $index => $row)
                <tr>
                  <td class="text-center">{{ $index + 1 }}</td>
                  <td>{{ \Carbon\Carbon::parse($row['work_date'])->format('d M Y') }}</td>
                  <td class="text-nowrap">{{ $row['work_time'] }}</td>
                  <td>{{ $row['employee_name'] }}</td>
                  <td>{{ $row['departement_id'] }}</td>
                  <td>{{ $row['work_description'] }}</td>
                  <td class="text-center">
                    @if ($row['proof_url'])
                      <a href="{{ $row['proof_url'] }}" target="_blank"
                        class="btn btn-sm btn-outline-success">Lihat</a>
                    @else
                      <span class="text-muted">Tidak Ada</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
          <button type="submit" class="btn btn-success btn-lg px-4 shadow">
            ✅ Konfirmasi & Simpan
          </button>
        </div>
      </form>
    @else
      <div class="alert alert-warning">
        <strong>⚠️ Tidak ada data valid</strong> ditemukan dari file yang diunggah.
      </div>
    @endif
  </div>
@endsection
