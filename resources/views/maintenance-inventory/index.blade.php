@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = auth()->user();
    @endphp
    {{-- END GLOBAL VARIABLE --}}
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('monthly.budget.report.index') }}">Maintenance Inventory
                        Reports</a>
                </li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col">
                <h2 class="fw-bold">Maintenance Inventory
                    Reports</h2>
            </div>
            <div class="col text-end">
                @php
                    $showCreateButton = false;
                    if (!$authUser->is_head && !$authUser->is_gm && $authUser->department->name !== 'DIRECTOR') {
                        $showCreateButton = true;
                    }
                @endphp
                @if ($showCreateButton)
                @endif
                <a href="" class="btn btn-primary">New Report</a>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nomor Dokumen</th>
                                <th>Master ID</th>
                                <th>Revision Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reports as $report)
                                <tr>
                                    <td>{{ $loop->iteration }}< /td>
                                    <td>{{ $report->no_dokumen }}</td>
                                    <td>{{ $report->master_id }}</td>
                                    <td>{{ $report->revision_date }}</td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="20">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
