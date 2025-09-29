@extends('layouts.app')

@section('content')
    <div class="wrapper">
        <section class="invoice">
            <div class="row">
                <div class="col-12">
                    <h2 class="page-header">
                        <i class="fas fa-globe"></i> Daijo Industrial.
                        <small class="float-right">Date: {{ now()->format('j/n/Y') }}</small>
                    </h2>
                </div>
            </div>
        </section>
    </div>

    <div class="export-buttons">
        <a href="/foremind-detail/print/customer/excel/{{ $vendorCode }}" class="btn btn-success">Export
            to Excel</a>
    </div>

    <style>
        @media print {

            /* Hide the export button when printing */
            .export-buttons {
                display: none;
            }
        }

        .table-bordered td {
            border: 1px solid #000;
            padding: 1px;
        }
    </style>
    <!-- Table row -->
    @if (!empty($materials))
        <div class="row">
            <div class="col-12">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="table-bordered">Material Code</th>
                            <th class="table-bordered">Material Name</th>
                            <th class="table-bordered">Unit Measure</th>
                            @foreach ($mon as $month)
                                <th class="table-bordered">{{ \Carbon\Carbon::parse($month)->format('M-Y') }}</th>
                            @endforeach
                            <th>Total</th>
                            <th>Customer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $currentMaterialCode = null;
                            $materialTotal = 0;
                            $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
                        @endphp

                        @foreach ($materials as $key => $material)
                            @if ($material->material_code != $currentMaterialCode)
                                <!-- Print material code, name, and unit of measure only for the first occurrence -->
                                @if ($currentMaterialCode != null)
                                    <tr>
                                        <td class="table-bordered">{{ $currentMaterialCode }}</td>
                                        <td class="table-bordered">{{ $currentMaterialName }}</td>
                                        <td class="table-bordered">{{ $currentMaterialMeasure }}</td>
                                        @foreach ($monthlyTotals as $monthlyTotal)
                                            <td class="table-bordered">
                                                <strong>{{ number_format($monthlyTotal, 2) }}</strong>
                                            </td>
                                        @endforeach
                                        <td class="table-bordered">
                                            <strong>{{ number_format(array_sum($monthlyTotals), 2) }}</strong>
                                        </td>
                                        <td class="table-bordered">{{ $currentCustomer }}</td>
                                    </tr>
                                @endif

                                <!-- Initialize for the new material code -->
                                @php
                                    $currentMaterialCode = $material->material_code;
                                    $currentMaterialName = $material->material_name;
                                    $currentMaterialMeasure = $material->unit_of_measure;
                                    $materialTotal = 0;
                                    $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
                                    $currentCustomer = $material->customer;
                                @endphp
                            @endif

                            <!-- Accumulate data for each month -->
                            @foreach ($qforecast[$key] as $index => $value)
                                @php
                                    $calculation = $value * $material->quantity_material;
                                    $materialTotal += $calculation;
                                    $monthlyTotals[$index] += $calculation;
                                @endphp
                            @endforeach
                        @endforeach

                        <!-- Print the final row for the last material -->
                        <tr>
                            <td class="table-bordered">{{ $currentMaterialCode }}</td>
                            <td class="table-bordered">{{ $currentMaterialName }}</td>
                            <td class="table-bordered">{{ $currentMaterialMeasure }}</td>
                            @foreach ($monthlyTotals as $monthlyTotal)
                                <td class="table-bordered">
                                    <strong>{{ $monthlyTotal }}</strong>
                                </td>
                            @endforeach
                            <td class="table-bordered">
                                <strong>{{ array_sum($monthlyTotals) }}</strong>
                            </td>
                            <td class="table-bordered">{{ $currentCustomer }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
