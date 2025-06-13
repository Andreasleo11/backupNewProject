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

        @if ($header->department->name === 'MOULDING')

            <div class="col my-2">
                <h2>Supervisor</h2>
                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2" id="autographuser2"></div>

                @if (Auth::check() && $currentUser->department->name === $header->department->name && $currentUser->name === 'fery')
                    <button id="btn2" class="btn btn-primary"
                        onclick="addAutograph(2 , {{ $header->id }})">Accept</button>
                    @if ($header->autograph_2 === null)
                        @include('partials.reject-modal', [
                            'id' => $header->id,
                            'route' => 'overtime.reject',
                        ])
                        <button class="btn btn-danger ms-5" data-bs-toggle="modal"
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
                            $currentUser->department->name === $header->department->name &&
                            $currentUser->name === 'fang' &&
                            $header->autograph_2)
                        <button id="btn3" class="btn btn-primary"
                            onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                        @if ($header->autograph_3 === null)
                            @include('partials.reject-modal', [
                                'id' => $header->id,
                                'route' => 'overtime.reject',
                            ])
                            <button class="btn btn-danger ms-5" data-bs-toggle="modal"
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
                            $currentUser->department->name === $header->department->name &&
                            $currentUser->name === 'ong' &&
                            $header->autograph_2)
                        <button id="btn3" class="btn btn-primary"
                            onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                        @if ($header->autograph_3 === null)
                            @include('partials.reject-modal', [
                                'id' => $header->id,
                                'route' => 'overtime.reject',
                            ])
                            <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">Reject</button>
                        @endif
                    @endif
                </div>
            @endif

            <div class="col my-2">
                <h2>Director</h2>
                <div class="autograph-box container" id="autographBox4"></div>
                <div class="container mt-2" id="autographuser4"></div>

                @if (Auth::check() && $currentUser->specification->name == 'DIRECTOR' && $header->is_approve === null)
                    <button id="btn4" class="btn btn-primary"
                        onclick="addAutograph(4 , {{ $header->id }})">Accept</button>
                    @if ($header->autograph_4 === null)
                        @include('partials.reject-modal', [
                            'id' => $header->id,
                            'route' => 'overtime.reject',
                        ])
                        <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">Reject</button>
                    @endif
                @endif
            </div>
        @endif


        @if ($header->branch === 'Karawang')
            <div class="col my-2">
                <h2>GM</h2>
                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2" id="autographuser3"></div>

                @if (Auth::check() && $currentUser->is_gm === 1 && $header->autograph_2)
                    <button id="btn3" class="btn btn-primary"
                        onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                    @if ($header->autograph_3 === null)
                        @include('partials.reject-modal', [
                            'id' => $header->id,
                            'route' => 'overtime.reject',
                        ])
                        <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">Reject</button>
                    @endif
                @endif
            </div>


            <div class="col my-2">
                <h2>Director</h2>
                <div class="autograph-box container" id="autographBox4"></div>
                <div class="container mt-2" id="autographuser4"></div>

                @if (Auth::check() &&
                        $currentUser->specification->name == 'DIRECTOR' &&
                        $header->autograph_3 &&
                        $header->is_approve === null)
                    <button id="btn4" class="btn btn-primary"
                        onclick="addAutograph(4 , {{ $header->id }})">Accept</button>
                    @if ($header->autograph_4 === null)
                        @include('partials.reject-modal', [
                            'id' => $header->id,
                            'route' => 'overtime.reject',
                        ])
                        <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">Reject</button>
                    @endif
                @endif
            </div>
        @else
            @if ($header->department->name !== 'MOULDING')
                @php
                    $showDeptHeadApprovalButton = false;
                    if ($header->department->name === 'SECOND PROCESS') {
                        if (Auth::check() && $currentUser->email === 'wiji@daijo.co.id') {
                            $showDeptHeadApprovalButton = true;
                        }
                    } elseif (Auth::check() && $currentUser->is_head === 1) {
                        if ($currentUser->department->name === $header->department->name) {
                            $showDeptHeadApprovalButton = true;
                        } elseif (
                            $currentUser->department->name === 'LOGISTIC' &&
                            $header->department->name === 'STORE'
                        ) {
                            $showDeptHeadApprovalButton = true;
                        }
                    }
                @endphp

                <div class="col my-2">
                    <h2>Dept Head</h2>
                    <div class="autograph-box container" id="autographBox2"></div>
                    <div class="container mt-2" id="autographuser2"></div>

                    @if ($showDeptHeadApprovalButton)
                        <button id="btn2" class="btn btn-primary"
                            onclick="addAutograph(2 , {{ $header->id }})">Accept</button>
                        @if ($header->autograph_2 === null)
                            @include('partials.reject-modal', [
                                'id' => $header->id,
                                'route' => 'overtime.reject',
                            ])
                            <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">Reject</button>
                        @endif
                    @endif
                </div>

                @if ($header->department->is_office === 0)

                    @if ($header->department->name !== 'QA' && $header->department->name !== 'QC')
                        <div class="col my-2">
                            <h2>GM</h2>
                            <div class="autograph-box container" id="autographBox3"></div>
                            <div class="container mt-2" id="autographuser3"></div>

                            @if (Auth::check() && $currentUser->is_gm === 1 && $header->autograph_2)
                                <button id="btn3" class="btn btn-primary"
                                    onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                                @if ($header->autograph_3 === null)
                                    @include('partials.reject-modal', [
                                        'id' => $header->id,
                                        'route' => 'overtime.reject',
                                    ])
                                    <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">Reject</button>
                                @endif
                            @endif
                        </div>

                        <div class="col my-2">
                            <h2>Director</h2>
                            <div class="autograph-box container" id="autographBox4"></div>
                            <div class="container mt-2" id="autographuser4"></div>

                            @if (Auth::check() &&
                                    $currentUser->specification->name === 'DIRECTOR' &&
                                    $header->autograph_3 &&
                                    $header->is_approve === null)
                                <button id="btn4" class="btn btn-primary"
                                    onclick="addAutograph(4 , {{ $header->id }})">Accept</button>
                                @if ($header->autograph_4 === null)
                                    @include('partials.reject-modal', [
                                        'id' => $header->id,
                                        'route' => 'overtime.reject',
                                    ])
                                    <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">Reject</button>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="col my-2">
                            <h2>Director</h2>
                            <div class="autograph-box container" id="autographBox3"></div>
                            <div class="container mt-2" id="autographuser3"></div>

                            @if (Auth::check() &&
                                    $currentUser->specification->name == 'DIRECTOR' &&
                                    $header->autograph_2 &&
                                    $header->is_approve === null)
                                <button id="btn3" class="btn btn-primary"
                                    onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                                @if ($header->autograph_3 === null)
                                    @include('partials.reject-modal', [
                                        'id' => $header->id,
                                        'route' => 'overtime.reject',
                                    ])
                                    <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">Reject</button>
                                @endif
                            @endif
                        </div>
                    @endif
                @else
                    <div class="col my-2">
                        <h2>Director</h2>
                        <div class="autograph-box container" id="autographBox3"></div>
                        <div class="container mt-2" id="autographuser3"></div>

                        @if (Auth::check() &&
                                $currentUser->specification->name == 'DIRECTOR' &&
                                $header->autograph_2 &&
                                $header->is_approve === null)
                            <button id="btn3" class="btn btn-primary"
                                onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                            @if ($header->autograph_3 === null)
                                @include('partials.reject-modal', [
                                    'id' => $header->id,
                                    'route' => 'overtime.reject',
                                ])
                                <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">Reject</button>
                            @endif
                        @endif
                    </div>
                @endif
            @endif
        @endif
    </div>
</section>

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

    // Call the function to check autograph status on page load
    window.onload = function() {
        checkAutographStatus({{ $header->id }});
    };
</script>
