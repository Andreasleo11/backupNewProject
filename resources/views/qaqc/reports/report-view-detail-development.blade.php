@extends('layouts.app')

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('extraCss')
    <link rel="stylesheet" href="{{ asset('css/report_view_detail.css') }}">
@endpush

@section('content')

    <!-- Signs -->
    <div class="row text-center mb-3">
        <div class="col-lg-4 col-sm-6 text-center">
            <h2>QA Inspector</h2>
            <div class="autograph-container d-flex flex-column justify-content-center align-items-center">
                <div class="row my-2">
                    <div class="container">
                        <div class="autograph-box" id="autographBox2"></div>
                    </div>
                </div>
                @if(Auth::check() && Auth::user()->department == 'QA')
                    <button id="btn1" class="btn btn-primary" onclick="addAutograph(1, {{ $report->id }})">Acc QA Inspector</button>
                @endif
                <div class="container" id="autographuser2"></div>
            </div>
        </div>

        <div class="col-lg-4 col-sm-6 text-center">
            <h2>QA Leader</h2>
            <div class="autograph-container d-flex flex-column justify-content-center align-items-center">
                <div class="row my-2">
                    <div class="container">
                        <div class="autograph-box" id="autographBox2"></div>
                    </div>
                </div>
                @if(Auth::check() && Auth::user()->department == 'QA')
                    <button id="btn2" class="btn btn-primary" onclick="addAutograph(2, {{ $report->id }})">Acc QA Leader</button>
                @endif
                <div class="container" id="autographuser2"></div>
            </div>
        </div>

        <div class="col-lg-4 text-center">
            <h2>QA Head</h2>
            <div class="autograph-container d-flex flex-column justify-content-center align-items-center ">
                <div class="row my-2">
                    <div class="container">
                        <div class="autograph-box" id="autographBox3"></div>
                    </div>
                </div>
                @if(Auth::check() && Auth::user()->department == 'QC')
                    <button id="btn3" class="btn btn-primary" onclick="addAutograph(3, {{ $report->id }})">Acc QC Head</button>
                @endif
                <div class="container" id="autographuser3"></div>
            </div>

        </div>
    </div>

    <!-- Report -->
    <div class="card">
        <div class="card-body">
            <!-- header -->
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

            <!-- table report -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Part Name</th>
                            <th>Rec Quantity</th>
                            <th>Verify Quantity</th>
                            <th>Production Date</th>
                            <th>Shift</th>
                            <th>Can Use</th>
                            <th>Cust Defect</th>
                            <th>Daijo Defect</th>
                            <th>Customer Defect Detail</th>
                            <th>Remark Customer</th>
                            <th>Daijo Defect Detail</th>
                            <th>Remark Daijo</th>


                            <!-- Add more headers as needed -->
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($report->details as $detail)
                            <tr>
                                <td>{{ $detail->part_name}}</td>
                                <td>{{ $detail->rec_quantity}}</td>
                                <td>{{ $detail->verify_quantity}}</td>
                                <td>{{ $detail->prod_date}}</td>
                                <td>{{ $detail->shift}}</td>
                                <td>{{ $detail->can_use}}</td>
                                <td>{{ $detail->customer_defect}}</td>
                                <td>{{ $detail->daijo_defect}}</td>

                                <!-- Display customer_defect_detail if available and not null -->
                                <td>
                                    @foreach ($detail->customer_defect_detail as $key => $value)
                                        @if (!is_null($value))
                                            {{ $key }}: {{ $value }}<br>
                                        @endif
                                    @endforeach
                                </td>

                                <!-- Display remark_customer if available and not null -->
                                <td>
                                    @foreach ($detail->remark_customer as $key => $value)
                                        @if (!is_null($value))
                                            {{ $key }}: {{ $value }}<br>
                                        @endif
                                    @endforeach
                                </td>

                                <!-- Display daijo_defect_detail if available and not null -->
                                <td>
                                    @foreach ($detail->daijo_defect_detail as $key => $value)
                                        @if (!is_null($value))
                                            {{ $key }}: {{ $value }}<br>
                                        @endif
                                    @endforeach
                                </td>

                                <!-- Display remark_daijo if available and not null -->
                                <td>
                                    @foreach ($detail->remark_daijo as $key => $value)
                                        @if (!is_null($value))
                                            {{ $key }}: {{ $value }}<br>
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

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
            location.reload()
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
        autograph_name_1: '{{ $report->autograph_user_1 ?? null }}',
        autograph_name_2: '{{ $report->autograph_user_2 ?? null }}',
        autograph_name_3: '{{ $report->autograph_user_3 ?? null }}',
    };

    // Loop through each autograph status and update the UI accordingly
    for (var i = 1; i <= 3; i++) {
        var autographBox = document.getElementById('autographBox' + i);
        var autographInput = document.getElementById('autographInput' + i);
        var autographNameBox = document.getElementById('autographuser' + i);

        var autographBtn = document.getElementById('btn' + i);

        // Check if autograph status is present in the database
        if (autographs['autograph_' + i]) {
            autographBox.style.display = 'block';

           // Construct URL based on the current location
           var url = '/' + autographs['autograph_' + i];

           if(autographBtn){
            autographBtn.style.display = 'none';
           }

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


