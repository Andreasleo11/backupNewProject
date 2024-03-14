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
    <section>
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('qaqc.home')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{route('qaqc.report.index')}}">Reports</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                {{-- TODO: EMAIL FEATURE --}}
                @if(Auth::user()->name == 'Deni')
                    <button class="btn btn-outline-primary me-2" data-bs-target="#send-mail-modal" data-bs-toggle="modal">
                        <i class='bx bx-envelope' ></i> Send mail
                    </button>
                    @include('partials.send-mail-modal', ['report' => $report])

                    <a href="{{ route('qaqc.report.sendEmail', $report->id) }}" class="btn btn-outline-secondary">Test email</a>
                @endif
                <button class="btn btn-outline-primary" data-bs-target="#upload-files-modal" data-bs-toggle="modal">
                    <i class='bx bx-upload'></i> Upload
                </button>
                @include('partials.upload-files-modal', ['doc_id' => $report->doc_num])
            </div>
        </div>
    </section>

    <div class="mt-4">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class='bx bx-check-circle me-2' style="font-size:20px;" ></i>
                {{ $message }}
                <button id="closeAlertButton" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                        <button id="closeAlertButton" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif
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
                {{-- @if(Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == "INSPECTOR")
                    <button id="btn1" class="btn btn-primary" onclick="addAutograph(1, {{ $report->id }})">Acc QC Inspector</button>
                @endif --}}
            </div>

            <div class="col">
                <h2>QC Leader</h2>
                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2 border-1" id="autographuser2"></div>
                @if(Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == 'LEADER')
                    <button id="btn2" class="btn btn-primary" onclick="addAutograph(2, {{ $report->id }})">Acc QC Leader</button>
                @endif
            </div>

            <div class="col">
                <h2>QC Head</h2>
                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2 border-1" id="autographuser3"></div>
                @if(Auth::check() && $currentUser->department->name == 'QC' && $currentUser->specification->name == 'HEAD' && ($report->autograph_1 || $report->autograph_2) != null)
                    <button id="btn3" class="btn btn-primary" onclick="addAutograph(3, {{ $report->id }}, {{$user->id}})">Acc QC Head</button>
                @endif
            </div>
        </div>
    </section>

    <section aria-label="table-report" class="container mt-5">
        <div class="card">
            <div class="pt-4 text-center">
                <span class="h1 fw-semibold">Verification Reports</span> <br>
                <div class="mt-1">
                    <span class="fs-5">{{ $report->doc_num ?? '-'}} </span> <br>
                    <span class="fs-6 ">Created By : {{ $report->created_by ?? '-'}} </span>
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
                                    <td>{{ $detail->part_name}}</td>
                                    <td>{{ $detail->rec_quantity}}</td>
                                    <td>{{ $detail->verify_quantity}}</td>
                                    <td>{{ $detail->can_use}}</td>
                                    <td>{{ $detail->cant_use}}</td>
                                    <td colspan="3" class="p-0">
                                        @foreach($detail->defects as $defect)
                                            @if ($defect->is_daijo)
                                                <table class="table table-borderless mb-0">
                                                    <tbody class="text-center" >
                                                        <td style="background-color: transparent; width:33%;"> {{ $defect->quantity }}</td>
                                                        <td style="background-color: transparent; width:34%;"> {{ $defect->category->name }}</td>
                                                        <td style="background-color: transparent"> {{ $defect->remarks}}</td>
                                                    </tbody>
                                                </table>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td colspan="3" class="p-0">
                                        @foreach($detail->defects as $defect)
                                            @if (!$defect->is_daijo)
                                                <table class="table table-borderless mb-0">
                                                    <tbody class="text-center" >
                                                        <td style="background-color: transparent; width:33%;"> {{ $defect->quantity }}</td>
                                                        <td style="background-color: transparent; width:34%;"> {{ $defect->category->name }}</td>
                                                        <td style="background-color: transparent"> {{ $defect->remarks}}</td>
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
        <div class="container mt-5">
            <h4 class="mb-3">Files</h4>
            @forelse ($files as $file)
                @if (!function_exists('formatFileSize'))
                    @php
                    function formatFileSize($bytes) {
                        if ($bytes < 1024) {
                            return $bytes . ' bytes';
                        } else if ($bytes < 1024 * 1024) {
                            return number_format($bytes / 1024, 2) . ' KB';
                        } else if ($bytes < 1024 * 1024 * 1024) {
                            return number_format($bytes / (1024 * 1024), 2) . ' MB';
                        } else {
                            return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
                        }
                    }
                    @endphp
                @endif
                @php
                    $filename = basename($file->name);
                @endphp
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                LOGO
                            </div>
                            <div class="col text-secondary ">
                                <span>{{ $filename }}</span> <br>
                                <span>{{ $file->mime_type }}</span>
                                <span>{{ formatFileSize($file->size) }}</span>
                            </div>
                            <div class="col-auto">
                                <a href="{{ asset('storage/files/' . $filename) }}" download="{{ $filename }}" class="pt-1 pb-0 btn btn-success">
                                    <i class='bx bxs-download'></i>
                                </a>
                                <button class="pt-1 pb-0 btn btn-danger" onclick="document.getElementById('deleteForm').submit();">
                                    <i class='bx bxs-trash-alt'></i>
                                </button>
                                <form id="deleteForm" action="{{ route('file.delete', $file->id) }}" method="post"> @csrf @method('DELETE')</form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p>No Files Were Uploaded</p>
            @endforelse
        </div>
    </section>

    {{-- <section>
        <div class="custom-container mt-5 ">
            <div class="header-section">
                <h1>Upload Files</h1>
                <p>Upload files you want to share</p>
                <p>PDF, images, Excel are allowed.</p>
            </div>
            <div class="drop-section">
                <div class="col">
                    <div class="cloud-icon"></div>
                    <span>Drag & Drop your files here</span>
                    <span>Or</span>
                    <button class="file-selector">Browse Files</button>
                    <input type="file" class="file-selector-input" multiple >
                </div>
                <div class="col">
                    <div class="drop-here">Drop here</div>
                </div>
            </div>
            <div class="list-section">
                <div class="list-title">Uploaded Files</div>
                 <div class="list">
                    <li class="in-prog">
                        <div class="col">
                            <img src="icons/image.png" alt="image" srcset="">
                        </div>
                        <div class="col">
                            <div class="file-name">
                                <div class="name">File Name Here</div>
                                <span>50%</span>
                            </div>
                            <div class="file-progress">
                                <span></span>
                            </div>
                            <div class="file-size">2.2 MB</div>
                        </div>
                        <div class="col"></div>
                    </li>
                 </div>
            </div>
        </div>
    </section>

    <style>
        .custom-container{
            text-align: center;
            width: 100%;
            max-width: 500px;
            min-height: 435px;
            margin: auto;
            background-color: white;
            border-radius: 16px;
        }

        .header-section{
            padding: 25px 0;
        }

        .header-section h1{
            font-weight: 500;
            font-size: 1.7rem;
            text-transform: uppercase;
            color: #707EA0;
            margin: 0px;
            margin-bottom: 8px;
        }

        .header-section p{
            margin: 5px;
            font-size: 0.95rem;
        }
        .drop-section {
            min-height: 250px;
            border: 1px dashed #A8B3E3;
            background-image: linear-gradient(180deg, white, #F1F6FF);
            margin: 5px 35px 35px 35px;
            border-radius: 12px;
            position: relative;
        }

        .drop-section div.col:first-child{
            opacity: 1;
            visibility: visible;
            transition-duration: 0.2s;
            transform: scale(1);
            width: 200px;
            margin: auto;
        }

        .drop-section div.col:last-child{
            font-size: 40px;
            font-weight: 700;
            color: #c0cae1;
            position: absolute;
            top: 0px;
            bottom: 0px;
            left: 0px;
            right: 0px;
            margin: auto;
            width: 200px;
            height: 55px;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transform: scale(0.6);
            transition-duration: 0.2s;
        }

        .drag-over-effect div.col:first-child{
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: scale(1.1);
        }
        .drag-over-effect div.col:last-child{
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }


        .drop-section .cloud-icon {
            margin-top: 25px;
            margin-bottom: 20px;
        }

        .drop-section span,
        .drop-section button {
            display: block;
            margin: auto;
            color: #707EA0;
            margin-bottom: 10px;
        }

        .drop-section button {
            color: white;
            background-color: #5874C6;
            border: none;
            outline: none;
            padding: 7px 20px;
            border-radius: 8px;
            margin-top: 20px;
            cursor: pointer;
        }

        .drop-section input{
            display: none;
        }

        .list-section{
            text-align: left;
            margin: 0px 35px;
            font-size: 0.95rem;
            color: #707EA0;
        }

        .list-section li{
            display: flex;
            margin: 15px 0;
            padding-top: 4px;
            padding-bottom: 2px;
            border-radius: 8px;
            transition-duration: 0.2s;
        }

        .list-section li:hover{
            box-shadow: #E3EAF9 0px 0px 4px 0px, #E3EAF9 0px 12px 16px 0px;
        }

        .list-section li .col {
            flex: .1;
        }

        .list-section li .col:nth-child(1){
            flex: .15;
            text-align: center;
        }

        .list-section li .col:nth-child(2){
            flex: .75;
            text-align:left;
            font-size: 0.9rem;
            color: #3e4046;
            padding: 8px 10px;
        }

        .list-section li .col:nth-child(2) div.name {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            max-width: 250px;
            display: inline-block;
        }

        .list-section li .col .file-name span {
            color: #707EA0;
            float: right;
        }

        .list-section li .file-progress{
            width: 100%;
            height: 5px;
            margin-top: 8px;
            border-radius: 8px;
            background-color: #dee6fd;
        }

        .li-section li .file-progress span{
            display: block;
            width: 50%;
            height: 100%;
            border-radius: 8px;
            background-image: linear-gradient(120deg, #6b99fd, #9385ff);
            transition-duration: 0.4s;
        }

        .list-section li .col .file-size{
            font-size: 0.75rem;
            margin-top: 3px;
            color: #70&EA0;
        }

        .list-section li .col svg.cross,
        .list-section li .col svg.tick {
            fill: #8694d2;
            background-color: #dee6fd;
            position: relative;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
        }

        .list-section li .col svg.tick{
            fill: #50a156;
            background-color: transparent;
        }

        .list-section li.complete span,
        .list-section li.complete .file-progress,
        .list-section li.complete svg.cross{
            display: none;
        }

        .list-section li.in-prog .file-size,
        .list-section li.in-prog svg.tick{
            display: none;
        }


    </style>

    <script>
        const dropArea = document.querySelector('.drop-section')
        const listSection = document.querySelector('.list-section')
        const listContainer = document.querySelector('.list')
        const fileSelector = document.querySelector('.file-selector')
        const fileSelectorInput = document.querySelector('.file-selector-input')

        //upload files with browse button

        fileSelector.onclick = () => fileSelectorInput.click()
        fileSelectorInput.onchange = () => {
            [...fileSelectorInput.files].forEach((file) => {
                if(typeValidation(file.type)){
                    uploadFile(file)
                }
            })
        }

        // when file is over the drag area
        dropArea.ondragover = (e) => {
            e.preventDefault();
            [...e.dataTransfer.items].forEach((item) => {
                dropArea.classList.add('drag-over-effect')
            })
        }

        // when file leave the drop area
        dropArea.ondragleave = () => {
            dropArea.classList.remove('drag-over-effect')
        }

        //when file drop on the drag area
        dropArea.ondrop = (e) => {
            e.preventDefault();
            dropArea.classList.remove('drag-over-effect');
            if(e.dataTransfer.items){
                [...e.dataTransfer.items].forEach((item) => {
                    if(item.kind === 'file'){
                        const file = item.getAsFile();
                        if(typeValidation(file.type)){
                            uploadFile(file);
                        }
                    }
                })
            } else {
                [...e.dataTransfer.files].forEach((file) => {
                    if(typeValidation(file.type)){
                        uploadFile(file);
                    }
                })
            }
        }

        //check the file type
        function typeValidation(type){
            var splitType= type.split('/')[0]
            if(type == 'application/pdf' || splitType == 'image' || splitType == 'video'){
                return true;
            }
        }

        function uploadFile(file){
            console.log(file);
        }
    </script> --}}
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
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
            });

            checkAutographStatus(reportId);
        }

        function checkAutographStatus(reportId)
        {
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
            checkAutographStatus({{ $report->id }});
        };

        const closeAlertButton = document.getElementById('closeAlertButton');

        // Function to hide the alert after 3 seconds
        setTimeout(() => {
            if(closeAlertButton){
                closeAlertButton.click();
            }
        }, 3000);
    </script>
@endpush






