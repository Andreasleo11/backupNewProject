@extends('layouts.app')

@section('content')
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('mastertinta.index') }}">Management Stock</a>
                </li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </nav>

        <div class="row d-flex">
            <div class="col">
                <h2 class="fw-bold">Management Stock</h1>
            </div>
            <div class="col-auto">
                @if (Auth::user()->department->name !== 'DIRECTOR')
                    <a href="{{ route('transaction.list') }}" class="btn btn-primary">List Transaction</a>
                    <a href="{{ route('testing.request') }}" class="btn btn-primary">Request Stock</a>
                    <a href="{{ route('mastertinta.transaction.index') }}" class="btn btn-primary">Edit Stock</a>
                    
                @endif

            </div>
        </div>


        <div class="row mt-4">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Stock Type ID</th>
                                        <th>Dept ID</th>
                                        <th>Stock Code</th>
                                        <th>Stock Description</th>
                                        <th>Stock Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($datas as $data)
                                    <tr>
                                        <td>{{ $data->stockType->name }}</td>
                                        <td>{{ $data->department->dept_no }}</td>
                                        <td>{{ $data->stock_code }}</td>
                                        <td>{{ $data->stock_description }}</td>
                                        <td>{{ $data->stock_quantity }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
