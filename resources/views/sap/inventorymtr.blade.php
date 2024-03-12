@extends('layouts.app')

@section('content')

    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">LIST INVENTORY MTR </h1>
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
                                <th class="p-3">FG Code</th>
                                <th>Material Code</th>
                                <th>Material Name</th>
                                <th>Bom Quantity</th>
                                <th>In Stock</th>
                                <th>Item Group</th>
                                <th>Vendor Code</th>
                                <th>Vendor Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datas as $data)
                                <tr class="align-middle">
                                    <td>{{ $data->fg_code }}</td>
                                    <td>{{ $data->material_code }}</td>
                                    <td>{{ $data->material_name }}</td>
                                    <td>{{ $data->bom_quantity }}</td>
                                    <td>{{ $data->in_stock }}</td>
                                    <td>{{ $data->item_group }}</td>
                                    <td>{{ $data->vendor_code }}</td>
                                    <td>{{ $data->vendor_name }}</td>
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
    </section>
@endsection

@push('extraJs')
@endpush
