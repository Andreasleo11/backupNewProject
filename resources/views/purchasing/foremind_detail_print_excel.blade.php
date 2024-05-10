<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forecast {{$vendorName}}</title>
</head>
<body>
    @if (!empty($materials))
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                    <td colspan="15"   align="center" style="vertical-align: middle; font-size: 30px;" >Forecast Report Internal</td>
                    </tr>
                    <tr>
                        <th align="center" style="vertical-align: middle; height:60px; font-size: 18px;">No</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Material Code</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Material Name</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Item No</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Vendor Code</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">U/M</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">NET Quantity</th>
                        @foreach ($mon as $month)
                            <th align="center" style="vertical-align: middle; font-size: 18px;">{{ \Carbon\Carbon::parse($month)->format('M-Y') }}</th>
                        @endforeach
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Keterangan</th>
                        <th align="center" style="vertical-align: middle; font-size: 18px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $previousMaterialCode = null;
                        $monthlyTotalPredictions = array_fill(0, count($mon), 0);
                        $i = 1;
                    @endphp

                    @foreach ($materials as $material)
                        @if ($material->material_code != $previousMaterialCode)
                            <!-- Calculate and display the total prediction per month before the blank row -->
                            @if ($previousMaterialCode !== null)
                            <tr>
                                <td colspan="7" align=right>Total Akhir</td>

                                @foreach ($monthlyTotalPredictions as $monthlyTotalPrediction)
                                    <td align="center" style="vertical-align: middle;"><strong>{{ $monthlyTotalPrediction }}</strong></td>
                                @endforeach
                                <td></td>
                                <td align="center" style="vertical-align: middle;"><strong>{{ array_sum($monthlyTotalPredictions) }}</strong></td>
                            </tr>

                            <!-- Add a blank row to separate material codes -->
                            <tr>
                                <td colspan="13" style="height:60px;"></td>
                            </tr>
                            @endif
                            <!-- Reset monthly total predictions for the new material code -->
                            @php
                                $monthlyTotalPredictions = array_fill(0, count($mon), 0);
                            @endphp
                        @endif

                        <tr>
                            <td  rowspan = 2 align="center" style="vertical-align: middle;">{{$i++}}</td>
                            <td  rowspan = 2 align="center" style="vertical-align: middle;">{{ $material->material_code }}</td>
                            <td  rowspan = 2 align="center" style="vertical-align: middle;">{{ $material->material_name }}</td>
                            <td  rowspan = 2 align="center" style="vertical-align: middle;">{{ $material->item_no }}</td>
                            <td  rowspan = 2 align="center" style="vertical-align: middle;">{{ $material->vendor_code }}</td>
                            <td  rowspan = 2 align="center" style="vertical-align: middle;">{{ $material->unit_of_measure }}</td>
                            <td  rowspan = 2 align="center" style="vertical-align: middle;">{{ $material->quantity_material }}</td>
                            @php
                            $total = 0;
                        @endphp

                            @foreach ($qforecast[$loop->index] as $index => $value)
                                @php
                                    $calculation = $value * $material->quantity_material;
                                    $total += $calculation;
                                    
                                    $monthlyTotalPredictions[$index] += $calculation;
                                @endphp
                                
                                    <td align="center" style="vertical-align: middle;">
                                        {{ $value }}
                                    </td>
                                    
                            @endforeach
                        </tr>
                        

                   
                       
                        <tr>
                            @foreach ($qforecast[$loop->index] as $index => $value)
                                @php
                                    $calculation = $value * $material->quantity_material;
                                    $total += $calculation;
                                    
                                @endphp
                                
                                <td align="center" style="vertical-align: middle;">
                                    <b>{{ $calculation }}</b>
                                </td>
                                
                            @endforeach
                        </tr>
                          
                        @php
                            $previousMaterialCode = $material->material_code;
                        @endphp
                    @endforeach




                    <!-- Calculate and display the total prediction per month for the last material code -->
                    <tr>
                    <td colspan="7" align=right>Total Akhir</td>
                        

                        @foreach ($monthlyTotalPredictions as $monthlyTotalPrediction)
                            <td align="center" style="vertical-align: middle;"><strong>{{ $monthlyTotalPrediction }}</strong></td>
                        @endforeach
                        <td></td>
                        <td align="center" style="vertical-align: middle;"><strong>{{ array_sum($monthlyTotalPredictions) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

   
</body>
</html>