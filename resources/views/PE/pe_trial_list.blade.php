@extends('layouts.app')

@section('content')

<div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">FROM REQUEST TRIAL LIST</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Part Name</th>
                                <th>Model</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trial as $report)
                                <tr>
                                    <td>{{ $report->id }}</td>
                                    <td>{{ $report->customer }}</td>
                                    <td>{{ $report->part_name }}</td>
                                    <td>{{ $report->model }}</td>
                                    <td>
                                        <a href="{{ route('trial.detail', ['id' => $report->id]) }}" class="btn btn-info btn-sm">View Details</a>
                                    </td>
                                    <td>
                                        @if($report->requested_by && $report->verify_by && $report->confirmed_by_1 && $report->confirmed_by_2 && $report->confirmed_by_3 && $report->approved_by)
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