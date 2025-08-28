<!-- resources/views/maintenance-inventory/show.blade.php -->
@extends('layouts.app')

@section('content')
  <div class="container">
    <h1>Maintenance Inventory Report</h1>

    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        Report Details
      </div>
      <div class="card-body">
        <p><strong>ID:</strong> {{ $report->id }}</p>
        <p><strong>Document Number:</strong> {{ $report->no_dokumen }}</p>
        <p><strong>Master ID:</strong> {{ $report->master_id }}</p>
        <p><strong>Revision Date:</strong> {{ $report->revision_date }}</p>
        <p><strong>Periode Caturwulan:</strong> {{ $report->periode_caturwulan }}</p>
        <p><strong>Created At:</strong> {{ $report->created_at }}</p>
        <p><strong>Updated At:</strong> {{ $report->updated_at }}</p>
      </div>
    </div>

    <h2>Detail Maintenance Reports</h2>

    @if ($report->detail->isEmpty())
      <p>No details available.</p>
    @else
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Category Name</th>
            <th>Condition</th>
            <th>Checked By</th>
            <th>Remark</th>
            <th>Created At</th>
            <th>Updated At</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($report->detail as $detail)
            <tr>
              <td>{{ $detail->typecategory->name }}</td>
              <td>{{ $detail->condition }}</td>
              <td>{{ $detail->checked_by }}</td>
              <td>{{ $detail->remark ?? 'N/A' }}</td>
              <td>{{ $detail->created_at }}</td>
              <td>{{ $detail->updated_at }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  <style>
    body {
      background-color: #f7f9fc;
      color: #333;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    h1,
    h2 {
      color: #343a40;
      text-align: center;
    }

    .container {
      margin-top: 30px;
    }

    .card {
      border: 1px solid #dee2e6;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header {
      background-color: #6c757d;
      font-size: 1.25rem;
    }

    .card-body {
      padding: 20px;
      background-color: #ffffff;
    }

    p {
      font-size: 1rem;
      margin: 0.5rem 0;
    }

    .table {
      margin-top: 20px;
      border-radius: 8px;
      overflow: hidden;
    }

    .table thead {
      background-color: #343a40;
      color: #ffffff;
    }

    .table-striped tbody tr:nth-of-type(odd) {
      background-color: #f2f2f2;
    }

    .table tbody tr:hover {
      background-color: #e9ecef;
    }

    th,
    td {
      padding: 12px;
      text-align: center;
      vertical-align: middle;
    }

    /* Make buttons more appealing */
    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
      padding: 10px 20px;
      font-size: 1rem;
      border-radius: 5px;
      margin-top: 20px;
    }

    .btn-primary:hover {
      background-color: #0056b3;
      border-color: #004085;
    }
  </style>

@endsection
