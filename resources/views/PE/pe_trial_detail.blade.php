@extends('layouts.app')

@section('content')

<h1>Trial Details</h1>

<table class="detail-table">
    <tr>
        <th>Field</th>
        <th>Value</th>
    </tr>
    
    <tr>
        <td>Customer:</td>
        <td>{{ $trial->customer }}</td>
    </tr>
    
    <tr>
        <td>Part Name:</td>
        <td>{{ $trial->part_name }}</td>
    </tr>

    <!-- Add more rows for other details -->
</table>

@endsection

<style>
    .detail-table {
        border-collapse: collapse;
        width: 100%;
    }

    .detail-table th,
    .detail-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .detail-table td:nth-child(2) {
        padding-left: 20px; /* Adjust the padding-left value as needed for indentation */
    }
</style>