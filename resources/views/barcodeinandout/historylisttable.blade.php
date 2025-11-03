@extends('layouts.app')

@section('content')
    <!DOCTYPE html>
    <html>

    <head>
        <title>Barcode History List</title>
        <style>
            table {
                width: 100%;
                border-collapse: collapse;
            }

            table,
            th,
            td {
                border: 1px solid black;
            }

            th,
            td {
                padding: 10px;
                text-align: left;
            }
        </style>
    </head>

    <body>
        <h1>Barcode History List</h1>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('barcode.historytable') }}">
            <label for="datescan">Date Scan:</label>
            <input type="date" id="datescan" name="datescan" value="{{ request('datescan') }}">

            <label for="barcode_type">Barcode Type:</label>
            <select id="barcode_type" name="barcode_type">
                <option value="">-- Select Barcode Type --</option>
                <option value="IN" {{ request('barcode_type') == 'IN' ? 'selected' : '' }}>IN</option>
                <option value="OUT" {{ request('barcode_type') == 'OUT' ? 'selected' : '' }}>OUT</option>
            </select>

            <label for="location">Location:</label>
            <select id="location" name="location">
                <option value="">-- Select Location --</option>
                <option value="Jakarta" {{ request('location') == 'Jakarta' ? 'selected' : '' }}>Jakarta
                </option>
                <option value="Karawang" {{ request('location') == 'Karawang' ? 'selected' : '' }}>Karawang
                </option>
            </select>

            <label for="partNo">Part No:</label>
            <select id="partNo" name="partNo">
                <option value="">-- Select Part No --</option>
                @foreach ($distinctPartNos as $partNo)
                    <option value="{{ $partNo->partNo }}" {{ request('partNo') == $partNo->partNo ? 'selected' : '' }}>
                        {{ $partNo->partNo }}
                    </option>
                @endforeach
            </select>

            <button type="submit">Filter</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Document No</th>
                    <th>Barcode Type</th>
                    <th>Location</th>
                    <th>Part No</th>
                    <th>Quantity</th>
                    <th>Label</th>
                    <th>Position</th>
                    <th>Scan Time</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQuantity = 0;
                @endphp
                @foreach ($items as $item)
                    @foreach ($item->detailbarcode as $detail)
                        <tr>
                            <td>{{ $item->noDokumen }}</td>
                            <td>{{ $item->tipeBarcode }}</td>
                            <td>{{ $item->location }}</td>
                            <td>{{ $detail->partNo }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>{{ $detail->label }}</td>
                            <td>{{ $detail->position }}</td>
                            <td>{{ $detail->scantime }}</td>
                        </tr>
                        @php
                            $totalQuantity += $detail->quantity;
                        @endphp
                    @endforeach
                @endforeach
                <tr>
                    <td colspan="4"></td>
                    <td><strong>Total Quantity: {{ $totalQuantity }}</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
    </body>

    </html>
@endsection
