@extends('layouts.app')

@section('content')

    <section class="header">
        <div class="row align-items-center">
            <div class="col-auto">
                <h1 class="h1"> Holiday List </h1>
            </div>
            <div class="col text-end">
                @include('partials.add-new-holiday-modal')
                <a class="btn btn-primary" data-bs-target="#add-new-holiday" data-bs-toggle="modal"> + Tambah </a>
                @include('partials.holiday-list-template-modal')
                <button data-bs-target="#holiday-list-template" data-bs-toggle="modal" class="btn btn-outline-primary">Holiday
                    List Template</button>
            </div>
        </div>
    </section>

    @include('partials.alert-success-error')

    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center mb-0">
                        <thead>
                            <tr class="align-middle fw-semibold fs-5">
                                <th>Tanggal</th>
                                <th>Nama Libur</th>
                                <th>Deskripsi</th>
                                <th>Half Day</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($datas->isEmpty())
                                <tr>
                                    <td colspan="8">DATA UNAVAILABLE</td>
                                </tr>
                            @else
                                <!-- Loop through $data and display the rows -->
                                @foreach ($datas as $item)
                                    <tr>
                                        <td>{{ $item->date }}</td>
                                        <td>{{ $item->holiday_name }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->half_day }}</td>
                                        <td>

                                            @include('partials.edit-holiday-modal')
                                            <button data-bs-target="#edit-holiday-modal-{{ $item->id }}"
                                                data-bs-toggle="modal" class="btn btn-primary my-1 me-1">
                                                <i class='bx bx-edit'></i> <span class="d-none d-sm-inline">Edit</span>
                                            </button>


                                            @include('partials.delete-confirmation-modal', [
                                                'id' => $item->id,
                                                'title' => 'Delete holiday confirmation',
                                                'body' =>
                                                    'Are you sure want to delete ' . $item->holiday_name . '?',
                                            ])
                                            <button class="btn btn-danger my-1 me-1" data-bs-toggle="modal"
                                                data-bs-target="#delete-confirmation-modal-{{ $item->id }}">
                                                <i class='bx bx-trash-alt'></i> <span
                                                    class="d-none d-sm-inline">Delete</span>
                                            </button>
                                        </td>
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
