@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Data</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Barcode Data</h1>

    @foreach ($result as $item)
        <h2>Date Scan: {{ $item['dateScan'] }}</h2>
        <p>No Dokumen: {{ $item['noDokumen'] }}</p>
        <p>Tipe Barcode: {{ strtoupper($item['tipeBarcode']) }}</p>
        <p>Location: {{ strtoupper($item['location']) }}</p>    
        <table>
            <thead>
                <tr>
                    <th>Part No</th>
                    <th>Label</th>
                    <th>Position</th>
                    <th>Scan Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($item[$item['noDokumen']] as $detail)
                    <tr>
                        <td>{{ $detail['partNo'] }}</td>
                        <td>{{ $detail['label'] }}</td>
                        <td>{{ $detail['position'] }}</td>
                        <td>{{ $detail['scantime'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr>
    @endforeach
</body>
</html>


@endsection