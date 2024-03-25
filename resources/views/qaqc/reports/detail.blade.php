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
    <section>
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('qaqc.home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('qaqc.report.index') }}">Reports</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                @php
                    $user = Auth::user();
                @endphp
                @if ($user->department->name == 'QC' && $user->specification->name == 'INSPECTOR')
                    <button class="btn btn-outline-primary me-2"
                        @if ($report->has_been_emailed) data-bs-target="#send-mail-confirmation-modal"
                    @else
                        data-bs-target="#send-mail-modal" @endif
                        data-bs-toggle="modal">
                        <i class='bx bx-envelope'></i> Send mail
                    </button>
                    @include('partials.send-mail-modal', ['report' => $report])
                    @include('partials.send-mail-confirmation')
                    {{--
                    <a href="{{ route('qaqc.report.sendEmail', $report->id) }}" class="btn btn-outline-secondary">Test
                        email</a> --}}
                @endif
                <button class="btn btn-outline-primary" data-bs-target="#upload-files-modal" data-bs-toggle="modal">
                    <i class='bx bx-upload'></i> Upload
                </button>
                @include('partials.upload-files-modal', ['doc_id' => $report->doc_num])
            </div>
        </div>
    </section>

    <div class="mt-4">
        @include('partials.alert-success-error')
    </div>

    <section aria-label="header" class="container">
        <div class="row text-center mt-5">
            <div class="col">
                @php
                    $currentUser = Auth::user();
                @endphp
                <h2>QC Inspector</h2>
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2" id="autographuser1"></div>
                {{-- @if (Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == 'INSPECTOR')
                    <button id="btn1" class="btn btn-primary" onclick="addAutograph(1, {{ $report->id }})">Acc QC Inspector</button>
                @endif --}}
            </div>

            <div class="col">
                <h2>QC Leader</h2>
                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2 border-1" id="autographuser2"></div>
                @if (Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == 'LEADER')
                    <button id="btn2" class="btn btn-primary" onclick="addAutograph(2, {{ $report->id }})">Acc QC
                        Leader</button>
                @endif
            </div>

            <div class="col">
                <h2>QC Head</h2>
                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2 border-1" id="autographuser3"></div>
                @if (Auth::check() &&
                        $currentUser->department->name == 'QC' &&
                        $currentUser->specification->name == 'HEAD' &&
                        ($report->autograph_1 || $report->autograph_2) != null)
                    <button id="btn3" class="btn btn-primary"
                        onclick="addAutograph(3, {{ $report->id }}, {{ $user->id }})">Acc QC Head</button>
                @endif
            </div>
        </div>
    </section>

    <section aria-label="table-report" class="container mt-5">
        <div class="card">
            <div class="pt-4 text-center">
                <span class="h1 fw-semibold">Verification Reports</span> <br>
                <div class="mt-1">
                    <span class="fs-5">{{ $report->doc_num ?? '-' }} </span> <br>
                    <span class="fs-6 ">Created By : {{ $report->created_by ?? '-' }} </span>
                </div>
                @include('partials.vqc-status-badge')
                <hr>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderlesss">
                        <tbody>
                            <tr>
                                <th>Rec Date</th>
                                <td>: @formatDate($report->rec_date)</td>
                                <th>Customer</th>
                                <td>: {{ $report->customer }}</td>
                            </tr>
                            <tr>
                                <th>Verify Date</th>
                                <td>: @formatDate($report->verify_date)</td>
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
                                                        <td style="background-color: transparent; width:34%;">
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
                                                        <td style="background-color: transparent; width:34%;">
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
                                <td colspan="9">No data</td>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="uploaded">
        @include('partials.uploaded-section', [
            'showDeleteButton' => Auth::user()->name == $report->autograph_user_1,
        ])
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
            checkAutographStatus({{ $report->id }});
        };
    </script>
@endpush
