@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="row d-flex">
        <div class="col">
            <h1 class="h1">Purchase Requisition List</h1>
        </div>
        <div class="col-auto">
            @if (Auth::user()->department->name !== 'DIRECTOR')
                <a href="{{ route('purchaserequest.create') }}" class="btn btn-primary">Create PR </a>
            @endif
        </div>
        <div class="col-auto">
            <a href="{{ route('purchaserequest.export.excel') }}" class="btn btn-outline-primary">Export Excel</a>
        </div>
    </div>

    <form action="{{ route('purchaserequest.home') }}" method="get">
        <div class="div mt-3 row">
            <div class="col-auto">
                <label for="start_date" class="form-label">Start date</label>
                <input type="date" name="start_date" class="form-control" value="{{ Session::get('start_date') ?? '' }}">
            </div>
            <div class="col-auto">
                <label for="end_date" class="form-label">End date</label>
                <input type="date" name="end_date" class="form-control" value="{{ Session::get('end_date') ?? '' }}">
            </div>
            <div class="col-auto">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="0" {{ session('status') === null ? 'selected' : '' }}>All Status</option>
                    <option value="1" {{ session('status') == 1 ? 'selected' : '' }}>Waiting for Dept Head</option>
                    <option value="7" {{ session('status') == 7 ? 'selected' : '' }}>Waiting for GM</option>
                    <option value="6" {{ session('status') == 6 ? 'selected' : '' }}>Waiting for Purchaser</option>
                    <option value="2" {{ session('status') == 2 ? 'selected' : '' }}>Waiting for Verificator</option>
                    <option value="3" {{ session('status') == 3 ? 'selected' : '' }}>Waiting for Director</option>
                    <option value="5" {{ session('status') == 5 ? 'selected' : '' }}>Rejected</option>
                    <option value="4" {{ session('status') == 4 ? 'selected' : '' }}>Approved</option>
                </select>
            </div>

            <div class="col-auto">
                <label for="branch" class="form-label">Branch</label>
                <select class="form-select" name="branch">
                    <option value="jakarta" {{ session('branch') === 'jakarta' ? 'selected' : '' }}>Jakarta</option>
                    <option value="karawang" {{ session('branch') == 'karawang' ? 'selected' : '' }}>Karawang</option>
                </select>
            </div>

            <div class="col-auto align-content-end ">
                <a href="{{ route('purchaserequest.home', ['reset' => 1]) }}" class="btn btn-secondary">Reset</a>
            </div>
            <div class="col-auto align-content-end ">
                <button class="btn btn-primary mt-3">Filter</button>
            </div>
        </div>
    </form>

    <section class="content">
        <div class="card mt-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped text-center mb-0">
                        <thead>
                            <tr>
                                <th class="fw-semibold align-content-center fs-5">No</th>
                                <th class="fw-semibold align-content-center fs-5">Doc Num</th>
                                <th class="fw-semibold align-content-center fs-5">Branch</th>
                                <th class="fw-semibold align-content-center fs-5">Date PR</th>
                                <th class="fw-semibold align-content-center fs-5">From Department</th>
                                <th class="fw-semibold align-content-center fs-5">To Department</th>
                                <th class="fw-semibold align-content-center fs-5">PR No </th>
                                <th class="fw-semibold align-content-center fs-5">Supplier</th>
                                <th class="fw-semibold align-content-center fs-5">Action</th>
                                <th class="fw-semibold align-content-center fs-5">Status</th>
                                <th class="fw-semibold align-content-center fs-5">Approved Date</th>
                                <th class="fw-semibold align-content-center fs-5">PO Number</th>
                                @if (auth()->user()->department->name === 'PURCHASING')
                                    <th class="fw-semibold align-content-center fs-5">Purpose</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $user = Auth::user();
                            @endphp
                            @forelse ($purchaseRequests as $pr)
                                <tr class="align-middle">
                                    <td>{{ $loop->iteration }}</td>
                                    <td> {{ $pr->doc_num }} </td>
                                    <td> {{ $pr->branch }} </td>
                                    <td> @formatDate($pr->date_pr) </td>
                                    <td>{{ $pr->from_department ?? $pr->createdBy->department->name }}</td>
                                    <td>{{ $pr->to_department }}</td>
                                    <td>{{ $pr->pr_no }}</td>
                                    <td>{{ $pr->supplier }}</td>
                                    <td>
                                        @include('partials.pr-action-buttons')
                                    </td>
                                    <td>
                                        @include('partials.pr-status-badge')
                                    </td>
                                    <td>@formatDate($pr->approved_at)</td>
                                    <td>{{ $pr->po_number }}</td>
                                    @if (auth()->user()->department->name === 'PURCHASING')
                                        @if ($pr->to_department == 'Purchasing' && $pr->from_department == 'MOULDING')
                                            <td>{{ optional($pr->itemDetail->first())->purpose }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endif

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="20">No Data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $purchaseRequests->links() }}
        </div>
    </section>
@endsection
