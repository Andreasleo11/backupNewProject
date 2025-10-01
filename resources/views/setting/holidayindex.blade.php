@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h1"> Holiday List </h1>
            </div>
            <div class="col text-end">
                @include('partials.add-new-holiday-modal')
                <a class="btn btn-primary" data-bs-target="#add-new-holiday" data-bs-toggle="modal"> + Tambah
                </a>
                @include('partials.holiday-list-template-modal')
                <button data-bs-target="#holiday-list-template" data-bs-toggle="modal" class="btn btn-outline-primary">Holiday
                    List Template</button>
            </div>
        </div>
    </section>

    @include('partials.alert-success-error')

    <section class="content">
        <div class="card mt-5">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>

        @foreach ($datas as $item)
            @include('partials.edit-holiday-modal')
            @include('partials.delete-confirmation-modal', [
                'id' => $item->id,
                'route' => 'holiday.delete',
                'title' => 'Delete holiday confirmation',
                'body' => 'Are you sure want to delete ' . $item->holiday_name . '?',
            ])
        @endforeach
    </section>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}
@endpush
