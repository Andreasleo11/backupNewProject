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
                                <th>FG LINK</th>
                                <th>SO NUMBER</th>
                                <th>DELIVERY DATE</th>
                                <th>CUSTOMER CODE</th>
                                <th>CUSTOMER NAME</th>
                                <th>FG CODE</th>
                                <th>FG NAME</th>
                                <th>OUTSTANDING(DEL)</th>
                                <th>WIP CODE</th>
                                <th>WIP NAME</th>
                                <th>DEPARTEMENT</th>
                                <th>BOM LEVEL 1</th>
                                <th>BOM QUANTITY 2</th>
                                <th>REQUIREMENT QUANTITY</th>
                                <th>STOCK (WIP)</th>
                                <th>BALANCE (WIP)</th>
                                <th>OUTSTANDING(DEL AFTER)</th>
                                <th>STATUS </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datas as $data)
                                <tr class="align-middle">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->fglink_id }}</td>
                                    <td>{{ $data->so_number }}</td>
                                    <td>{{ $data->delivery_date }}</td>
                                    <td>{{ $data->customer_code }}</td>
                                    <td>{{ $data->customer_name }}</td>
                                    <td>{{ $data->item_code }}</td>
                                    <td>{{ $data->item_name }}</td>
                                    <td>{{ $data->outstanding_del }}</td>
                                    <td>{{ $data->wip_code }}</td>
                                    <td>{{ $data->wip_name }}</td>
                                    <td>{{ $data->departement }}</td>
                                    <td>{{ $data->bom_level }}</td>
                                    <td>{{ $data->bom_quantity }}</td>
                                    <td>{{ $data->req_quantity }}</td>
                                    <td>{{ $data->stock_wip }}</td>
                                    <td>{{ $data->balance_wip }}</td>
                                    <td>{{ $data->outstanding_wip }}</td>
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

        <a href="{{ route('indexds') }}" class="btn btn-secondary float-right"> FINAL DS</a>
        <a href="#" class="btn btn-secondary float-right"> Update</a>
    </section>

@endsection


@push('extraJs')
@endpush