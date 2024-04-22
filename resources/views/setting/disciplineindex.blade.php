@extends('layouts.app')

@section('content')

<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Table</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Employee Table</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Dept</th>
                <th>Start Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->id }}</td>
                    <td>{{ $employee->NIK }}</td>
                    <td>{{ $employee->karyawan->Nama }}</td>
                    <td>{{ $employee->karyawan->Dept }}</td>
                    <td>{{ $employee->start_date }}</td>
                    <td>{{ $employee->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> -->


<section class="content">
        <div class="card mt-5">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </section>

    {{ $dataTable->scripts() }}


@endsection