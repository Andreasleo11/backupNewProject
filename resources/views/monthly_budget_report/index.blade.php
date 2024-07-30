@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = auth()->user();
    @endphp
    {{-- END GLOBAL VARIABLE --}}

    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('monthly.budget.report.index') }}">Monthly Budget
                        Reports</a>
                </li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col">
                <h2 class="fw-bold">Monthly Budget Report</h2>
            </div>
            <div class="col text-end">
                @php
                    $showCreateButton = false;
                    if (!$authUser->is_head && !$authUser->is_gm && $authUser->department->name !== 'DIRECTOR') {
                        $showCreateButton = true;
                    }
                @endphp
                @if ($showCreateButton)
                    <a href="{{ route('monthly.budget.report.create') }}" class="btn btn-primary">New Report</a>
                @endif
            </div>
        </div>

        <div class="card mt-5">
            <div class="card-body pb-0 pb-1">
                <table class="table table-border text-center mb-0">
                    <thead>
                        <tr>
                            <th>Doc. Number</th>
                            <th>Dept No</th>
                            <th>Report Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            @php
                                $reportDate = Carbon\Carbon::parse($report->report_date);
                                $formatedDate = $reportDate->format('F Y');
                            @endphp
                            <tr>
                                <td>{{ $report->doc_num }}</td>
                                <td>{{ $report->dept_no }}</td>
                                <td> @formatDate($report->report_date) </td>
                                <td>
                                    @include('partials.monthly-budget-report-status', [
                                        'status' => $report->status,
                                        'isCancel' => $report->is_cancel,
                                    ])
                                </td>
                                <td>
                                    <a href="{{ route('monthly.budget.report.show', $report->id) }}"
                                        class="btn btn-secondary">Detail</a>
                                    @if (auth()->user()->id === $report->user->id)
                                        @if (!$report->created_autograph)
                                            <a href="{{ route('monthly.budget.report.edit', $report->id) }}"
                                                class="btn btn-primary">Edit</a>
                                            @include('partials.delete-confirmation-modal', [
                                                'id' => $report->id,
                                                'route' => 'monthly.budget.report.delete',
                                                'title' => 'Delete report confirmation',
                                                'body' => "Are you sure want to delete this report with id <strong>$report->id</strong>?",
                                            ])

                                            <button class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#delete-confirmation-modal-{{ $report->id }}">Delete</button>
                                        @elseif(!$report->is_cancel)
                                            @include('partials.cancel-confirmation-modal', [
                                                'id' => $report->id,
                                                'route' => route('monthly.budget.report.cancel', $report->id),
                                            ])
                                            <button class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#cancel-confirmation-modal-{{ $report->id }}">Cancel</button>
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
