@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('formovertime.index') }}">Form Overtime</a>
            </li>
            <li class="breadcrumb-item active">List</li>
        </ol>
    </nav>
    <div class="row d-flex">
        <div class="col">
            <h1 class="h1 fw-bold">Form Overtime List</h1>
        </div>
        <div class="col-auto">
            @if (Auth::user()->department->name !== 'MANAGEMENT')
                <a href="{{ route('formovertime.create') }}" class="btn btn-primary">Create Form Overtime </a>
            @endif
        </div>
    </div>
    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center mb-0">
                        <thead>
                            <tr>
                                <th class="fw-semibold fs-5">ID</th>
                                <th class="fw-semibold fs-5">Admin</th>
                                <th class="fw-semibold fs-5">Dept</th>
                                <th class="fw-semibold fs-5">Create Overtime Date</th>
                                <th class="fw-semibold fs-5">Status</th>
                                <th class="fw-semibold fs-5">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dataheader as $fot)
                                <tr class="align-middle">
                                    <td>{{ $fot->id }}</td>
                                    <td>{{ $fot->Relationuser->name }}</td>
                                    <td>{{ $fot->Relationdepartement->name }}</td>
                                    <td> @formatDate($fot->create_date) </td>
                                    <td>
                                        @include('partials.formovertime-status', ['fot' => $fot])
                                    </td>
                                    <td>
                                        <a href="{{ route('formovertime.detail', ['id' => $fot->id]) }}"
                                            class="btn btn-secondary">
                                            <i class='bx bx-info-circle'></i> Detail
                                        </a>
                                        @include('partials.delete-confirmation-modal', [
                                            'id' => $fot->id,
                                            'title' => 'Delete Form Overtime',
                                            'body' => "Are your sure want to delete this report with <strong>id = $fot->id </strong>?",
                                            'route' => 'formovertime.delete',
                                        ])
                                        <button data-bs-toggle="modal"
                                            data-bs-target="#delete-confirmation-modal-{{ $fot->id }}"
                                            class="btn btn-danger">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">No Data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
