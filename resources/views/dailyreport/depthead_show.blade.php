@extends('layouts.app')

@section('content')
  <div class="container py-5">
    <a href="{{ route('daily-reports.index') }}" class="btn btn-outline-secondary mb-3">
      <i class="bi bi-arrow-left-circle me-1"></i> Kembali ke Daftar Karyawan
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold text-primary mb-0">Detail Laporan Harian</h2>
      <span class="badge bg-secondary fs-6">NIK: {{ $employee_id }}</span>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="row g-3 align-items-end mb-4">
      <div class="col-md-4">
        <label for="filter_start_date" class="form-label">Tanggal Mulai</label>
        <input type="date" name="filter_start_date" id="filter_start_date"
          value="{{ $filter_start_date }}" class="form-control shadow-sm">
      </div>
      <div class="col-md-4">
        <label for="filter_end_date" class="form-label">Tanggal Selesai</label>
        <input type="date" name="filter_end_date" id="filter_end_date"
          value="{{ $filter_end_date }}" class="form-control shadow-sm">
      </div>
      <div class="col-md-auto">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-funnel-fill me-1"></i> Terapkan
        </button>
        <a href="{{ route('reports.depthead.show', $employee_id) }}"
          class="btn btn-outline-secondary px-4">
          <i class="bi bi-x-circle me-1"></i> Reset
        </a>
      </div>
    </form>

    <!-- Report Table -->
    @if ($reports->isEmpty())
      <div class="alert alert-warning text-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Tidak ada laporan tersedia untuk karyawan
        ini.
      </div>
    @else
      <!-- Styles -->
      <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

      <!-- Scripts -->
      <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

      <div id="calendar" class="mb-5"></div>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const calendarEl = document.getElementById('calendar');
          const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 500,
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: ''
            },
            events: @json($calendarEvents)
          });
          calendar.render();
        });
      </script>

      <div class="row mb-4">
        <div class="col">
          <div class="card shadow-sm border-start border-success border-4">
            <div class="card-body">
              <h6 class="text-muted">Total Hari dengan Laporan</h6>
              <h4 class="mb-0">{{ $submittedDates->count() }} hari</h4>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card shadow-sm border-start border-danger border-4">
            <div class="card-body">
              <h6 class="text-muted">Hari Tanpa Laporan</h6>
              <h4 class="mb-0">{{ $missingDates->count() }} hari</h4>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card shadow-sm border-start border-info border-4">
            <div class="card-body">
              <h6 class="text-muted">Periode Data</h6>
              <h4 class="mb-0">
                {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
              </h4>
            </div>
          </div>
        </div>
      </div>

      <div class="table-responsive bg-white shadow rounded">
        <table class="table table-hover table-bordered align-middle mb-0">
          <thead class="table-light text-center">
            <tr>
              <th>Tanggal</th>
              <th>Jam Kerja</th>
              <th>Deskripsi Pekerjaan</th>
              <th>Bukti</th>
              <th>Waktu Submit</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($reports as $report)
              <tr>
                <td class="text-nowrap text-center">
                  {{ \Carbon\Carbon::parse($report->work_date)->format('d M Y') }}</td>
                <td class="text-center">{{ $report->work_time }}</td>
                <td>{{ $report->work_description }}</td>
                <td class="text-center">
                  @if ($report->proof_url)
                    <a href="{{ $report->proof_url }}" target="_blank"
                      class="btn btn-sm btn-outline-info">
                      <i class="bi bi-image"></i> Lihat
                    </a>
                  @else
                    <span class="text-muted">Tidak ada</span>
                  @endif
                </td>
                <td class="text-nowrap text-center">
                  {{ \Carbon\Carbon::parse($report->submitted_at)->format('d M Y H:i') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endsection
