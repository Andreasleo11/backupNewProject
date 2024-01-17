<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Form</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

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


<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Verification Form</h2>

            <form action="/report/store" method="post">
                @csrf

                {{-- Rec'D Date --}}
                <div class="mb-3">
                    <label for="Rec_Date" class="form-label">Rec'D Date:</label>
                    <input type="date" id="Rec_Date" name="Rec_Date" class="form-control" required>
                </div>

                {{-- Verify Date --}}
                <div class="mb-3">
                    <label for="Verify_Date" class="form-label">Verify Date:</label>
                    <input type="date" id="Verify_Date" name="Verify_Date" class="form-control" required>
                </div>

                {{-- Customer --}}
                <div class="mb-3">
                    <label for="Customer" class="form-label">Customer:</label>
                    <input type="text" id="Customer" name="Customer" class="form-control" required>
                </div>

                {{-- Invoice No --}}
                <div class="mb-3">
                    <label for="Invoice_No" class="form-label">Invoice No:</label>
                    <input type="text" id="Invoice_No" name="Invoice_No" class="form-control" required>
                </div>

                {{-- Number of Parts --}}
                <div class="mb-3">
                    <label for="num_of_parts" class="form-label">Number of Parts:</label>
                    <input type="number" id="num_of_parts" name="num_of_parts" class="form-control" min="1" required>
                </div>

                <!-- {{-- Customer Defect Detail --}}
                <div class="mb-3">
                    <label for="customer_defect_details" class="form-label">Number of Customer Defect Details:</label>
                    <input type="number" id="customer_defect_details" name="customer_defect_details" class="form-control" min="0">
                </div>

                {{-- Daijo Defect Detail --}}
                <div class="mb-3">
                    <label for="daijo_defect_details" class="form-label">Number of Daijo Defect Details:</label>
                    <input type="number" id="daijo_defect_details" name="daijo_defect_details" class="form-control" min="0">
                </div> -->

                {{-- Part Details --}}
                <div id="partDetails" class="mb-3 row">
                </div>

                <button type="submit" class="btn btn-primary mt-3">Submit</button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS (optional, if you need JavaScript features) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/create_header.js') }}"></script>

</body>
</html>