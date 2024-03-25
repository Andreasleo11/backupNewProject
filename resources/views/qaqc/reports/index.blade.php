@extends('layouts.app')

@section('content')
    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">Verification Reports</h1>
            </div>
            <div class="col text-end">
                @php
                    $currentUser = Auth::user();
                @endphp
                @if ($currentUser->department->name == 'QC' && $currentUser->specification->name == 'INSPECTOR')
                    <a href="{{ route('qaqc.report.create') }}" class="btn btn-primary">
                        <i class='bx bx-plus'></i> Add <span class="d-none d-sm-inline">Report</span>
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('qaqc.home') }}">Home</a></li>
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
                                <th class="p-3">Doc. Number</th>
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
                            @forelse ($reports as $report)
                                <tr class="align-middle">
                                    <td>{{ $report->doc_num }}</td>
                                    <td>{{ $report->invoice_no }}</td>
                                    <td>{{ $report->customer }}</td>
                                    <td> @formatDate($report->rec_date)</td>
                                    <td> @formatDate($report->verify_date)</td>
                                    <td>
                                        <a href="{{ route('qaqc.report.detail', $report->id) }}"
                                            class="btn btn-secondary my-1 me-1 ">
                                            <i class='bx bx-info-circle'></i> <span
                                                class="d-none d-sm-inline ">Detail</span>
                                        </a>

                                        @php
                                            $hoursDifference = Date::now()->diffInHours($report->rejected_at);
                                        @endphp

                                        <form class="d-none" action="{{ route('qaqc.report.rejectAuto', $report->id) }}"
                                            method="get" id="form-reject-report-{{ $report->id }}"><input type="hidden"
                                                name="description" value="Automatically rejected after 24 hours"></form>

                                        <script>
                                            @if ($hoursDifference > 24 && $report->is_approve === 2 && $report->is_locked == false)
                                                document.getElementById('form-reject-report-{{ $report->id }}').submit();
                                            @endif
                                        </script>

                                        <a href="{{ route('qaqc.report.edit', $report->id) }}"
                                            class="btn btn-primary my-1 me-1 @if (
                                                $report->created_by !== Auth::user()->name ||
                                                    $hoursDifference > 24 ||
                                                    $report->is_approve == 1 ||
                                                    $report->is_locked) d-none @endif">
                                            <i class='bx bx-edit'></i> <span class="d-none d-sm-inline">Edit</span>
                                        </a>


                                        @include('partials.delete-report-modal')
                                        <button
                                            class="btn btn-danger my-1 me-1 @if (
                                                $report->created_by !== Auth::user()->name ||
                                                    $hoursDifference > 24 ||
                                                    $report->autograph_3 ||
                                                    $report->is_approve == 1 ||
                                                    $report->is_locked) d-none @endif"
                                            data-bs-toggle="modal"
                                            data-bs-target="#delete-report-modal{{ $report->id }}">
                                            <i class='bx bx-trash-alt'></i> <span class="d-none d-sm-inline">Delete</span>
                                        </button>

                                        @include('partials.lock-report-confirmation-modal')

                                        <div class="btn-group" role="group">

                                            <button type="button"
                                                class="btn text-success border border-success dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                More
                                            </button>

                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href="{{ route('qaqc.report.download', $report->id) }}"
                                                        class="btn btn-success my-1 dropdown-item">
                                                        <i class='bx bxs-file-pdf'></i> <span
                                                            class="d-none d-sm-inline">Export PDF</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="btn btn-success dropdown-item @if ($report->is_locked || $report->is_approve) disabled @endif"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#lock-report-modal-confirmation-{{ $report->id }}">
                                                        <i class='bx bxs-lock'></i>
                                                        Lock
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        @include('partials.vqc-status-badge')
                                    </td>
                                    <td>{{ $report->description }}</td>
                                </tr>
                            @empty
                                <td colspan="8">No data</td>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if ($reports != null)
            <div class="d-flex justify-content-end mt-3">
                {{ $reports->appends(['status' => $status])->links() }}
            </div>
        @endif
    </section>
@endsection
