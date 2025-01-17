@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employee_trainings.index') }}">Employee Trainings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Training</li>
            </ol>
        </nav>

        <h2 class="h2 mb-3 fw-bold">Edit Training</h2>
        <div class="card">
            <div class="card-body">
                <x-employee-training-form :action="route('employee_trainings.update', $training->id)" method="PUT" :employees="$employees" :employee-id="$training->employee_id"
                    :description="$training->description" :last-training-at="$training->last_training_at" :evaluated="$training->evaluated" submit-label="Update" />
            </div>
        </div>
    </div>
@endsection
