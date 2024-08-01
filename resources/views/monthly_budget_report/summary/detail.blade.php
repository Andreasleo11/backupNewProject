@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    <section class="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('monthly.budget.summary.report.index') }}">Monthly Budget
                        Summary Reports</a>
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

        /* Optional: Add styling for merged rows */
        .merged-row {
            font-style: italic;
            color: #888;
        }
    </style>

    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = Auth::user();
    @endphp

    <section class="autographs">
        @include('partials.monthly-budget-summary-report-autograph')
    </section>
    <section aria-label="report">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="h2 fw-bold mt-4">Monthly Budget Summary Report</div>
                            <div class="fs-6 mt-2">
                                <div class="fs-6 text-secondary">Created At : {{ $formattedCreatedAt }}</div>
                                <div class="fs-6 text-secondary">Month : {{ $monthYear }} </div>
                                <div class="mt-1">
                                    @include('partials.monthly-budget-summary-report-status', [
                                        'status' => $report->status,
                                    ])
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-center table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Dept</th>
                                                <th>Quantity</th>
                                                <th>UoM</th>
                                                <th>Supplier</th>
                                                <th>Cost Per Unit</th>
                                                <th>Total Cost</th>
                                                <th>Remark</th>
                                                @if (($report->status === 1 && $report->user->id === $authUser->id) || ($report->status === 2 && $authUser->is_gm === 1))
                                                    <th>Action</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $rowIndex = 0; // Initialize row index
                                            @endphp
                                            @foreach ($groupedDetailsForView as $index => $group)
                                                @php
                                                    $rowspanCount = count($group['items']); // Calculate rowspan for the name column
                                                @endphp
                                                @foreach ($group['items'] as $itemIndex => $item)
                                                    @php
                                                        $totalCost = $item['quantity'] * $item['cost_per_unit'];
                                                    @endphp
                                                    <tr>
                                                        {{-- Render rowspan for the first row of each group --}}
                                                        @if ($itemIndex === 0)
                                                            <td rowspan="{{ $rowspanCount }}">{{ ++$rowIndex }}</td>
                                                            <td rowspan="{{ $rowspanCount }}">{{ $group['name'] }}</td>
                                                        @endif
                                                        <td>{{ $item['dept_no'] }}</td>
                                                        <td>{{ $item['quantity'] }}</td>
                                                        <td>{{ $item['uom'] }}</td>
                                                        <td>{{ $item['supplier'] ?? '-' }}</td>
                                                        <td>@currency($item['cost_per_unit'])</td>
                                                        <td>@currency($totalCost)</td>
                                                        <td>{{ $item['remark'] }}</td>
                                                        <td>
                                                            @if (($report->status === 1 && $report->user->id === $authUser->id) || ($report->status === 2 && $authUser->is_gm === 1))
                                                                @include('partials.edit-monthly-budget-report-summary-detail')
                                                                <button class="btn btn-primary"
                                                                    data-bs-target="#edit-monthly-budget-report-summary-detail-{{ $item['id'] }}"
                                                                    data-bs-toggle="modal">Edit</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            @if (empty($groupedDetailsForView))
                                                <tr>
                                                    <td colspan="10">No Data</td>
                                                </tr>
                                            @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
