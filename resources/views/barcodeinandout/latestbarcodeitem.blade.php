@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Barcode Items</title>
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
    <h1>Latest Barcode Items</h1>

    <table>
        <thead>
            <tr>
                <th>Part No</th>
                <th>Label</th>
                <th>Scan Time</th>
                <th>No Dokumen</th>
                <th>Position</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sortedItems as $item)
                <tr>
                <td>{{ (string) $item->partNo }}</td>
                    <td>{{ (string) $item->label }}</td>
                    <td>{{ (string) $item->scantime }}</td>
                    <td>{{ (string) $item->noDokumen }}</td>
                    <td>{{ (string) $item->position }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>


@endsection