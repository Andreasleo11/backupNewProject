@extends('layouts.app')
@push('extraCss')
    <style>
        .autograph-box {
            width: 200px; /* Adjust the width as needed */
            height: 100px; /* Adjust the height as needed */
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid #ccc; /* Add border for better visibility */
        }
    </style>
@endpush

@section('content')

<section aria-label="header" class="container">
        <div class="row text-center">
            <div class="col">
                <h2>Preparation</h2>
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2" id="autographuser1"></div>
            </div>

            <div class="col">
                <h2>Dept Head</h2>
                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2 border-1" id="autographuser2"></div>
                @if(Auth::check() && $userCreatedBy->department && Auth::user()->is_head == 1)
                    <button id="btn2" class="btn btn-primary" onclick="addAutograph(2, {{ $purchaseRequests->id }})">Acc Dept Head</button>
                @endif
            </div>

            <div class="col">
                <h2>Verificator</h2>
                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2 border-1" id="autographuser3"></div>
                @if(Auth::check() && Auth::user()->department == "HRD" && Auth::user()->is_head == 1)
                    <button id="btn3" class="btn btn-primary" onclick="addAutograph(3, {{ $purchaseRequests->id }}, {{$user->id}})">Acc Verificator</button>
                @endif
            </div>

            <div class="col">
                <h2>He Who Remains</h2>
                <div class="autograph-box container" id="autographBox4"></div>
                <div class="container mt-2 border-1" id="autographuser4"></div>
                @if(Auth::check() && Auth::user()->department == 'Direktur')
                    <button id="btn3" class="btn btn-primary" onclick="addAutograph(4, {{ $purchaseRequests->id }}, {{$user->id}})">Acc He Who Remains</button>
                @endif
            </div>
        </div>
    </section>




<section aria-label="table-report" class="container mt-5">
        <div class="card">
            <div class="mx-3 mt-4 mb-5 text-center">
                <span class="h1 fw-semibold">Purchase Requisition</span>
                <p class="fs-5 mt-2">Created By : {{ $userCreatedBy->name }}</p>
                <p class="fs-5 mt-2">From Department : {{ $userCreatedBy->department }}</p>
                <hr>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderlesss">
                        <tbody>
                            <tr>
                                <th>Date PR</th>
                                <td>: {{ $purchaseRequests->date_pr }}</td>
                                <th>Date Required</th>
                                <td>: {{ $purchaseRequests->date_required }}</td>
                            </tr>
                            <tr>
                                <th>To Department</th>
                                <td>: {{ $purchaseRequests->to_department }}</td>
                                <th>PR No</th>
                                <td>: {{ $purchaseRequests->pr_no }}</td>
                            </tr>
                            <tr>
                            <th>Supplier</th>
                                <td>: {{ $purchaseRequests->supplier }}</td>
                            <th>remark</th>
                                <td>: {{ $purchaseRequests->remark }}</td>
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
                            @foreach($purchaseRequests->itemDetail as $detail)
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
            </div>
        </div>
    </section>


@endsection
<script>


function checkAutographStatus(reportId)
    {
        // Assume you have a variable from the server side indicating the autograph status
        var autographs = {
            autograph_1: '{{ $purchaseRequests->autograph_1 ?? null }}',
            autograph_2: '{{ $purchaseRequests->autograph_2 ?? null }}',
            autograph_3: '{{ $purchaseRequests->autograph_3 ?? null }}',
            autograph_4: '{{ $purchaseRequests->autograph_4 ?? null }}',
        };

        var autographNames = {
            autograph_name_1: '{{ $purchaseRequests->autograph_user_1 ?? null }}',
            autograph_name_2: '{{ $purchaseRequests->autograph_user_2 ?? null }}',
            autograph_name_3: '{{ $purchaseRequests->autograph_user_3 ?? null }}',
            autograph_name_3: '{{ $purchaseRequests->autograph_user_4 ?? null }}',
        };

        // Loop through each autograph status and update the UI accordingly
        for (var i = 1; i <= 3; i++) {
            var autographBox = document.getElementById('autographBox' + i);
            var autographInput = document.getElementById('autographInput' + i);
            var autographNameBox = document.getElementById('autographuser' + i);
            var btnId = document.getElementById('btn' + i);



            // Check if autograph status is present in the database
            if (autographs['autograph_' + i]) {

                if(btnId){
                    // console.log(btnId);
                    btnId.style.display = 'none';
                }

                // Construct URL based on the current location
                var url = '/' + autographs['autograph_' + i];

                // Update the background image using the URL
                autographBox.style.backgroundImage = "url('" + url + "')";

                var autographName = autographNames['autograph_name_' + i];
                autographNameBox.textContent = autographName;
                autographNameBox.style.display = 'block';
            }
        }
    }

    // Call the function to check autograph status on page load
    window.onload = function () {
        checkAutographStatus({{ $purchaseRequests->id }});
    };
</script>