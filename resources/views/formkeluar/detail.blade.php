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
        <div class="row text-center">


            <div class="col">
                <h2>Dept Head</h2>
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2 border-1" id="autographuser1"></div>
                @if (Auth::check() &&
                        Auth::user()->department &&
                        Auth::user()->is_head == 1 &&
                        Auth::user()->department == $formkeluar->department)
                    <button id="btn2" class="btn btn-primary" onclick="addAutograph(1, {{ $formkeluar->id }})">Acc Dept
                        Head</button>
                @endif
            </div>


            <div class="col">
                @php
                    $path2 = 'default_image_path.jpg'; // Set a default image path

                    if ($formkeluar->signature) {
                        $path = $formkeluar->signature->getSignatureImagePath();
                        $path2 = str_replace('public/', 'storage/', $path);
                    }
                @endphp

                <h2>Yang Bersangkutan</h2>
                @if (!$formkeluar->hasBeenSigned())
                    <form action="{{ $formkeluar->getSignatureRoute() }}" method="POST">
                        @csrf
                        <div style="text-align: center">
                            <x-creagia-signature-pad />
                        </div>
                    </form>
                @else
                    <div class=" autograph-box container" id="specialbox">
                        @if ($formkeluar->signature)
                            <img src="{{ asset($path2) }}" style="width:200px; " alt="Signature Image">
                        @endif
                    </div>
                    {{ $formkeluar->name }}
                @endif
            </div>
        </div>
    </section>




    <section aria-label="table-report" class="container mt-5">
        <div class="card">
            <div class="card-body">
                <div class="mt-2 text-center">
                    <span class="h1 fw-semibold">FORM KELUAR</span>
                    <div class="fs-6 col mt-2">
                        <span class="text-secondary">Doc No :</span> {{ $formkeluar->doc_num }} <br>
                        <span class="text-secondary">No Karyawan :</span> {{ $formkeluar->no_karyawan }} <br>
                        <span class="text-secondary">Dibuat oleh :</span> {{ $formkeluar->name }} <br>
                    </div>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover text-center table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="align-middle">Name</th>
                                <th class="align-middle">Jabatan</th>
                                <th class="align-middle">Departement</th>
                                <th class="align-middle">Pengganti</th>
                                <th class="align-middle">Keperluan</th>
                                <th class="align-middle">Tanggal Permohonan</th>
                                <th class="align-middle">Waktu Keluar</th>
                                <th class="align-middle">Jam Keluar</th>
                                <th class="align-middle">Jam Kembali</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr class="align-middle">
                                <td>{{ $formkeluar->name }}</td>
                                <td>{{ $formkeluar->jabatan }}</td>
                                <td>{{ $formkeluar->department }}</td>
                                <td>{{ $formkeluar->pengganti }}</td>
                                <td>{{ $formkeluar->keperluan }}</td>
                                <td>{{ $formkeluar->tanggal_permohonan }}</td>
                                <td>{{ $formkeluar->waktu_keluar }}</td>
                                <td>{{ $formkeluar->jam_keluar }}</td>
                                <td>{{ $formkeluar->jam_kembali }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/sign-pad/sign-pad.min.js') }}"></script>

@endsection

<script>
    // Function to add autograph to the specified box
    function addAutograph(section, formId) {
        // Get the div element
        var autographBox = document.getElementById('autographBox' + section);

        console.log('Section:', section);
        console.log('Report ID:', formId);
        var username = '{{ Auth::check() ? Auth::user()->name : '' }}';
        console.log('username :', username);
        var imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');
        console.log('image path :', imageUrl);

        autographBox.style.backgroundImage = "url('" + imageUrl + "')";

        // Make an AJAX request to save the image path
        fetch('/save-autosignature-path/' + formId + '/' + section, {
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

        checkAutographStatus(formId);
    }


    function checkAutographStatus(formId) {
        // Assume you have a variable from the server side indicating the autograph status
        var autographs = {
            autograph_1: '{{ $formkeluar->autograph_1 ?? null }}',
        };

        var autographNames = {
            autograph_name_1: '{{ $formkeluar->autograph_user_1 ?? null }}',
        };

        // Loop through each autograph status and update the UI accordingly
        i = 1;
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

            var autographName = autographNames['autograph_name_' + i];
            autographNameBox.textContent = autographName;
            autographNameBox.style.display = 'block';
        }
    }


    // Call the function to check autograph status on page load
    window.onload = function() {
        checkAutographStatus({{ $formkeluar->id }});
    };
</script>
