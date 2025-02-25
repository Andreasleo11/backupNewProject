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
                <li class="breadcrumb-item"><a href="{{ route('maintenance.inventory.index') }}">Maintenance Inventory Reports</a></li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </nav>


        <div class="row">
            <div class="col">
                <h2 class="fw-bold">Maintenance Inventory Reports</h2>
            </div>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usernameStatusModal">
                Show Username Statuses
            </button>
            
        {{-- Filter Form --}}
        <form method="GET" action="{{ route('maintenance.inventory.index') }}">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="periode" class="form-label">Periode</label>
                    <select name="periode" id="periode" class="form-select">
                        <option value="">All</option>
                        <option value="1" {{ request('periode') == 1 ? 'selected' : '' }}>1 (Januari - April)</option>
                        <option value="2" {{ request('periode') == 2 ? 'selected' : '' }}>2 (Mei - Agustus)</option>
                        <option value="3" {{ request('periode') == 3 ? 'selected' : '' }}>3 (September - Desember)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label">Year</label>
                    <select name="year" id="year" class="form-select">
                        @for($i = date('Y'); $i <= date('Y') + 5; $i++)
                            <option value="{{ $i }}" {{ request('year', date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
        {{-- End Filter Form --}}
            <div class="col text-end">
                <a href="{{ route('maintenance.inventory.create') }}" class="btn btn-primary">New Report</a>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nomor Dokumen</th>
                                <th>Username</th>
                                <th>Periode</th>
                                <th>Revision Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reports as $report)
                                <tr>
                                    <td>{{ $report->id }}</td>
                                    <td>{{ $report->no_dokumen }}</td>
                                    <td>{{ $report->master->username }}</td>
                                    <td>{{ $report->periode_caturwulan }}</td>
                                    <td>{{ $report->revision_date }}</td>
                                    <td>
                                        <a href="{{ route('maintenance.inventory.show', $report->id) }}" class="btn btn-secondary">Detail</a>
                                        <a href="{{ route('maintenance.inventory.edit', $report->id) }}" class="btn btn-primary">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="6">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="usernameStatusModal" tabindex="-1" aria-labelledby="usernameStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usernameStatusModalLabel">Username Statuses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <div class="modal-body">
                    <ul class="list-group">
                        @foreach($usernameStatuses as $username => $status)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>{{ $username }}</strong> <!-- Username is bold -->
                                @if($status === 'yes')
                                    <span class="badge bg-success">Yes</span> <!-- or use a checkmark icon -->
                                @else
                                    <span class="badge bg-danger">No</span> <!-- or use a cross icon -->
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
