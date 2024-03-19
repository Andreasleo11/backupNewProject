@extends('layouts.app')

<meta name="csrf-token" content="{{ csrf_token() }}">

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
        <div class="row text-center">
            <div class="col">
                @php
                    $currentUser = Auth::user();
                @endphp
                <h2>QC Inspector</h2>
                @if (Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == 'INSPECTOR')
                    <button class="btn btn-primary" onclick="addAutograph(1, {{ $report->id }})">Acc QC Inspector</button>
                @endif
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2" id="autographuser1"></div>
            </div>

            <div class="col">
                <h2>QC Leader</h2>
                @if (Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == 'LEADER')
                    <button class="btn btn-primary" onclick="addAutograph(2, {{ $report->id }})">Acc QC Leader</button>
                @endif

                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2 border-1" id="autographuser2"></div>
            </div>

            <div class="col">
                <h2>QC HEAD</h2>
                @if (Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == 'HEAD')
                    <button class="btn btn-primary" onclick="addAutograph(3, {{ $report->id }}, {{ $user->id }})">Acc
                        QC Head</button>
                @endif

                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2 border-1" id="autographuser3"></div>
            </div>
        </div>
    </section>

    <section aria-label="table-report" class="container mt-5">
        <div class="card">
            <div class="pt-4 pb-5 text-center">
                <span class="h1 fw-semibold">Verification Reports</span>
                <p class="fs-5 mt-2">Created By : {{ $report->created_by }}</p>
                <span
                    class="badge rounded-pill @if ($report->is_approve === 1) text-bg-success @elseif($report->is_approve === 0) text-bg-danger @else text-bg-warning @endif px-3 py-2 fs-6 fw-medium">
                    @if ($report->is_approve === 1)
                        APPROVED
                    @elseif($report->is_approve === 0)
                        REJECTED
                    @else
                        WAITING TO BE APPROVED
                    @endif
                </span>
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
                        <thead class="align-middle">
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">Part Name</th>
                                <th rowspan="2">Rec Quantity</th>
                                <th rowspan="2">Verify Quantity</th>
                                <th rowspan="2">Can Use</th>
                                <th rowspan="2">Can't Use</th>
                                <th colspan="3">Daijo Defect</th>
                                <th colspan="3">Customer Defect</th>
                            </tr>
                            <tr>
                                <th>Quantity</th>
                                <th>Category</th>
                                <th>Remark</th>
                                <th>Quantity</th>
                                <th>Category</th>
                                <th>Remark</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($report->details as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $detail->part_name }}</td>
                                    <td>{{ $detail->rec_quantity }}</td>
                                    <td>{{ $detail->verify_quantity }}</td>
                                    <td>{{ $detail->can_use }}</td>
                                    <td>{{ $detail->cant_use }}</td>
                                    <td colspan="3" class="p-0">
                                        @foreach ($detail->defects as $defect)
                                            @if ($defect->is_daijo)
                                                <table class="table table-borderless mb-0">
                                                    <tbody class="text-center">
                                                        <td style="background-color: transparent; width:33%;">
                                                            {{ $defect->quantity }}</td>
                                                        <td style="background-color: transparent">
                                                            {{ $defect->category->name }}</td>
                                                        <td style="background-color: transparent"> {{ $defect->remarks }}
                                                        </td>
                                                    </tbody>
                                                </table>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td colspan="3" class="p-0">
                                        @foreach ($detail->defects as $defect)
                                            @if (!$defect->is_daijo)
                                                <table class="table table-borderless mb-0">
                                                    <tbody class="text-center">
                                                        <td style="background-color: transparent; width:33%;">
                                                            {{ $defect->quantity }}</td>
                                                        <td style="background-color: transparent">
                                                            {{ $defect->category->name }}</td>
                                                        <td style="background-color: transparent"> {{ $defect->remarks }}
                                                        </td>
                                                    </tbody>
                                                </table>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <td colspan="11">No data</td>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="uploaded">
        @include('partials.uploaded-section')
    </section>

    <section aria-label="approval" class="container mt-5 mb-8">
        @if ($report->is_approve === null)
            @include('partials.reject-modal', ['id' => $report->id])

            <div class="container text-center">
                <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                    data-bs-target="#rejectModal">Reject</button>
                <form action="{{ route('director.qaqc.approve', ['id' => $report->id]) }}" method="post" class="d-inline">
                    @method('PUT')
                    @csrf
                    <button class="btn btn-success btn-lg" type="submit">Approve</button>
                </form>
            </div>
        @elseif(!isset($report->attachment))
            <div class="text-center">No attachment, can't proceed to approve or reject this document.</div>
        @endif
    </section>

@endsection

@push('extraJs')
    <script>
        // Function to add autograph to the specified box
        function addAutograph(section, reportId) {
            // Get the div element
            var autographBox = document.getElementById('autographBox' + section);
            var username = '{{ Auth::check() ? Auth::user()->name : '' }}';
            var imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');

            autographBox.style.backgroundImage = "url('" + imageUrl + "')";

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
        window.onload = function() {
            checkAutographStatus({{ $report->id }});
        };
    </script>
@endpush
