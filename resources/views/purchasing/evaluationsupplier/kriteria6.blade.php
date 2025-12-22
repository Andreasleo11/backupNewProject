@extends('new.layouts.app')

@push('head')
    <style>
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

            /* Tampilkan area print saja */
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
                max-width: 110px;
            }

            @page {
                margin: 10px;
            }

            .print-area tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

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
                    <h1 class="h4 mb-1">Vendor List Certificate</h1>
                    <p class="text-muted small mb-0">
                        Daftar sertifikat vendor (ISO 9001, ISO 14001, IATF 16949) beserta masa berlakunya.
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('purchasing.evaluationsupplier.index') }}"
                       class="btn btn-outline-secondary btn-sm">
                        ← Back to Supplier Evaluation
                    </a>
                    <button type="button"
                            class="btn btn-outline-primary btn-sm"
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
                    <span class="fw-semibold">Filter Vendor</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('kriteria6') }}" method="GET" class="row g-3 align-items-end">
                        @csrf

                        <div class="col-12 col-md-6 col-lg-4">
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

                        <div class="col-12 col-md-3 col-lg-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- PRINT AREA (TITLE + TABLE) --}}
        <section class="print-area">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h6 mb-0 print-title">Vendor List Certificate</h1>
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
                                    <th>ISO 9001 Document</th>
                                    <th>ISO 9001 Start Date</th>
                                    <th>ISO 9001 End Date</th>
                                    <th>ISO 14001 Document</th>
                                    <th>ISO 14001 Start Date</th>
                                    <th>ISO 14001 End Date</th>
                                    <th>IATF 16949 Document</th>
                                    <th>IATF 16949 Start Date</th>
                                    <th>IATF 16949 End Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($datas as $data)
                                    <tr>
                                        <td>{{ $data->id }}</td>
                                        <td>{{ $data->vendor_code }}</td>
                                        <td>{{ $data->vendor_name }}</td>

                                        {{-- ISO 9001 --}}
                                        <td class="text-start">{{ $data->iso_9001_doc }}</td>
                                        <td>
                                            {{ $data->iso_9001_start_date == '0000-00-00' || !$data->iso_9001_start_date
                                                ? '-'
                                                : \Carbon\Carbon::parse($data->iso_9001_start_date)->format('d-m-Y') }}
                                        </td>
                                        <td>
                                            {{ $data->iso_9001_end_date == '0000-00-00' || !$data->iso_9001_end_date
                                                ? '-'
                                                : \Carbon\Carbon::parse($data->iso_9001_end_date)->format('d-m-Y') }}
                                        </td>

                                        {{-- ISO 14001 --}}
                                        <td class="text-start">{{ $data->iso_14001_doc }}</td>
                                        <td>
                                            {{ $data->iso_14001_start_date == '0000-00-00' || !$data->iso_14001_start_date
                                                ? '-'
                                                : \Carbon\Carbon::parse($data->iso_14001_start_date)->format('d-m-Y') }}
                                        </td>
                                        <td>
                                            {{ $data->iso_14001_end_date == '0000-00-00' || !$data->iso_14001_end_date
                                                ? '-'
                                                : \Carbon\Carbon::parse($data->iso_14001_end_date)->format('d-m-Y') }}
                                        </td>

                                        {{-- IATF 16949 --}}
                                        <td class="text-start">{{ $data->iatf_16949_doc }}</td>
                                        <td>
                                            {{ $data->iatf_16949_start_date == '0000-00-00' || !$data->iatf_16949_start_date
                                                ? '-'
                                                : \Carbon\Carbon::parse($data->iatf_16949_start_date)->format('d-m-Y') }}
                                        </td>
                                        <td>
                                            {{ $data->iatf_16949_end_date == '0000-00-00' || !$data->iatf_16949_end_date
                                                ? '-'
                                                : \Carbon\Carbon::parse($data->iatf_16949_end_date)->format('d-m-Y') }}
                                        </td>
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
