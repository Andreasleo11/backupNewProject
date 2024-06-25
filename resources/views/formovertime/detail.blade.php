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

        .rejection-textarea {
            background-color: #ffe6e6;
            border: 1px solid #ff0000;
            font-size: 1rem;
            padding: 10px;
            resize: none;
        }
    </style>
@endpush

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('formovertime.index') }}">Form Overtime</a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    @include('partials.edit-form-overtime-modal', [
        'prheader' => $header,
        'datas' => $datas,
    ])
    <div class="row">
        <div class="col text-end">
            <button data-bs-target="#edit-form-overtime-modal-{{ $header->id }}" data-bs-toggle="modal"
                class="btn btn-primary"><i class='bx bx-edit'></i> Edit</button>
            <a href="{{ route('export.overtime', $header->id) }}" class="btn btn-success">Export to Excel</a>
        </div>
    </div>

    <section aria-label="autographs" class="container mt-3">

        <div class="row text-center">
            {{-- PREPARATION AUTOGRAPH --}}
            <div class="col my-2">
                <h2>Preparation</h2>
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2" id="autographuser1"></div>
            </div>
            @php
                $currentUser = Auth::user();

            @endphp


            @if ($header->Relationdepartement->name === 'MOULDING')

                <div class="col my-2">
                    <h2>Supervisor</h2>
                    <div class="autograph-box container" id="autographBox2"></div>
                    <div class="container mt-2" id="autographuser2"></div>

                    @if (Auth::check() &&
                            $currentUser->department->name === $header->Relationdepartement->name &&
                            $currentUser->name === 'fery')
                        <button id="btn2" class="btn btn-primary" onclick="addAutograph(2 , {{ $header->id }})">Acc
                            Supervisor</button>
                        @if ($header->autograph_2 === null)
                            @include('partials.reject-modal', [
                                'id' => $header->id,
                                'route' => 'overtime.reject',
                            ])
                            <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">Reject</button>
                        @endif
                    @endif
                </div>


                @if ($header->is_design === 1)
                    <div class="col my-2">
                        <h2>Dept Head Design (FANG)</h2>
                        <div class="autograph-box container" id="autographBox3"></div>
                        <div class="container mt-2" id="autographuser3"></div>

                        @if (Auth::check() &&
                                $currentUser->department->name === $header->Relationdepartement->name &&
                                $currentUser->name === 'fang' &&
                                $header->autograph_2)
                            <button id="btn3" class="btn btn-primary"
                                onclick="addAutograph(3 , {{ $header->id }})">Acc DeptHead</button>
                            @if ($header->autograph_3 === null)
                                @include('partials.reject-modal', [
                                    'id' => $header->id,
                                    'route' => 'overtime.reject',
                                ])
                                <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">Reject</button>
                            @endif
                        @endif
                    </div>
                @else
                    <div class="col my-2">
                        <h2>Dept Head (ONG)</h2>
                        <div class="autograph-box container" id="autographBox3"></div>
                        <div class="container mt-2" id="autographuser3"></div>

                        @if (Auth::check() &&
                                $currentUser->department->name === $header->Relationdepartement->name &&
                                $currentUser->name === 'ong' &&
                                $header->autograph_2)
                            <button id="btn3" class="btn btn-primary"
                                onclick="addAutograph(3 , {{ $header->id }})">Acc DeptHead</button>
                            @if ($header->autograph_3 === null)
                                @include('partials.reject-modal', [
                                    'id' => $header->id,
                                    'route' => 'overtime.reject',
                                ])
                                <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">Reject</button>
                            @endif
                        @endif
                    </div>

                @endif

                <div class="col my-2">
                    <h2>Director</h2>
                    <div class="autograph-box container" id="autographBox4"></div>
                    <div class="container mt-2" id="autographuser4"></div>

                    @if (Auth::check() && $currentUser->department->name == 'DIRECTOR' && $header->autograph_3)
                        <button id="btn4" class="btn btn-primary" onclick="addAutograph(4 , {{ $header->id }})">Acc
                            Director</button>
                        @if ($header->autograph_4 === null)
                            @include('partials.reject-modal', [
                                'id' => $header->id,
                                'route' => 'overtime.reject',
                            ])
                            <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">Reject</button>
                        @endif
                    @endif
                </div>`

            @endif


            @if ($header->Relationdepartement->name !== 'MOULDING')

                <div class="col my-2">
                    <h2>Dept Head</h2>
                    <div class="autograph-box container" id="autographBox2"></div>
                    <div class="container mt-2" id="autographuser2"></div>

                    @if (Auth::check() &&
                            $currentUser->department->name === $header->Relationdepartement->name &&
                            $currentUser->is_head === 1)
                        <button id="btn2" class="btn btn-primary" onclick="addAutograph(2 , {{ $header->id }})">Acc
                            Dept Head</button>
                        @if ($header->autograph_2 === null)
                            @include('partials.reject-modal', [
                                'id' => $header->id,
                                'route' => 'overtime.reject',
                            ])
                            <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">Reject</button>
                        @endif
                    @endif
                </div>

                @if ($header->Relationdepartement->is_office === 0)

                    @if ($header->Relationdepartement->name === 'QA' || $header->Relationdepartement->name === 'QC')
                        <div class="col my-2">
                            <h2>Verificator</h2>
                            <div class="autograph-box container" id="autographBox3"></div>
                            <div class="container mt-2" id="autographuser3"></div>

                            @if (Auth::check() && $currentUser->name === 'Bernadett' && $header->autograph_2)
                                <button id="btn3" class="btn btn-primary"
                                    onclick="addAutograph(3 , {{ $header->id }})">Acc Verificator</button>
                                @if ($header->autograph_3 === null)
                                    @include('partials.reject-modal', [
                                        'id' => $header->id,
                                        'route' => 'overtime.reject',
                                    ])
                                    <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">Reject</button>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="col my-2">
                            <h2>GM</h2>
                            <div class="autograph-box container" id="autographBox3"></div>
                            <div class="container mt-2" id="autographuser3"></div>

                            @if (Auth::check() && $currentUser->is_gm === 1 && $header->autograph_2)
                                <button id="btn3" class="btn btn-primary"
                                    onclick="addAutograph(3 , {{ $header->id }})">Acc GM</button>
                                @if ($header->autograph_3 === null)
                                    @include('partials.reject-modal', [
                                        'id' => $header->id,
                                        'route' => 'overtime.reject',
                                    ])
                                    <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">Reject</button>
                                @endif
                            @endif
                        </div>
                    @endif

                    <div class="col my-2">
                        <h2>Director</h2>
                        <div class="autograph-box container" id="autographBox4"></div>
                        <div class="container mt-2" id="autographuser4"></div>

                        @if (Auth::check() && $currentUser->department->name == 'DIRECTOR' && $header->autograph_3)
                            <button id="btn4" class="btn btn-primary"
                                onclick="addAutograph(4 , {{ $header->id }})">Acc Director</button>
                            @if ($header->autograph_4 === null)
                                @include('partials.reject-modal', [
                                    'id' => $header->id,
                                    'route' => 'overtime.reject',
                                ])
                                <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">Reject</button>
                            @endif
                        @endif
                    </div>
                @else
                    <div class="col my-2">
                        <h2>Verificator</h2>
                        <div class="autograph-box container" id="autographBox3"></div>
                        <div class="container mt-2" id="autographuser3"></div>

                        @if (Auth::check() && $currentUser->name === 'Bernadett' && $header->autograph_2)
                            <button id="btn3" class="btn btn-primary"
                                onclick="addAutograph(3 , {{ $header->id }})">Acc Verificator</button>
                            @if ($header->autograph_3 === null)
                                @include('partials.reject-modal', [
                                    'id' => $header->id,
                                    'route' => 'overtime.reject',
                                ])
                                <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">Reject</button>
                            @endif
                        @endif
                    </div>


                    <div class="col my-2">
                        <h2>Director</h2>
                        <div class="autograph-box container" id="autographBox4"></div>
                        <div class="container mt-2" id="autographuser4"></div>

                        @if (Auth::check() && $currentUser->department->name == 'DIRECTOR' && $header->autograph_3)
                            <button id="btn4" class="btn btn-primary"
                                onclick="addAutograph(4 , {{ $header->id }})">Acc Director</button>
                            @if ($header->autograph_4 === null)
                                @include('partials.reject-modal', [
                                    'id' => $header->id,
                                    'route' => 'overtime.reject',
                                ])
                                <button class="btn btn-danger btn-lg me-4" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">Reject</button>
                            @endif
                        @endif
                    </div>
                @endif

            @endif

        </div>

    </section>

    <div class="text-center">
        <span class="h1 fw-semibold">Form Overtime {{ ucfirst(strtolower($header->Relationdepartement->name)) }}</span>
        <br>
        <div class="fs-6 mt-2">
            <span class="fs-6 text-secondary">Dibuat Tanggal : </span> {{ $header->create_date }}
        </div>
    </div>
    <hr>


    <div class="table-responsive mt-4">
        <table class="table table-bordered table-hover text-center table-striped mb-0">
            <thead>
                <tr>
                    <th class="align-middle">No</th>
                    <th class="align-middle">NIK</th>
                    <th class="align-middle">Nama</th>
                    <th class="align-middle">Job Description</th>
                    <th class="align-middle">Start Date</th>
                    <th class="align-middle">Start Time</th>
                    <th class="align-middle">End Date</th>
                    <th class="align-middle">End Time</th>
                    <th class="align-middle">Break (Dalam Menit)</th>
                    <th class="align-middle">Lama OT</th>
                    </th>
                    <th class="align-middle">Remark</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @forelse($datas as $data)
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $data->NIK }}</td>
                        <td>{{ $data->nama }}</td>
                        <td>{{ $data->job_desc }}</td>
                        <td>{{ $data->start_date }}</td>
                        <td>{{ $data->start_time }}</td>
                        <td>{{ $data->end_date }}</td>
                        <td>{{ $data->end_time }}</td>
                        <td>{{ $data->break }}</td>
                        <td>
                            @php
                                // Parse the start and end datetime
                                $start = \Carbon\Carbon::createFromFormat(
                                    'Y-m-d H:i:s',
                                    $data->start_date . ' ' . $data->start_time,
                                );
                                $end = \Carbon\Carbon::createFromFormat(
                                    'Y-m-d H:i:s',
                                    $data->end_date . ' ' . $data->end_time,
                                );

                                // Calculate the total minutes between start and end
                                $totalMinutes = $start->diffInMinutes($end);

                                // Subtract the break time (which is in minutes)
                                $totalMinutesAfterBreak = $totalMinutes - $data->break;

                                // Calculate the hours and minutes from the remaining total minutes
                                $hours = floor($totalMinutesAfterBreak / 60);
                                $minutes = $totalMinutesAfterBreak % 60;

                                // Display the result
                                echo "{$hours} hours {$minutes} minutes";
                            @endphp
                        </td>
                        <td>{{ $data->remarks }}</td>

                </tr>
            @empty
                <tr>
                    <td colspan="8">No Data</td>
                </tr>
                @endforelse
            </tbody>
        </table>



        @if ($header->is_approve === 0)
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Alasan Ditolak</h4>
                <textarea class="form-control rejection-textarea" rows="5" readonly>{{ $header->description }}</textarea>
            </div>
        @endif


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
                fetch('/save-autographot-path/' + reportId + '/' + section, {
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
                    autograph_1: '{{ $header->autograph_1 }}',
                    autograph_2: '{{ $header->autograph_2 }}',
                    autograph_3: '{{ $header->autograph_3 }}',
                    autograph_4: '{{ $header->autograph_4 }}',
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
                        //error di code ini -- next fix tampilan tanda tangan

                        var autographName = autographNames['autograph_name_' + i];
                        autographNameBox.textContent = autographName;
                        autographNameBox.style.display = 'block';
                    }
                }
            }

            // Call the function to check autograph status on page load
            window.onload = function() {
                checkAutographStatus({{ $header->id }});
            };
        </script>
    @endsection
