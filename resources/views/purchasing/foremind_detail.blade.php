@extends('layouts.app')

@section('content')
    <!-- Main content -->
    <form method="GET" action="/foremind-detail/print" target="_blank">
        @csrf
        <div class="form-group">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <label class="form-label" for="vendor_code">Enter Vendor Code(for Internal)</label>
                </div>
                <div class="col-auto">
                    <input class="form-control" type="text" id="vendor_code" name="vendor_code" required>
                </div>
                <div class="col">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </div>
        </div>
    </form>

    <form method="GET" action="/foremind-detail/printCustomer" target="_blank">
        @csrf
        <div class="form-group mt-2">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <label class="form-label for="vendor_code">Enter Vendor Code(for Customer)</label>
                </div>
                <div class="col-auto">
                    <input class="form-control" type="text" id="vendor_code" name="vendor_code" required>
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
@endsection
