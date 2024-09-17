<style>
    .autograph-box {
        width: 200px;
        height: 100px;
        background-size: contain;
        background-repeat: no-repeat;
        border: 1px solid #ccc;
    }
</style>

@include('partials.reject-confirmation-modal', [
    'route' => route('monthly.budget.report.reject', $report->id),
    'doc_num' => $report->doc_num,
])

<div class="row text-center">
    {{-- CREATOR AUTOGRAPH --}}
    <div class="col my-3">
        <h3>Creator</h3>
        <div class="autograph-box container" id="autographBox1"></div>
        <div class="container mt-2" id="autographUser1"></div>
        @php
            $showCreatorAutographButtons = false;
            if (!$report->creator_autograph && $report->status_laporan === 0 && $report->pelapor === $authUser->name) {
                $showCreatorAutographButtons = true;
            }
        @endphp
        @if ($showCreatorAutographButtons)
            <div class="row px-4 d-flex justify-content-center g-2 gx-4">
                <div class="col-auto">
                    <form action="{{ route('spk.save.autograph', $report->id) }}" method="POST"
                        id="formCreatorAutograph">
                        @csrf @method('PUT')
                        <input type="hidden" name="creator_autograph" value="{{ ucwords($authUser->name) . '.png' }}">
                    </form>
                    @include('partials.confirmation-modal', [
                        'id' => '1',
                        'title' => 'Sign Confirmation',
                        'body' => 'Are you sure want to sign this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formCreatorAutograph\').submit()">Confirm</button>',
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-1"
                        class="btn btn-success">Sign</button>
                </div>
            </div>
        @endif
    </div>

    {{-- DEPT HEAD AUTOGRAPH --}}
    <div class="col my-3 {{ $report->to_department === 'COMPUTER' ? 'd-none' : '' }}">
        <h3>Dept Head</h3>
        <div class="autograph-box container" id="autographBox2"></div>
        <div class="container mt-2 border-1" id="autographUser2"></div>
        @php
            $showDeptHeadAutographButtons = false;
            if (
                !$report->dept_head_autograph &&
                $report->status_laporan === 1 &&
                $authUser->department->name == $report->from_department &&
                $authUser->is_head
            ) {
                $showDeptHeadAutographButtons = true;
            }
        @endphp
        @if ($showDeptHeadAutographButtons)
            <div class="row px-4 d-flex justify-content-center g-2 gx-4">
                <div class="col-auto">
                    <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                        class="btn btn-danger">Reject</button>
                </div>
                <div class="col-auto">
                    <form action="{{ route('spk.save.autograph', $report->id) }}" method="POST"
                        id="formDeptHeadAutograph">
                        @csrf @method('PUT')
                        <input type="hidden" name="dept_head_autograph"
                            value="{{ ucwords($authUser->name) . '.png' }}">
                    </form>
                    @include('partials.confirmation-modal', [
                        'id' => '2',
                        'title' => 'Approval Confirmation',
                        'body' => 'Are you sure want to approve this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formDeptHeadAutograph\').submit()">Confirm</button>',
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-2"
                        class="btn btn-success">Approve</button>
                </div>
            </div>
        @endif
    </div>

    {{-- PPIC AUTOGRAPH --}}
    <div class="col my-3 {{ $report->to_department === 'MAINTENANCE MOULDING' ? '' : 'd-none' }}">
        <h3>PPIC</h3>
        <div class="autograph-box container" id="autographBox2"></div>
        <div class="container mt-2 border-1" id="autographUser2"></div>
        @php
            $showPpicAutographButtons = false;
            if (
                !$report->ppic_autograph &&
                $report->status_laporan === 6 &&
                $report->tanggal_estimasi &&
                $authUser->department->name === 'PPIC' &&
                $authUser->is_head
            ) {
                $showPpicAutographButtons = true;
            }
        @endphp
        @if ($showPpicAutographButtons)
            <div class="row px-4 d-flex justify-content-center g-2 gx-4">
                <div class="col-auto">
                    <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                        class="btn btn-danger">Reject</button>
                </div>
                <div class="col-auto">
                    <form action="{{ route('spk.save.autograph', $report->id) }}" method="POST" id="formPpicAutograph">
                        @csrf @method('PUT')
                        <input type="hidden" name="ppic_autograph" value="{{ ucwords($authUser->name) . '.png' }}">
                    </form>
                    @include('partials.confirmation-modal', [
                        'id' => '6',
                        'title' => 'Approval Confirmation',
                        'body' => 'Are you sure want to approve this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formPpicAutograph\').submit()">Confirm</button>',
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-6"
                        class="btn btn-success">Approve</button>
                </div>
            </div>
        @endif
    </div>

    {{-- ADMIN AUTOGRAPH --}}
    <div class="my-3 col {{ $report->to_department === 'COMPUTER' ? 'd-none' : '' }}">
        <h3>Admin</h3>
        <div class="autograph-box container" id="autographBox3"></div>
        <div class="container mt-2 border-1" id="autographUser3"></div>
        @php
            $showAdminAutographButtons = false;
            if (
                !$report->admin_autograph &&
                $report->status_laporan === 2 &&
                $report->pic &&
                $report->tindakan &&
                $report->tanggal_estimasi &&
                $report->tanggal_mulai
            ) {
                $showAdminAutographButtons = true;
            }
        @endphp
        @if ($showAdminAutographButtons)
            <div class="row px-4 d-flex justify-content-center g-2 gx-4">
                <div class="col-auto">
                    <form action="{{ route('spk.save.autograph', $report->id) }}" method="POST"
                        id="formAdminAutograph">
                        @csrf @method('PUT')
                        <input type="hidden" name="admin_autograph" value="{{ ucwords($authUser->name) . '.png' }}">
                    </form>
                    @include('partials.confirmation-modal', [
                        'id' => '3',
                        'title' => 'Sign Confirmation',
                        'body' => 'Are you sure want to sign this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formAdminAutograph\').submit()">Confirm</button>',
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-3"
                        class="btn btn-success">Sign</button>
                </div>
            </div>
        @endif
    </div>
    {{-- PIC AUTOGRAPH --}}
    <div class="my-3 col">
        <h3>PIC</h3>
        <div class="autograph-box container" id="autographBox4"></div>
        <div class="container mt-2 border-1" id="autographUser4"></div>
        @php
            $showPicAutographButtons = false;
            if (!$report->pic_autograph && $report->admin_autograph && $report->tanggal_mulai) {
                $showPicAutographButtons = true;
            } elseif (
                $report->to_department === 'COMPUTER' &&
                !$report->pic_autograph &&
                $report->pic === $authUser->name
            ) {
                $showPicAutographButtons = true;
            }
        @endphp
        @if ($showPicAutographButtons)
            <div class="row px-4 d-flex justify-content-center g-2 gx-4">
                <div class="col-auto">
                    <form action="{{ route('spk.save.autograph', $report->id) }}" method="POST" id="formPicAutograph">
                        @csrf @method('PUT')
                        <input type="hidden" name="pic_autograph" value="{{ ucwords($authUser->name) . '.png' }}">
                    </form>
                    @include('partials.confirmation-modal', [
                        'id' => '4',
                        'title' => 'Sign Confirmation',
                        'body' => 'Are you sure want to sign this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formPicAutograph\').submit()">Confirm</button>',
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-4"
                        class="btn btn-success">Sign</button>
                </div>
            </div>
        @endif
    </div>
    {{-- APPROVED AUTOGRAPH AUTOGRAPH --}}
    <div class="my-3 col">
        <h3>Approved</h3>
        <div class="autograph-box container" id="autographBox5"></div>
        <div class="container mt-2 border-1" id="autographUser5"></div>
        @php
            $showApprovedAutographButtons = false;
            if (
                !$report->approved_autograph &&
                $report->status_laporan === 4 &&
                $authUser->is_head &&
                $report->to_department === $authUser->department->name
            ) {
                $showApprovedAutographButtons = true;
            } elseif (
                $report->to_department === 'COMPUTER' &&
                !$report->approved_autograph &&
                $authUser->is_head &&
                $report->status_laporan === 4
            ) {
                $showApprovedAutographButtons = true;
            }
        @endphp
        @if ($showApprovedAutographButtons)
            <div class="row px-4 d-flex justify-content-center g-2 gx-4">
                <div class="col-auto">
                    <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                        class="btn btn-danger">Reject</button>
                </div>
                <div class="col-auto">
                    <form action="{{ route('spk.save.autograph', $report->id) }}" method="POST"
                        id="formApprovedAutograph">
                        @csrf @method('PUT')
                        <input type="hidden" name="approved_autograph"
                            value="{{ ucwords($authUser->name) . '.png' }}">
                    </form>
                    @include('partials.confirmation-modal', [
                        'id' => '5',
                        'title' => 'Approval Confirmation',
                        'body' => 'Are you sure want to approve this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formApprovedAutograph\').submit()">Confirm</button>',
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-5"
                        class="btn btn-success">Approve</button>
                </div>
            </div>
        @endif
    </div>
</div>

@push('extraJs')
    <script>
        checkAutographStatus();

        function checkAutographStatus() {
            // Assume you have a variable from the server side indicating the autograph status
            var autographs = {
                autograph_1: '{{ $report->creator_autograph ?? null }}',
                autograph_2: '{{ $report->dept_head_autograph ?? null }}',
                autograph_3: '{{ $report->admin_autograph ?? null }}',
                autograph_4: '{{ $report->pic_autograph ?? null }}',
                autograph_5: '{{ $report->approved_autograph ?? null }}',
                autograph_6: '{{ $report->ppic_autograph ?? null }}',
            };

            // Loop through each autograph status and update the UI accordingly
            for (var i = 1; i <= 6; i++) {
                var autographBox = document.getElementById('autographBox' + i);
                var autographNameBox = document.getElementById('autographUser' + i);

                // Check if autograph status is present in the database
                if (autographs['autograph_' + i]) {
                    var url = '/' + autographs['autograph_' + i];
                    var autographName = autographs['autograph_' + i].split('.')[0];

                    autographBox.style.backgroundImage = "url('" + url + "')";
                    autographNameBox.textContent = autographName;
                    autographNameBox.style.display = 'block';
                }
            }
        }
    </script>
@endpush
