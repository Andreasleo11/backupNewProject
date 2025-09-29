@extends('layouts.app')

@section('content')
    <style>
        .button-container {
            margin-top: 20px;
        }

        .btn {
            margin-right: 10px;
            padding: 10px 20px;
            font-size: 16px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn:hover {
            opacity: 0.8;
        }
    </style>

    <h1>Index Untuk Barcode In & Out Feature </h1>

    <div class="button-container">
        <a href="{{ route('inandout.index') }}" class="btn btn-primary">Barcode In & Out</a>
        <a href="{{ route('updated.barcode.item.position') }}" class="btn btn-primary">Latest Barcode
            Item</a> <br>
        <br>
        <a href="{{ route('list.barcode') }}" class="btn btn-secondary">Full Report Barcode History</a>
        <a href="{{ route('barcodeindex') }}" class="btn btn-secondary">Index Barcode</a>
        <a href="{{ route('missingbarcode.index') }}" class="btn btn-secondary">Missing Barcode
            Generator</a>
    </div>
@endsection
