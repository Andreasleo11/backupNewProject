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
    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = auth()->user();
    @endphp
    {{-- END GLOBAL VARIABLE --}}

    @include('partials.alert-success-error')
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
            @if ($header->status === 1 && $authUser->id === $header->Relationuser->id)
                <button data-bs-target="#edit-form-overtime-modal-{{ $header->id }}" data-bs-toggle="modal"
                    class="btn btn-primary"><i class='bx bx-edit'></i> Edit</button>
            @endif
            @if ($header->status === 5 && $authUser->specification->name === 'VERIFICATOR')
                <a href="{{ route('export.overtime', $header->id) }}" class="btn btn-success">Export to Excel</a>
            @endif
        </div>
    </div>
     @if ($header->is_approve === 1 && $authUser->specification->name === 'VERIFICATOR')
                        <button 
                            id="btnPushAll" 
                            data-header-id="{{ $header->id }}" 
                            class="bg-red-600 hover:bg-red-700 text-black font-semibold px-4 py-2 rounded">
                            Push All to JPayroll
                        </button>

                        <!-- Tempat notifikasi -->
                        <div id="pushAllResult" class="mt-2 text-sm"></div>
                    @endif


    @include('partials.formovertime-autographs')

    <div class="mt-5 container">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <span class="h1 fw-semibold">Form Overtime</span>
                        <br>
                        <div class="fs-6 mt-2">
                            <span class="fs-6 text-secondary">Create Date : </span>
                            {{ \Carbon\Carbon::parse($header->create_date)->format('d/m/Y') }}
                        </div>
                        <div class="fs-6">
                            <span class="fs-6 text-secondary">Created By : </span>
                            {{ $header->Relationuser->name }}
                        </div>
                        <div class="fs-6">
                            <span class="fs-6 text-secondary">Department : </span>
                            {{ $header->Relationdepartement->name }} ({{ $header->Relationdepartement->dept_no }})
                        </div>
                        <div class="mt-2">
                            @include('partials.formovertime-status', ['fot' => $header])
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
                                    @if ($header->is_approve === 1 && $authUser->specification->name === 'VERIFICATOR')
                                        <th class="align-middle">Action</th>
                                    @endif
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
                                        @if ($header->is_approve === 1 && $authUser->specification->name === 'VERIFICATOR')
                                            <td> @include('partials.delete-confirmation-modal', [
                                                'id' => $data->id,
                                                'route' => 'formovertime.destroyDetail',
                                                'title' => 'Delete item detail',
                                                'body' => 'Are you sure want to delete this?',
                                            ])
                                                <!-- <button class="btn btn-danger btn-sm"
                                                    data-bs-target="#delete-confirmation-modal-{{ $data->id }}"
                                                    data-bs-toggle="modal">Delete</button> -->
                                                @if ($data->is_processed == 1 && $data->status === 'Approved')
                                                    <span class="text-success fw-bold">APPROVED</span>
                                                @elseif ($data->status === 'Rejected')
                                                    <span class="text-danger fw-bold">REJECTED</span>
                                                @else
                                                    <button class="btn btn-success btn-sm" onclick="handleOvertimeAction({{ $data->id }}, 'approve')">
                                                        Approve
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="handleOvertimeAction({{ $data->id }}, 'reject')">
                                                        Reject
                                                    </button>
                                                @endif
                                            </td>
                                           

                                        @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12">No Data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($header->is_approve === 0)
        <div class="alert alert-danger mt-3" role="alert">
            <h4 class="alert-heading">Alasan Ditolak</h4>
            <textarea class="form-control-plaintext rounded-2 border border-danger p-2" rows="5" readonly>
                {{ $header->description }}</textarea>
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
                    location.reload();
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

                // Check if autograph status is present in the database
                if (autographs['autograph_' + i]) {

                    if (btnId) {
                        // console.log(btnId);
                        btnId.style.display = 'none';
                    }

                    // Construct URL based on the current location
                    var url = '/autographs/' + autographs['autograph_' + i];

                    // Update the background image using the URL
                    autographBox.style.backgroundImage = "url('" + url + "')";
                    //error di code ini -- next fix tampilan tanda tangan

                    var autographName = autographNames['autograph_name_' + i];
                    autographNameBox.textContent = autographName;
                    autographNameBox.style.display = 'block';
                }
            }
        }

        function handleOvertimeAction(detailId, actionType) {
            if (!['approve', 'reject'].includes(actionType)) {
                alert('Aksi tidak valid.');
                return;
            }

            if (!confirm(`Yakin ingin ${actionType === 'approve' ? 'menyetujui' : 'menolak'} lembur ini?`)) {
                return;
            }

            fetch(`/push-overtime-detail/${detailId}?action=${actionType}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Berhasil diproses.');
                    location.reload(); // Refresh agar data update
                } else {
                    alert(data.message || 'Gagal memproses.');
                    console.error(data);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan saat proses.');
            });
        }

        document.getElementById('btnPushAll').addEventListener('click', function() {
        const headerId = this.dataset.headerId;

        if (!confirm("Apakah Anda yakin ingin mem-push semua data detail yang belum ditolak (Rejected)?")) {
            return;
        }

        // Tampilkan loader
        document.getElementById('pushAllLoader').classList.remove('hidden');
        document.getElementById('pushAllResult').innerText = '';

        fetch(`/overtime/push-all/${headerId}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('pushAllLoader').classList.add('hidden');
            
            if (data.success) {
                document.getElementById('pushAllResult').innerHTML = 
                    `<span class="text-green-600 font-semibold">✅ ${data.message}</span>`;
            } else {
                document.getElementById('pushAllResult').innerHTML = 
                    `<span class="text-red-600 font-semibold">❌ ${data.message}</span>`;
            }
        })
        .catch(error => {
            document.getElementById('pushAllLoader').classList.add('hidden');
            document.getElementById('pushAllResult').innerHTML = 
                `<span class="text-red-600 font-semibold">❌ Terjadi kesalahan saat memproses.</span>`;
            console.error('Error:', error);
        });
    });

        // Call the function to check autograph status on page load
        window.onload = function() {
            checkAutographStatus({{ $header->id }});
        };
    </script>
@endsection
