@extends('layouts.app')
@section('title', 'Form Overtime List - ' . env('APP_NAME'))
@section('content')

    @include('partials.alert-success-error')

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0">Form Overtime List</h2>
        @if (Auth::user()->department->name !== 'MANAGEMENT')
            <a href="{{ route('formovertime.create') }}" class="btn btn-success shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Create Form Overtime
            </a>
        @endif
    </div>

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb bg-light rounded py-2">
            <li class="breadcrumb-item"><a href="{{ route('formovertime.index') }}">Form Overtime</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
    </nav>

    {{-- Filter Form --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('formovertime.index') }}" class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control shadow-sm" name="start_date" id="start_date"
                        value="{{ request('start_date') }}">
                </div>

                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control shadow-sm" name="end_date" id="end_date"
                        value="{{ request('end_date') }}">
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
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>BELUM SELESAI</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>SELESAI</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="info_status" class="form-label">Info</label>
                        <select class="form-select shadow-sm" name="info_status" id="info_status">
                            <option value="">-- Semua --</option>
                            <option value="pending" {{ request('info_status') === 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="approved" {{ request('info_status') === 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="rejected" {{ request('info_status') === 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="is_push" class="form-label">Push by Verificator</label>
                        <select class="form-select shadow-sm" name="is_push" id="is_push">
                            <option value="">-- All --</option>
                            <option value="1" {{ request('is_push') === 'enabled' ? 'selected' : '' }}>Already Pushed
                            </option>
                            <option value="0" {{ request('is_push') === 'disabled' ? 'selected' : '' }}>Not Yet Pushed
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="info_status" class="form-label">Info</label>
                        <select class="form-select shadow-sm" name="info_status" id="info_status">
                            <option value="">-- Semua --</option>
                            <option value="pending" {{ request('info_status') === 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="approved" {{ request('info_status') === 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="rejected" {{ request('info_status') === 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="is_push" class="form-label">Push by Verificator</label>
                        <select class="form-select shadow-sm" name="is_push" id="is_push">
                            <option value="">-- All --</option>
                            <option value="1" {{ request('is_push') === 'enabled' ? 'selected' : '' }}>Already Pushed
                            </option>
                            <option value="0" {{ request('is_push') === 'disabled' ? 'selected' : '' }}>Not Yet Pushed
                            </option>
                        </select>
                    </div>
                @endif

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-filter-circle me-1"></i> Filter
                    </button>
                    <a href="{{ route('formovertime.index') }}" class="btn btn-outline-secondary w-100 shadow-sm">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
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
                            <th>Is After Hour?</th>
                            <th>Info</th>
                            <th>Action</th>
                            <th>Created At</th>
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
                                <td>
                                    {{ $fot->is_after_hour ? 'Yes' : 'No' }}
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
                                <td>{{ \Carbon\Carbon::parse($fot->created_at)->format('d-m-Y') }}</td>
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

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">
            Showing {{ $dataheader->firstItem() }} to {{ $dataheader->lastItem() }}
            of {{ $dataheader->total() }} entries
        </div>

        <div>
            {{ $dataheader->withQueryString()->links() }}
        </div>
    </div>

@endsection
