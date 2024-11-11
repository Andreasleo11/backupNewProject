@extends('layouts.app')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vendor Accuracy Good</title>
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

            table,
            th,
            td {
                border: 1px solid #ddd;
            }

            th,
            td {
                padding: 10px;
                text-align: center;
            }

            th {
                background-color: #f8f9fa;
            }

            td {
                background-color: #ffffff;
            }

            /* PRINT STYLES */
            @media print {

                /* Hide everything initially */
    body * {
        visibility: hidden;
    }

    /* Ensure visibility of the title and table */
    h1.mb-4,
    .printable-table,
    .printable-table * {
        visibility: visible;
    }

    /* Style the title for print */
    h1.mb-4 {
        position: absolute;
        top: 10px;
        width: 100%;
        text-align: center;
        font-size: 18px; /* Adjust font size for print */
        margin: 0;
        padding-bottom: 10px;
    }

    /* Adjust the table layout for print */
    .printable-table {
        position: absolute;
        top: 50px; /* Position the table below the title */
        left: 0;
        width: 100%;
        font-size: 12px;
        border-collapse: collapse;
        page-break-inside: auto;
    }

    /* Ensure table cells break gracefully without overflow */
    .printable-table td,
    .printable-table th {
        padding: 8px;
        border: 1px solid #ddd;
        word-wrap: break-word;
        max-width: 100px; /* Set max-width to prevent overflow */
    }

    /* Configure page settings */
    @page {
        margin: 10px; /* Adjust as needed for minimal print margin */
    }

    /* Avoid page breaks within table rows */
    .printable-table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
            }
        </style>
    </head>

    <body>
        <a href="{{ route('purchasing.evaluationsupplier.index') }}">
            <button type="button">Back to Supplier Evaluation</button>
        </a>
        <h1 class="mb-4">Vendor Accuracy Good</h1>

        <!-- Filter Form -->
        <div class="filter">
            <form action="{{ route('kriteria2') }}" method="GET">
                @csrf
                <label for="vendor_name">Select Vendor:</label>
                <select name="vendor_name" id="vendor_name">
                    <option value="">-- All Vendors --</option>
                    @foreach ($vendorNames as $vendor)
                        <option value="{{ $vendor }}" {{ request('vendor_name') == $vendor ? 'selected' : '' }}>
                            {{ $vendor }}
                        </option>
                    @endforeach
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>

        <!-- Data Table -->
        <table class="printable-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vendor Code</th>
                    <th>Vendor Name</th>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Delivery No</th>
                    <th>Incoming Date</th>
                    <th>Delivery Quantity</th>
                    <th>Received Quantity</th>
                    <th>Shortage Quantity</th>
                    <th>Over Quantity</th>
                    <th>Close Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $data)
                    <tr>
                        <td>{{ $data->id }}</td>
                        <td>{{ $data->vendor_code }}</td>
                        <td>{{ $data->vendor_name }}</td>
                        <td>{{ $data->item_code }}</td>
                        <td>{{ $data->description }}</td>
                        <td>{{ $data->delivery_no }}</td>
                        <td>{{ \Carbon\Carbon::parse($data->incoming_date)->format('d-m-Y') }}</td>
                        <td>{{ $data->delivery_quantity }}</td>
                        <td>{{ $data->received_quantity }}</td>
                        <td>{{ $data->shortage_quantity }}</td>
                        <td>{{ $data->over_quantity }}</td>
                        <td>{{ $data->close_status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>

    </html>
@endsection
