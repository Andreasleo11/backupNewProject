@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">Monthly Purchase Requisition List</h1>
            </div>
            <div class="col-auto">
                <a href="{{ route('purchaserequest.monthly') }}" class="btn btn-primary">GENERATE MONTHLY PR
                </a>
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
                                <th class="fw-semibold fs-5">MONTH PR</th>
                                <th class="fw-semibold fs-5">YEAR PR</th>
                                <th class="fw-semibold fs-5">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthlist as $pr)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ date('F', mktime(0, 0, 0, $pr->month, 1)) }}</td>
                                    <td>{{ $pr->year }}</td>
                                    <td>
                                        <a href="{{ route('purchaserequest.monthlydetail', ['id' => $pr->id]) }}"
                                            class="btn btn-secondary">
                                            <i class='bx bx-info-circle'></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endsection
