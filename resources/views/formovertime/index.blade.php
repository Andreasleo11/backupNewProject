@extends('layouts.app')
@section('title', 'Form Overtime List - ' . env('APP_NAME'))
@section('content')
    <style>
        .table thead th {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.85rem;
        }

        .card {
            border-radius: 0.75rem;
        }

        .btn {
            border-radius: 0.5rem;
        }
    </style>
    @include('partials.alert-success-error')

    {{-- Filter Form --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('formovertime.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="date" class="form-label">Overtime Date</label>
                    <input type="date" class="form-control shadow-sm" name="date" id="date"
                        value="{{ request('date') }}">
                </div>

                <div class="col-md-3">
                    <label for="dept" class="form-label">Department</label>
                    <select class="form-select shadow-sm" name="dept" id="dept">
                        <option value="">-- All --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('dept') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if (Auth::user()->specification->name == 'VERIFICATOR')
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select shadow-sm" name="status" id="status">
                            <option value="">-- Semua --</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>SELESAI</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>BELUM SELESAI</option>
                        </select>
                    </div>
                @endif

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-filter-circle me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light rounded px-3 py-2">
            <li class="breadcrumb-item"><a href="{{ route('formovertime.index') }}">Form Overtime</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold text-primary mb-0">Form Overtime List</h2>
        @if (Auth::user()->department->name !== 'MANAGEMENT')
            <a href="{{ route('formovertime.create') }}" class="btn btn-success shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Create Form Overtime
            </a>
        @endif
    </div>

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Admin</th>
                            <th>Dept</th>
                            <th>Branch</th>
                            <th>Overtime Date</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Info</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataheader as $fot)
                            <tr>
                                <td>{{ $fot->id }}</td>
                                <td>{{ $fot->user->name }}</td>
                                <td>{{ $fot->department->name }}</td>
                                <td>{{ $fot->branch }}</td>
                                <td>@formatDate($fot->details[0]->start_date)</td>
                                <td>
                                    @include('partials.formovertime-status', ['fot' => $fot])
                                    @if ($fot->is_push == 1)
                                        <div class="text-success small mt-1">
                                            <i class="bi bi-check-circle me-1"></i> Finish by Bu Bernadett
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge rounded-pill px-3 py-2 fs-6 
                                    {{ $fot->is_planned ? 'bg-light text-secondary border border-secondary' : 'bg-danger text-white' }}">
                                        {{ $fot->is_planned ? 'Planned' : 'Urgent' }}
                                    </span>
                                </td>
                                <td class="text-start">
                                    @php
                                        $approvedCount = $fot->details->where('status', 'Approved')->count();
                                        $rejectedCount = $fot->details->where('status', 'Rejected')->count();
                                        $nullCount = $fot->details->whereNull('status')->count();
                                    @endphp
                                    <div class="d-flex flex-column gap-1">
                                        @if ($approvedCount)
                                            <span class="badge bg-success">Approved: {{ $approvedCount }}</span>
                                        @endif
                                        @if ($rejectedCount)
                                            <span class="badge bg-danger">Rejected: {{ $rejectedCount }}</span>
                                        @endif
                                        @if ($nullCount)
                                            <span class="badge bg-secondary">Pending: {{ $nullCount }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                                        <a href="{{ route('formovertime.detail', ['id' => $fot->id]) }}"
                                            class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-info-circle"></i> Detail
                                        </a>

                                        @include('partials.delete-confirmation-modal', [
                                            'id' => $fot->id,
                                            'title' => 'Delete Form Overtime',
                                            'body' => "Are you sure you want to delete this report with <strong>ID = $fot->id</strong>?",
                                            'route' => 'formovertime.delete',
                                        ])

                                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#delete-confirmation-modal-{{ $fot->id }}">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
