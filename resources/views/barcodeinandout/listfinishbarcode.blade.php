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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Barcode Data</h1>

    <form id="filterForm">
        <label for="tipeBarcode">Tipe Barcode:</label>
        <select name="tipeBarcode" id="tipeBarcode">
            <option value="">All</option>
            <option value="IN">IN</option>
            <option value="OUT">OUT</option>
        </select>

        <label for="location">Location:</label>
        <select name="location" id="location">
            <option value="">All</option>
            <option value="JAKARTA">JAKARTA</option>
            <option value="KARAWANG">KARAWANG</option>
        </select>


        <label for="dateScan">Date Scan:</label>
        <input type="date" name="dateScan" id="dateScan">

        <button type="button" id="filterButton">Filter</button>
    </form>

    <div id="barcodeData">
        @include('barcodeinandout.partials.barcode_table', ['result' => $result])
    </div>

    <script>
        $(document).ready(function() {
            $('#filterButton').on('click', function() {
                var tipeBarcode = $('#tipeBarcode').val();
                var location = $('#location').val();
                var dateScan = $('#dateScan').val();
                console.log(tipeBarcode);
                console.log(location);
                console.log(dateScan);
                $.ajax({
                    url: '{{ route("barcode.filter") }}',
                    method: 'GET',
                    data: {
                        tipeBarcode: tipeBarcode,
                        location: location,
                        dateScan: dateScan
                    },
                    success: function(response) {
                        $('#barcodeData').html(response);
                    }
                });
            });
        });
    </script>
</body>
</html>
@endsection
