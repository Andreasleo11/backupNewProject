@php
    // Pastikan selalu ada $authUser
    $authUser = $authUser ?? auth()->user();
@endphp

@include('partials.reject-confirmation-modal', [
    'route' => route('monthly.budget.summary.report.reject', $report->id),
    'doc_num' => $report->doc_num,
])

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="row text-center gy-4">
            {{-- CREATED AUTOGRAPH --}}
            <div class="col-12 col-md-4 my-2">
                <div class="text-uppercase text-secondary small mb-1">Dibuat</div>
                <div class="autograph-box mx-auto" id="autographBox1"></div>
                <div class="mt-2 small fw-semibold" id="autographUser1"></div>

                @php
                    $showCreatedApproval = false;

                    if (!$report->created_autograph) {
                        if ($report->creator_id === $authUser->id) {
                            $showCreatedApproval = true;
                        }
                    }
                @endphp

                @if ($showCreatedApproval)
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button type="button"
                            class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1"
                            onclick="window.dispatchEvent(new CustomEvent('open-reject-confirmation'))">
                            Reject
                        </button>


                        <div>
                            <form action="{{ route('monthly.budget.summary.save.autograph', $report->id) }}"
                                method="POST" id="formCreatedAutograph">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="created_autograph" value="{{ ucwords($authUser->name) }}">
                            </form>

                            @include('partials.confirmation-modal', [
                                'id' => $report->id,
                                'title' => 'Approval Confirmation',
                                'body' => 'Are you sure want to approve this report?',
                                'submitButton' =>
                                    '<button class="btn btn-success" onclick="document.getElementById(\'formCreatedAutograph\').submit()">Confirm</button>',
                            ])

                            <button type="button"
                                class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                                onclick="window.dispatchEvent(new CustomEvent('open-confirmation-{{ $report->id }}'))">
                                Approve
                            </button>

                        </div>
                    </div>
                @endif
            </div>

            {{-- IS KNOWN AUTOGRAPH --}}
            <div class="col-12 col-md-4 my-2">
                <div class="text-uppercase text-secondary small mb-1">Diketahui</div>
                <div class="autograph-box mx-auto" id="autographBox2"></div>
                <div class="mt-2 small fw-semibold" id="autographUser2"></div>

                @php
                    $showIsKnownApproval = false;

                    if ($report->created_autograph && !$report->is_known_autograph) {
                        if ($authUser->is_gm && $report->is_moulding === 0) {
                            // Non-moulding: GM
                            $showIsKnownApproval = true;
                        } elseif (
                            $authUser->is_head &&
                            $authUser->department->name === 'MOULDING' &&
                            $report->is_moulding === 1
                        ) {
                            // Moulding: Head Moulding
                            $showIsKnownApproval = true;
                        }
                    }

                    $showIsKnownApproval = $showIsKnownApproval && $report->is_reject === 0;
                @endphp

                @if ($showIsKnownApproval)
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                            class="btn btn-sm btn-outline-danger">
                            <i class="bx bx-x-circle me-1"></i> Reject
                        </button>

                        <div>
                            <form action="{{ route('monthly.budget.summary.save.autograph', $report->id) }}"
                                method="POST" id="formIsKnownAutograph">
                                @csrf
                                @method('PUT')
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
                                class="btn btn-sm btn-success">
                                <i class="bx bx-check-circle me-1"></i> Approve
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- APPROVED AUTOGRAPH --}}
            <div class="col-12 col-md-4 my-2">
                <div class="text-uppercase text-secondary small mb-1">Disetujui</div>
                <div class="autograph-box mx-auto" id="autographBox3"></div>
                <div class="mt-2 small fw-semibold" id="autographUser3"></div>

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
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                            class="btn btn-sm btn-outline-danger">
                            <i class="bx bx-x-circle me-1"></i> Reject
                        </button>

                        <div>
                            <form action="{{ route('monthly.budget.summary.save.autograph', $report->id) }}"
                                method="POST" id="formApprovedAutograph">
                                @csrf
                                @method('PUT')
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
                                class="btn btn-sm btn-success">
                                <i class="bx bx-check-circle me-1"></i> Approve
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        checkAutographStatus();

        function checkAutographStatus() {
            var autographs = {
                autograph_1: '{{ $report->created_autograph ?? null }}',
                autograph_2: '{{ $report->is_known_autograph ?? null }}',
                autograph_3: '{{ $report->approved_autograph ?? null }}',
            };

            for (var i = 1; i <= 3; i++) {
                var autographBox = document.getElementById('autographBox' + i);
                var autographNameBox = document.getElementById('autographUser' + i);

                if (autographs['autograph_' + i]) {
                    var url = '/autographs/' + autographs['autograph_' + i];

                    autographBox.style.backgroundImage = "url('" + url + ".png')";

                    var autographName = autographs['autograph_' + i].split('.')[0];
                    autographNameBox.textContent = autographName;
                    autographNameBox.style.display = 'block';
                }
            }
        }
    </script>
@endpush
