@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Claims</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .table-container {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .table tbody td {
            text-align: center;
        }
        .filter-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">

    <a href="{{ route('purchasing.evaluationsupplier.index') }}">
        <button type="button">Back to Supplier Evaluation</button>
    </a>
    <h1 class="mb-4">Vendor Claims</h1>

    <div class="filter">
        <form action="{{ route('kriteria1') }}" method="GET">
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
    <div class="table-container">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vendor Code</th>
                    <th>Vendor Name</th>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Delivery No</th>
                    <th>Incoming Date</th>
                    <th>Quantity</th>
                    <th>Claim Start Date</th>
                    <th>Claim Finish Date</th>
                    <th>Can Use</th>
                    <th>Remarks</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($datas as $data)
                    <tr>
                        <td>{{ $data->id }}</td>
                        <td>{{ $data->vendor_code }}</td>
                        <td>{{ $data->vendor_name }}</td>
                        <td>{{ $data->item_code }}</td>
                        <td>{{ $data->description }}</td>
                        <td>{{ $data->delivery_no }}</td>
                        <td>{{ $data->incoming_date}}</td>
                        <td>{{ $data->quantity }}</td>
                        <td>{{ $data->claim_start_date }}</td>
                        <td>{{ $data->claim_finish_date }}</td>
                        <td>{{ $data->can_use }}</td>
                        <td>{{ $data->remarks }}</td>
                        <td>{{ $data->reason }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center">No data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

@endsection