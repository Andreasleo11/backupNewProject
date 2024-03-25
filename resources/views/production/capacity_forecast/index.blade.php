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
            <div class="card-body p-0">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
        <a href="{{ route('capacityforecastline') }}" class="btn btn-secondary float-right"> Line</a>
        <a href="{{ route('capacityforecastdistribution') }}" class="btn btn-secondary float-right"> Distribution</a>
        <a href="{{ route('capacityforecastdetail') }}" class="btn btn-secondary float-right"> Detail</a>
    </section>

 
{{ $dataTable->scripts() }}
@endsection