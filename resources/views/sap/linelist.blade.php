@extends('layouts.app')

@section('content')

<section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">Line List View</h1>
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
        @include('partials.add-new-line-modal')
        <a class="btn btn-secondary float-right" data-bs-target="#add-new-line" data-bs-toggle="modal" > add </a>
    </section>

     
{{ $dataTable->scripts() }}
@endsection


@push('extraJs')
@endpush