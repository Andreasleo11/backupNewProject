@extends('layouts.app')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')

   <!-- Content Wrapper. Contains page content -->
   <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">Home</li>
              <li class="breadcrumb-item">Reminder</li>
			  <li class="breadcrumb-item active">Detail</li>				  
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>


    <!-- Autograph input boxes -->
<div class="autograph-container">
    <!-- Autograph Button 1 -->
    @if(Auth::check() && Auth::user()->department == 'QA')
    <button onclick="addAutograph(1, {{ $report->id }})">Acc QA Inspector</button>
    <!-- Autograph File Input 1 -->
    @endif
    <h2>QA Inspector</h2>
    <div class="autograph-box" id="autographBox1"></div>
    <div class="autograph-textbox" id="autographuser1"></div>
</div>



<div class="autograph-container">
    <!-- Autograph Button 2 -->
    @if(Auth::check() && Auth::user()->department == 'QA')
    <button onclick="addAutograph(2, {{ $report->id }})">Acc QA Leader</button>
    @endif
    <h2>QA Leader</h2>
    <div class="autograph-box" id="autographBox2"></div>
    <div class="autograph-textbox" id="autographuser2"></div>
</div>



<div class="autograph-container">
    <!-- Autograph Button 3 -->
    @if(Auth::check() && Auth::user()->department == 'QC')
    <button onclick="addAutograph(3, {{ $report->id }}, {{$user->id}})">Acc QC Head</button>
    @endif
    <!-- Autograph Textbox 3 -->
    <h2>QC HEAD</h2>
    <div class="autograph-box" id="autographBox3"></div>
    <div class="autograph-textbox" id="autographuser3"></div>
</div>

    <style>
    .report-table {
        width: 100%; /* Adjust the width as needed */
        border-collapse: collapse;
    }

    .report-table th, .report-table td {
        border: 1px solid #ddd; /* Border for better visibility */
        padding: 8px; /* Adjust padding as needed */
        text-align: left;
    }

    .spacer {
        width: 50px; /* Adjust the width to control the spacing */
    }
</style>


    <!-- <div class="container"> -->
      <div class="card">
      <div class="card-header">
        <h1>Verification Reports</h1>
        <h2>Created By : {{ $report->created_by }}<h2>
        </div>
        <table class="report-table">
                  <tr>
                    <th>Rec Date :</th> 
                    <td>{{ $report->rec_date }}</td><br>
                    <!-- <td class="spacer"></td> -->
                    <th>Customer :</th>  <td>{{ $report->customer }}</td><br>
                  </tr>
                  <tr>
                  <th>Verify Date :</th>
                    <td>{{ $report->verify_date }}</td><br>
                    <!-- <td class="spacer"></td> -->
                    <th>Invoice No : </th>
                    <td>{{ $report->invoice_no }}</td> <br>
                    
                  </tr>
          </table>
  </div>

  

  <div class="card">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Part Name</th>
                    <th>Rec Quantity</th>
                    <th>Verify Quantity</th>
                    <th>Production Date</th>
                    <th>Shift</th>
                    <th>Can Use</th>
                    <th>Cant Use</th>
                    <th>Customer Defect Detail</th>
                    <th>Daijo Defect Detail</th>
                    <th>Remark</th>
                   
            
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
                    <td>{{ $detail->cant_use}}</td> 
                    <!-- Display customer_defect_detail if available and not null -->
                <td>
                    @foreach ($detail->customer_defect_detail as $key => $value)
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
                    @foreach ($detail->remark as $key => $value)
                        @if (!is_null($value))
                            {{ $key }}: {{ $value }}<br>
                        @endif
                    @endforeach
                </td>
            </tr>
                   
                @endforeach
            </tbody>


            @if($report->is_approve === null)
            <div>
            <form action="{{ route('approval.joni', ['id' => $report->id]) }}" method="post">
                @csrf

                <label>
                    <input type="checkbox" name="approve" value="1">
                    Approve
                </label>

                <label>
                    <input type="checkbox" name="reject" value="1">
                    Reject
                </label>

                <div>
                    <label>Description:</label>
                    <textarea name="description"></textarea>
                </div>

                <button type="submit">Submit</button>
            </form>
            </div>
            @endif
@endsection




<style>
    .autograph-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .autograph-container button {
        margin-right: 10px; /* Adjust the spacing between buttons */
    }

    .autograph-box {
        width: 200px; /* Adjust the width as needed */
        height: 100px; /* Adjust the height as needed */
        background-size: contain;
        background-repeat: no-repeat;
        border: 1px solid #ccc; /* Add border for better visibility */
    }
    .autograph-textbox {
    position: relative;
    width: 200px; /* Set the width based on your preference */
    margin-top: 10px; /* Adjust the margin based on your layout */
    text-align: center;
    border: 1px solid black;
    display: none; /* Hide initially */
    }
</style>

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


