@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">DELIVERY SCHEDULE</h1>
            </div>
            <div class="col-auto">
                <a href="{{ route('delsched.averagemonth') }}" class="btn btn-secondary"> Average PerMonth</a>
                <a href="{{ route('deslsched.step1') }}" class="btn btn-primary"> Update</a>
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

        <div class="d-flex justify-content-between mt-3 ">
            <a href="{{ route('indexfinalwip') }}" class="btn btn-secondary"> Delivery Schedule (WIP)</a>
            <a href="{{ route('rawdelsched') }}" class="btn btn-secondary"> Delivery Schedule (RAW)</a>
        </div>
    </section>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}
@endpush
