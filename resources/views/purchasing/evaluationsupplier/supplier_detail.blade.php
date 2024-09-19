
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Evaluation Details</title>
    <style>
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
            flex: 0 0 300px; /* Adjust width as needed */
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
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
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
    </style>
</head>
<body>
    <h2>Header Information</h2>
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
                <p>Grade A  : Diteruskan</p>
                <p>Grade B  : Dipertahankan dengan dilakukan re-evaluasi</p>
                <p>Grade C  : Dilakukan Evaluasi selama 3 bulan dan grade harus naik</p>
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
    </table>

    <h2>Monthly Evaluation Data</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                    <th>{{ $month }}</th>
                @endforeach
                <th>Average</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['kualitas_barang', 'ketepatan_kuantitas_barang', 'ketepatan_waktu_pengiriman', 'kerjasama_permintaan_mendadak', 'respon_klaim', 'sertifikasi'] as $category)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $category)) }}</td>
                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                        <td>{{ $result[$month][$category] ?? 0 }}</td>
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
                $categories = ['kualitas_barang', 'ketepatan_kuantitas_barang', 'ketepatan_waktu_pengiriman', 'kerjasama_permintaan_mendadak', 'respon_klaim', 'sertifikasi'];

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
                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                    <td></td> <!-- Empty cells for months -->
                @endforeach
                <td>{{ number_format($overallAverage, 2) }}</td> <!-- Display overall average -->
            </tr>
        </tfoot>
    </table>
</body>
</html>


