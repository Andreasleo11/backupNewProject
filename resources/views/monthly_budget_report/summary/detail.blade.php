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
                            <div class="h2 fw-bold mt-4">Monthly Budget Summary Report <span
                                    class="{{ $report->is_moulding ? '' : 'd-none' }}">Moulding</span></div>
                            <div class="fs-6 mt-2">
                                <div class="fs-5 ">Doc. Num : {{ $report->doc_num }}</div>
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
                                    <table class="table text-center table-striped ">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Dept</th>
                                                <th>Quantity</th>
                                                <th>UoM</th>
                                                @if ($report->is_moulding)
                                                    <th>Spec</th>
                                                    <th>Last Recorded Stock</th>
                                                    <th>Usage Per Month</th>
                                                @endif
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
                                                $grandTotal = 0; // Initialize grand total
                                            @endphp
                                            @foreach ($groupedDetailsForView as $index => $group)
                                                @php
                                                    $rowspanCount = count($group['items']); // Calculate rowspan for the name column
                                                @endphp
                                                @foreach ($group['items'] as $itemIndex => $item)
                                                    @php
                                                        $totalCost = $item['quantity'] * $item['cost_per_unit'];
                                                        $grandTotal += $totalCost; // Accumulate total cost
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
                                                        @if ($report->is_moulding)
                                                            <td>{{ $item['spec'] ?? '-' }}</td>
                                                            <td>{{ $item['last_recorded_stock'] ?? '-' }}</td>
                                                            <td>{{ $item['usage_per_month'] ?? '-' }}</td>
                                                        @endif
                                                        <td>{{ $item['supplier'] ?? '-' }}</td>
                                                        <td>@currency($item['cost_per_unit'])</td>
                                                        <td>@currency($totalCost)</td>
                                                        <td style="width: 25%;">
                                                            {{ $item['remark'] }}
                                                        </td>
                                                        <td>
                                                            @if (
                                                                ($report->status === 1 && $report->user->id === $authUser->id) ||
                                                                    ($report->status === 2 && $authUser->is_gm === 1) ||
                                                                    ($report->status === 3 && $authUser->is_head === 1 && $authUser->department->name === 'MOULDING'))
                                                                @include('partials.edit-monthly-budget-report-summary-detail')
                                                                <button class="btn btn-primary"
                                                                    data-bs-target="#edit-monthly-budget-report-summary-detail-{{ $item['id'] }}"
                                                                    data-bs-toggle="modal"><i
                                                                        class='bx bx-edit'></i></button>
                                                                @include(
                                                                    'partials.delete-confirmation-modal',
                                                                    [
                                                                        'title' => 'Delete item',
                                                                        'body' =>
                                                                            'Are you sure want to delete this item?',
                                                                        'id' => $item['id'],
                                                                        'route' =>
                                                                            'monthly.budget.report.summary.detail.destroy',
                                                                    ]
                                                                )

                                                                <button class="btn btn-danger my-1"
                                                                    data-bs-target="#delete-confirmation-modal-{{ $item['id'] }}"
                                                                    data-bs-toggle="modal"><i class='bx bx-trash-alt'></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            @if (empty($groupedDetailsForView))
                                                <tr>
                                                    <td colspan="13">No Data</td>
                                                </tr>
                                            @endif
                                            {{-- Display grand total row --}}
                                            <tr>
                                                <td colspan="{{ $report->is_moulding ? 10 : 7 }}"
                                                    class="text-end align-content-center fw-bold">Total
                                                </td>
                                                <td class="fw-bold">@currency($grandTotal)</td>
                                                <td colspan="2"></td>
                                            </tr>
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

    <div class="mt-2">
        @include('partials.uploaded-section', [
            'showDeleteButton' => $report->status === 1,
            'files' => $report->files,
        ])
    </div>
@endsection
