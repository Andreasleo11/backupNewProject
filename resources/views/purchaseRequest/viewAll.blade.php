@extends('layouts.app')

@section('content')

    <section class="header">
            <div class="row">
                <div class="col">
                    <h1 class="h1">Purchase Requisition List</h1>
                </div>
            </div>
    </section>


    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center">
                        <thead>
                            <tr>
                                <th class="fw-semibold fs-5">No</th>
                                <th class="fw-semibold fs-5">Date PR</th>
                                <th class="fw-semibold fs-5">To Department</th>
                                <th class="fw-semibold fs-5">PR No </th>
                                <th class="fw-semibold fs-5">Supplier</th>
                                <th class="fw-semibold fs-5">Action</th>
                                <th class="fw-semibold fs-5">Status</th>
                                <th class="fw-semibold fs-5">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseRequests as $pr)
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $pr->date_pr }}</td>
                            <td>{{ $pr->to_department }}</td>
                            <td>{{ $pr->pr_no }}</td>
                            <td>{{ $pr->supplier }}</td>
                            <td>
                                <a href="{{ route('purchaserequest.detail', ['id' => $pr->id]) }}" class="btn btn-secondary">
                                    <i class='bx bx-info-circle' ></i> Detail
                                </a>
                            </td>
                            <td>
                                @if($pr->status === 0)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR PREPARATION</span>
                                    
                                @elseif($pr->status === 1)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DEPT HEAD</span>
                                   
                                @elseif($pr->status === 2)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR VERIFICATION</span>
                                @elseif($pr->attachment === null)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ATTACHMENT</span>
                                @elseif($pr->status === 3)
                                    <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DIRECTOR</span>
                                @elseif($pr->status === 4)
                                    <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
                                @elseif($pr->status === 5)
                                <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>
                                    
                                @endif
                            </td>
                            @endforeach
                        </tbody>

@endsection