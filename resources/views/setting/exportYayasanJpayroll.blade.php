@extends('layouts.app') <!-- Adjust this if you use a different layout -->

@section('content')
  <div class="container">
    <h1 class="text-center my-4">Department Status</h1>

    <div class="row">
      <div class="col-md-6">
        <p><strong>Selected Month:</strong>
          {{ \Carbon\Carbon::createFromDate($currentYear, $selectedMonth, 1)->format('F Y') }}</p>
      </div>
    </div>

    <form id="exportForm" action="{{ route('export.yayasan.jpayroll') }}" method="GET">
      @csrf
      <!-- Hidden inputs to pass selected month and year -->
      <input type="hidden" name="filter_status" value="{{ $selectedMonth }}">
      <input type="hidden" name="year" value="{{ $currentYear }}">

      <div class="row">
        <div class="col-md-6 d-flex justify-content-start">
          <!-- Submit Button to Export Data -->
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-download"></i> Export Yayasan Data
          </button>
        </div>
      </div>
    </form>
    <!-- Table to display the department status -->
    <table class="table table-bordered">
      <thead>
        <tr>
          <th scope="col">Department Name</th>
          <th scope="col">Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($departmentStatus as $department => $status)
          <tr>
            <td>{{ $department }}</td>
            <td>
              <span
                class="badge 
                                @if ($status === 'Ready') bg-success 
                                @else 
                                    bg-danger @endif
                            ">
                {{ ucfirst($status) }}
              </span>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="col-md-6 d-flex justify-content-end">
    <!-- Back Button -->
    <a href="{{ route('exportyayasan.dateinput') }}" class="btn btn-secondary">
      <i class="bx bx-arrow-back"></i> Back
    </a>
  </div>
@endsection
