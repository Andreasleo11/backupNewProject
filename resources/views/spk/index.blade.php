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
                <li class="breadcrumb-item"><a href="#">SPK</a>
                </li>
                <li class="breadcrumb-item active">List</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col">
                <h2 class="fw-bold">SPK List</h2>
            </div>
            <div class="col text-end">
                @php
                    $showCreateButton = false;
                    if ($authUser->department->name !== 'DIRECTOR') {
                        $showCreateButton = true;
                    }
                @endphp
                @if ($showCreateButton)
                    <a href="{{ route('spk.create') }}" class="btn btn-primary">New Report</a>
                @endif
                <a href="{{ route('spk.monthlyreport') }}" class="btn btn-primary">Monthly Report</a>
            </div>
        </div>

        <form action="{{ route('spk.index') }}" method="get">
            <div class="div mt-3 row ">
                <div class="col-auto">
                    <label for="start_date" class="form-label">Start date</label>
                    <input type="date" name="start_date" class="form-control"
                        value="{{ Session::get('start_date') ?? '' }}">
                </div>
                <div class="col-auto">
                    <label for="end_date" class="form-label">End date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ Session::get('end_date') ?? '' }}">
                </div>
                <div class="col-auto">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="0" {{ session('status') === null ? 'selected' : '' }}>ALL</option>
                        <option value="1" {{ session('status') === 1 ? 'selected' : '' }}>WAITING</option>
                        <option value="2" {{ session('status') == 2 ? 'selected' : '' }}>IN PROGRESS</option>
                        <option value="3" {{ session('status') == 3 ? 'selected' : '' }}>DONE</option>
                    </select>
                </div>
                <div class="col-auto align-content-end ">
                    <a href="{{ route('spk.index', ['status' => null]) }}" class="btn btn-secondary">Reset</a>
                </div>
                <div class="col-auto align-content-end ">
                    <button class="btn btn-primary mt-3">Filter</button>
                </div>
            </div>
        </form>

        <div class="card mt-5">
            <div class=card-body>
                <table class="table table-border text-center mb-0">
                    <thead>
                        <tr>
                            <th>No. Dokumen</th>
                            <th>Pelapor</th>
                            <th>Tanggal Lapor</th>
                            <th>Judul Laporan</th>
                            <th>PIC</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td class="align-content-center">{{ $report->no_dokumen }}</td>
                                <td class="align-content-center">{{ $report->pelapor }}</td>
                                <td class="align-content-center">@formatDate($report->tanggal_lapor)</td>
                                <td class="align-content-center">{{ $report->judul_laporan }}</td>
                                <td class="align-content-center">{{ $report->pic ?? 'Not Assigned' }}</td>
                                <td class="align-content-center">@include('partials.spk-status', ['status' => $report->status_laporan])</td>
                                <td class="align-content-center">@include('partials.spk-actions')</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $reports->links() }}
        </div>
    </div>
@endsection
