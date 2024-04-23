@extends('layouts.pdf')

@section('content')
    <style>
        .autograph-box {
            width: 200px;
            /* Adjust the width as needed */
            height: 100px;
            /* Adjust the height as needed */
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid #ccc;
            /* Add border for better visibility */
        }
    </style>
    {{-- <table class="table table-borderless">
        <tbody>
            <tr class="text-center">
                <td>
                    <h2>QA Inspector</h2>
                    @if ($report->autograph_1 != null)
                        @php
                            $path = $report->autograph_1;
                            $type = pathInfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $base64img = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        @endphp
                        <img class="autograph-box container" src="{{$base64img}}" alt="" srcset="">
                    @else
                        <div class="autograph-box container"></div>
                    @endif


                    <div class="container mt-2" id="autographuser1">{{$report->autograph_user_1}}</div>
                </td>
                <td>
                    <h2>QA Leader</h2>
                    @if ($report->autograph_2 != null)
                        @php
                            $path = $report->autograph_2;
                            $type = pathInfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $base64img = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        @endphp
                        <img class="autograph-box container" src="{{ $base64img }}" alt="" srcset="">
                    @else
                        <div class="autograph-box container"></div>
                    @endif
                    <div class="container mt-2 border-1" id="autographuser2">{{$report->autograph_user_2}}</div>
                </td>
                <td>
                    <h2>QC HEAD</h2>
                    @if ($report->autograph_3 != null)
                        @php
                            $path = $report->autograph_3;
                            $type = pathInfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $base64img = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        @endphp
                        <img class="autograph-box container" src="{{ $base64img }}" alt="" srcset="">
                    @else
                        <div class="autograph-box container"></div>
                    @endif
                    <div class="container mt-2 border-1" id="autographuser3">{{$report->autograph_user_3}}</div>
                </td>
            </tr>
        </tbody>
    </table> --}}
    <div class="pt-4 pb-5 text-center">
        <span class="h3 fw-semibold">Verification Reports</span>
        {{-- <p class="fs-5 mt-2">Created By : {{ $report->created_by ?? '-'}} </p>
            @if ($report->autograph_1 && $report->autograph_2 && $report->autograph_3 && $report->is_approve === 1)
                <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
            @elseif($report->is_approve === 0)
                <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>
            @elseif($report->autograph_1 && $report->autograph_2 && $report->autograph_3)
                <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ON APPROVAL</span>
            @else
                <span class="badge text-bg-secondary px-3 py-2 fs-6">WAITING SIGNATURE</span>
            @endif --}}
        <div class="mt-1">
            <span class="fs-5">{{ $report->doc_num ?? '-' }} </span> <br>
            <span class="fs-6 ">Created By : {{ $report->created_by ?? '-' }} </span>
        </div>
    </div>
    <table class="table table-borderlesss">
        <tbody>
            <tr>
                <th>Rec Date</th>
                <td>: {{ \Carbon\Carbon::parse($report->rec_date)->format('d-m-Y') }}</td>
                <th>Customer</th>
                <td>: {{ $report->customer }}</td>
            </tr>
            <tr>
                <th>Verify Date</th>
                <td>: {{ \Carbon\Carbon::parse($report->verify_date)->format('d-m-Y') }}</td>
                <th>Invoice No</th>
                <td>: {{ $report->invoice_no }}</td>
            </tr>
        </tbody>
    </table>
    <table class="table table-bordered text-center table-striped">
        <thead>
            <tr>
                <th class="align-middle">No</th>
                <th class="align-middle">Part Name</th>
                <th class="align-middle">Rec Quantity</th>
                <th class="align-middle">Verify Quantity</th>
                <th class="align-middle">Can Use</th>
                <th class="align-middle">Can't Use</th>
                <th class="align-middle">Daijo Defect Detail</th>
                <th class="align-middle">Customer Defect Detail</th>
                <th class="align-middle">Price per Quantity</th>
                <th class="align-middle">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($report->details as $detail)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $detail->part_name }}</td>
                    <td style="width: 5%">{{ $detail->rec_quantity }}</td>
                    <td style="width: 5%">{{ $detail->verify_quantity }}</td>
                    <td style="width: 5%">{{ $detail->can_use }}</td>
                    <td style="width: 5%">{{ $detail->cant_use }}</td>
                    <td style="width: 40%">
                        @foreach ($detail->defects as $defect)
                            @if ($defect->is_daijo)
                                {{ $defect->quantity . ' : ' . ($defect->category ? $defect->category->name : '-') . ' (' . $defect->remarks . ') ' }}
                                <br>
                            @endif
                        @endforeach
                    </td>
                    <td>
                        @foreach ($detail->defects as $defect)
                            @if (!$defect->is_daijo)
                                {{ $defect->quantity . ' : ' . ($defect->category ? $defect->category->name : '-') . ' (' . $defect->remarks . ') ' }}
                                <br>
                            @endif
                        @endforeach
                    </td>
                    <td width="15%"> @currency($detail->price) </td>
                    <td width="15%"> @currency($detail->price * $detail->rec_quantity) </td>
                </tr>
            @empty
                <td colspan="11">No data</td>
            @endforelse
        </tbody>
    </table>
@endsection
