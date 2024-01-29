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
                    <i class='bx bx-plus' ></i> Add Report
                </a>
            </div>
        </div>
    </section>

    <section>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('staff.home')}}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reports</li>
            </ol>
        </nav>
    </section>

    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center">
                        <thead>
                            <tr>
                                <th class="fw-semibold fs-5">No</th>
                                <th class="fw-semibold fs-5">Invoice No</th>
                                <th class="fw-semibold fs-5">Customer</th>
                                <th class="fw-semibold fs-5">Rec Date</th>
                                <th class="fw-semibold fs-5">Verify Date</th>
                                <th class="fw-semibold fs-5">Action</th>
                                <th class="fw-semibold fs-5">Status</th>
                                <th class="fw-semibold fs-5">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $report->invoice_no }}</td>
                                    <td>{{ $report->customer }}</td>
                                    <td>{{ $report->rec_date }}</td>
                                    <td>{{ $report->verify_date }}</td>
                                    <td class="text-start">
                                        <a href="{{ route('qaqc.report.detail', ['id' => $report->id]) }}" class="btn btn-secondary">
                                            <i class='bx bx-info-circle' ></i> Detail
                                        </a>
                                        <a href="{{ route('qaqc.report.edit', $report->id) }}" class="btn btn-primary">
                                            <i class='bx bx-edit' ></i> Edit
                                        </a>

                                        <form action="{{ route('qaqc.report.delete', $report->id) }}" method="post" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class='bx bx-trash-alt' ></i> Delete
                                            </button>
                                        </form>

                                        @if($report->attachment)
                                            @php
                                                $filename = basename($report->attachment);
                                            @endphp
                                            <a href="{{ asset('storage/attachments/' . $report->attachment) }}" class="btn btn-primary" download="{{ $filename }}">
                                                <i class='bx bx-download'></i> Download
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($report->autograph_1 && $report->autograph_2 && $report->autograph_3 && $report->is_approve === 1)
                                            <span class="badge text-bg-success px-3 py-2 fs-6 fw-light">APPROVED</span>

                                        @elseif($report->is_approve === 0)
                                            <span class="badge text-bg-danger px-3 py-2 fs-6 fw-light">REJECTED</span>
                                        @elseif($report->autograph_1 && $report->autograph_2 && $report->autograph_3)
                                            <span class="badge text-bg-warning px-3 py-2 fs-6 fw-light">WAITING ON APPROVAL</span>
                                        @else
                                            <span class="badge text-bg-warning px-3 py-2 fs-6 fw-light">WAITING SIGNATURE</span>
                                        @endif
                                    </td>
                                    <td>{{ $report->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div><!-- /.card-body -->
        </div>
    </section>



@endsection
