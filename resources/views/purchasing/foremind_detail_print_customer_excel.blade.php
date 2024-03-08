<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Export</title>
</head>
<body>
    @if (!empty($materials))
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Material Code</th>
                        <th>Material Name</th>
                        <th>U/M</th>
                        @foreach ($mon as $month)
                            <th>{{ \Carbon\Carbon::parse($month)->format('Y-m') }}</th>
                        @endforeach
                        <th>Total</th>
                        <th>Customer<th>
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
                            <strong>{{ $monthlyTotal }}</strong>
                        </td>
                    @endforeach
                    <td class="table-bordered">
                        <strong>{{ array_sum($monthlyTotals) }}</strong>
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

   
</body>
</html>