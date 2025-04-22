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

.signature-container {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }
    .signature-box {
        width: 30%;
        text-align: center;
        border-bottom: 2px solid black;
        padding-top: 10px;
    }
    .signature-box p {
        margin: 0;
        font-weight: bold;
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
                    @if($magang == 1 || $magang === null)
                    <h3>Employee Details </h3>
                    @else
                    <h3>Employee Details </h3>
                    @endif
                    
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
                            <p><strong>Evaluasi Periode {{$year}}</strong></p>
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
                                <th colspan="5">Score <br>(lingkari atau coret nilai)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="3"><strong>Kemampuan Kerja</strong></td>
                                <td>A1 Kemampuan Melakukan Pekerjaan<br>
                                </td>
                                            <td>17</td>
                                            <td>15.3</td>
                                            <td>13.6</td>
                                            <td>11.9</td>
                                            <td>0</td>
                            </tr>
                            <tr>
                                <td>A2 Kecerdasan Melakukan Pekerjaan<br>
                            </td>
                                            <td>16</td>
                                            <td>14.4</td>
                                            <td>12.8</td>
                                            <td>11.2</td>
                                            <td>0</td>
                            </tr>
                            <tr>
                                <td>A3 Kwantitas dan Kwalitas Pekerjaan<br>
                            </td>
                                            <td>11</td>
                                            <td>9.9</td>
                                            <td>8.8</td>
                                            <td>7.2</td>
                                            <td>0</td>
                            </tr>

                            <tr>
                                <td rowspan="3"><strong>Sikap and Kelakuan</strong></td>
                                <td>B1 Kesopanan dan Kejujuran<br>
                            </td>
                                            <td>8</td>
                                            <td>7.2</td>
                                            <td>6.4</td>
                                            <td>5.6</td>
                                            <td>0</td>
                            </tr>
                            <tr>
                                <td>B2 Loyalitas dan Tanggung Jawab<br>
                            </td>
                                            <td>10</td>
                                            <td>9</td>
                                            <td>8</td>
                                            <td>7</td>
                                            <td>0</td>
                            </tr>
                            <tr>
                                <td>B3 Kerjasama dan Ketaatan<br>
                            </td>
                                            <td>10</td>
                                            <td>9</td>
                                            <td>8</td>
                                            <td>7</td>
                                            <td>0</td>
                            </tr>



                             <!-- Disiplin and Kerapian -->
                            <tr>
                                <td rowspan="3"><strong>Disiplin and Kerapian</strong></td>
                                <td>C1 Disiplin Waktu<br>
                            </td>
                                            <td>10</td>
                                            <td>9</td>
                                            <td>8</td>
                                            <td>7</td>
                                            <td>0</td>
                            </tr>
                            <tr>
                                <td>C2 Kebersihan Lingkungan Kerja<br>
                            </td>
                                            <td>10</td>
                                            <td>9</td>
                                            <td>8</td>
                                            <td>7</td>
                                            <td>0</td>
                            </tr>
                            <tr>
                                <td>C3 Kerapihan Pakaian dan Peralatan<br>
                            </td>
                                            <td>8</td>
                                            <td>7.2</td>
                                            <td>6.4</td>
                                            <td>5.6</td>
                                            <td>0</td>
                            </tr>
                            <tr >
                                <td colspan = "2">
                                    Total Nilai
                                </td>
                                <td colspan= "5">
                                </td>
                            </tr>
                            <tr >
                                <td colspan = "2" style="text-align: center;">
                                    Total Grade <br>
                                    <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
                                    <div style="border: 1px solid black; padding: 10px; width: 80px; text-align: center;">100-91 = A</div>
                                    <div style="border: 1px solid black; padding: 10px; width: 80px; text-align: center;">90-81 = B</div>
                                    <div style="border: 1px solid black; padding: 10px; width: 80px; text-align: center;">80-71 = C</div>
                                    <div style="border: 1px solid black; padding: 10px; width: 80px; text-align: center;">70-61 = D</div>
                                    <div style="border: 1px solid black; padding: 10px; width: 80px; text-align: center;">61-0 = E</div>
                                </div>
                                </td>
                                <td colspan= "5">
                                </td>
                            </tr>
                            <tr >
                                <td colspan = "2">
                                  Layak (1) Diperpanjang Atau Tidak (2)
                                </td>
                                <td colspan= "5">
                                </td>
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
            
            <div class="signature-container">
                    <div class="signature-box">
                        <p>Yang Menyetujui</p>
                        <br><br><br>
                    </div>
                    <div class="signature-box">
                        <p>Yang Menyetujui</p>
                        <br><br><br>
                    </div> 
                    <div class="signature-box">
                        <p>Yang Menyetujui</p>
                        <br><br><br>
                    </div>
                </div>
            </div>
            
            @if (!$loop->last)
                <div style="page-break-after: always;"></div>
            @endif
        @endforeach
    </div>
@endsection
