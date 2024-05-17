@extends('layouts.app')

@section('content')

    <div class="row d-flex">
        <div class="col">
            <h1 class="h1">Form Overtime List</h1>
        </div>
        <div class="col-auto">
            @if (Auth::user()->department->name !== 'DIRECTOR')
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
                                <th class="fw-semibold fs-5">No</th>
                                <th class="fw-semibold fs-5">Admin</th>
                                <th class="fw-semibold fs-5">Dept</th>
                                <th class="fw-semibold fs-5">Create Overtime Date</th>
                                <th class="fw-semibold fs-5">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dataheader as $fot)
                                <tr class="align-middle">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $fot->Relationuser->name }}</td>
                                    <td>{{ $fot->Relationdepartement->name }}</td>
                                    <td> @formatDate($fot->create_date) </td>
                                    <td>
                                        <a href="{{ route('formovertime.detail', ['id' => $fot->id]) }}"
                                            class="btn btn-secondary">
                                            <i class='bx bx-info-circle'></i> Detail
                                        </a>
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