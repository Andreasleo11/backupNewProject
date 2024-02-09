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
                <h2>Admin</h2>
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2 border-1" id="autographuser1"></div>
                @if(Auth::check() && Auth::user()->name == "Vicky" )
                    <button id="btn1" class="btn btn-primary" onclick="addAutograph(1, {{ $monthdetail->id }})">Acc Admin</button>
                @endif
            </div>

            <div class="col">
                <h2>Verificator</h2>
                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2 border-1" id="autographuser2"></div>
                @if(Auth::check() && Auth::user()->department == "HRD" && Auth::user()->is_head == 1)
                    <button id="btn2" class="btn btn-primary" onclick="addAutograph(2, {{ $monthdetail->id }})">Acc Verificator</button>
                @endif
            </div>

            <div class="col">
                <h2>He Who Remains</h2>
                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2 border-1" id="autographuser3"></div>
                @if(Auth::check() && Auth::user()->department == 'DIREKTUR')
                    <button id="btn3" class="btn btn-primary" onclick="addAutograph(3, {{ $monthdetail->id }})">Acc He Who Remains</button>
                @endif
            </div>
    </div>
</section>



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

<script>
    // Function to add autograph to the specified box
    function addAutograph(section, monthprId) {
        // Get the div element
        var autographBox = document.getElementById('autographBox' + section);

        console.log('Section:', section);
        console.log('Report ID:', monthprId);
        var username = '{{ Auth::check() ? Auth::user()->name : '' }}';
        console.log('username :', username);
        var imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');
        console.log('image path :', imageUrl);

        autographBox.style.backgroundImage = "url('" +imageUrl + "')";

         // Make an AJAX request to save the image path
        fetch('/save-signature-path-monthlydetail/' + monthprId + '/' + section, {
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

        checkAutographStatus(monthprId);
    }


    function checkAutographStatus(monthprId)
    {
        // Assume you have a variable from the server side indicating the autograph status
        var autographs = {
            autograph_1: '{{ $monthdetail->autograph_1 ?? null }}',
            autograph_2: '{{ $monthdetail->autograph_2 ?? null }}',
            autograph_3: '{{ $monthdetail->autograph_3 ?? null }}',
        };

        var autographNames = {
            autograph_name_1: '{{ $monthdetail->autograph_user_1 ?? null }}',
            autograph_name_2: '{{ $monthdetail->autograph_user_2 ?? null }}',
            autograph_name_3: '{{ $monthdetail->autograph_user_3 ?? null }}',
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
        checkAutographStatus({{ $monthdetail->id }});
    };
    
</script>