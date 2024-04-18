<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Export</title>
    <style>
        /* Table styles */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* Table header styles */
        th {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            background-color: #f2f2f2;
        }

        /* Table data styles */
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>

</head>
<body>
    @if (!empty($materials))
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped table-bordered" >
                <thead>
                    <tr>
                    <td style="vertical-align: middle; height:60px; font-size: 18px;">Nama Vendor</td>
                    <td colspan="11" style="vertical-align: middle; height:60px; font-size: 18px;">{{$vendorName}}</td>
                    </tr>
                    <tr>
                    <td style="vertical-align: middle; height:60px; font-size: 18px;">Date</td>
                    <td  colspan="11" style="vertical-align: middle; height:60px; font-size: 18px;" >{{ now()->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }}</td>
                       <!-- Blank cell with colspan="10" -->
                    </tr>
                    <tr>
                    <td style="vertical-align: middle; height:60px; font-size: 18px;">ATT</td>
                    <td  colspan="11" style="vertical-align: middle; height:60px; font-size: 18px;"> {{ $contact->persontocontact }} </td>
                    </tr>
                    <tr>
                    <td style="vertical-align: middle; height:60px; font-size: 18px;">FR </td>
                    <td  colspan="11" style="vertical-align: middle; height:60px; font-size: 18px;">{{ $contact->p_member }}</td>
                    </tr>
                    <tr>
                    <td colspan="12"   align="center" style="vertical-align: middle; font-size: 30px;" >Forecast Report</td>
                    </tr>
                    <tr>
                        <td colspan="12"></td>
                    </tr>
                    <tr>
                        <th align="center" style="vertical-align: middle; height:60px; font-size: 18px;" >No</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Material Code</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Material Name</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">U/M</th>
                        @foreach ($mon as $month)
                            <th align="center" style="vertical-align: middle; font-size: 18px;">{{ \Carbon\Carbon::parse($month)->format('Y-m') }}</th>
                        @endforeach
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Total</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Customer<th>
                    </tr>
                </thead>
                <tbody>
    @php
        $currentMaterialCode = null;
        $materialTotal = 0;
        $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
        $i = 1;
    @endphp

    @foreach ($materials as $key => $material)
        @if ($material->material_code != $currentMaterialCode)
            <!-- Print material code, name, and unit of measure only for the first occurrence -->
            @if ($currentMaterialCode != null)
                <tr>
                    <td class="table-bordered"  align="center" style="vertical-align: middle;">{{$i++}}</td>
                    <td class="table-bordered"  align="center" style="vertical-align: middle;">{{ $currentMaterialCode }}</td>
                    <td class="table-bordered"   align="center" style="vertical-align: middle;">{{ $currentMaterialName }}</td>
                    <td class="table-bordered"   align="center" style="vertical-align: middle;">{{ $currentMaterialMeasure }}</td>
                    @foreach ($monthlyTotals as $monthlyTotal)
                        <td class="table-bordered"   align="center" style="vertical-align: middle;">
                            <strong>{{ $monthlyTotal }}</strong>
                        </td>
                    @endforeach
                    <td class="table-bordered"   align="center" style="vertical-align: middle;">
                        <strong>{{ array_sum($monthlyTotals) }}</strong>
                    </td>
                    <td class="table-bordered" rowspan="2"  align="center" style="vertical-align: middle;">{{ $currentCustomer }}</td>
                </tr>
                <tr>
                    <td colspan="3" align=right  style="vertical-align: middle;"><strong>Total</strong></td>
                    @foreach ($monthlyTotals as $monthlyTotal)
                        <td   align="center" style="vertical-align: middle;"><strong>{{ $monthlyTotal }}</strong></td>
                    @endforeach
                    <td  align="center" style="vertical-align: middle;"><strong>{{ array_sum($monthlyTotals) }}</strong></td>
                    <td></td> <!-- Empty cell for customer -->
                </tr>
                <tr>
                    <td colspan="12"></td>
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
        <td class="table-bordered"  align="center" style="vertical-align: middle;">{{$i++}}</td>
        <td class="table-bordered"   align="center" style="vertical-align: middle;">{{ $currentMaterialCode }}</td>
        <td class="table-bordered"   align="center" style="vertical-align: middle;">{{ $currentMaterialName }}</td>
        <td class="table-bordered"   align="center" style="vertical-align: middle;">{{ $currentMaterialMeasure }}</td>
        @foreach ($monthlyTotals as $monthlyTotal)
            <td class="table-bordered"   align="center" style="vertical-align: middle;">
                <strong>{{ $monthlyTotal }}</strong>
            </td>
        @endforeach
        <td class="table-bordered"  align="center"  style="vertical-align: middle;">
            <strong>{{ array_sum($monthlyTotals) }}</strong>
        </td>
        <td class="table-bordered" rowspan="2"   align="center" style="vertical-align: middle;">{{ $currentCustomer }}</td>
    </tr>
    <tr>
            <td colspan="3" align=right  style="vertical-align: middle;"><strong>Total</strong></td>
            @foreach ($monthlyTotals as $monthlyTotal)
                <td   align="center" style="vertical-align: middle;"><strong>{{ $monthlyTotal }}</strong></td>
            @endforeach
            <td   align="center" style="vertical-align: middle;"><strong>{{ array_sum($monthlyTotals) }}</strong></td>
            <td></td> <!-- Empty cell for customer -->
        </tr>
</tbody>
            </table>
        </div>
    </div>
    @endif

</body>
</html>
