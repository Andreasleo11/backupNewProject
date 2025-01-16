@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        @include('partials.alert-success-error')


        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employee_trainings.index') }}">Employee Trainings</a></li>
                <li class="breadcrumb-item active" aria-current="page">List</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="h2 fw-bold">Employee Trainings</span>
            <a href="{{ route('employee_trainings.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Training
            </a>
        </div>

        {{ $dataTable->table(['class' => 'table table-bordered table-striped table-hover'], true) }}
    </div>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}
@endpush
