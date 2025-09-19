@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row mb-3">
      <div class="col">
        <h3>Form Request Trials</h3>
      </div>
      <div class="col text-end">
        <a class="btn btn-primary " href="{{ route('pe.trial') }}"> + Add Request Trial</a>
      </div>
    </div>
    <div class="card">
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
                    <a href="{{ route('trial.detail', ['id' => $report->id]) }}"
                      class="btn btn-info btn-sm">View Details</a>
                  </td>
                  <td>
                    @if (
                        $report->autograph_1 &&
                            $report->autograph_2 &&
                            $report->autograph_3 &&
                            $report->autograph_4 &&
                            $report->autograph_6)
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
