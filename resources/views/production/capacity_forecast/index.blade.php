@extends('layouts.app')

@section('content')
    
<section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1"> Capacity By Forecast </h1>

                <a href="{{ route('viewstep1') }}" class="btn btn-secondary float-right"> Mulai Proses</a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card mt-5">
            <h3> Daftar Capacity By Forecast {{ $startdate }}</h3>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center mb-0">
                        <thead>
                            <tr class="align-middle fw-semibold fs-5">
                                <th class="p-3">Line Category</th>
                                <th>Line Quantity</th>
                                <th>Work Day</th>
                                <th>Ready Time(H)</th>
                                <th>Efficiency</th>
                                <th>Max Capacity</th>
                                <th>Capacity Req (H)</th>
                                <th>Capacity Req (%)</th>
                                </tr>
                        </thead>
                            <tbody>
                            @if($data->isEmpty())
                            <tr>
                                <td colspan="8">DATA UNAVAILABLE</td>
                            </tr>
                            @else
                            <!-- Loop through $data and display the rows -->
                            @foreach($data as $item)
                            <tr>
                                <td>{{ $item->line_category }}</td>
                                <td>{{ $item->line_quantity }}</td>
                                <td>{{ $item->work_day }}</td>
                                <td>{{ $item->ready_time }}</td>
                                <td>{{ $item->efficiency }}</td>
                                <td>{{ $item->max_capacity }}</td>
                                <td>{{ $item->capacity_req_hour }}</td>
                                <td>{{ $item->capacity_req_percentage }}</td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection