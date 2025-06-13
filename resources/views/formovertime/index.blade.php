@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')

    <form method="GET" action="{{ route('formovertime.index') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
            <label for="date" class="form-label">Overtime Date</label>
            <input type="date" class="form-control" name="date" id="date" value="{{ request('date') }}">
        </div>

        <div class="col-md-3">
            <label for="dept" class="form-label">Department</label>
            <select class="form-select" name="dept" id="dept">
                <option value="">-- All --</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('dept') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if (Auth::user()->specification->name == 'VERIFICATOR')
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">-- Semua --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>SELESAI</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>BELUM SELESAI</option>
                </select>
            </div>
        @endif

        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>


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
                                <th class="fw-semibold fs-5">Overtime Date</th>
                                <th class="fw-semibold fs-5">Status</th>
                                <th class="fw-semibold fs-5">Is Planned?</th>
                                <th class="fw-semibold fs-5">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dataheader as $fot)
                                <tr class="align-middle">
                                    <td>{{ $fot->id }}</td>
                                    <td>{{ $fot->user->name }}</td>
                                    <td>{{ $fot->department->name }}</td>
                                    <td> @formatDate($fot->create_date) </td>
                                    <td>
                                        @include('partials.formovertime-status', ['fot' => $fot])
                                        @if ($fot->is_push == 1)
                                            <span class="text-success">
                                                <i class="bx bx-check-circle me-1" title="Pushed to JPayroll"></i>
                                                Finish by Bu Bernadett
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- planned / urgent badge --}}
                                        <span
                                            class="px-3 py-2 fs-6 badge {{ $fot->is_planned ? 'text-secondary border border-secondary' : 'text-danger border border-danger' }}">
                                            {{ $fot->is_planned ? 'Planned' : 'Urgent' }}
                                        </span>
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
