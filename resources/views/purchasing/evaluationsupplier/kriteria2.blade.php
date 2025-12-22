@extends('new.layouts.app')

@push('head')
    <style>
        /* Layout & table styling */
        .table-container {
            margin-top: 1rem;
        }

        .table thead th {
            background-color: #f8f9fa;
            text-align: center;
            white-space: nowrap;
        }

        .table tbody td {
            text-align: center;
            vertical-align: middle;
        }

        /* PRINT STYLES */
        @media print {
            /* Sembunyikan semua dulu */
            body * {
                visibility: hidden;
            }

            /* Hanya area ini yang kelihatan saat print */
            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }

            h1.print-title {
                text-align: center;
                font-size: 18px;
                margin: 0 0 10px 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
            }

            table th,
            table td {
                border: 1px solid #000;
                padding: 6px;
                word-wrap: break-word;
                max-width: 100px;
            }

            @page {
                margin: 10px;
            }

            .print-area tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            /* Sembunyikan control saat print */
            .d-print-none {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3">

        {{-- HEADER --}}
        <section class="mb-3 d-print-none">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h1 class="h4 mb-1">Vendor Accuracy Good</h1>
                    <p class="text-muted small mb-0">
                        Akurasi kuantitas pengiriman vendor (delivery vs received) pada periode yang dipilih.
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('purchasing.evaluationsupplier.index') }}"
                       class="btn btn-outline-secondary btn-sm">
                        ← Back to Supplier Evaluation
                    </a>
                    <button type="button"
                            class="btn btn-outline-primary btn-sm d-print-none"
                            onclick="window.print()">
                        Print
                    </button>
                </div>
            </div>
        </section>

        {{-- FILTER FORM --}}
        <section class="mb-4 d-print-none">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <span class="fw-semibold">Filter Vendor Accuracy</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('kriteria2') }}" method="GET" class="row g-3 align-items-end">
                        @csrf

                        {{-- Vendor --}}
                        <div class="col-12 col-md-4">
                            <label for="vendor_name" class="form-label fw-semibold">Vendor</label>
                            <select name="vendor_name" id="vendor_name" class="form-select">
                                <option value="">-- All Vendors --</option>
                                @foreach ($vendorNames as $vendor)
                                    <option value="{{ $vendor }}" {{ request('vendor_name') == $vendor ? 'selected' : '' }}>
                                        {{ $vendor }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Month --}}
                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="month" class="form-label fw-semibold">Month</label>
                            <select name="month" id="month" class="form-select">
                                <option value="">-- All Months --</option>
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Year --}}
                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="year" class="form-label fw-semibold">Year</label>
                            <select name="year" id="year" class="form-select">
                                <option value="">-- All Years --</option>
                                @foreach (range(2020, 2040) as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Button --}}
                        <div class="col-12 col-lg-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- PRINT AREA: TITLE + TABLE --}}
        <section class="print-area">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h6 mb-0 print-title">Vendor Accuracy Good</h1>
                    <span class="small text-muted d-none d-print-inline">
                        Printed at {{ now()->format('d-m-Y H:i') }}
                    </span>
                    <span class="small text-muted d-none d-md-inline d-print-none">
                        Total records: {{ $datas->count() }}
                    </span>
                </div>

                <div class="card-body p-0 table-container">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Vendor Code</th>
                                    <th>Vendor Name</th>
                                    <th>Item Code</th>
                                    <th>Description</th>
                                    <th>Delivery No</th>
                                    <th>Incoming Date</th>
                                    <th>Delivery Qty</th>
                                    <th>Received Qty</th>
                                    <th>Shortage Qty</th>
                                    <th>Over Qty</th>
                                    <th>Close Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($datas as $data)
                                    <tr>
                                        <td>{{ $data->id }}</td>
                                        <td>{{ $data->vendor_code }}</td>
                                        <td>{{ $data->vendor_name }}</td>
                                        <td>{{ $data->item_code }}</td>
                                        <td class="text-start">{{ $data->description }}</td>
                                        <td>{{ $data->delivery_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data->incoming_date)->format('d-m-Y') }}</td>
                                        <td>{{ $data->delivery_quantity }}</td>
                                        <td>{{ $data->received_quantity }}</td>
                                        <td>{{ $data->shortage_quantity }}</td>
                                        <td>{{ $data->over_quantity }}</td>
                                        <td>{{ $data->close_status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-3">
                                            No data available for current filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
