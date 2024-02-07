@extends('layouts.app')

<meta name="csrf-token" content="{{ csrf_token() }}">

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

    <section>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('director.home')}}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{route('director.qaqc.index')}}">Qa & QC Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail</li>
            </ol>
        </nav>
    </section>

    <section aria-label="header" class="container">
        <div class="row text-center">
            <div class="col">
                <h2>QA Inspector</h2>
                @if(Auth::check() && Auth::user()->department == 'QA')
                    <button class="btn btn-primary" onclick="addAutograph(1, {{ $report->id }})">Acc QA Inspector</button>
                @endif
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2" id="autographuser1"></div>
            </div>

            <div class="col">
                <h2>QA Leader</h2>
                @if(Auth::check() && Auth::user()->department == 'QA')
                    <button class="btn btn-primary" onclick="addAutograph(2, {{ $report->id }})">Acc QA Leader</button>
                @endif

                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2 border-1" id="autographuser2"></div>
            </div>

            <div class="col">
                <h2>QC HEAD</h2>
                @if(Auth::check() && Auth::user()->department == 'QC')
                    <button class="btn btn-primary" onclick="addAutograph(3, {{ $report->id }}, {{$user->id}})">Acc QC Head</button>
                @endif

                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2 border-1" id="autographuser3"></div>
            </div>
        </div>
    </section>

    <section aria-label="table-report" class="container mt-5">
        <div class="card">
            <div class="mx-3 mt-4 mb-5 text-center">
                <span class="h1 fw-semibold">Verification Reports</span>
                <p class="fs-5 mt-2">Created By : {{ $report->created_by }}</p>
                <hr>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderlesss">
                        <tbody>
                            <tr>
                                <th>Rec Date</th>
                                <td>: {{ $report->rec_date }}</td>
                                <th>Customer</th>
                                <td>: {{ $report->customer }}</td>
                            </tr>
                            <tr>
                                <th>Verify Date</th>
                                <td>: {{ $report->verify_date }}</td>
                                <th>Invoice No</th>
                                <td>: {{ $report->invoice_no }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover text-center table-striped">
                        <thead>
                            <tr>
                                <th class="align-middle">No</th>
                                <th class="align-middle">Part Name</th>
                                <th class="align-middle">Rec Quantity</th>
                                <th class="align-middle">Verify Quantity</th>
                                <th class="align-middle">Production Date</th>
                                <th class="align-middle">Shift</th>
                                <th class="align-middle">Can Use</th>
                                <th class="align-middle">Cant Use</th>
                                <th class="align-middle">Customer Defect Detail</th>
                                <th class="align-middle">Daijo Defect Detail</th>
                                <th class="align-middle">Remark</th>

                                <!-- Add more headers as needed -->
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($report->details as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $detail->part_name}}</td>
                                    <td>{{ $detail->rec_quantity}}</td>
                                    <td>{{ $detail->verify_quantity}}</td>
                                    <td>{{ $detail->prod_date}}</td>
                                    <td>{{ $detail->shift}}</td>
                                    <td>{{ $detail->can_use}}</td>
                                    <td>{{ $detail->cant_use}}</td>
                                    <td>
                                        @foreach ($detail->customer_defect_detail as $key => $value)
                                            @if (!is_null($value))
                                                {{ $key }}: {{ $value }}<br>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ($detail->daijo_defect_detail as $key => $value)
                                            @if (!is_null($value))
                                                {{ $key }}: {{ $value }}<br>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ($detail->remark as $key => $value)
                                            @if (!is_null($value))
                                                {{ $key }}: {{ $value }}<br>
                                            @endif
                                        @endforeach
                                    </td>

                                @empty
                                    <td colspan="12"> No data</td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="approval" class="container mt-5 mb-8">
        @if($report->is_approve === null)
            <div class="modal fade" id="rejectModal">
                <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="{{route('director.qaqc.reject', ['id' => $report->id])}}" method="post">
                                @method('PUT')
                                @csrf
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5">Reason</h1>
                                </div>
                                <div class="modal-body">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" class="form-control" placeholder="Tell us why you rejecting this report..." required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Confirm</button>
                                </div>
                            </form>
                        </div>
                </div>
            </div>

            <div class="container text-center">
                <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                <form action="{{route('director.qaqc.approve', ['id' => $report->id])}}" method="post" class="d-inline">
                    @method('PUT')
                    @csrf
                    <button class="btn btn-success btn-lg" type="submit">Approve</button>
                </form>
            </div>
        @endif
    </section>

@endsection

@push('extraJs')
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

            autographBox.style.backgroundImage = "url('" +imageUrl + "')";

            // Make an AJAX request to save the image path
            fetch('/save-image-path/' + reportId + '/' + section, {
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
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function checkAutographStatus(reportId) {
        // Assume you have a variable from the server side indicating the autograph status
            var autographs = {
                autograph_1: '{{ $report->autograph_1 ?? null }}',
                autograph_2: '{{ $report->autograph_2 ?? null }}',
                autograph_3: '{{ $report->autograph_3 ?? null }}',
            };

            var autographNames = {
                autograph_name_1: '{{ $autographNames['autograph_name_1'] ?? null }}',
                autograph_name_2: '{{ $autographNames['autograph_name_2'] ?? null }}',
                autograph_name_3: '{{ $autographNames['autograph_name_3'] ?? null }}',
            };

            // Loop through each autograph status and update the UI accordingly
            for (var i = 1; i <= 3; i++) {
                var autographBox = document.getElementById('autographBox' + i);
                var autographInput = document.getElementById('autographInput' + i);
                var autographNameBox = document.getElementById('autographuser' + i);

                // Check if autograph status is present in the database
                if (autographs['autograph_' + i]) {
                    autographBox.style.display = 'block';

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
            checkAutographStatus({{ $report->id }});
        };
    </script>
@endpush



