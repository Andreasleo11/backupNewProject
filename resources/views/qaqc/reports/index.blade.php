@extends('layouts.app')
<!-- Content Wrapper. Contains page content -->

@section('content')

    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">Verification Reports</h1>
            </div>
            <div class="col text-end">
                <a href="{{route('qaqc.report.create')}}" class="btn btn-primary">
                    <i class='bx bx-plus' ></i> Add <span class="d-none d-sm-inline">Report</span>
                </a>
            </div>
        </div>
    </section>

    <section>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('qaqc.home')}}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reports</li>
            </ol>
        </nav>
    </section>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center mb-0">
                        <thead>
                            <tr class="align-middle fw-semibold fs-5">
                                <th>No</th>
                                <th>Doc. Number</th>
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Rec Date</th>
                                <th>Verify Date</th>
                                <th>Action</th>
                                <th>Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                <tr class="align-middle">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $report->doc_num }}</td>
                                    <td>{{ $report->invoice_no }}</td>
                                    <td>{{ $report->customer }}</td>
                                    <td>{{ $report->rec_date }}</td>
                                    <td>{{ $report->verify_date }}</td>
                                    <td>
                                        <a href="{{ route('qaqc.report.detail', $report->id) }}" class="btn btn-secondary my-1">
                                            <i class='bx bx-info-circle' ></i> <span class="d-none d-sm-inline ">Detail</span>
                                        </a>

                                        <a href="{{ route('qaqc.report.edit', $report->id) }}" class="btn btn-primary my-1">
                                            <i class='bx bx-edit' ></i> <span class="d-none d-sm-inline">Edit</span>
                                        </a>

                                        <form action="{{ route('qaqc.report.delete', $report->id) }}" method="post" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger my-1">
                                                <i class='bx bx-trash-alt' ></i> <span class="d-none d-sm-inline">Delete</span>
                                            </button>
                                        </form>

                                        <a href="{{ route('qaqc.report.download', $report->id) }}" class="btn btn-success my-1">
                                            <i class='bx bxs-file-pdf' ></i> <span class="d-none d-sm-inline">Export PDF</span>
                                        </a>

                                        @if($report->attachment)
                                            @php
                                                $filename = basename($report->attachment);
                                            @endphp

                                            <a href="{{ asset('storage/attachments/' . $report->attachment) }}" download="{{ $filename }}" class="btn border border-success text-success fw-semibold my-1">
                                                <i class='bx bx-download' ></i> <span class="d-none d-sm-inline">Download Attachment</span>
                                            </a>
                                        @endif

                                    </td>
                                    <td>
                                        @if($report->autograph_1 && $report->autograph_2 && $report->autograph_3 && $report->is_approve === 1)
                                            <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
                                        @elseif($report->is_approve === 0)
                                            <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>
                                        @elseif($report->autograph_1 && $report->autograph_2 && $report->autograph_3)
                                            <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ON APPROVAL</span>
                                        @else
                                            <span class="badge text-bg-secondary px-3 py-2 fs-6">WAITING SIGNATURE</span>
                                        @endif
                                    </td>
                                    <td>{{ $report->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

@endsection