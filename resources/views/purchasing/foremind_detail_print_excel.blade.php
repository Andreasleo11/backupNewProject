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
                        <th>Item No</th>
                        <th>Vendor Code</th>
                        <th>U/M</th>
                        <th>NET Quantity</th>
                        @foreach ($mon as $month)
                            <th>{{ \Carbon\Carbon::parse($month)->format('Y-m') }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $previousMaterialCode = null;
                        $monthlyTotalPredictions = array_fill(0, count($mon), 0);
                    @endphp

                    @foreach ($materials as $material)
                        @if ($material->material_code != $previousMaterialCode)
                            <!-- Calculate and display the total prediction per month before the blank row -->
                            @if ($previousMaterialCode !== null)
                            <tr>
                                <td>Total Akhir</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>

                                @foreach ($monthlyTotalPredictions as $monthlyTotalPrediction)
                                    <td><strong>{{ $monthlyTotalPrediction }}</strong></td>
                                @endforeach

                                <td><strong>{{ array_sum($monthlyTotalPredictions) }}</strong></td>
                            </tr>

                            <!-- Add a blank row to separate material codes -->
                            <tr></tr>
                            @endif
                            <!-- Reset monthly total predictions for the new material code -->
                            @php
                                $monthlyTotalPredictions = array_fill(0, count($mon), 0);
                            @endphp
                        @endif

                        <tr>
                            <td>{{ $material->material_code }}</td>
                            <td>{{ $material->material_name }}</td>
                            <td>{{ $material->item_no }}</td>
                            <td>{{ $material->vendor_code }}</td>
                            <td>{{ $material->unit_of_measure }}</td>
                            <td>{{ $material->quantity_material }}</td>

                            @php
                                $total = 0;
                            @endphp

                            @foreach ($qforecast[$loop->index] as $index => $value)
                                @php
                                    $calculation = $value * $material->quantity_material;
                                    $total += $calculation;
                                    
                                    $monthlyTotalPredictions[$index] += $calculation;
                                @endphp

                                <td>
                                    <div>{{ $value }} <br></div>
                                    <div><b>{{ $calculation }}</b></div>
                                
                                </td>
                                
                            @endforeach

                            
                        </tr>

                        @php
                            $previousMaterialCode = $material->material_code;
                        @endphp
                    @endforeach

                    <!-- Calculate and display the total prediction per month for the last material code -->
                    <tr>
                        <td>Total Akhir</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        

                        @foreach ($monthlyTotalPredictions as $monthlyTotalPrediction)
                            <td><strong>{{ $monthlyTotalPrediction }}</strong></td>
                        @endforeach

                        <td><strong>{{ array_sum($monthlyTotalPredictions) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

   
</body>
</html>