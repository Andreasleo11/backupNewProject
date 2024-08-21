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
    {{-- REQUESTED BY AUTOGRAPH --}}
    <div class="col my-3">
        <h3>Requested By</h3>
        <div class="autograph-box container" id="autographBox1"></div>
        <div class="container mt-2" id="autographUser1"></div>
    </div>

    {{-- PREPARED BY AUTOGRAPH --}}
    <div class="col my-3">
        <h3>Prepared By</h3>
        <div class="autograph-box container" id="autographBox2"></div>
        <div class="container mt-2 border-1" id="autographUser2"></div>
        @php
            $showPreparedByAutograph = false;
            if (!$report->prepared_by_autograph && $report->status_laporan === 0) {
                $showPreparedByAutograph = true;
            }
        @endphp
        @if ($showPreparedByAutograph)
            <div class="row px-4 d-flex justify-content-center g-2 gx-4">
                <div class="col-auto">
                    <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                        class="btn btn-danger">Reject</button>
                </div>
                <div class="col-auto">
                    <form action="{{ route('spk.save.autograph', $report->id) }}" method="POST"
                        id="formPreparedByAutograph">
                        @csrf @method('PUT')
                        <input type="hidden" name="prepared_by_autograph"
                            value="{{ ucwords($authUser->name) . '.png' }}">
                    </form>
                    @include('partials.confirmation-modal', [
                        'id' => '2',
                        'title' => 'Approval Confirmation',
                        'body' => 'Are you sure want to approve this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formPreparedByAutograph\').submit()">Confirm</button>',
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-2"
                        class="btn btn-success">Approve</button>
                </div>
            </div>
        @endif

    </div>

    {{-- PIC AUTOGRAPH --}}
    <div class="my-3 col">
        <h3>PIC</h3>
        <div class="autograph-box container" id="autographBox3"></div>
        <div class="container mt-2 border-1" id="autographUser3"></div>
        @php
            $showPicAutograph = false;
            if (
                !$report->pic_autograph &&
                $report->pic === $authUser->name &&
                $report->tindakan &&
                $report->tanggal_estimasi &&
                $report->tanggal_mulai
            ) {
                $showPicAutograph = true;
            }
        @endphp
        @if ($showPicAutograph)
            <div class="row px-4 d-flex justify-content-center g-2 gx-4">
                <div class="col-auto">
                    <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                        class="btn btn-danger">Reject</button>
                </div>
                <div class="col-auto">
                    <form action="{{ route('spk.save.autograph', $report->id) }}" method="POST" id="formPicAutograph">
                        @csrf @method('PUT')
                        <input type="hidden" name="pic_autograph" value="{{ ucwords($authUser->name) . '.png' }}">
                    </form>
                    @include('partials.confirmation-modal', [
                        'id' => '3',
                        'title' => 'Approval Confirmation',
                        'body' => 'Are you sure want to approve this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formPicAutograph\').submit()">Confirm</button>',
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-3"
                        class="btn btn-success">Approve</button>
                </div>
            </div>
        @endif
    </div>
    {{-- FINISHED BY AUTOGRAPH --}}
    <div class="my-3 col">
        <h3>Finished By</h3>
        <div class="autograph-box container" id="autographBox4"></div>
        <div class="container mt-2 border-1" id="autographUser4"></div>
        @php
            $showFinishedByAutograph = false;
            if (!$report->finished_by_autograph && $report->tanggal_selesai) {
                $showFinishedByAutograph = true;
            }
        @endphp
        @if ($showFinishedByAutograph)
            <div class="row px-4 d-flex justify-content-center g-2 gx-4">
                <div class="col-auto">
                    <button data-bs-toggle="modal" data-bs-target="#reject-confirmation"
                        class="btn btn-danger">Reject</button>
                </div>
                <div class="col-auto">
                    <form action="{{ route('spk.save.autograph', $report->id) }}" method="POST"
                        id="formFinishedByAutograph">
                        @csrf @method('PUT')
                        <input type="hidden" name="finished_by_autograph"
                            value="{{ ucwords($authUser->name) . '.png' }}">
                    </form>
                    @include('partials.confirmation-modal', [
                        'id' => '4',
                        'title' => 'Approval Confirmation',
                        'body' => 'Are you sure want to approve this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formFinishedByAutograph\').submit()">Confirm</button>',
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#confirmation-modal-4"
                        class="btn btn-success">Approve</button>
                </div>
            </div>
        @endif
    </div>
    {{-- DEPT HEAD AUTOGRAPH AUTOGRAPH --}}
    <div class="my-3 col">
        <h3>Dept Head </h3>
        <div class="autograph-box container" id="autographBox5"></div>
        <div class="container mt-2 border-1" id="autographUser5"></div>
        @php
            $showDeptHead = false;
            if (
                !$report->dept_head_autograph &&
                $authUser->is_head &&
                $report->to_department === $authUser->department->name
            ) {
                $showDeptHead = true;
            }
        @endphp
        @if ($showDeptHead)
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
                        'id' => '5',
                        'title' => 'Approval Confirmation',
                        'body' => 'Are you sure want to approve this report?',
                        'submitButton' =>
                            '<button class="btn btn-success" onclick="document.getElementById(\'formDeptHeadAutograph\').submit()">Confirm</button>',
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
                autograph_1: '{{ $report->requested_by_autograph ?? null }}',
                autograph_2: '{{ $report->prepared_by_autograph ?? null }}',
                autograph_3: '{{ $report->pic_autograph ?? null }}',
                autograph_4: '{{ $report->finished_by_autograph ?? null }}',
                autograph_5: '{{ $report->dept_head_autograph ?? null }}',
            };

            console.log(autographs);

            // Loop through each autograph status and update the UI accordingly
            for (var i = 1; i <= 5; i++) {
                var autographBox = document.getElementById('autographBox' + i);
                var autographNameBox = document.getElementById('autographUser' + i);
                console.log(autographBox)
                console.log(autographNameBox)

                // Check if autograph status is present in the database
                if (autographs['autograph_' + i]) {
                    if (i === 1) {
                        var url = '/storage/' + autographs['autograph_' + i];
                        var autographName = '{{ $report->requested_by }}';
                    } else {
                        var url = '/' + autographs['autograph_' + i];
                        var autographName = autographs['autograph_' + i].split('.')[0];
                    }
                    autographBox.style.backgroundImage = "url('" + url + "')";
                    autographNameBox.textContent = autographName;
                    autographNameBox.style.display = 'block';
                }
            }
        }
    </script>
@endpush
