@extends('layouts.app')


@section('content')
<div class="wrapper">
  <!-- Main content -->
  <section class="invoice">
    <!-- title row -->
    <div class="row">
      <div class="col-12">
        <h2 class="page-header">
          <i class="fas fa-globe"></i> Daijo Industrial.
          <small class="float-right">Date: {{ now()->format('j/n/Y') }}</small>
        </h2>
      </div>
      <!-- /.col -->
    </div>
    <!-- info row -->
    <!-- <div class="row invoice-info">
      <div class="col-sm-4 invoice-col">
        From
        <address>
          <strong>Admin, Inc.</strong><br>
          795 Folsom Ave, Suite 600<br>
          San Francisco, CA 94107<br>
          Phone: (804) 123-5432<br>
          Email: info@almasaeedstudio.com
        </address>
      </div> -->
      <!-- /.col -->
      <!-- <div class="col-sm-4 invoice-col">
        To
        <address>
          <strong>John Doe</strong><br>
          795 Folsom Ave, Suite 600<br>
          San Francisco, CA 94107<br>
          Phone: (555) 539-1037<br>
          Email: john.doe@example.com
        </address>
      </div> -->
      <!-- /.col -->
      <!-- <div class="col-sm-4 invoice-col">
        <b>Invoice #007612</b><br>
        <br>
        <b>Order ID:</b> 4F3S8J<br>
        <b>Payment Due:</b> 2/22/2014<br>
        <b>Account:</b> 968-34567
      </div> -->
      <!-- /.col -->
    </div>
    <!-- /.row -->
    


    </div>
    <div class="export-buttons">
    <a href="/foremind-detail/print/customer/excel/{{ $vendorCode }}" class="btn btn-success">Export to Excel</a>
</div>

                <style>
                    @media print {
                        /* Hide the export button when printing */
                        .export-buttons {
                            display: none;
                        }
                    }
                </style>

                <style>
                        .table-bordered td {
                            border: 1px solid #000;
                            padding: 1px;
                        }
                    </style>

    
    <!-- Table row -->
    @if (!empty($materials))
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="table-bordered">Material Code</th>
                        <th class="table-bordered">Material Name</th>
                        <th class="table-bordered">Unit Measure</th>
                        @foreach ($mon as $month)
                            <th class="table-bordered">{{ \Carbon\Carbon::parse($month)->format('Y-m') }}</th>
                        @endforeach
                        <th>Total</th>
                        <th>Customer</th>
                    </tr>
                </thead>
                <tbody>
    @php
        $currentMaterialCode = null;
        $materialTotal = 0;
        $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
    @endphp

    @foreach ($materials as $key => $material)
        @if ($material->material_code != $currentMaterialCode)
            <!-- Print material code, name, and unit of measure only for the first occurrence -->
            @if ($currentMaterialCode != null)
                <tr>
                    <td class="table-bordered">{{ $currentMaterialCode }}</td>
                    <td class="table-bordered">{{ $currentMaterialName }}</td>
                    <td class="table-bordered">{{ $currentMaterialMeasure }}</td>
                    @foreach ($monthlyTotals as $monthlyTotal)
                        <td class="table-bordered">
                            <strong>{{ $monthlyTotal }}</strong>
                        </td>
                    @endforeach
                    <td class="table-bordered">
                        <strong>{{ array_sum($monthlyTotals) }}</strong>
                    </td>
                    <td class="table-bordered">{{ $currentCustomer }}</td>
                </tr>
            @endif

            <!-- Initialize for the new material code -->
            @php
                $currentMaterialCode = $material->material_code;
                $currentMaterialName = $material->material_name;
                $currentMaterialMeasure = $material->unit_of_measure;
                $materialTotal = 0;
                $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
                $currentCustomer = $material->customer;
            @endphp
        @endif

        <!-- Accumulate data for each month -->
        @foreach ($qforecast[$key] as $index => $value)
            @php
                $calculation = $value * $material->quantity_material;
                $materialTotal += $calculation;
                $monthlyTotals[$index] += $calculation;
            @endphp
        @endforeach
    @endforeach

    <!-- Print the final row for the last material -->
    <tr>
        <td class="table-bordered">{{ $currentMaterialCode }}</td>
        <td class="table-bordered">{{ $currentMaterialName }}</td>
        <td class="table-bordered">{{ $currentMaterialMeasure }}</td>
        @foreach ($monthlyTotals as $monthlyTotal)
            <td class="table-bordered">
                <strong>{{ $monthlyTotal }}</strong>
            </td>
        @endforeach
        <td class="table-bordered">
            <strong>{{ array_sum($monthlyTotals) }}</strong>
        </td>
        <td class="table-bordered">{{ $currentCustomer }}</td>
    </tr>
</tbody>
            </table>
        </div>
    </div>
@endif
    <!-- /.row -->
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>

@endsection
<script>
  window.addEventListener("load", window.print());
</script>
</body>
</html>