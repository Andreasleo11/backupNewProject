@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Master PO List</h1>
    <a href="{{ route('pouploadview') }}" class="btn btn-secondary">Create PO Sign Request</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Filename</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datas as $data)
                <tr>
                    <td>{{ $data->po_number }}</td>
                    <td>{{ $data->filename }}</td>
                    <td>
                        <!-- Button to view the PDF -->
                        <a href="{{ route('pdf.view', ['filename' => $data->id]) }}" class="btn btn-primary" target="_blank">View PDF</a>
            
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
