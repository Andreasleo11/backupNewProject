@extends('layouts.app')

@section('content')

    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">LIST INVENTORY FG </h1>
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
                                <th class="p-3">ITEM CODE</th>
                                <th>ITEM NAME</th>
                                <th>ITEM GROUP</th>
                                <th>DAY SET PPS</th>
                                <th>SETUP TIME</th>
                                <th>CYCLE TIME</th>
                                <th>CAVITY</th>
                                <th>SAFETY STOCK</th>
                                <th>DAILY LIMIT</th>
                                <th>STOCK</th>
                                <th>TOTAL SPK</th>
                                <th>PRODUCTION MIN QTY</th>
                                <th>STANDART PACKING</th>
                                <th>PAIR</th>
                                <th>MAN POWER</th>
                                <th>WAREHOUSE</th>
                                <th>PROCESS OWNER</th>
                                <th>SPECIAL CONDITION</th>
                                <th>FG CODE 1</th>
                                <th>FG CODE 2</th>
                                <th>WIP CODE</th>
                                <th>MATERIAL PERCENTAGE</th>
                                <th>CONTINUE PRODUCTION</th>
                                <th>FAMILY</th>
                                <th>MATERIAL GROUP</th>
                                <th>OLD MOULD</th>
                                <th>PACKAGING</th>
                                <th>BOM LEVEL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datas as $data)
                                <tr class="align-middle">
                                    <td>{{ $data->item_code }}</td>
                                    <td>{{ $data->item_name }}</td>
                                    <td>{{ $data->item_group }}</td>
                                    <td>{{ $data->day_set_pps }}</td>
                                    <td>{{ $data->setup_time }}</td>
                                    <td>{{ $data->cycle_time }}</td>
                                    <td>{{ $data->cavity }}</td>
                                    <td>{{ $data->safety_stock }}</td>
                                    <td>{{ $data->daily_limit }}</td>
                                    <td>{{ $data->stock }}</td>
                                    <td>{{ $data->total_spk }}</td>
                                    <td>{{ $data->production_min_qty }}</td>
                                    <td>{{ $data->standar_packing }}</td>
                                    <td>{{ $data->pair }}</td>
                                    <td>{{ $data->man_power }}</td>
                                    <td>{{ $data->warehouse }}</td>
                                    <td>{{ $data->process_owner }}</td>
                                    <td>{{ $data->owner_code }}</td>
                                    <td>{{ $data->special_condition }}</td>
                                    <td>{{ $data->fg_code_1 }}</td>
                                    <td>{{ $data->fg_code_2 }}</td>
                                    <td>{{ $data->wip_code }}</td>
                                    <td>{{ $data->material_percentage }}</td>
                                    <td>{{ $data->continue_production }}</td>
                                    <td>{{ $data->family }}</td>
                                    <td>{{ $data->material_group }}</td>
                                    <td>{{ $data->old_mould }}</td>
                                    <td>{{ $data->packaging }}</td>
                                    <td>{{ $data->bom_level }}</td>
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
