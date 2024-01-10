@extends('layouts.app')

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
    <button onclick="addAutograph(1)">Acc QA Inspector</button>

    <!-- Autograph Textbox 1 -->
    <div class="autograph-box" id="autographBox1"></div>
</div>

<div class="autograph-container">
    <!-- Autograph Button 2 -->
    <button onclick="addAutograph(2)">Acc QA Leader</button>

    <!-- Autograph Textbox 2 -->
    <div class="autograph-box" id="autographBox2"></div>
</div>

<div class="autograph-container">
    <!-- Autograph Button 3 -->
    <button onclick="addAutograph(3)">Acc QC Head</button>

    <!-- Autograph Textbox 3 -->
    <div class="autograph-box" id="autographBox3"></div>
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
        display: none;
        border: 1px solid #ccc; /* Add border for better visibility */
    }
</style>

<script>
    // Function to add autograph to the specified box
    function addAutograph(section) {
        // Get the div element
        var autographBox = document.getElementById('autographBox' + section);

        // Set the background image based on the button clicked
        switch (section) {
            case 1:
                // Action for Button 1
                autographBox.style.backgroundImage = "url('/tandatangan1.png')";
                break;
            case 2:
                // Action for Button 2
                autographBox.style.backgroundImage = "url('/tandatangan2.png')";
                break;
            case 3:
                // Action for Button 3
                autographBox.style.backgroundImage = "url('/tandatangan3.png')";
                break;
            default:
                // Default action
                break;
        }

        // Make the div visible
        autographBox.style.display = "block";
    }
</script>
