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
      </div>
  </div>

  <div class="export-buttons">
    <a href="/foremind-detail/print/excel/{{ $vendorCode }}" class="btn btn-success">Export to
      Excel</a>
  </div>

  <style>
    @media print {

      /* Hide the export button when printing */
      .export-buttons {
        display: none;
      }
    }

    .table-bordered td {
      border: 1px solid #000;
      padding: 1px;
    }
  </style>

  <style>
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
              <th class="table-bordered">Item No</th>
              <!-- <th>Vendor Name</th> -->
              <th class="table-bordered">Vendor Code</th>
              <th class="table-bordered">Unit Measure</th>
              <th class="table-bordered">Quantity Material</th>
              @foreach ($mon as $month)
                <th class="table-bordered">{{ \Carbon\Carbon::parse($month)->format('M-Y') }}</th>
              @endforeach
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            @php
              $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
              $currentMaterialCode = null;
            @endphp

            @foreach ($materials as $key => $material)
              <tr>
                @if ($material->material_code != $currentMaterialCode)
                  <!-- Display material code and material name only for the first occurrence -->
                  <td class="table-bordered">{{ $material->material_code }}</td>
                  <td class="table-bordered">{{ $material->material_name }}</td>
                  @php
                    $currentMaterialCode = $material->material_code;
                  @endphp
                @else
                  <!-- Display blank columns for subsequent occurrences of the same material code -->
                  <td class="table-bordered"></td>
                  <td class="table-bordered"></td>
                @endif
                <td class="table-bordered">{{ $material->item_no }}</td>
                <!-- <td>{{ $material->vendor_name }}</td> -->
                <td class="table-bordered">{{ $material->vendor_code }}</td>
                <td class="table-bordered">{{ $material->unit_of_measure }}</td>
                <td class="table-bordered">{{ $material->quantity_material }}</td>

                @php
                  $total = 0;
                @endphp

                @foreach ($qforecast[$loop->index] as $index => $value)
                  @php
                    $calculation = $value * $material->quantity_material;
                    $total += $calculation;
                    $monthlyTotals[$index] += $calculation;
                  @endphp

                  <td class="table-bordered">
                    <div>{{ $value }}</div>
                    <strong>{{ $calculation }}</strong>
                  </td>
                  <!-- Display the calculated value -->
                @endforeach

                <td class="table-bordered"><strong>{{ $total }}</strong></td>
                <!-- Add this line for the total -->

              </tr>
              <!-- Calculate and display the total for each month after processing the last material code -->
              @if ($loop->last)
                <tr>
                  <td class="table-bordered" colspan="5"></td>
                  <td class="table-bordered">Monthly Total</td>
                  @foreach ($monthlyTotals as $monthlyTotal)
                    <td class="table-bordered">
                      <strong>{{ $monthlyTotal }}</strong>
                    </td>
                  @endforeach
                  <td class="table-bordered"><strong>{{ array_sum($monthlyTotals) }}</strong></td>
                  <!-- Add this line for the monthly total -->
                </tr>
              @endif
              </tr>

              @if (!$loop->last && $material->material_code != $materials[$loop->index + 1]->material_code)
                <!-- Calculate and display the total for each month before the empty line -->
                <tr>
                  <td class="table-bordered" colspan="5"></td>
                  <td class="table-bordered">Monthly Total</td>
                  @foreach ($monthlyTotals as $monthlyTotal)
                    <td class="table-bordered">
                      <strong>{{ $monthlyTotal }}</strong>
                    </td>
                  @endforeach
                  <td class="table-bordered"><strong>{{ array_sum($monthlyTotals) }}</strong></td>
                  <!-- Add this line for the monthly total -->
                </tr>

                <!-- Reset monthly totals for the new material code -->
                @php
                  $monthlyTotals = array_fill(0, count($qforecast[0]), 0);
                @endphp

                <!-- Add a break line after the monthly total -->
                <tr>
                  <td class="table-bordered" colspan="11"></td>
                </tr>
              @endif
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
@endsection
