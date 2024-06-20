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
            <div class=card-body>
                <table class="table table-border text-center mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Dept No</th>
                            <th>Report Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>{{ $report->dept_no }}</td>
                                <td>{{ $report->report_date }}</td>
                                <td>
                                    @if ($report->approved_autograph)
                                        <span class="badge text-bg-success px-3 py-2 fs-6">Approved</span>
                                    @elseif($report->is_known_autograph)
                                        @if ($report->department->name === 'QA' || $report->department->name === 'QC')
                                            <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting Director</span>
                                        @else
                                            <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting GM</span>
                                        @endif
                                    @elseif($report->created_autograph)
                                        <span class="badge text-bg-secondary px-3 py-2 fs-6">Waiting Dept Head</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('monthly.budget.report.detail', $report->id) }}"
                                        class="btn btn-secondary">Detail</a>
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
