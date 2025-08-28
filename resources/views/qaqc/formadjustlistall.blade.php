@extends('layouts.app')

@section('content')
  <section class="header">
    <div class="row">
      <div class="col">
        <h1 class="h1">List Form Adjust</h1>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="card mt-5">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered table-hover table-striped text-center mb-0">
            <thead>
              <tr class="align-middle fw-semibold fs-5">
                <th class="p-3">ID</th>
                <th>Report ID</th>
                <th>Customer</th>
                <th>Invoice Name</th>
                <th>Action</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($datas as $report)
                <tr class="align-middle">
                  <td>{{ $report->id }}</td>
                  <td>{{ $report->report_id }}</td>
                  <td>{{ $report->report->customer }}</td>
                  <td>{{ $report->report->invoice_no }}</td>
                  <td>
                    <a href="{{ route('adjustview', ['report_id' => $report->report_id]) }}"
                      class="btn btn-secondary my-1 me-1 ">
                      <i class='bx bx-info-circle'></i> <span
                        class="d-none d-sm-inline ">Detail</span>
                    </a>

                    {{-- DEV ONLY --}}
                    {{-- <a href="{{ route('qaqc.report.preview', $report->id) }}"
                                            class="btn btn-primary">preview</a> --}}
                  </td>
                  <td>
                    @include('partials.formadjust-status-badge')
                  </td>
                </tr>
              @empty
                <td colspan="9">No data</td>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
@endsection
