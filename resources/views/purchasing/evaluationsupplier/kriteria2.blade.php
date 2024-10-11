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

                /* Hide everything except the table */
                body * {
                    visibility: hidden;
                }

                table,
                table * {
                    visibility: visible;
                }

                table {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                }

                /* Remove page margins for the table */
                @page {
                    margin: 0;
                }

                /* Adjust table size to ensure it fits the page */
                table {
                    width: 100%;
                    font-size: 12px;
                    page-break-inside: auto;
                }

                tr {
                    page-break-inside: avoid;
                    page-break-after: auto;
                }

                td,
                th {
                    word-wrap: break-word;
                    max-width: 100px;
                    /* Set max-width to prevent overflow */
                }
            }
        </style>
    </head>

    <body>
        <a href="{{ route('purchasing.evaluationsupplier.index') }}">
            <button type="button">Back to Supplier Evaluation</button>
        </a>
        <h1>Vendor Accuracy Good</h1>

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
        <table>
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
