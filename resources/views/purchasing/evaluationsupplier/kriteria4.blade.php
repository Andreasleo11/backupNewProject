@extends('layouts.app')

@section('content')


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Urgent Request</title>
    <style>
       
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .filter {
            margin-bottom: 20px;
        }
        .filter form {
            display: flex;
            align-items: center;
        }
        .filter label {
            margin-right: 10px;
        }
        .filter select {
            padding: 5px;
            margin-right: 10px;
        }
        button {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
        }
        td {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <a href="{{ route('purchasing.evaluationsupplier.index') }}">
        <button type="button">Back to Supplier Evaluation</button>
    </a>
    <h1>Vendor Urgent Request</h1>

    <!-- Filter Form -->
    <div class="filter">
        <form action="{{ route('kriteria4') }}" method="GET">
            @csrf
            <label for="vendor_name">Select Vendor:</label>
            <select name="vendor_name" id="vendor_name">
                <option value="">-- All Vendors --</option>
                @foreach($vendorNames as $vendor)
                    <option value="{{ $vendor }}" {{ request('vendor_name') == $vendor ? 'selected' : '' }}>
                        {{ $vendor }}
                    </option>
                @endforeach
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>PO No</th>
                <th>PO Date</th>
                <th>Item Code</th>
                <th>Description</th>
                <th>Request Date</th>
                <th>Request Quantity</th>
                <th>Incoming Date</th>
                <th>Incoming Quantity</th>
                <th>Vendor Code</th>
                <th>Vendor Name</th>
                <th>Special Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datas as $data)
                <tr>
                    <td>{{ $data->id }}</td>
                    <td>{{ $data->po_no }}</td>
                    <td>{{ \Carbon\Carbon::parse($data->po_date)->format('d-m-Y') }}</td>
                    <td>{{ $data->item_code }}</td>
                    <td>{{ $data->description }}</td>
                    <td>{{ \Carbon\Carbon::parse($data->request_date)->format('d-m-Y') }}</td>
                    <td>{{ $data->request_quantity }}</td>
                    <td>{{ \Carbon\Carbon::parse($data->incoming_date)->format('d-m-Y') }}</td>
                    <td>{{ $data->incoming_quantity }}</td>
                    <td>{{ $data->vendor_code }}</td>
                    <td>{{ $data->vendor_name }}</td>
                    <td>{{ $data->special_price }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>


@endsection