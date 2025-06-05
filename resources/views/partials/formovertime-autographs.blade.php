<section aria-label="autographs" class="container mt-3">

    <div class="row text-center">
        {{-- PREPARATION AUTOGRAPH --}}
        <div class="col my-2">
            <h2>Preparation</h2>
            <div class="autograph-box container" id="autographBox1"></div>
            <div class="container mt-2" id="autographuser1"></div>
        </div>
        @php
            $currentUser = Auth::user();
        @endphp

        @if ($header->Relationdepartement->name === 'MOULDING')

            <div class="col my-2">
                <h2>Supervisor</h2>
                <div class="autograph-box container" id="autographBox2"></div>
                <div class="container mt-2" id="autographuser2"></div>

                @if (Auth::check() &&
                        $currentUser->department->name === $header->Relationdepartement->name &&
                        $currentUser->name === 'fery')
                    <button id="btn2" class="btn btn-primary"
                        onclick="addAutograph(2 , {{ $header->id }})">Accept</button>
                    @if ($header->autograph_2 === null)
                        @include('partials.reject-modal', [
                            'id' => $header->id,
                            'route' => 'overtime.reject',
                        ])
                        <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">Reject</button>
                    @endif
                @endif
            </div>

            @if ($header->is_design === 1)
                <div class="col my-2">
                    <h2>Dept Head Design (FANG)</h2>
                    <div class="autograph-box container" id="autographBox3"></div>
                    <div class="container mt-2" id="autographuser3"></div>

                    @if (Auth::check() &&
                            $currentUser->department->name === $header->Relationdepartement->name &&
                            $currentUser->name === 'fang' &&
                            $header->autograph_2)
                        <button id="btn3" class="btn btn-primary"
                            onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                        @if ($header->autograph_3 === null)
                            @include('partials.reject-modal', [
                                'id' => $header->id,
                                'route' => 'overtime.reject',
                            ])
                            <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">Reject</button>
                        @endif
                    @endif
                </div>
            @else
                <div class="col my-2">
                    <h2>Dept Head (ONG)</h2>
                    <div class="autograph-box container" id="autographBox3"></div>
                    <div class="container mt-2" id="autographuser3"></div>

                    @if (Auth::check() &&
                            $currentUser->department->name === $header->Relationdepartement->name &&
                            $currentUser->name === 'ong' &&
                            $header->autograph_2)
                        <button id="btn3" class="btn btn-primary"
                            onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                        @if ($header->autograph_3 === null)
                            @include('partials.reject-modal', [
                                'id' => $header->id,
                                'route' => 'overtime.reject',
                            ])
                            <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">Reject</button>
                        @endif
                    @endif
                </div>
            @endif

            <div class="col my-2">
                <h2>Director</h2>
                <div class="autograph-box container" id="autographBox4"></div>
                <div class="container mt-2" id="autographuser4"></div>

                @if (Auth::check() && $currentUser->specification->name == 'DIRECTOR' && $header->is_approve === null)
                    <button id="btn4" class="btn btn-primary"
                        onclick="addAutograph(4 , {{ $header->id }})">Accept</button>
                    @if ($header->autograph_4 === null)
                        @include('partials.reject-modal', [
                            'id' => $header->id,
                            'route' => 'overtime.reject',
                        ])
                        <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">Reject</button>
                    @endif
                @endif
            </div>
        @endif


        @if ($header->branch === 'Karawang')
            <div class="col my-2">
                <h2>GM</h2>
                <div class="autograph-box container" id="autographBox3"></div>
                <div class="container mt-2" id="autographuser3"></div>

                @if (Auth::check() && $currentUser->is_gm === 1 && $header->autograph_2)
                    <button id="btn3" class="btn btn-primary"
                        onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                    @if ($header->autograph_3 === null)
                        @include('partials.reject-modal', [
                            'id' => $header->id,
                            'route' => 'overtime.reject',
                        ])
                        <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">Reject</button>
                    @endif
                @endif
            </div>


            <div class="col my-2">
                <h2>Director</h2>
                <div class="autograph-box container" id="autographBox4"></div>
                <div class="container mt-2" id="autographuser4"></div>

                @if (Auth::check() &&
                        $currentUser->specification->name == 'DIRECTOR' &&
                        $header->autograph_3 &&
                        $header->is_approve === null)
                    <button id="btn4" class="btn btn-primary"
                        onclick="addAutograph(4 , {{ $header->id }})">Accept</button>
                    @if ($header->autograph_4 === null)
                        @include('partials.reject-modal', [
                            'id' => $header->id,
                            'route' => 'overtime.reject',
                        ])
                        <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">Reject</button>
                    @endif
                @endif
            </div>
        @else
            @if ($header->Relationdepartement->name !== 'MOULDING')
                @php
                    $showDeptHeadApprovalButton = false;
                    if ($header->Relationdepartement->name === 'SECOND PROCESS') {
                        if (Auth::check() && $currentUser->email === 'wiji@daijo.co.id') {
                            $showDeptHeadApprovalButton = true;
                        }
                    } elseif (Auth::check() && $currentUser->is_head === 1) {
                        if ($currentUser->department->name === $header->Relationdepartement->name) {
                            $showDeptHeadApprovalButton = true;
                        } elseif (
                            $currentUser->department->name === 'LOGISTIC' &&
                            $header->Relationdepartement->name === 'STORE'
                        ) {
                            $showDeptHeadApprovalButton = true;
                        }
                    }
                @endphp

                <div class="col my-2">
                    <h2>Dept Head</h2>
                    <div class="autograph-box container" id="autographBox2"></div>
                    <div class="container mt-2" id="autographuser2"></div>

                    @if ($showDeptHeadApprovalButton)
                        <button id="btn2" class="btn btn-primary"
                            onclick="addAutograph(2 , {{ $header->id }})">Accept</button>
                        @if ($header->autograph_2 === null)
                            @include('partials.reject-modal', [
                                'id' => $header->id,
                                'route' => 'overtime.reject',
                            ])
                            <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">Reject</button>
                        @endif
                    @endif
                </div>

                @if ($header->Relationdepartement->is_office === 0)

                    @if ($header->Relationdepartement->name !== 'QA' && $header->Relationdepartement->name !== 'QC')
                        <div class="col my-2">
                            <h2>GM</h2>
                            <div class="autograph-box container" id="autographBox3"></div>
                            <div class="container mt-2" id="autographuser3"></div>

                            @if (Auth::check() && $currentUser->is_gm === 1 && $header->autograph_2)
                                <button id="btn3" class="btn btn-primary"
                                    onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                                @if ($header->autograph_3 === null)
                                    @include('partials.reject-modal', [
                                        'id' => $header->id,
                                        'route' => 'overtime.reject',
                                    ])
                                    <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">Reject</button>
                                @endif
                            @endif
                        </div>

                        <div class="col my-2">
                            <h2>Director</h2>
                            <div class="autograph-box container" id="autographBox4"></div>
                            <div class="container mt-2" id="autographuser4"></div>

                            @if (Auth::check() &&
                                    $currentUser->specification->name === "DIRECTOR" &&
                                    $header->autograph_3 &&
                                    $header->is_approve === null)
                                <button id="btn4" class="btn btn-primary"
                                    onclick="addAutograph(4 , {{ $header->id }})">Accept</button>
                                @if ($header->autograph_4 === null)
                                    @include('partials.reject-modal', [
                                        'id' => $header->id,
                                        'route' => 'overtime.reject',
                                    ])
                                    <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">Reject</button>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="col my-2">
                            <h2>Director</h2>
                            <div class="autograph-box container" id="autographBox3"></div>
                            <div class="container mt-2" id="autographuser3"></div>

                            @if (Auth::check() &&
                                    $currentUser->specification->name == 'DIRECTOR' &&
                                    $header->autograph_2 &&
                                    $header->is_approve === null)
                                <button id="btn3" class="btn btn-primary"
                                    onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                                @if ($header->autograph_3 === null)
                                    @include('partials.reject-modal', [
                                        'id' => $header->id,
                                        'route' => 'overtime.reject',
                                    ])
                                    <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">Reject</button>
                                @endif
                            @endif
                        </div>
                    @endif
                    
                @else
                    <div class="col my-2">
                        <h2>Director</h2>
                        <div class="autograph-box container" id="autographBox3"></div>
                        <div class="container mt-2" id="autographuser3"></div>

                        @if (Auth::check() &&
                                $currentUser->specification->name == 'DIRECTOR' &&
                                $header->autograph_2 &&
                                $header->is_approve === null)
                            <button id="btn3" class="btn btn-primary"
                                onclick="addAutograph(3 , {{ $header->id }})">Accept</button>
                            @if ($header->autograph_3 === null)
                                @include('partials.reject-modal', [
                                    'id' => $header->id,
                                    'route' => 'overtime.reject',
                                ])
                                <button class="btn btn-danger ms-5" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">Reject</button>
                            @endif
                        @endif
                    </div>
                @endif
            @endif
        @endif
    </div>
</section>
