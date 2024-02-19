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
                @if(Auth::check() &&  Auth::user()->department && Auth::user()->is_head == 1 && Auth::user()->department == $userCreatedBy->department)
                    <button id="btn2" class="btn btn-primary" onclick="addAutograph(2, {{ $purchaseRequests->id }})">Acc Dept Head</button>
                @endif
            </div>

            <div class="col">
                <h2>Verificator</h2>
                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2 border-1" id="autographuser3"></div>
                @if(Auth::check() && Auth::user()->department->name == "HRD" && Auth::user()->is_head == 1)
                    <button id="btn3" class="btn btn-primary" onclick="addAutograph(3, {{ $purchaseRequests->id }})">Acc Verificator</button>
                @endif
            </div>

            <div class="col">
                <h2>Director</h2>
                <div class="autograph-box container" id="autographBox4"></div>
                <div class="container mt-2 border-1" id="autographuser4"></div>
                @if(Auth::check() && Auth::user()->department->name == 'DIRECTOR')
                    <button id="btn4" class="btn btn-primary" onclick="addAutograph(4, {{ $purchaseRequests->id }}, {{$user->id}})">Acc Director</button>
                @endif
            </div>
        </div>
    </section>




<section aria-label="table-report" class="container mt-5">
        <div class="card">
            <div class="mt-4 text-center">
                <span class="h1 fw-semibold">Purchase Requisition</span> <br>
                <div class="fs-6 mt-2">
                    <span class="fs-6 text-secondary">Created By : </span> {{ $userCreatedBy->name }} <br>
                    <span class="fs-6 text-secondary">From Department : </span> {{ $userCreatedBy->department->name }}
                </div>
            </div>
            <hr>

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
                            <th>Remark</th>
                                <td>: {{ $purchaseRequests->remark }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover text-center table-striped mb-0">
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
                                <td> @currency($detail->unit_price) </td>
                                <td> @currency($detail->quantity * $detail->unit_price) </td>
                                @php
                                    $totalall += $detail->quantity * $detail->unit_price; // Update the total
                                @endphp
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Total</strong></td>
                                <td class="table-active fw-semibold">@currency($totalall)</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>


@endsection
<script>
    // Function to add autograph to the specified box
    function addAutograph(section, prId) {
        // Get the div element
        var autographBox = document.getElementById('autographBox' + section);

        console.log('Section:', section);
        console.log('Report ID:', prId);
        var username = '{{ Auth::check() ? Auth::user()->name : '' }}';
        console.log('username :', username);
        var imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');
        console.log('image path :', imageUrl);

        autographBox.style.backgroundImage = "url('" +imageUrl + "')";

         // Make an AJAX request to save the image path
        fetch('/save-signature-path/' + prId + '/' + section, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                imagePath: imageUrl,
            }),
        })
        .then(response => response.json())
        .then(data => {
            console.log(data.message);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
        });

        checkAutographStatus(prId);
    }


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
            autograph_name_4: '{{ $purchaseRequests->autograph_user_4 ?? null }}',
        };

        // Loop through each autograph status and update the UI accordingly
        for (var i = 1; i <= 4; i++) {
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
