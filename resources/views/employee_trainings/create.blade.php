@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employee_trainings.index') }}">Employee Trainings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Training</li>
            </ol>
        </nav>

        <h2 class="h2 mb-3 fw-bold">Add Training</h2>
        <div class="card">
            <div class="card-body">
                <x-employee-training-form :action="route('employee_trainings.store')" :employees="$employees" submit-label="Save" />
            </div>
        </div>
    </div>
@endsection
