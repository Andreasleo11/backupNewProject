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
    <section class="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('purchaserequest.home') }}">Purchase Requests</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </section>

    <div class="row">
        <div class="col"></div>
        <div class="col-auto">
            @if (Auth::user()->id == $userCreatedBy->id)
                <button class="btn btn-outline-primary" data-bs-target="#upload-files-modal" data-bs-toggle="modal">
                    <i class='bx bx-upload'></i> Upload
                </button>

                @include('partials.upload-files-modal', ['doc_id' => $purchaseRequest->doc_num])
            @endif
        </div>
    </div>

    <div class="mt-4">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class='bx bx-check-circle me-2' style="font-size:20px;"></i>
                {{ $message }}
                <button id="closeAlertButton" type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
            </div>
        @elseif ($errors->any())
            <div class="alert alert-danger alert-dismissable fade show" role="alert">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <button id="closeAlertButton" type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <section aria-label="header" class="container">
        <div class="row text-center">
            <div class="col">
                <h2>Preparation</h2>
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2" id="autographuser1"></div>
            </div>

            @include('partials.reject-pr-confirmation', $purchaseRequest)

            <div class="col">
                <h2>Dept Head</h2>
                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2 border-1" id="autographuser2"></div>
                @if (Auth::check() &&
                        Auth::user()->department &&
                        Auth::user()->is_head == 1 &&
                        Auth::user()->department == $userCreatedBy->department &&
                        $purchaseRequest->status == 1)
                    <div class="row px-4 d-flex justify-content-center">
                        <div class="col-auto me-3">
                            <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                class="btn btn-danger">Reject</button>
                        </div>
                        <div class="col-auto">
                            <button id="btn2" class="btn btn-success"
                                onclick="addAutograph(2, {{ $purchaseRequest->id }})">Approve</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col">
                <h2>Verificator</h2>
                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2 border-1" id="autographuser3"></div>
                @if (Auth::check() &&
                        Auth::user()->department->name == 'HRD' &&
                        Auth::user()->is_head == 1 &&
                        $purchaseRequest->status == 2)
                    <div class="row px-4 d-flex justify-content-center">
                        <div class="col-auto me-3">
                            <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                class="btn btn-danger">Reject</button>
                        </div>
                        <div class="col-auto">
                            <button id="btn3" class="btn btn-success"
                                onclick="addAutograph(3, {{ $purchaseRequest->id }})">Approve</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col">
                <h2>Director</h2>
                <div class="autograph-box container" id="autographBox4"></div>
                <div class="container mt-2 border-1" id="autographuser4"></div>
                @if (Auth::check() && Auth::user()->department->name == 'DIRECTOR' && $purchaseRequest->status == 3)
                    <div class="row px-4 d-flex justify-content-center ">
                        <div class="col-auto me-3">
                            <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                class="btn btn-danger">Reject</button>
                        </div>
                        <div class="col-auto">
                            <button id="btn4" class="btn btn-success"
                                onclick="addAutograph(4, {{ $purchaseRequest->id }}, {{ $user->id }})">Approve</button>
                        </div>
                    </div>
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
                                <td>: {{ $purchaseRequest->date_pr }}</td>
                                <th>Date Required</th>
                                <td>: {{ $purchaseRequest->date_required }}</td>
                            </tr>
                            <tr>
                                <th>To Department</th>
                                <td>: {{ $purchaseRequest->to_department }}</td>
                                <th>PR No</th>
                                <td>: {{ $purchaseRequest->pr_no }}</td>
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td>: {{ $purchaseRequest->supplier }}</td>
                                <th>Remark</th>
                                <td>: {{ $purchaseRequest->remark }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover text-center table-striped mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">No</th>
                                <th rowspan="2" class="align-middle">Item Name</th>
                                <th rowspan="2" class="align-middle">Quantity</th>
                                <th rowspan="2" class="align-middle">Purpose</th>
                                <th colspan="2" class="align-middle">Unit Price</th>
                                <th rowspan="2" class="align-middle">Subtotal</th>
                            </tr>
                            <tr>
                                <th>Before</th>
                                <th>Current</th>
                            </tr>
                        </thead>
                        @php
                            $totalall = 0; // Initialize the variable
                        @endphp
                        <tbody>
                            @forelse($purchaseRequest->itemDetail as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $detail->item_name }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td>{{ $detail->purpose }}</td>
                                    <td> @currency($detail->master->price) </td>
                                    <td> @currency($detail->price) </td>
                                    <td> @currency($detail->quantity * $detail->price) </td>
                                    @php
                                        $totalall += $detail->quantity * $detail->price; // Update the total
                                    @endphp
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">No Data</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-right"><strong>Total</strong></td>
                                <td class="table-active fw-semibold">@currency($totalall)</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="uploaded">
        @include('partials.uploaded-section', [
            'showDeleteButton' => Auth::user()->id == $userCreatedBy->id,
        ])
    </section>
@endsection

@push('extraJs')
    <script>
        // Function to add autograph to the specified box
        function addAutograph(section, prId) {
            // Get the div element
            var autographBox = document.getElementById('autographBox' + section);

            console.log('Section:', section);
            console.log('Report ID:', prId);
            var username = '{{ Auth::check() ? Auth::user()->name : '' }}';
            var imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');
            console.log('username :', username);
            console.log('image path :', imageUrl);

            autographBox.style.backgroundImage = "url('" + imageUrl + "')";

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


        function checkAutographStatus(reportId) {
            // Assume you have a variable from the server side indicating the autograph status
            var autographs = {
                autograph_1: '{{ $purchaseRequest->autograph_1 ?? null }}',
                autograph_2: '{{ $purchaseRequest->autograph_2 ?? null }}',
                autograph_3: '{{ $purchaseRequest->autograph_3 ?? null }}',
                autograph_4: '{{ $purchaseRequest->autograph_4 ?? null }}',
            };

            var autographNames = {
                autograph_name_1: '{{ $purchaseRequest->autograph_user_1 ?? null }}',
                autograph_name_2: '{{ $purchaseRequest->autograph_user_2 ?? null }}',
                autograph_name_3: '{{ $purchaseRequest->autograph_user_3 ?? null }}',
                autograph_name_4: '{{ $purchaseRequest->autograph_user_4 ?? null }}',
            };

            // Loop through each autograph status and update the UI accordingly
            for (var i = 1; i <= 4; i++) {
                var autographBox = document.getElementById('autographBox' + i);
                var autographInput = document.getElementById('autographInput' + i);
                var autographNameBox = document.getElementById('autographuser' + i);
                var btnId = document.getElementById('btn' + i);


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
            checkAutographStatus({{ $purchaseRequest->id }});
        };
    </script>
@endpush
