<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Evaluation Form</title>
    <style>
        @media print {
            @page {
                size: A4;
                /* Set to A4 paper size */
                margin: 10mm;
            }

            body {
                font-size: 10px;
            }

            .no-print {
                display: none !important;
            }


            h1,
            h2 {
                font-size: 16px;
                margin: 10px;
            }

            table {
                font-size: 9px;
                margin: 10px 0;
            }

            th,
            td {
                padding: 6px;
            }

            .grid-container {
                grid-template-columns: 1fr 1fr;
                /* Reduce column size to fit A4 */
            }

            .signature-section {
                flex: 1;
                padding: 5px;
            }

            .signature-box {
                width: 80px;
                height: 40px;
            }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        h1 {
            font-size: 28px;
            margin: 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .info {
            font-size: 14px;
            padding: 10px;
            background-color: #e9ecef;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-left: 20px;
            flex: 0 0 300px;
            /* Adjust width as needed */
        }

        .info p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }

        td {
            background-color: #f9f9f9;
        }

        tr:nth-child(even) td {
            background-color: #f2f2f2;
        }

        tr.footer td {
            background-color: #dcdcdc;
            font-weight: bold;
            text-align: right;
        }

        .signature-rectangle {
            position: relative;
            bottom: 20px;
            right: 20px;
            width: 100%;
            margin-left: auto;
            max-width: 900px;
            /* Adjust width as needed */
            border: 1px solid #ddd;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .signature-section {
            flex: 1;
            border-right: 1px solid #ddd;
            padding: 10px;
            box-sizing: border-box;
        }

        .signature-section:last-of-type {
            border-right: none;
            /* Remove border for the last section */
        }

        .section-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 20px;
            /* Adjust space between title and name */
        }

        .signature-box {
            border: 10px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            width: 100px;
            height: 50px;
        }

        .signature-name {
            margin: 0;
            margin-bottom: 20px;
            /* Add space between name and role */
        }

        .signature-role {
            margin: 0;
        }

        /* Grid system for layout */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 10px;
        }

        .grid-item {
            background-color: #f2f2f2;
            padding: 10px;
            text-align: center;
            font-size: 18px;
        }

        /* Minimize table space for better fit */
        th,
        td {
            padding: 5px;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>
                    <h1>PT DAIJO INDUSTRIAL</h1>
                </th>
                <th>
                    <span>No Dokumen</span> <br>
                    <span>Tgl. Dikeluarkan</span> <br>
                    <span>Revisi</span> <br>
                    <span>Halaman</span>
                </th>
                <th>
                    <span>DI-F-P/PR/04/PU-002</span> <br>
                    <span>4 November 2024</span><br>
                    <span>-01</span> <br>
                    <span>1 dari 1</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3">
                    <h2>SUPPLIER EVALUATION FORM</h2>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width:50%">
        <tbody>
            <tr>
                <th>Tanggal</th>
                <td>
                    <input type="date" id="tanggalInput" value="{{ $header->created_at->format('Y-m-d') }}"
                        onchange="updateTanggalView(this.value)" class="no-print">

                    <span id="tanggalView">
                        {{ $header->created_at->format('d F Y') }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Nama Supplier</th>
                <td>{{ $header->vendor_name }}</td>
            </tr>
            <tr>
                <th>Periode</th>
                <td>{{ $header->start_month }} {{ $header->year }} - {{ $header->end_month }} {{ $header->year_end }}
                </td>
            </tr>
            <tr>
                <th>Kriteria yang dinilai</th>
                <td>: </td>
            </tr>
        </tbody>
    </table>

    <style>
        .grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
        }

        .grid-item {
            background-color: #f2f2f2;
            padding: 10px;
            text-align: center;
            font-size: 18px;
        }
    </style>
    <div class="grid-container">
        <div class="grid-item">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kriteria</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Kualitas barang mencakup 20%</td>
                        <td>20</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Ketepatan kuantitas barang mencakup 20%</td>
                        <td>20</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Ketepatan waktu pengiriman mencakup 20%</td>
                        <td>20</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Kerjasama dalam permintaan mendadak mencakup 10%</td>
                        <td>10</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Respon klaim mencakup 10%</td>
                        <td>10</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Sertifikasi supplier mencakup 10%</td>
                        <td>10</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Menyebabkan Customer StopLine</td>
                        <td>10</td>
                    <tr>
                        <td></td>
                        <td>TOTAL NILAI</td>
                        <td>100</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="grid-item">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>A</td>
                        <td>Nilai 81% - 100&</td>
                    </tr>
                    <tr>
                        <td>B</td>
                        <td>Nilai 61% - 80&</td>
                    </tr>
                    <tr>
                        <td>C</td>
                        <td>Nilai <= 61%</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="grid-item">
            <table>
                <thead>
                    <th>Grade</th>
                    <td>{{ $header->grade }}</td>
                </thead>
                <tbody>
                    <tr>
                        <th>Status</th>
                        <td>{{ $header->status }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="grid-item">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 8px; width: 20%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;"><strong>Grade A</strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px;">Diteruskan, tidak perlu dilakukan
                            Audit
                            Supplier, cukup Evaluasi Supplier 1 tahun sekali</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;"><strong>Grade B</strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px;">Dipertahankan dan dilakukan Audit
                            Supplier
                            setelah 1-3 bulan dari Evaluasi Supplier tahunan</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;"><strong>Grade C</strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px;">Dilakukan Monitoring performa selama 3
                            bulan
                            dan dilakukan Audit Supplier di bulan berikutnya. Gradenya harus naik, bila gradenya
                            tidak
                            naik, akan dipertimbangkan untuk pemutusan kerjasama.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- <hr>

    <h1 style="margin-top:100px">PT Daijo Industrial </h1>
    <h2>Sup</h2>
    <table>
        <tr>
            <th>Dibuat Tanggal</th>
            <td>{{ $header->created_at->format('d F Y') }}</td>
            <td>Info</td>
        </tr>
        <tr>
            <th>Vendor Code</th>
            <td>{{ $header->vendor_code }}</td>
            <td rowspan="20">
                <p><strong>Keterangan:</strong></p>
                <p>A nilai 81 - 100</p>
                <p>B Nilai 61 - 80</p>
                <p>C Nilai <= 60</p>
                <p><strong>Status</strong></p>
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr>
                            <th colspan="2" style="border: 1px solid #ddd; padding: 8px; width: 20%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;"><strong>Grade A</strong></td>
                            <td style="border: 1px solid #ddd; padding: 8px;">Diteruskan, tidak perlu dilakukan audit supplier, cukup evaluasi supplier 1 tahun sekali</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;"><strong>Grade B</strong></td>
                            <td style="border: 1px solid #ddd; padding: 8px;">Dipertahankan dan dilakukan Audit Supplier setelah 1-3 bulan dari Evaluasi Supplier tahunan</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;"><strong>Grade C</strong></td>
                            <td style="border: 1px solid #ddd; padding: 8px;">Dilakukan Monitoring performa selama 3 bulan dan dilakukan Audit Supplier di bulan berikutnya. Gradenya harus naik, bila gradenya tidak naik, akan dipertimbangkan untuk pemutusan kerjasama.</td>
                        </tr>
                    </tbody>
                </table>

            </td>
        </tr>
        <tr>
            <th>Vendor Name</th>
            <td>{{ $header->vendor_name }}</td>
        </tr>
        <tr>
            <th>Year</th>
            <td>{{ $header->year }}</td>
        </tr>
        <tr>
            <th>Grade</th>
            <td>{{ $header->grade }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $header->status }}</td>
        </tr>
    </table> -->

    <h2>Penilaian Per Tahun</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                @foreach ($result as $month => $values)
                    @if ($month !== 'rata-rata')
                        <th>{{ $month }}</th>
                    @endif
                @endforeach
                <th>Average</th>
            </tr>
        </thead>
        <tbody>
            @foreach (['kualitas_barang', 'ketepatan_kuantitas_barang', 'ketepatan_waktu_pengiriman', 'kerjasama_permintaan_mendadak', 'respon_klaim', 'sertifikasi', 'customer_stopline'] as $category)
                <tr>
                    <td>
                        @php
                            $nameMap = [
                                'kerjasama_permintaan_mendadak' => 'Kerjasama dalam Permintaan Mendadak',
                                'sertifikasi' => 'Sertifikasi Supplier',
                                'customer_stopline' => 'Menyebabkan Customer Stop Line',
                            ];
                        @endphp
                        {{ $nameMap[$category] ?? ucfirst(str_replace('_', ' ', $category)) }}
                    </td>
                    @foreach ($result as $month => $values)
                        @if ($month !== 'rata-rata')
                            <td>{{ $values[$category] ?? 0 }}</td>
                        @endif
                    @endforeach
                    <td>{{ $result['rata-rata'][$category] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                // Initialize sums for each category and calculate the total average
                $totalSums = 0;
                $totalCount = 0;
                $categories = [
                    'kualitas_barang',
                    'ketepatan_kuantitas_barang',
                    'ketepatan_waktu_pengiriman',
                    'kerjasama_permintaan_mendadak',
                    'respon_klaim',
                    'sertifikasi',
                    'customer_stopline',
                ];

                foreach ($categories as $category) {
                    $categoryAverage = $result['rata-rata'][$category] ?? 0;
                    if ($categoryAverage > 0) {
                        $totalSums += $categoryAverage;
                        $totalCount++;
                    }
                }

                $overallAverage = $totalCount > 0 ? $totalSums : 0;
            @endphp
            <tr class="footer">
                <td>Total Average</td>
                @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                    <td></td> <!-- Empty cells for months -->
                @endforeach
                <td>{{ number_format($overallAverage, 2) }}</td> <!-- Display overall average -->
            </tr>
        </tfoot>
    </table>

    <div class="signature-rectangle">
        <div class="signature-section">
            <div class="section-content">
                <div class="section-title">Disiapkan Oleh</div>
                <div class="signature-box">

                </div>
                <p class="signature-name"><br>Brandon <br>Purchasing Staff</p>
            </div>
        </div>
        <div class="signature-section">
            <div class="section-content">
                <div class="section-title">Diperiksa Oleh</div>
                <div class="signature-box">

                </div>
                <p class="signature-name"><br>{{ $header->contact->p_member ?? '-' }}<br>Purchasing </p>
            </div>
        </div>
        <div class="signature-section">
            <div class="section-content">
                <div class="section-title">Disahkan Oleh</div>
                <div class="signature-box">

                </div>
                <p class="signature-name"><br>Korintani <br>Purchasing Dept Head</p>
            </div>
        </div>
    </div>
</body>


<script>
    function updateTanggalView(value) {
        let options = {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        };
        let formatted = new Date(value).toLocaleDateString('id-ID', options);
        document.getElementById('tanggalView').innerText = formatted;
    }
</script>

</html>
