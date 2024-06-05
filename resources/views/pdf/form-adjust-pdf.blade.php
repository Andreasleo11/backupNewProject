@extends('layouts.pdf')

@section('content')

<div>
    <table class="table table-bordered text-center table-striped">
    <thead>
        <tr>
        <th rowspan="2">No</th>
        <th rowspan="2">Part No</th>
        <th rowspan="2">Description</th>
        <th colspan="2">Quantity Adjust</th>
        <th rowspan="2">Measure</th>
        <th colspan="2">Verification Result</th>
        <th rowspan="2">Warehouse</th>
        <th rowspan="2">Remark</th>
        @if(auth()->user()->name === 'Ari')
        <th rowspan="2">Action</th>
        @endif
        </tr>
        <tr>
        <th>+</th>
        <th>-</th>
        <th>Can Use</th>
        <th>Daijo Defect</th>
         </tr>
    </thead>



<tbody>
    @foreach($datas->report->details as $detail)
    <tr>
        <td>{{ $loop->iteration }}</td>
        @php
        $partName = $detail->part_name;
        list($partNumber, $partDescription) = explode('/', $partName, 2);
        @endphp
        <td>{{$partNumber}}</td>
        <td>{{$partDescription}}</td>
        <td>-</td>
        <td>{{$detail->rec_quantity}}</td>
        <td>{{$detail->fg_measure}}</td>
        <td>{{$detail->can_use}}</td>
        <td>{{$detail->cant_use}}</td>
        <td>{{$detail->fg_warehouse_name}}</td>
        <td>{{$detail->remark}}</td>
    </tr>
        @foreach($detail->adjustdetail as $adjustDetail)
        <tr>
            @php
            $totalquantity = ($adjustDetail->rm_quantity * $detail->rec_quantity) * 90 / 100;
            @endphp
            <td></td>
            <td>{{$adjustDetail->rm_code}}</td>
            <td>{{$adjustDetail->rm_description}}</td>
            <td>{{$totalquantity}}</td>
            <td>-</td>
            <td>{{$adjustDetail->rm_measure}}</td>
            <td>-</td>
            <td>-</td>
            <td>{{$adjustDetail->warehouse_name}}</td>
            <td>{{$adjustDetail->remark}}</td>
        </tr>
        @endforeach
    @endforeach


</tbody>



</table>
</div>


@endsection
