@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <style>
        @keyframes rainbow {
            0% {
                color: red;
            }

            14% {
                color: orange;
            }

            28% {
                color: yellow;
            }

            42% {
                color: green;
            }

            57% {
                color: blue;
            }

            71% {
                color: indigo;
            }

            85% {
                color: violet;
            }

            100% {
                color: red;
            }
        }

        .rainbow-text {
            animation: rainbow 3s linear infinite;
            font-weight: bold;
        }
    </style>

    <h1 class="rainbow-text">
        Terakhir di update :
        {{ \Carbon\Carbon::parse($log->updated_at)->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }}
    </h1>

    <!-- Main content -->
    @include('partials.alert-success-error')
    <form method="GET" action="/foremind-detail/print" target="_blank">
        @csrf
        <div class="form-group">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <label class="form-label" for="vendor_code_internal">Select Vendor Name (for
                        Internal)</label>
                </div>
                <div class="col">
                    <select class="form-select" id="vendor_code_internal" name="vendor_code" required>
                        <option value="" selected disabled>Select Vendor Name</option>
                        @foreach ($contacts as $contact)
                            <option value="{{ $contact->vendor_code }}">
                                {{ $contact->vendor_code }} - {{ $contact->vendor_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Form for Customer Vendor Selection -->
    <form method="GET" action="/foremind-detail/printCustomer" target="_blank">
        @csrf
        <div class="form-group mt-2">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <label class="form-label" for="vendor_code_customer">Enter Vendor Code (for
                        Customer)</label>
                </div>
                <div class="col">
                    <select class="form-select" id="vendor_code_customer" name="vendor_code" required>
                        <option value="" selected disabled>Select Vendor Name</option>
                        @foreach ($contacts as $contact)
                            <option value="{{ $contact->vendor_code }}">
                                {{ $contact->vendor_code }} - {{ $contact->vendor_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table mb-0 table-hover ">
                    <thead class="fs-5 align-items-center">
                        <tr>
                            <th>Material Code</th>
                            <th>Material Name</th>
                            <th>Item No</th>
                            <th>Vendor Code</th>
                            <th>Unit of Measure</th>
                            <th>Quantity Material</th>

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
                                    <!-- Display material code and material name only for the first occurrence -->
                                    <td class="table-bordered">{{ $material->material_code }}</td>
                                    <td class="table-bordered">{{ $material->material_name }}</td>
                                    @php
                                        $currentMaterialCode = $material->material_code;
                                    @endphp
                                @else
                                    <!-- Display blank columns for subsequent occurrences of the same material code -->
                                    <td class="table-bordered"></td>
                                    <td class="table-bordered"></td>
                                @endif
                                <td class="table-bordered">{{ $material->item_no }}</td>
                                <!-- <td>{{ $material->vendor_name }}</td> -->
                                <td class="table-bordered">{{ $material->vendor_code }}</td>
                                <td class="table-bordered">{{ $material->unit_of_measure }}</td>
                                <td class="table-bordered">{{ $material->quantity_material }}</td>

                                @php
                                    $total = 0;
                                @endphp

                                @foreach ($qforecast[$loop->index] as $index => $value)
                                    @php
                                        $calculation = $value * $material->quantity_material;
                                        $total += $calculation;
                                        $monthlyTotals[$index] += $calculation;
                                    @endphp

                                    <td class="table-bordered">
                                        <div>{{ $value }}</div>
                                        <strong>{{ $calculation }}</strong>
                                    </td>
                                    <!-- Display the calculated value -->
                                @endforeach

                                <td class="table-bordered"><strong>{{ $total }}</strong></td>
                                <!-- Add this line for the total -->
                            </tr>

                            @if (!$loop->last && $material->material_code != $materials[$loop->index + 1]->material_code)
                                <!-- Calculate and display the total for each month before the empty line -->
                                <tr>
                                    <td class="table-bordered" colspan="5"></td>
                                    <td class="table-bordered">Monthly Total</td>
                                    @foreach ($monthlyTotals as $monthlyTotal)
                                        <td class="table-bordered">
                                            <strong>{{ $monthlyTotal }}</strong>
                                        </td>
                                    @endforeach
                                    <td class="table-bordered"><strong>{{ array_sum($monthlyTotals) }}</strong>
                                    </td> <!-- Add this line for the monthly total -->
                                </tr>

                                <!-- Reset monthly totals for the new material code -->
                                @php
                                    $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
                                @endphp

                                <!-- Add a break line after the monthly total -->
                                <tr>
                                    <td class="table-bordered" colspan="13"></td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3 text-end ">
        {{ $materials->links() }}
    </div>

    <!-- Initialize Tom Select -->
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
@endsection
