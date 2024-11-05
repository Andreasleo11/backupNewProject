@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Purchase Orders</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('po.index') }}">Purchase Orders</a></li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </nav>
        <div class="text-end my-2">
            <a href="{{ route('po.upload') }}" class="btn btn-primary">+ Create</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover ">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Status</th>
                        <th>Approved Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($datas as $data)
                        <tr>
                            <td>{{ $data->po_number }}</td>
                            <td>
                                @include('partials.po-status')
                            </td>
                            <td>{{ $data->approved_date }}</td>
                            <td>
                                <a href="{{ route('pdf.view', ['filename' => $data->id]) }}"
                                    class="btn btn-outline-primary">View PDF</a>
                                <a href="{{ route('pdf.download', $data->filename) }}"
                                    class="btn btn-outline-secondary">Download</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
