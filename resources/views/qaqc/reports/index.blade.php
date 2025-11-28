@extends('layouts.app')
{{-- @push('extraCss')
    <link rel="stylesheet" href="{{ asset('css/toast.css') }} ">
@endpush --}}
@section('content')
    {{-- <button onclick="showToast(successMsg)">Success</button>
    <button onclick="showToast(errorMsg)">Error</button>
    <button onclick="showToast(invalidMsg)">Invalid</button>
    <div id="toastBox"></div> --}}

    <section class="header">
        <div class="row">
            <div class="col">
                <h1 class="h1">Verification Reports</h1>
            </div>
            <div class="col-auto">
                <div class="row">
                    <div class="col-auto mb-2">
                        <a href="{{ route('qaqc.summarymonth') }}" class="btn btn-outline-primary">
                            Summary Per Month
                        </a>
                    </div>
                    <div class="col-auto mb-2">
                        <a href="{{ route('export.formadjusts') }}" class="btn btn-outline-primary">
                            Export all Form Adjust To Excel
                        </a>
                    </div>
                    <div class="col-auto mb-2">
                        <a href="{{ route('export.reports') }}" class="btn btn-outline-primary">
                            Export All To Excel
                        </a>

                    </div>

                    @php
                        $currentUser = Auth::user();
                    @endphp
                    @if ($currentUser->department->name == 'QC' && $currentUser->specification->name == 'INSPECTOR')
                        <div class="col-auto mb-2">
                            <a href="{{ route('qaqc.report.create') }}" class="btn btn-primary">
                                <i class='bx bx-plus'></i> Add <span class="d-none d-sm-inline">Report</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('qaqc') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reports</li>
            </ol>
        </nav>
    </section>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">

        </div>
    </div>
    <div class="card mt-3">
        <div class="card-body">
            <div class="row d-flex mb-3 align-items-center justify-content-end">
                Filter by month
                <div class="col-auto">
                    <input type="text" id="monthPicker" class="form-control" placeholder="Select Month">
                </div>
            </div>
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}

    <script type="module">
        // Initialize the month picker
        $('#monthPicker').datepicker({
            format: "mm-yyyy",
            startView: "months",
            minViewMode: "months",
            autoclose: true
        });

        // Reload DataTable on month change
        $('#monthPicker').on('change', function() {
            window.LaravelDataTables["vqcreports-table"].draw();
        });

        // Extend DataTable with month filter
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var selectedMonth = $('#monthPicker').val();
                if (!selectedMonth) return true; // Skip filter if no month is selected

                var recDate = data[3]; // Ensure this matches the 'rec_date' column index
                var date = new Date(recDate);
                var month = ('0' + (date.getMonth() + 1)).slice(-2) + '-' + date.getFullYear();

                return month === selectedMonth;
            }
        );
    </script>
@endpush
