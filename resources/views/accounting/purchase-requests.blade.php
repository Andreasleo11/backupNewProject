@extends('layouts.app')

@section('content')
    <div class="row d-flex">
        <div class="col">
            <h1 class="h1">Purchase Requisition List (Approved)</h1>
        </div>
    </div>
    <section class="content">
        <div class="card mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    {{-- <div class="mb-3 row d-flex">
                        <div class="col-auto">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="form-label">Filter by status</div>
                                </div>
                                <div class="col-auto">
                                    <select name="filter_status" id="status-filter" class="form-select">
                                        <option value="" selected>All</option>
                                        <option value="3">Waiting</option>
                                        <option value="4">Approved</option>
                                        <option value="5">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}
@endpush
