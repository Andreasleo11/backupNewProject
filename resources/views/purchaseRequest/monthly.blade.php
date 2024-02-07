@extends('layouts.app')

@section('content')


<form action="/purchaserequest/month-selected" method="get">
    <label for="month">Select a month:</label>
    <input type="month" id="month" name="month">
    <button type="submit">Submit</button>
</form>


<section aria-label="table-report" class="container mt-5">
        <div class="card">
            <div class="mx-3 mt-4 mb-5 text-center">
                <span class="h1 fw-semibold">Purchase Requisition</span>
                <hr>
            </div>

        
            <div class="card-body">
            @foreach($purchaseRequests as $pr)
                <div class="table-responsive">
                    <table class="table table-borderlesss">
                        <tbody>
                            <tr>
                                <th>Date PR</th>
                                <td>: {{ $pr->date_pr }}</td>
                                <th>Date Required</th>
                                <td>: {{ $pr->date_required }}</td>
                            </tr>
                            <tr>
                                <th>To Department</th>
                                <td>: {{ $pr->to_department }}</td>
                                <th>PR No</th>
                                <td>: {{ $pr->pr_no }}</td>
                            </tr>
                            <tr>
                            <th>Supplier</th>
                                <td>: {{ $pr->supplier }}</td>
                            <th>remark</th>
                                <td>: {{ $pr->remark }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-hover text-center table-striped">
                            <thead>
                                <tr>
                                    <th class="align-middle">No</th>
                                    <th class="align-middle">Item Name</th>
                                    <th class="align-middle">Quantity</th>
                                    <th class="align-middle">Purpose</th>
                                    <th class="align-middle">Unit Price</th>
                                    <th class="align-middle">Total</th>
    
                                </tr>
                            </thead>
                            @php
                                $totalall = 0; // Initialize the variable
                            @endphp
                            <tbody>
                                @foreach($pr->itemDetail as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $detail->item_name}}</td>
                                    <td>{{ $detail->quantity}}</td>
                                    <td>{{ $detail->purpose}}</td>
                                    <td>{{ $detail->unit_price}}</td>
                                    <td>{{$detail->quantity * $detail->unit_price }}</td>
                                    @php
                                        $totalall += $detail->quantity * $detail->unit_price; // Update the total
                                    @endphp
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-right"><strong>Total:</strong></td>
                                    <td>{{ $totalall }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection