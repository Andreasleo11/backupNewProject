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
                <li class="breadcrumb-item"><a href="{{ route('monthly.budget.summary.report.index') }}">Monthly Budget
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
                    if (!$authUser->is_head && !$authUser->is_gm && $authUser->department->name !== 'DIRECTOR') {
                        $showGenerateButton = true;
                    }
                @endphp
                @if ($showGenerateButton)
                    <form action="{{ route('monthly.budget.summary.report.store') }}" method="post"
                        class="row row-cols-lg-auto g-3 align-items-center justify-content-end">
                        @csrf
                        <input type="hidden" name="created_autograph" value="{{ ucwords(auth()->user()->name) }}">
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
                <table class="table table-border text-center mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Report Date</th>
                            <th>Created At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            @php
                                // Extract the month name
                                $reportDate = Carbon\Carbon::parse($report->report_date);
                                $monthName = $reportDate->format('F'); // Full month name
                                $year = $reportDate->format('Y'); // Year
                                $monthYear = $monthName . ' ' . $year;

                                $dateString = $report->created_at;
                                // Parse the date string into a Carbon instance
                                $carbonDate = Carbon\Carbon::parse($dateString);
                                // Format the date as dd-mm-yyyy
                                $formattedCreatedAt = $carbonDate->format('d/m/Y (H:i:s)'); // Output: dd-mm-yyyy
                            @endphp
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>{{ $monthYear }}</td>
                                <td>{{ $formattedCreatedAt }}</td>
                                <td>
                                    @if ($report->approved_autograph)
                                        <span class="badge text-bg-success px-3 py-2 fs-6">Approved</span>
                                    @elseif($report->is_known_autograph)
                                        <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting for Director</span>
                                    @elseif($report->created_autograph)
                                        <span class="badge text-bg-secondary px-3 py-2 fs-6">Waiting for GM</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('monthly.budget.summary.report.show', $report->id) }}"
                                        class="btn btn-secondary">Detail</a>
                                    @include('partials.delete-confirmation-modal', [
                                        'id' => $report->id,
                                        'route' => 'monthly.budget.summary.report.delete',
                                        'title' => 'Delete report confirmation',
                                        'body' => "Are you sure want to delete this report with id = <strong>$report->id</strong>?",
                                    ])
                                    <button class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#delete-confirmation-modal-{{ $report->id }}">Delete</button>
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
