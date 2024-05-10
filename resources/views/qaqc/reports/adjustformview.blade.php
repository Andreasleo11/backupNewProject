@extends('layouts.app')

@push('extraCss')
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
@endpush

@section('content')



<section aria-label="header" class="container">
        <div class="row text-center mt-5">
            <div class="col">
                @php
                    $currentUser = Auth::user();
                   
                @endphp
                <h3>QC Head</h3>
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2" id="autographuser1"></div>
                @if (Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == 'HEAD')
                    <button id="btn1" class="btn btn-primary" onclick="addAutograph(1, {{ $datas->id }})">Acc QC Head</button>
                @endif 
            </div>

            <div class="col">
                <h3>PPIC Head</h3>
                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2 border-1" id="autographuser2"></div>
                @if (Auth::check() && $currentUser->department->name == 'PPIC' && $currentUser->specification->name == 'HEAD')
                    <button id="btn2" class="btn btn-primary" onclick="addAutograph(2, {{ $datas->id }})">Acc PPIC
                        Head</button>
                @endif
            </div>

            <div class="col">
                <h3>STORE Head</h3>
                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2 border-1" id="autographuser3"></div>
                @if (Auth::check() &&
                        $currentUser->department->name == 'STORE' &&
                        $currentUser->specification->name == 'HEAD')
                    <button id="btn3" class="btn btn-primary"
                        onclick="addAutograph(3, {{ $datas->id }})">Acc Store Head</button>
                @endif
            </div>

            <div class="col">
                <h3>LOGISTIC Head</h3>
                <div class="autograph-box container" id="autographBox4"></div>
                <div class="container mt-2 border-1" id="autographuser4"></div>
                @if (Auth::check() &&
                        $currentUser->department->name == 'LOGISTIC' &&
                        $currentUser->specification->name == 'HEAD')
                    <button id="btn4" class="btn btn-primary"
                        onclick="addAutograph(4, {{ $datas->id }})">Acc Logistic Head</button>
                @endif
            </div>

            <div class="col">
                    <h3>GM</h3>
                    <div class="autograph-box container" id="autographBox5"></div>
                    <div class="container mt-2 border-1" id="autographuser5"></div>
                    @if (Auth::check() &&
                            $currentUser->department->name == 'PLASTIC INJECTION' &&
                            $currentUser->is_gm == 1)
                        <button id="btn5" class="btn btn-primary"
                            onclick="addAutograph(5, {{ $datas->id }})">Acc GM </button>
                    @endif
            </div>

            <div class="col">
                    <h3>Accounting Head</h3>
                    <div class="autograph-box container" id="autographBox6"></div>
                    <div class="container mt-2 border-1" id="autographuser6"></div>
                    @if (Auth::check() &&
                            $currentUser->department->name == 'ACCOUNTING' &&
                            $currentUser->specification->name == 'HEAD')
                        <button id="btn6" class="btn btn-primary"
                            onclick="addAutograph(6, {{ $datas->id }})">Acc Accounting Head</button>
                    @endif
            </div>

            <div class="col">
                    <h3>Director</h3>
                    <div class="autograph-box container" id="autographBox7"></div>
                    <div class="container mt-2 border-1" id="autographuser7"></div>
                    @if (Auth::check() &&
                            $currentUser->department->name == 'DIRECTOR')
                        <button id="btn7" class="btn btn-primary"
                            onclick="addAutograph(7, {{ $datas->id }})">Acc Director</button>
                    @endif
            </div>
        </div>
    </section>




<div class="table-responsive mt-4">
    <table class="table table-bordered table-hover text-center table-striped">
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
        @if(auth()->user()->name === 'Ari')
        <td>
        @include('partials.add_remark_modal')
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
             data-bs-target="#add-remark-modal-{{ $detail->id }}">
             + Add Remark
        </button>
        </td>
        @endif
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



<script>
        // Function to add autograph to the specified box
        function addAutograph(section, reportId) {
            // Get the div element
            var autographBox = document.getElementById('autographBox' + section);

            console.log('Section:', section);
            console.log('Report ID:', reportId);
            var username = '{{ Auth::check() ? Auth::user()->name : '' }}';
            console.log('username :', username);
            var imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');
            console.log('image path :', imageUrl);

            autographBox.style.backgroundImage = "url('" + imageUrl + "')";

            // Make an AJAX request to save the image path
            fetch('/save-autograph-path/' + reportId + '/' + section, {
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

            checkAutographStatus(reportId);
        }

        function checkAutographStatus(reportId) {
            // Assume you have a variable from the server side indicating the autograph status
            var autographs = {
                autograph_1: '{{ $datas->autograph_1 }}',
                autograph_2: '{{ $datas->autograph_2 }}',
                autograph_3: '{{ $datas->autograph_3  }}',
                autograph_4: '{{ $datas->autograph_4  }}',
                autograph_5: '{{ $datas->autograph_5  }}',
                autograph_6: '{{ $datas->autograph_6  }}',
                autograph_7: '{{ $datas->autograph_7  }}',
            };

            var autographNames = {};

            for (var key in autographs) {
                if (autographs.hasOwnProperty(key)) {
                    var autographNumber = key.split('_')[1]; // Extract the autograph number
                    var autographName = autographs[key]; // Get the autograph name
                     autographName = autographName.replace(/\.png$/, ''); // Remove the .png extension
                    autographNames['autograph_name_' + autographNumber] = autographName; // Append .png to autograph name
                }
            }

            console.log('name:', autographNames);

            // Loop through each autograph status and update the UI accordingly
            for (var i = 1; i <= 7; i++) {
                var autographBox = document.getElementById('autographBox' + i);
                var autographInput = document.getElementById('autographInput' + i);
                var autographNameBox = document.getElementById('autographuser' + i);
                var btnId = document.getElementById('btn' + i);
                console.log('testbox:', autographInput);
                



                // Check if autograph status is present in the database
                if (autographs['autograph_' + i]) {

                    if (btnId) {
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
        window.onload = function() {
            checkAutographStatus({{ $datas->id }});
        };
    </script>
 

@endsection