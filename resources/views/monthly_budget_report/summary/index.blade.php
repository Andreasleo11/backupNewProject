@extends('layouts.app')

@section('content')
  {{-- GLOBAL VARIABLE --}}
  @php
    $authUser = auth()->user();
  @endphp
  {{-- END GLOBAL VARIABLE --}}

  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('monthly-budget-summary-report.index') }}">Monthly
            Budget
            Summary Reports</a>
        </li>
        <li class="breadcrumb-item active">List</li>
      </ol>
    </nav>
    <div class="row">
      <div class="col">
        <h2 class="fw-bold">Monthly Budget
          Summary Reports</h2>
      </div>
      <div class="col text-end">
        @php
          $showGenerateButton = false;
          if (
              !$authUser->is_head &&
              !$authUser->is_gm &&
              $authUser->department->name !== 'MANAGEMENT'
          ) {
              $showGenerateButton = true;
          }
        @endphp
        @if ($showGenerateButton)
          <form action="{{ route('monthly.budget.summary.report.store') }}" method="post"
            class="row row-cols-lg-auto g-3 align-items-center justify-content-end">
            @csrf
            <input type="hidden" name="created_autograph"
              value="{{ ucwords(auth()->user()->name) }}">
            <div class="col-12">
              <input type="text" id="monthPicker" name="month" class="form-control"
                placeholder="Select Month" required>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary">Generate</button>
            </div>
          </form>
        @endif
      </div>
    </div>

    <div class="card mt-5">
      <div class=card-body>
        <table class="table text-center mb-0 align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Doc. Number</th>
              <th>Report Date</th>
              <th>Total Amount</th>
              <th>Comparison with Prev Month</th>
              <th>Created At</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody class="table-group-divider">
            @forelse ($reports as $report)
              @php
                $reportDate = Carbon\Carbon::parse($report->report_date);
                $monthYear = $reportDate->format('F Y');

                $createdAt = Carbon\Carbon::parse($report->created_at);
                $formattedCreatedAt = $createdAt->format('d/m/Y (H:i:s)');
              @endphp
              <tr>
                <th>{{ $loop->iteration }}</th>
                <td>{{ $report->doc_num }}</td>
                <td>{{ $monthYear }}</td>
                <td class="text-end fw-semibold">
                  {{ number_format($report->total_amount, 0, ',', '.') }}
                </td>
                <td class="text-end">
                  @php($m = $report->mom)
                  @if(!$m['has_prev'])
                    <span class="text-muted">—</span>
                  @elseif($m['direction'] === 'up')
                    <span class="badge bg-success-subtle border border-success-subtle text-success" title="Prev: {{ number_format($m['prev'], 0, ',', '.') }}">
                      <i class='bx bx-trending-up'></i>
                      {{ number_format($m['diff'], 0, ',', '.') }} ({{ number_format($m['pct'], 2, ',', '.') }}%)
                    </span>
                  @elseif($m['direction'] === 'down')
                    <span class="badge bg-danger-subtle border border-danger-subtle text-danger" title="Prev: {{ number_format($m['prev'], 0, ',', '.') }}">
                      <i class='bx bx-trending-down'></i>
                      {{ number_format($m['diff'], 0, ',', '.') }} ({{ number_format($m['pct'], 2, ',', '.') }}%)
                    </span>
                  @else
                    <span class="badge bg-secondary-subtle border border-secondary-subtle text-secondary" title="Prev: {{ number_format($m['prev'], 0, ',', '.') }}">
                      <i class='bx bx-minus'></i> 0 (0%)
                    </span>
                  @endif
                </td>
                <td>{{ $formattedCreatedAt }}</td>
                <td>
                  @include('partials.monthly-budget-summary-report-status', [
                      'status' => $report->status,
                  ])
                </td>
                <td>
                  <a href="{{ route('monthly.budget.summary.report.show', $report->id) }}"
                    class="btn btn-secondary"><i class='bx bx-info-circle'></i> Detail</a>
                  @include('partials.delete-confirmation-modal', [
                      'id' => $report->id,
                      'route' => 'monthly.budget.summary.report.delete',
                      'title' => 'Delete report confirmation',
                      'body' => "Are you sure want to delete report <strong>$report->doc_num</strong>?",
                  ])
                  @if ($authUser->id == $report->creator_id)
                    @if ($report->status === 1)
                      <button class="btn btn-danger" data-bs-toggle="modal"
                        data-bs-target="#delete-confirmation-modal-{{ $report->id }}"><i
                          class='bx bx-trash-alt'></i> Delete</button>
                    @elseif($report->status === 2 || $report->status === 3 || $report->status === 4)
                      @include('partials.cancel-confirmation-modal', [
                          'id' => $report->id,
                          'route' => route(
                              'monthly.budget.summary.report.cancel',
                              $report->id),
                      ])
                      <button class="btn btn-outline-danger" data-bs-toggle="modal"
                        data-bs-target="#cancel-confirmation-modal-{{ $report->id }}"><i
                          class='bx bx-x-circle'></i> Cancel</button>
                    @endif
                  @endif

                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5">No data</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="d-flex justify-content-end mt-3">
      {{ $reports->links() }}
    </div>
  </div>
@endsection
@push('extraJs')
  <script type="module">
    // Initialize the month picker
    $('#monthPicker').datepicker({
      format: "mm-yyyy",
      startView: "months",
      minViewMode: "months",
      autoclose: true
    });
  </script>
@endpush
