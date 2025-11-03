@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">FORECAST CUSTOMER MASTER DATA</h1>
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

        @include('partials.add-new-forecastcustomer-modal')
        <a class="btn btn-secondary float-right" data-bs-target="#add-new-forecastcustomer" data-bs-toggle="modal"> add </a>
    </section>

    {{ $dataTable->scripts() }}
@endsection
