@extends('layouts.app')

@section('content')
    <section class="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('monthly.budget.report.index') }}">Monthly Budget Reports</a>
                </li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </section>

    <style>
        .autograph-box {
            width: 200px;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid #ccc;
        }
    </style>

    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = Auth::user();
    @endphp

    <section class="autographs">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="row text-center">
                    {{-- CREATED AUTOGRAPH --}}
                    <div class="col my-2">
                        <h2>Dibuat</h2>
                        <div class="autograph-box container" id="autographBox1"></div>
                        <div class="container mt-2" id="autographUser1"></div>
                        @php
                            $showCreatedAutograph = false;
                            if (!$report->created_autograph) {
                                $showCreatedAutograph = true;
                            }
                        @endphp

                        @if ($showCreatedAutograph)
                            <div class="row px-4 d-flex justify-content-center">
                                <div class="col-auto me-2">
                                    <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                        class="btn btn-danger">Reject</button>
                                </div>
                                <div class="col-auto">
                                    <form action="{{ route('monthly.budget.save.autograph', $report->id) }}" method="POST"
                                        id="formCreatedAutograph">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="created_autograph"
                                            value="{{ ucwords($authUser->name) }}">
                                    </form>
                                    @include('partials.approve-confirmation-modal2', [
                                        'id' => '1',
                                        'title' => 'Approval Confirmation',
                                        'body' => 'Are you sure want to approve this report?',
                                        'submitButton' =>
                                            '<button class="btn btn-success" onclick="document.getElementById(\'formCreatedAutograph\').submit()">Confirm</button>',
                                    ])
                                    <button data-bs-toggle="modal" data-bs-target="#approve-confirmation-modal-1"
                                        class="btn btn-success">Approve</button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- IS KNOWN AUTOGRAPH --}}
                    <div class="col my-2">
                        <h2>Diketahui</h2>
                        <div class="autograph-box container" id="autographBox2"></div>
                        <div class="container mt-2 border-1" id="autographUser2"></div>
                        @php
                            $showIsKnownAutograph = false;
                            if (!$report->is_known_autograph && $authUser->is_head === 1) {
                                if ($authUser->department->name === $report->department->name) {
                                    if (
                                        $report->department->name === 'MOULDING' &&
                                        $authUser->specification->name === 'DESIGN'
                                    ) {
                                        $showIsKnownAutograph = true;
                                    } elseif (!$report->department->is_office) {
                                        $showIsKnownAutograph = true;
                                    }
                                } elseif ($report->department->name === 'STORE') {
                                    if ($authUser->department->name === 'LOGISTIC') {
                                        $showIsKnownAutograph = true;
                                    }
                                }
                            }
                        @endphp

                        @if ($showIsKnownAutograph)
                            <div class="row px-4 d-flex justify-content-center">
                                <div class="col-auto me-2">
                                    <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                        class="btn btn-danger">Reject</button>
                                </div>
                                <div class="col-auto">
                                    <form action="{{ route('monthly.budget.save.autograph', $report->id) }}" method="POST"
                                        id="formIsKnownAutograph">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="is_known_autograph"
                                            value="{{ ucwords($authUser->name) }}">
                                    </form>
                                    @include('partials.approve-confirmation-modal2', [
                                        'id' => '1',
                                        'title' => 'Approval Confirmation',
                                        'body' => 'Are you sure want to approve this report?',
                                        'submitButton' =>
                                            '<button class="btn btn-success" onclick="document.getElementById(\'formIsKnownAutograph\').submit()">Confirm</button>',
                                    ])
                                    <button data-bs-toggle="modal" data-bs-target="#approve-confirmation-modal-1"
                                        class="btn btn-success">Approve</button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- APPROVED AUTOGRAPH --}}
                    <div class="my-2 col">
                        <h2>Disetujui</h2>
                        <div class="autograph-box container" id="autographBox3"></div>
                        <div class="container mt-2 border-1" id="autographUser3"></div>
                        @php
                            $showApprovedAutograph = false;
                            if (!$report->approved_autograph) {
                                if ($report->department->name === 'MOULDING') {
                                    if ($authUser->is_head && $authUser->specification->name !== 'DESIGN') {
                                        $showApprovedAutograph = true;
                                    }
                                } elseif ($report->department->name === 'QC' || $report->department->name === 'QA') {
                                    if ($authUser->department->name === 'DIRECTOR') {
                                        $showApprovedAutograph = true;
                                    }
                                } elseif (!$report->department->is_office) {
                                    if ($authUser->is_gm) {
                                        $showApprovedAutograph = true;
                                    }
                                }
                            }
                        @endphp
                        @if ($showApprovedAutograph)
                            <div class="row px-4 d-flex justify-content-center">
                                <div class="col-auto me-2 ">
                                    <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                        class="btn btn-danger">Reject</button>
                                </div>
                                <div class="col-auto">
                                    <form action="{{ route('monthly.budget.save.autograph', $report->id) }}" method="POST"
                                        id="formApprovedAutograph">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="approved_autograph"
                                            value="{{ ucwords($authUser->name) }}">
                                    </form>
                                    @include('partials.approve-confirmation-modal2', [
                                        'id' => '2',
                                        'title' => 'Approval Confirmation',
                                        'body' => 'Are you sure want to approve this report?',
                                        'submitButton' =>
                                            '<button class="btn btn-success" onclick="document.getElementById(\'formApprovedAutograph\').submit()">Confirm</button>',
                                    ])
                                    <button data-bs-toggle="modal" data-bs-target="#approve-confirmation-modal-2"
                                        class="btn btn-success">Approve</button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section aria-label="report">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="h2 fw-bold mt-4">Monthly Budget Report</div>
                            <div class="fs-6 mt-2">
                                <div class="fs-6 text-secondary">From Department : {{ $report->department->name }}
                                    ({{ $report->dept_no }})</div>
                                <div class="fs-6 text-secondary">Created By : {{ $report->user->name }}</div>
                                <div class="mt-1">
                                    @if ($report->approved_autograph)
                                        <span class="badge text-bg-success px-3 py-2 fs-6">Approved</span>
                                    @elseif($report->is_known_autograph)
                                        @if ($report->department->name === 'QA' || $report->department->name === 'QC')
                                            <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting Director</span>
                                        @else
                                            <span class="badge text-bg-warning px-3 py-2 fs-6">Waiting GM</span>
                                        @endif
                                    @elseif($report->created_autograph)
                                        <span class="badge text-bg-secondary px-3 py-2 fs-6">Waiting Dept Head</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-body">
                                <table class="table text-center">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            @if ($report->dept_no == 363)
                                                <th>Spec</th>
                                            @endif
                                            <th>UoM</th>
                                            @if ($report->dept_no == 363)
                                                <th>Last Recorded Stock</th>
                                                <th>Usage Per Month</th>
                                            @endif
                                            <th>Quantity</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report->details as $detail)
                                            <tr>
                                                <td>{{ $detail->name }}</td>
                                                @if ($report->dept_no == 363)
                                                    <td>{{ $detail->spec }}</td>
                                                @endif
                                                <td>{{ $detail->uom }}</td>
                                                @if ($report->dept_no == 363)
                                                    <td>{{ $detail->last_recorded_stock }}</td>
                                                    <td>{{ $detail->usage_per_month }}</td>
                                                @endif
                                                <td>{{ $detail->quantity }}</td>
                                                <td>{{ $detail->remark }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $report->dept_no == 363 ? '7' : '4' }}">No data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('extraJs')
    <script>
        checkAutographStatus();

        function checkAutographStatus() {
            // Assume you have a variable from the server side indicating the autograph status
            var autographs = {
                autograph_1: '{{ $report->created_autograph ?? null }}',
                autograph_2: '{{ $report->is_known_autograph ?? null }}',
                autograph_3: '{{ $report->approved_autograph ?? null }}',
            };

            // Loop through each autograph status and update the UI accordingly
            for (var i = 1; i <= 3; i++) {
                var autographBox = document.getElementById('autographBox' + i);
                var autographNameBox = document.getElementById('autographUser' + i);


                // Check if autograph status is present in the database
                if (autographs['autograph_' + i]) {

                    // Construct URL based on the current location
                    var url = '/' + autographs['autograph_' + i];

                    // Update the background image using the URL
                    autographBox.style.backgroundImage = "url('" + url + '.png' + "')";

                    var autographName = autographs['autograph_' + i].split('.')[0];
                    autographNameBox.textContent = autographName;
                    autographNameBox.style.display = 'block';
                }
            }
        }
    </script>
@endpush
