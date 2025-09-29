@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h1"> Employee Master List </h1>
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

        @include('partials.add-new-employee-modal')
        <a class="btn btn-secondary float-right" data-bs-target="#add-new-employee" data-bs-toggle="modal">
            add </a>
    </section>

    @foreach ($datas as $data)
        @include('partials.edit-employee-modal')

        @include('partials.delete-confirmation-modal', [
            'id' => str_replace(' ', '', $data->id),
            'route' => 'deleteemployee',
            'title' => 'Delete Line confirmation',
            'body' => 'Are you sure want to delete ' . $data->id . '?',
        ])
    @endforeach

    {{ $dataTable->scripts() }}
@endsection

@push('extraJs')
@endpush
