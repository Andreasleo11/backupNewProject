@extends('new.layouts.app')

@push('head')
    <style>
        @keyframes rainbow {
            0%   { color: #ef4444; }   /* red */
            16%  { color: #f97316; }   /* orange */
            32%  { color: #eab308; }   /* yellow */
            48%  { color: #22c55e; }   /* green */
            64%  { color: #3b82f6; }   /* blue */
            80%  { color: #6366f1; }   /* indigo */
            100% { color: #a855f7; }   /* violet */
        }

        .rainbow-text {
            animation: rainbow 3s linear infinite;
            font-weight: 600;
        }

        .table-forecast th,
        .table-forecast td {
            white-space: nowrap;
            font-size: 0.85rem;
        }

        .table-forecast thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #f8f9fa;
        }

        .table-forecast tbody td {
            vertical-align: middle;
        }

        .table-forecast tbody td strong {
            display: block;
            font-weight: 600;
        }

        .sub-row-separator td {
            border-top: 2px solid #dee2e6;
        }
    </style>
@endpush

@section('content')
    <div class="container py-3">

        {{-- HEADER --}}
        <section class="mb-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h1 class="h4 mb-1">Forecast Reminder Detail</h1>
                    <p class="text-muted small mb-0">
                        Monitoring kebutuhan material per vendor berdasarkan forecast & quantity material.
                    </p>
                </div>
                <div class="text-md-end">
                    <span class="badge bg-light text-muted border">
                        Terakhir di update :
                        <span class="rainbow-text">
                            -
                        </span>
                    </span>
                </div>
            </div>
        </section>

        {{-- FORM: INTERNAL VENDOR --}}
        <section class="mb-2">
            <form method="GET" action="/foremind-detail/print" target="_blank" class="card shadow-sm border-0 mb-2">
                @csrf
                <div class="card-body py-3">
                    <div class="row align-items-center g-2">
                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1" for="vendor_code_internal">
                                Vendor (Internal)
                            </label>
                            <small class="text-muted d-block">
                                Pilih vendor untuk cetak form internal.
                            </small>
                        </div>
                        <div class="col-12 col-md-5">
                            <select class="form-select" id="vendor_code_internal" name="vendor_code" required>
                                <option value="" selected disabled>Select Vendor Name</option>
                                @foreach ($contacts as $contact)
                                    <option value="{{ $contact->vendor_code }}">
                                        {{ $contact->vendor_code }} - {{ $contact->vendor_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 text-md-end">
                            <button class="btn btn-primary w-100 w-md-auto" type="submit">
                                Print Internal
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- FORM: CUSTOMER VENDOR --}}
            <form method="GET" action="/foremind-detail/printCustomer" target="_blank"
                  class="card shadow-sm border-0">
                @csrf
                <div class="card-body py-3">
                    <div class="row align-items-center g-2">
                        <div class="col-12 col-md-4">
                            <label class="form-label mb-1" for="vendor_code_customer">
                                Vendor (Customer)
                            </label>
                            <small class="text-muted d-block">
                                Pilih vendor untuk form ke customer.
                            </small>
                        </div>
                        <div class="col-12 col-md-5">
                            <select class="form-select" id="vendor_code_customer" name="vendor_code" required>
                                <option value="" selected disabled>Select Vendor Name</option>
                                @foreach ($contacts as $contact)
                                    <option value="{{ $contact->vendor_code }}">
                                        {{ $contact->vendor_code }} - {{ $contact->vendor_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 text-md-end">
                            <button class="btn btn-outline-primary w-100 w-md-auto" type="submit">
                                Print Customer
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        {{-- TABEL --}}
        <div class="card mt-3 shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive" style="max-height: 70vh;">
                    <table class="table table-hover table-forecast mb-0">
                        <thead class="align-middle">
                            <tr>
                                <th>Material Code</th>
                                <th>Material Name</th>
                                <th>Item No</th>
                                <th>Vendor Code</th>
                                <th>UoM</th>
                                <th>Qty Material</th>

                                @foreach ($mon as $month)
                                    <th>{{ \Carbon\Carbon::parse($month)->format('Y-m') }}</th>
                                @endforeach

                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
                                $currentMaterialCode = null;
                            @endphp

                            @foreach ($materials as $key => $material)
                                <tr>
                                    @if ($material->material_code != $currentMaterialCode)
                                        {{-- First row for material code --}}
                                        <td>{{ $material->material_code }}</td>
                                        <td>{{ $material->material_name }}</td>
                                        @php $currentMaterialCode = $material->material_code; @endphp
                                    @else
                                        {{-- Subsequent rows: empty cells for code & name --}}
                                        <td></td>
                                        <td></td>
                                    @endif

                                    <td>{{ $material->item_no }}</td>
                                    <td>{{ $material->vendor_code }}</td>
                                    <td>{{ $material->unit_of_measure }}</td>
                                    <td>{{ $material->quantity_material }}</td>

                                    @php $total = 0; @endphp

                                    @foreach ($qforecast[$loop->index] as $index => $value)
                                        @php
                                            $calculation = $value * $material->quantity_material;
                                            $total += $calculation;
                                            $monthlyTotals[$index] += $calculation;
                                        @endphp

                                        <td>
                                            <div>{{ $value }}</div>
                                            <strong>{{ $calculation }}</strong>
                                        </td>
                                    @endforeach

                                    <td>
                                        <strong>{{ $total }}</strong>
                                    </td>
                                </tr>

                                {{-- Ketika material_code berganti, tampilkan subtotal + separator --}}
                                @if (
                                    !$loop->last &&
                                        $material->material_code != $materials[$loop->index + 1]->material_code
                                )
                                    <tr class="table-light fw-semibold">
                                        <td colspan="5"></td>
                                        <td>Monthly Total</td>
                                        @foreach ($monthlyTotals as $monthlyTotal)
                                            <td>
                                                <strong>{{ $monthlyTotal }}</strong>
                                            </td>
                                        @endforeach
                                        <td>
                                            <strong>{{ array_sum($monthlyTotals) }}</strong>
                                        </td>
                                    </tr>

                                    @php
                                        $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
                                    @endphp

                                    <tr class="sub-row-separator">
                                        <td colspan="{{ 6 + count($qforecast[0]) + 1 }}"></td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3 d-flex justify-content-end">
            {{ $materials->links() }}
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#vendor_code_internal", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });

            new TomSelect("#vendor_code_customer", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    </script>
@endpush
