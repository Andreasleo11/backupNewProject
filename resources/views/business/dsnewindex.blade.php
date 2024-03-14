@extends('layouts.app')

@section('content')

<section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">DELIVERY SCHEDULE FINAL </h1>
            </div>
        </div>
    </section>


    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center mb-0">
                        <thead>
                            <tr class="align-middle fw-semibold fs-5">
                                <th class="p-3">NO</th>
                                <th>SO NUMBER</th>
                                <th>DELIVERY DATE</th>
                                <th>CUSTOMER CODE</th>
                                <th>CUSTOMER NAME</th>
                                <th>ITEM CODE</th>
                                <th>ITEM NAME</th>
                                <th>DEPARTMENT</th>
                                <th>DELIVERY QUANTITY</th>
                                <th>DELIVERED</th>
                                <th>OUTSTANDING (DEL)</th>
                                <th>OP STOCK</th>
                                <th>BALANCE STOCK</th>
                                <th>OUTSTANDING (DEL AFTER)</th>
                                <th>PACKAGING</th>
                                <th>STANDAR PACK</th>
                                <th>PACK QUANTITY</th>
                                <th>DOC STATUS</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datas as $data)
                                <tr class="align-middle">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->so_number }}</td>
                                    <td>{{ $data->delivery_date }}</td>
                                    <td>{{ $data->customer_code }}</td>
                                    <td>{{ $data->customer_name }}</td>
                                    <td>{{ $data->item_code }}</td>
                                    <td>{{ $data->item_name }}</td>
                                    <td>{{ $data->departement }}</td>
                                    <td>{{ $data->delivery_qty }}</td>
                                    <td>{{ $data->delivered }}</td>
                                    <td>{{ $data->outstanding }}</td>
                                    <td>{{ $data->stock }}</td>
                                    <td>{{ $data->balance }}</td>
                                    <td>{{ $data->outstanding_stk }}</td>
                                    <td>{{ $data->packaging_code }}</td>
                                    <td>{{ $data->standar_pack }}</td>
                                    <td>{{ $data->packaging_qty }}</td>
                                    <td>{{ $data->doc_status }}</td>
                                    <td>{{ $data->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $datas->links() }}
        </div>

        <a href="{{ route('indexfinalwip') }}" class="btn btn-secondary float-right"> WIP</a>
        <a href="#" class="btn btn-secondary float-right"> Update</a>
    </section>

@endsection