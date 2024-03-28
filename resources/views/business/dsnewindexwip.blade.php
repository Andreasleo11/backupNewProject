@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">DELIVERY SCHEDULE (WIP) </h1>
            </div>
            <div class="col-auto">

                <a href="{{ route('delschedwip.step1') }}" class="btn btn-primary"> Update</a>
            </div>
        </div>
    </section>


    <section class="content">
        <div class="card mt-5">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>

        <div class="text-end mt-3">
            <a href="{{ route('indexds') }}" class="btn btn-secondary"> Delivery Schedule </a>
        </div>
    </section>
@endsection


@push('extraJs')
    {{ $dataTable->scripts() }}
@endpush
