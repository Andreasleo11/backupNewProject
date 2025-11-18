@extends('layouts.app')

@section('content')
    <section aria-label="header">
        <div class="d-flex justify-content-between align-items-center">
            <span class="fs-1">Department List</span>
            <div>
                @include('partials.add-department-modal')
                <button type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-department-modal">
                    <i class="lni lni-plus"></i>
                    Add department
                </button>
            </div>
        </div>
    </section>

    <section class="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('superadmin') }}">Home</a></li>
                <li class="breadcrumb-item active">Departments</li>
            </ol>
        </nav>
    </section>

    @include('partials.alert-success-error')

    <section aria-label="table">
        <div class="card ">
            <!-- Table body -->
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </section>

    @foreach ($departments as $department)
        @include('partials.edit-department-modal')
        @include('partials.delete-department-modal')
    @endforeach
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}
@endpush
