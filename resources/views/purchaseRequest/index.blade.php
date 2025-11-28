@extends('layouts.app')

@section('content')
<div class="m-4">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="row d-flex">
        <div class="col">
            <h1 class="h1">Purchase Requisition List</h1>
        </div>
        <div class="col-auto">
            @if (Auth::user()->specification->name !== 'DIRECTOR')
                <a href="{{ route('purchaserequest.create') }}" class="btn btn-primary">Create PR </a>
            @endif
        </div>
        <div class="col-auto">
            <a href="{{ route('purchaserequest.export.excel') }}" class="btn btn-outline-primary">Export
                Excel</a>
        </div>
    </div>
    <div class="table-responsive">
        {{ $dataTable->table() }}
    </div>
    </div>

    <!-- Search Panes CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.1.1/css/searchPanes.bootstrap5.min.css">

    <!-- Search Panes JS -->
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/dataTables.searchPanes.min.js"></script>
    <script type="module" src="https://cdn.datatables.net/searchpanes/2.3.3/js/searchPanes.bootstrap5.min.js"></script>
</div>
@endsection
@push('extraJs')
    {{ $dataTable->scripts() }}
@endpush
