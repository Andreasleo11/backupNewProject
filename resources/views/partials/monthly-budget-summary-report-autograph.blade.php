@include('partials.reject-confirmation-modal', [
    'route' => route('monthly.budget.summary.report.reject', $report->id),
    'doc_num' => $report->doc_num,
])
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="row text-center">
            {{-- CREATED AUTOGRAPH --}}
            <div class="col my-2">
                <h2>Dibuat</h2>
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2" id="autographUser1"></div>

                @php
                    $showCreatedApproval = false;
                    if (!$report->created_autograph) {
                        if ($report->creator_id === auth()->user()->id) {
                            $showCreatedApproval = true;
                        }
                    }
                @endphp

                @if ($showCreatedApproval)
                    <div class="row px-4 d-flex justify-content-center">
                        <div class="col-auto me-2">
                            <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                                class="btn btn-danger">Reject</button>
                        </div>
                        <div class="col-auto">
                            <form action="{{ route('monthly.budget.summary.save.autograph', $report->id) }}"
                                method="POST" id="formCreatedAutograph">
                                @csrf @method('PUT')
                                <input type="hidden" name="created_autograph" value="{{ ucwords($authUser->name) }}">
                            </form>
                            @include('partials.confirmation-modal', [
                                'id' => $report->id,
                                'title' => 'Approval Confirmation',
                                'body' => 'Are you sure want to approve this report?',
                                'submitButton' =>
                                    '<button class="btn btn-success" onclick="document.getElementById(\'formCreatedAutograph\').submit()">Confirm</button>',
                            ])
                            <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-{{ $report->id }}"
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
                    $showIsKnownApproval = false;
                    if ($report->created_autograph && !$report->is_known_autograph) {
                        if ($authUser->is_gm && $report->is_moulding === 0) {
                            $showIsKnownApproval = true;
                        } elseif (
                            $authUser->is_head &&
                            $authUser->department->name === 'MOULDING' &&
                            $report->is_moulding === 1
                        ) {
                            $showIsKnownApproval = true;
                        }
                    }
                    $showIsKnownApproval = $showIsKnownApproval && $report->is_reject === 0;
                @endphp

                @if ($showIsKnownApproval)
                    <div class="row px-4 d-flex justify-content-center">
                        <div class="col-auto me-2">
                            <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                                class="btn btn-danger">Reject</button>
                        </div>
                        <div class="col-auto">
                            <form action="{{ route('monthly.budget.summary.save.autograph', $report->id) }}"
                                method="POST" id="formIsKnownAutograph">
                                @csrf @method('PUT')
                                <input type="hidden" name="is_known_autograph" value="{{ ucwords($authUser->name) }}">
                            </form>
                            @include('partials.confirmation-modal', [
                                'id' => $report->id,
                                'title' => 'Approval Confirmation',
                                'body' => 'Are you sure want to approve this report?',
                                'submitButton' =>
                                    '<button class="btn btn-success" onclick="document.getElementById(\'formIsKnownAutograph\').submit()">Confirm</button>',
                            ])
                            <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-{{ $report->id }}"
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
                    $showApprovedApproval = false;
                    if ($report->created_autograph && $report->is_known_autograph && !$report->approved_autograph) {
                        if ($authUser->specification->name === 'DIRECTOR') {
                            $showApprovedApproval = true;
                        }
                    }
                    $showApprovedApproval = $showApprovedApproval && $report->is_reject === 0;
                @endphp
                @if ($showApprovedApproval)
                    <div class="row px-4 d-flex justify-content-center">
                        <div class="col-auto me-2 ">
                            <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                                class="btn btn-danger">Reject</button>
                        </div>
                        <div class="col-auto">
                            <form action="{{ route('monthly.budget.summary.save.autograph', $report->id) }}"
                                method="POST" id="formApprovedAutograph">
                                @csrf @method('PUT')
                                <input type="hidden" name="approved_autograph" value="{{ ucwords($authUser->name) }}">
                            </form>
                            @include('partials.confirmation-modal', [
                                'id' => $report->id,
                                'title' => 'Approval Confirmation',
                                'body' => 'Are you sure want to approve this report?',
                                'submitButton' =>
                                    '<button class="btn btn-success" onclick="document.getElementById(\'formApprovedAutograph\').submit()">Confirm</button>',
                            ])
                            <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-{{ $report->id }}"
                                class="btn btn-success">Approve</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

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
                    var url = '/autographs/' + autographs['autograph_' + i];

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
