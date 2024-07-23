@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Barcode Items</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        form label {
            margin-right: 10px;
            font-weight: bold;
        }

        form select, form input {
            margin-right: 20px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        form button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Latest Barcode Items</h1>

    <form method="GET" action="{{ route('updated.barcode.item.position') }}">
        <div>
            <label for="partNo">Part No:</label>
            <select id="partNo" name="partNo">
                <option value="">Select Part No</option>
                @foreach ($partNumbers as $partNumber)
                    <option value="{{ $partNumber->partNo }}" {{ request('partNo') == $partNumber->partNo ? 'selected' : '' }}>
                        {{ $partNumber->partNo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="scantime">Scan Time (yyyy-mm-dd):</label>
            <input type="date" id="scantime" name="scantime" value="{{ request('scantime') }}">
        </div>

        <div>
            <label for="position">Position:</label>
            <select id="position" name="position">
                <option value="">Select Position</option>
                <option value="Jakarta" {{ request('position') == 'Jakarta' ? 'selected' : '' }}>Jakarta</option>
                <option value="Karawang" {{ request('position') == 'Karawang' ? 'selected' : '' }}>Karawang</option>
                <option value="Customer" {{ request('position') == 'Customer' ? 'selected' : '' }}>Customer</option>
            </select>
        </div>

        <div>
            <button type="submit">Filter</button>
        </div>
    </form>

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
