@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <h1>Project Tracker</h1>
        </div>
        <div class="col text-end">
            <a class="btn btn-primary" href="{{ route('pt.create') }}">+ Create</a>
        </div>
    </div>

    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center mb-0">
                        <thead>
                            <tr class="align-middle fw-semibold fs-5">
                                <th class="p-3">Project Name</th>
                                <th>PIC</th>
                                <th>Request Date</th>
                                <th>Action</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($datas as $report)
                                <tr class="align-middle">
                                    <td>{{ $report->project_name }}</td>
                                    <td>{{ $report->pic }}</td>
                                    <td>{{ $report->request_date }}</td>
                                    <td>
                                        <a href=" {{ route('pt.detail', ['id' => $report->id]) }}"
                                            class="btn btn-secondary my-1 me-1">
                                            <i class='bx bx-info-circle'></i> <span
                                                class="d-none d-sm-inline ">Detail</span>
                                        </a>
                                    </td>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>
@endsection
