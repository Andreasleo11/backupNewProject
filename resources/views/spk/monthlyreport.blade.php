<!-- resources/views/monthlyreport.blade.php -->
@extends('layouts.app')

@section('content')
    <html>

    <head>
        <title>Monthly Report</title>
        <style>
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            table,
            th,
            td {
                border: 1px solid black;
                padding: 8px;
                text-align: left;
            }
        </style>
    </head>

    <body>
        <h1>Monthly Report</h1>

        <form method="GET" action="{{ route('spk.monthlyreport') }}">
            <div class="form-group">
                <label for="month">Select Month:</label>
                <select name="month" id="month" class="form-control">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ Carbon\Carbon::createFromDate(null, $m, 1)->monthName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="year">Select Year:</label>
                <select name="year" id="year" class="form-control">
                    @foreach (range(date('Y') - 5, date('Y') + 5) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>No Dokumen</th>
                    <th>Pelapor</th>
                    <th>Dept</th>
                    <th>Judul</th>
                    <th>Keterangan Laporan</th>
                    <th>PIC</th>
                    <th>Keterangan PIC</th>
                    <th>Tanggal Lapor</th>
                    <th>Tanggal Terima</th>
                    <th>Tanggal Selesai</th>
                    <th>Durasi</th>
                    <th>Estimasi Kesepakatan</th>
                    <th>Menit Estimasi</th>
                    <th>Menit Durasi</th>
                    <th>Presentase</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPresentase = 0;
                @endphp

                @foreach ($monthlyReport as $report)
                    <tr>
                        <td>{{ $report['no_dokumen'] }}</td>
                        <td>{{ $report['pelapor'] }}</td>
                        <td>{{ $report['dept'] }}</td>
                        <td>{{ $report['judul'] }}</td>
                        <td>{{ $report['keterangan_laporan'] }}</td>
                        <td>{{ $report['pic'] }}</td>
                        <td>{{ $report['keterangan_pic'] }}</td>
                        <td>{{ $report['tanggal_lapor'] }}</td>
                        <td>{{ $report['tanggal_mulai'] }}</td>
                        <td>{{ $report['tanggal_selesai'] }}</td>
                        <td>{{ $report['durasi'] }}</td>
                        <td>{{ $report['estimasi_kesepakatan'] }}</td>
                        <td>{{ $report['menit_estimasi'] }}</td>
                        <td>{{ $report['menit_durasi'] }}</td>
                        <td>{{ $report['presentase'] }}%</td>
                    </tr>
                    @php
                        // Sum up presentase values for average calculation
                        $totalPresentase += $report['presentase'];
                    @endphp
                @endforeach

                @php
                    // Calculate average presentase
                    $averagePresentase = count($monthlyReport) > 0 ? $totalPresentase / count($monthlyReport) : 0;
                @endphp

                @if (count($monthlyReport) > 0)
                    <tr>
                        <td colspan="14"><strong>Average Presentase:</strong></td>
                        <td><strong>{{ number_format($averagePresentase, 2) }}%</strong></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </body>

    </html>
@endsection
