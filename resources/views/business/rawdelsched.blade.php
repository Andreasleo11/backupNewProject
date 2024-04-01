@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">DELIVERY SCHEDULE (RAW) </h1>
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

        <div class="mt-3">
            <a href="{{ route('indexds') }}" class="btn btn-secondary"> Back</a>
        </div>
    </section>
@endsection


@push('extraJs')
    {{ $dataTable->scripts() }}
@endpush
