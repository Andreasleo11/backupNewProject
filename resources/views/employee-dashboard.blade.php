@extends('layouts.guest')

@section('content')
    <div class="p-5">
        <x-employee-dashboard />
        <div class="mt-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filteredEmployeesModal">
                View Filtered Employees
            </button>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="filteredEmployeesModal" tabindex="-1" aria-labelledby="filteredEmployeesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filteredEmployeesModalLabel">Filtered Employees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        {{ $dataTable->table() }}
                        {{ $dataTable->scripts() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
