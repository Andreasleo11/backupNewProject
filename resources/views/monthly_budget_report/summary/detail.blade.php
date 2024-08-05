@extends('layouts.app')

@push('extraCss')
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
@endpush

@section('content')
    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = Auth::user();
    @endphp

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

    <div class="row">
        <div class="col-md-11 text-end mb-5">
            @if ($authUser->email === 'nur@daijo.co.id')
                <button class="btn btn-outline-primary" data-bs-target="#upload-files-modal" data-bs-toggle="modal">
                    <i class='bx bx-upload'></i> Upload
                </button>

                @include('partials.upload-files-modal', ['doc_id' => $report->doc_num])
            @endif
        </div>
    </div>

    <section class="autographs">
        @include('partials.monthly-budget-summary-report-autograph')
    </section>
    <section aria-label="report">
        <div class="row justify-content-center mt-5">
            <div class="col">
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
                                                <th>Spec <span class="text-danger">*</span></th>
                                                <th>Last Recorded Stock <span class="text-danger">*</span></th>
                                                <th>Usage Per Month <span class="text-danger">*</span></th>
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
                                                        <td>{{ $item['spec'] ?? '-' }}</td>
                                                        <td>{{ $item['last_recorded_stock'] ?? '-' }}</td>
                                                        <td>{{ $item['usage_per_month'] ?? '-' }}</td>
                                                        <td>{{ $item['supplier'] ?? '-' }}</td>
                                                        <td>@currency($item['cost_per_unit'])</td>
                                                        <td>@currency($totalCost)</td>
                                                        <td style="width: 25%;">
                                                            {{ $item['remark'] }}
                                                        </td>
                                                        <td>
                                                            @if (($report->status === 1 && $report->user->id === $authUser->id) || ($report->status === 2 && $authUser->is_gm === 1))
                                                                @include('partials.edit-monthly-budget-report-summary-detail')
                                                                <button class="btn btn-primary"
                                                                    data-bs-target="#edit-monthly-budget-report-summary-detail-{{ $item['id'] }}"
                                                                    data-bs-toggle="modal"><i
                                                                        class='bx bx-edit'></i></button>
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

                        <div class="mt-2 ms-2">
                            <h6 class="fw-semibold mt-3">NOTE :</h6>
                            <span class="text-danger">*</span> : Only Moulding Dept
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
