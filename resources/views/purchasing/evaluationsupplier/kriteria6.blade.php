@extends('layouts.app')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vendor List Certificate</title>
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

                /* Adjust table width for print */
                table {
                    width: 100%;
                    font-size: 12px;
                    page-break-inside: auto;
                }

                tr {
                    page-break-inside: avoid;
                    page-break-after: auto;
                }

                /* Ensure table cells don't overflow */
                td,
                th {
                    word-wrap: break-word;
                    max-width: 100px;
                    /* Adjust the max-width to prevent overflow */
                }

                /* Reduce font size for better fitting */
                th,
                td {
                    font-size: 10px;
                    /* Adjust as needed */
                }

                /* Remove margins for print */
                @page {
                    margin: 0;
                }

                /* Center the table for print */
                body {
                    margin: 0;
                    padding: 0;
                }
            }
        </style>
    </head>

    <body>
        <a href="{{ route('purchasing.evaluationsupplier.index') }}">
            <button type="button">Back to Supplier Evaluation</button>
        </a>
        <h1>Vendor List Certificate</h1>

        <!-- Filter Form -->
        <div class="filter">
            <form action="{{ route('kriteria6') }}" method="GET">
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
                    <th>ISO 9001 Document</th>
                    <th>ISO 9001 Start Date</th>
                    <th>ISO 9001 End Date</th>
                    <th>ISO 14001 Document</th>
                    <th>ISO 14001 Start Date</th>
                    <th>ISO 14001 End Date</th>
                    <th>IATF 16949 Document</th>
                    <th>IATF 16949 Start Date</th>
                    <th>IATF 16949 End Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $data)
                    <tr>
                        <td>{{ $data->id }}</td>
                        <td>{{ $data->vendor_code }}</td>
                        <td>{{ $data->vendor_name }}</td>
                        <td>{{ $data->iso_9001_doc }}</td>
                        <td>{{ $data->iso_9001_start_date == '0000-00-00' || !$data->iso_9001_start_date ? '-' : \Carbon\Carbon::parse($data->iso_9001_start_date)->format('d-m-Y') }}
                        </td>
                        <td>{{ $data->iso_9001_end_date == '0000-00-00' || !$data->iso_9001_end_date ? '-' : \Carbon\Carbon::parse($data->iso_9001_end_date)->format('d-m-Y') }}
                        </td>
                        <td>{{ $data->iso_14001_doc }}</td>
                        <td>{{ $data->iso_14001_start_date == '0000-00-00' || !$data->iso_14001_start_date ? '-' : \Carbon\Carbon::parse($data->iso_14001_start_date)->format('d-m-Y') }}
                        </td>
                        <td>{{ $data->iso_14001_end_date == '0000-00-00' || !$data->iso_14001_end_date ? '-' : \Carbon\Carbon::parse($data->iso_14001_end_date)->format('d-m-Y') }}
                        </td>
                        <td>{{ $data->iatf_16949_doc }}</td>
                        <td>{{ $data->iatf_16949_start_date == '0000-00-00' || !$data->iatf_16949_start_date ? '-' : \Carbon\Carbon::parse($data->iatf_16949_start_date)->format('d-m-Y') }}
                        </td>
                        <td>{{ $data->iatf_16949_end_date == '0000-00-00' || !$data->iatf_16949_end_date ? '-' : \Carbon\Carbon::parse($data->iatf_16949_end_date)->format('d-m-Y') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>

    </html>
@endsection
