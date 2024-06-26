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
                                $reportDate = Carbon\Carbon::parse($report->report_date);
                                $monthYear = $reportDate->format('F Y');

                                $createdAt = Carbon\Carbon::parse($report->created_at);
                                $formattedCreatedAt = $createdAt->format('d/m/Y (H:i:s)');
                            @endphp
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>{{ $monthYear }}</td>
                                <td>{{ $formattedCreatedAt }}</td>
                                <td>
                                    @include('partials.monthly-budget-summary-report-status')
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
