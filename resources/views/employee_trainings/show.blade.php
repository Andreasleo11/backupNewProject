@extends('layouts.app')

@section('content')
  <div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('employee_trainings.index') }}">Employee
            Trainings</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
      </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h2 fw-bold"><i class="fas fa-info-circle"></i>Employee Training Details</h2>
      <a href="{{ route('employee_trainings.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
      </a>
    </div>

    <!-- Card with Information -->
    <div class="card shadow-lg border-0 p-3">
      <div class="card-body">
        <div class="row">
          <!-- Employee Details -->
          <div class="col-md-6 mb-4">
            <h6 class="text-uppercase text-muted"><i class="fas fa-user"></i> Employee Details</h6>
            <div class="p-3">
              <p class="mb-2"><strong>Name:</strong> {{ $training->employee->Nama }}</p>
              <p class="mb-2"><strong>NIK:</strong> {{ $training->employee->NIK }}</p>
              <p class="mb-0"><strong>Department:</strong> {{ $training->employee->Dept }}</p>
            </div>
          </div>

          <!-- Training Details -->
          <div class="col-md-6 mb-4">
            <h6 class="text-uppercase text-muted"><i class="fas fa-chalkboard-teacher"></i> Training
              Details
            </h6>
            <div class="p-3">
              <p class="mb-2"><strong>Description:</strong> {{ $training->description }}</p>
              <p class="mb-2"><strong>Last Training Date:</strong>
                {{ \Carbon\Carbon::parse($training->last_training_at)->format('d-m-Y') }}</p>
              <p class="mb-0">
                <strong>Evaluated: </strong>
                @if ($training->evaluated)
                  <span class="badge bg-success">Yes</span>
                @else
                  <span class="badge bg-danger">No</span>
                @endif
              </p>

            </div>
          </div>
        </div>
      </div>
      <div class="card-footer text-end bg-light pb-0">
        <a href="{{ route('employee_trainings.edit', $training->id) }}"
          class="btn btn-warning btn-sm">Edit</a>
        <form action="{{ route('employee_trainings.destroy', $training->id) }}" method="POST"
          class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm"
            onclick="return confirm('Are you sure?')">Delete</button>
        </form>
      </div>
    </div>
  </div>
@endsection
