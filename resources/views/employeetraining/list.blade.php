@extends('layouts.app')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row d-flex">
    <div class="col">
        <h1 class="h1">Employee Training List</h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('training.create') }}" class="btn btn-primary">Add Training </a>
    </div>
</div>

<section class="header">
    <div class="row">
        <div class="col">

        </div>
    </div>
</section>


<section class="content">
<div class="card mt-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped text-center mb-0">
                <thead>
                    <tr>
                        <th class="fw-semibold fs-5">No</th>
                        <th class="fw-semibold fs-5">Doc Num</th>
                        <th class="fw-semibold fs-5">Name</th>
                        <th class="fw-semibold fs-5">Nik </th>
                        <th class="fw-semibold fs-5">Department</th>
                        <th class="fw-semibold fs-5">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $da)
                        <tr class="align-middle">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $da->doc_num }}</td>
                            <td>{{ $da->name }}</td>
                            <td>{{ $da->nik }}</td>
                            <td>{{ $da->department }}</td>
                            <td>
                                <a href="{{ route('training.detail', ['id' => $da->id]) }}" class="btn btn-secondary">
                                    <i class='bx bx-info-circle' ></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No Data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</section>


{{-- <div style="width: 30%;" class="m-auto mt-4">
    <canvas id="departmentChart" width="10" height="10 "></canvas>
</div> --}}

@endsection
