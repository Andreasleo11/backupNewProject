@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    <section class="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('monthly.budget.report.index') }}">Monthly Budget Reports</a>
                </li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </section>

    <style>
        .autograph-box {
            width: 200px;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid #ccc;
        }
    </style>

    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = Auth::user();
    @endphp

    <section class="autographs">
        @include('partials.monthly-budget-report-autograph')
    </section>

    <section aria-label="report">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="h2 fw-bold mt-4">Monthly Budget Report</div>
                            <div class="fs-6 mt-2">
                                <div class="fs-6 text-secondary">From Department : {{ $report->department->name }}
                                    ({{ $report->dept_no }})</div>
                                <div class="fs-6 text-secondary">Created By : {{ $report->user->name }}</div>
                                <div class="mt-1">
                                    @include('partials.monthly-budget-report-status', [
                                        'status' => $report->status,
                                        'isCancel' => $report->is_cancel,
                                    ])
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-body">
                                <table class="table text-center">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            @if ($report->dept_no == 363)
                                                <th>Spec</th>
                                            @endif
                                            <th>UoM</th>
                                            @if ($report->dept_no == 363)
                                                <th>Last Recorded Stock</th>
                                                <th>Usage Per Month</th>
                                            @endif
                                            <th>Quantity</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report->details as $detail)
                                            <tr>
                                                <td>{{ $detail->name }}</td>
                                                @if ($report->dept_no == 363)
                                                    <td>{{ $detail->spec }}</td>
                                                @endif
                                                <td>{{ $detail->uom }}</td>
                                                @if ($report->dept_no == 363)
                                                    <td>{{ $detail->last_recorded_stock }}</td>
                                                    <td>{{ $detail->usage_per_month }}</td>
                                                @endif
                                                <td>{{ $detail->quantity }}</td>
                                                <td>{{ $detail->remark }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $report->dept_no == 363 ? '7' : '4' }}">No data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
