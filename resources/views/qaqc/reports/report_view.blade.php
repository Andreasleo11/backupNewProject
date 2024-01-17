@extends('layouts.app')
<!-- Content Wrapper. Contains page content -->

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
        <!--
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Reminder</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div> 
        -->
            <div>
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark mb-5">Verification Reports</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <!-- Main content -->
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reports List</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Rec Date</th>
                                <th>Verify Date</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                <tr>
                                    <td>{{ $report->id }}</td>
                                    <td>{{ $report->invoice_no }}</td>
                                    <td>{{ $report->customer }}</td>
                                    <td>{{ $report->rec_date }}</td>
                                    <td>{{ $report->verify_date }}</td>
                                    <td>
                                        <a href="{{ route('report.detail', ['id' => $report->id]) }}" class="btn btn-info btn-sm">View Details</a>
                                    </td>
                                    <td>
                                        @if($report->autograph_1 && $report->autograph_2 && $report->autograph_3)
                                            <span style="color: green;">DONE</span>
                                        @else
                                            <span style="color: red;">NOT DONE</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div><!-- /.card-body -->
        </div><!-- /.card -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->
@endsection