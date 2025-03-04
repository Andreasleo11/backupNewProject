@extends('layouts.guest')

@section('content')
<style>
    /* General Styling */
.container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
}

.employee-info, .assessment-table, .absence-report {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
}

h3 {
    border-bottom: 2px solid #007bff;
    padding-bottom: 5px;
    color: #007bff;
}

.table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}

.table th, .table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}

.table th {
    background: #007bff;
    color: white;
}

.table input {
    width: 100%;
    border: none;
    text-align: center;
}

/* Page Break for Printing */
.page-break {
    page-break-before: always;
}

/* Printing Styles */
@media print {
    /* Set A4 page size */
    @page {
        size: A4 portrait;
        margin: 1cm; /* Adjust as needed */
    }

    /* Hide menu/sidebar */
    nav, aside, header, footer {
        display: none !important;
    }

    /* Ensure each employee fits within A4 */
    .container {
        width: 100%;
        padding: 0;
        margin: 0 auto;
        page-break-inside: avoid;
    }

    .page-break {
        page-break-before: always;
        height: 100%; /* Ensure proper page division */
    }

    /* Optimize text and layout to fit A4 */
    h3 {
        font-size: 16px;
        margin-bottom: 5px;
    }

    .employee-info, .assessment-table, .absence-report {
        padding: 10px;
        border: 1px solid #000;
        border-radius: 5px;
        font-size: 12px; /* Reduce font size to fit */
    }

    /* Table adjustments */
    .table {
        font-size: 10px; /* Reduce font size to fit content */
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 5px;
        border: 1px solid #000;
    }

    .table th {
        background: #007bff;
        color: white;
    }

    .table input {
        border: none;
        pointer-events: none;
        font-size: 10px;
        text-align: center;
    }

    /* Ensure proper spacing */
    .row {
        display: flex;
        justify-content: space-between;
    }

    .col-md-4 {
        flex: 1;
        padding: 5px;
    }
}
</style>
    <div class="container">
        @foreach ($employees as $user)
            <div class="page-break">
            <!-- <h2>Evaluasi Yayasan {{ now()->format('F Y') }}</h2> Add this line -->
                <div class="employee-info">
                    <h3>Employee Details</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>NIK:</strong> {{ $user->NIK }}</p>
                            <p><strong>Name:</strong> {{ $user->Nama }}</p>
                            <p><strong>Gender:</strong> {{ $user->Gender }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Department:</strong> {{ $user->department->name ?? 0}}</p>
                            <p><strong>Branch:</strong> {{ $user->Branch }}</p>
                            <p><strong>Employee Status:</strong> {{ $user->employee_status }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Status:</strong> {{ $user->status }}</p>
                            <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($user->start_date)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Performance Assessment -->
                <div class="assessment-table">
                    <h3>Performance Assessment</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Assessment Area</th>
                                <th>Aspect</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="3"><strong>Kemampuan Kerja</strong></td>
                                <td>A1 Kemampuan Melakukan Pekerjaan<br>
                                <small>Range Nilai 17 - 15,3 - 13,6 - 11,9 - 0</small>
                                </td>
                                <td><input type="text" class="form-control" name="kemampuan_kerja_A1"></td>
                            </tr>
                            <tr>
                                <td>A2 Kecerdasan Melakukan Pekerjaan<br>
                                <small>Range Nilai 16 - 14,4 - 12,8 - 11,2 - 0</small>
                            </td>
                                <td><input type="text" class="form-control" name="kemampuan_kerja_A2"></td>
                            </tr>
                            <tr>
                                <td>A3 Kwantitas dan Kwalitas Pekerjaan<br>
                                <small>Range Nilai 11 - 9,9 - 8,8 - 7,2 - 0</small>
                            </td>
                                <td><input type="text" class="form-control" name="kemampuan_kerja_A3"></td>
                            </tr>

                            <tr>
                                <td rowspan="3"><strong>Sikap and Kelakuan</strong></td>
                                <td>B1 Kesopanan dan Kejujuran<br>
                                <small>Range Nilai 8 - 7,2 - 6,4 - 5,6 - 0</small>
                            </td>
                                <td><input type="text" class="form-control" name="sikap_kelakuan_B1"></td>
                            </tr>
                            <tr>
                                <td>B2 Loyalitas dan Tanggung Jawab<br>
                                <small>Range Nilai 10 - 9 - 8 - 7 - 0</small>
                            </td>
                                <td><input type="text" class="form-control" name="sikap_kelakuan_B2"></td>
                            </tr>
                            <tr>
                                <td>B3 Kerjasama dan Ketaatan<br>
                                <small>Range Nilai 10 - 9 - 8 - 7 - 0</small>
                            </td>
                                <td><input type="text" class="form-control" name="sikap_kelakuan_B3"></td>
                            </tr>



                             <!-- Disiplin and Kerapian -->
                            <tr>
                                <td rowspan="3"><strong>Disiplin and Kerapian</strong></td>
                                <td>C1 Disiplin Waktu<br>
                                <small>Range Nilai 10 - 9 - 8 - 7 - 0</small>
                            </td>
                                <td><input type="text" class="form-control" name="disiplin_kerapian_C1"></td>
                            </tr>
                            <tr>
                                <td>C2 Kebersihan Lingkungan Kerja<br>
                                <small>Range Nilai 10 - 9 - 8 - 7 - 0</small>
                            </td>
                                <td><input type="text" class="form-control" name="disiplin_kerapian_C2"></td>
                            </tr>
                            <tr>
                                <td>C3 Kerapihan Pakaian dan Peralatan<br>
                                <small>Range Nilai 8 - 7,2 - 6,4 - 5,6 - 0</small>
                            </td>
                                <td><input type="text" class="form-control" name="disiplin_kerapian_C3"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Absence Report -->
                <div class="absence-report">
                    <h3>Laporan Absensi </h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Jenis Cuti</th>
                                @for ($i = 1; $i <= 12; $i++)
                                    <th>{{ date('M', mktime(0, 0, 0, $i, 1)) }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Alpha</td>
                                @for ($i = 1; $i <= 12; $i++)
                                    @php
                                        $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $monthData = $user->evaluationData->firstWhere(function($data) use ($month) {
                                            return \Carbon\Carbon::parse($data->Month)->format('m') == $month;
                                        });
                                    @endphp
                                    <td>{{ $monthData ? $monthData->Alpha : 'X' }}</td>
                                @endfor
                            </tr>

                            <tr>
                                <td>Cuti Ijin</td>
                                @for ($i = 1; $i <= 12; $i++)
                                    @php
                                        $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $monthData = $user->evaluationData->firstWhere(function($data) use ($month) {
                                            return \Carbon\Carbon::parse($data->Month)->format('m') == $month;
                                        });
                                    @endphp
                                    <td>{{ $monthData ? $monthData->Izin : 'X' }}</td>
                                @endfor
                            </tr>

                            <tr>
                                <td>Cuti Sakit</td>
                                @for ($i = 1; $i <= 12; $i++)
                                    @php
                                        $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $monthData = $user->evaluationData->firstWhere(function($data) use ($month) {
                                            return \Carbon\Carbon::parse($data->Month)->format('m') == $month;
                                        });
                                    @endphp
                                    <td>{{ $monthData ? $monthData->Sakit : 'X' }}</td>
                                @endfor
                            </tr>

                            <tr>
                                <td>Terlambat</td>
                                @for ($i = 1; $i <= 12; $i++)
                                    @php
                                        $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $monthData = $user->evaluationData->firstWhere(function($data) use ($month) {
                                            return \Carbon\Carbon::parse($data->Month)->format('m') == $month;
                                        });
                                    @endphp
                                    <td>{{ $monthData ? $monthData->Telat : 'X' }}</td>
                                @endfor
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if (!$loop->last)
                <div style="page-break-after: always;"></div>
            @endif
        @endforeach
    </div>
@endsection
