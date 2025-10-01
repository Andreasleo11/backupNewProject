<div class="row text-center">
    {{-- PREPARATION AUTOGRAPH --}}
    <div class="col my-2">
        <h2>Maker</h2>
        <div class="autograph-box container" id="autographBox1"></div>
        <div class="container mt-2" id="autographuser1"></div>
        @if (auth()->user()->email === $purchaseRequest->createdBy->email && !$purchaseRequest->autograph_1)
            <div class="col-auto">
                @include('partials.approve-pr-confirmation-modal', [
                    'title' => 'Sign confirmation',
                    'body' => 'Are you sure want to sign <strong>' . $purchaseRequest->doc_num . '</strong>?',
                    'confirmButton' => [
                        'id' => 'btn1',
                        'class' => 'btn btn-success',
                        'onclick' => 'addAutograph(1, ' . $purchaseRequest->id . ', ' . $user->id . ')',
                        'text' => 'Confirm',
                    ],
                ])
                <button data-bs-toggle="modal" data-bs-target="#approve-pr-confirmation-modal"
                    class="btn btn-primary">Sign</button>
            </div>
        @endif
    </div>

    @php
        $user = Auth::user();
    @endphp

    {{-- HEAD DESIGN AUTOGRAPH --}}
    @if (
        $purchaseRequest->from_department === 'MOULDING' &&
            !$purchaseRequest->is_import &&
            $purchaseRequest->to_department === 'Maintenance')
        <div class="col my-2">
            <h2>Dept Head</h2>
            <div class="autograph-box container" id="autographBox7"></div>
            <div class="container mt-2 border-1" id="autographuser7"></div>
            @php
                $detailObj = null;
                $count = 0;
                $isApproveNotEmpty = null;
                $countItemHasApprovalStatus = 0;
                $thereIsApprovedItem = false;
                foreach ($purchaseRequest->itemDetail as $detail) {
                    $count += 1;
                    if ($detail->is_approve_by_head !== null) {
                        $isApproveNotEmpty = true;
                        $detailObj = $detail;
                        $countItemHasApprovalStatus += 1;
                    }
                    if ($detail->is_approve_by_head === 1) {
                        $thereIsApprovedItem = true;
                    }
                }

                $showDeptHeadDesignApprovalButtons =
                    !$purchaseRequest->autograph_7 &&
                    $user->department->name === $purchaseRequest->from_department &&
                    $user->is_head == 1 &&
                    $user->specification->name === 'DESIGN' &&
                    $purchaseRequest->is_cancel === 0;

            @endphp
            @if ($showDeptHeadDesignApprovalButtons)
                @if ($count === $countItemHasApprovalStatus)
                    <div class="row px-4 d-flex justify-content-center">
                        <div
                            class="col-auto me-2 {{ ($count == 1 && $detailObj->is_approve_by_head) || $thereIsApprovedItem ? 'd-none' : '' }}">
                            <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                class="btn btn-danger">Reject</button>
                        </div>
                        <div
                            class="col-auto {{ ($count == 1 && !$detailObj->is_approve_by_head) || !$thereIsApprovedItem ? 'd-none' : '' }}">
                            @include('partials.approve-pr-confirmation-modal', [
                                'title' => 'Approve confirmation',
                                'body' =>
                                    'Are you sure want to approve <strong>' .
                                    $purchaseRequest->doc_num .
                                    '</strong>?',
                                'confirmButton' => [
                                    'id' => 'btn7',
                                    'class' => 'btn btn-success',
                                    'onclick' =>
                                        'addAutograph(7, ' . $purchaseRequest->id . ', ' . $user->id . ')',
                                    'text' => 'Approve',
                                ],
                            ])
                            <button data-bs-toggle="modal" data-bs-target="#approve-pr-confirmation-modal"
                                class="btn btn-success">Approve</button>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif

    {{-- DEPT HEAD AUTOGRAPH --}}
    <div
        class="col my-2 {{ $purchaseRequest->from_department === 'PERSONALIA' || $purchaseRequest->from_department === 'PLASTIC INJECTION' || ($purchaseRequest->from_department === 'MAINTENANCE MACHINE' && $purchaseRequest->branch === 'KARAWANG') ? 'd-none' : '' }}">
        <h2>
            {{ $purchaseRequest->from_department === 'MOULDING' && $purchaseRequest->to_department === 'Maintenance' ? 'Head Design' : 'Dept Head' }}
        </h2>
        <div class="autograph-box container" id="autographBox2"></div>
        <div class="container mt-2 border-1" id="autographuser2"></div>
        @php
            $detailObj = null;
            $count = 0;
            $isApproveNotEmpty = null;
            $countItemHasApprovalStatus = 0;
            $thereIsApprovedItem = false;
            foreach ($purchaseRequest->itemDetail as $detail) {
                $count += 1;
                if ($detail->is_approve_by_head !== null) {
                    $isApproveNotEmpty = true;
                    $detailObj = $detail;
                    $countItemHasApprovalStatus += 1;
                }
                if ($detail->is_approve_by_head === 1) {
                    $thereIsApprovedItem = true;
                }
            }

            $showDeptHeadApprovalButtons =
                !$purchaseRequest->autograph_2 &&
                $user->department->name === $purchaseRequest->from_department &&
                $user->is_head == 1 &&
                $purchaseRequest->status == 1 &&
                $isApproveNotEmpty &&
                $purchaseRequest->is_cancel === 0;

            // if ($purchaseRequest->from_department === 'MOULDING' && $user->specification->name === 'DESIGN') {
            //     $showDeptHeadApprovalButtons = false;
            // }

            if ($user->is_head == 1 && $purchaseRequest->status == 1 && $isApproveNotEmpty) {
                if (
                    $user->department->name === 'PERSONALIA' &&
                    $user->is_head == 1 &&
                    $purchaseRequest->from_department === 'PERSONALIA'
                ) {
                    $showDeptHeadApprovalButtons = true;
                } elseif ($user->department->name === 'LOGISTIC' && $purchaseRequest->from_department === 'STORE') {
                    $showDeptHeadApprovalButtons = true;
                } elseif ($purchaseRequest->from_department === 'MOULDING') {
                    if ($purchaseRequest->to_department === 'Maintenance') {
                        if ($user->specification->name === 'DESIGN') {
                            $showDeptHeadApprovalButtons = false;
                        }
                    } else {
                        if (
                            ($purchaseRequest->is_import !== 0 && $user->specification->name !== 'DESIGN') ||
                            ($purchaseRequest->is_import === 0 && $user->specification->name === 'DESIGN')
                        ) {
                            $showDeptHeadApprovalButtons = true;
                        }
                    }
                }
            }
        @endphp
        @if ($showDeptHeadApprovalButtons)
            @if ($count === $countItemHasApprovalStatus)
                <div class="row px-4 d-flex justify-content-center">
                    <div
                        class="col-auto me-2 {{ ($count == 1 && $detailObj->is_approve_by_head) || $thereIsApprovedItem ? 'd-none' : '' }}">
                        <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                            class="btn btn-danger">Reject</button>
                    </div>
                    <div
                        class="col-auto {{ ($count == 1 && !$detailObj->is_approve_by_head) || !$thereIsApprovedItem ? 'd-none' : '' }}">
                        @include('partials.approve-pr-confirmation-modal', [
                            'title' => 'Approve confirmation',
                            'body' =>
                                'Are you sure want to approve <strong>' . $purchaseRequest->doc_num . '</strong>?',
                            'confirmButton' => [
                                'id' => 'btn2',
                                'class' => 'btn btn-success',
                                'onclick' => 'addAutograph(2, ' . $purchaseRequest->id . ', ' . $user->id . ')',
                                'text' => 'Approve',
                            ],
                        ])
                        <button data-bs-toggle="modal" data-bs-target="#approve-pr-confirmation-modal"
                            class="btn btn-success">Approve</button>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- GM AUTOGRAPH --}}
    @if (
        $purchaseRequest->from_department !== 'MOULDING' &&
            $purchaseRequest->from_department !== 'QA' &&
            $purchaseRequest->from_department !== 'QC' &&
            $purchaseRequest->type !== 'office')
        <div class="my-2 col">
            <h2>GM</h2>
            <div class="autograph-box container" id="autographBox6"></div>
            <div class="container mt-2 border-1" id="autographuser6"></div>
            @php
                $detailObj = null;
                $count = 0;
                $isApproveNotEmpty = null;
                $countItemHasApprovalStatus = 0;
                $thereIsApprovedItem = false;
                foreach ($purchaseRequest->itemDetail as $detail) {
                    $count += 1;
                    if ($detail->is_approve_by_head !== null) {
                        $isApproveNotEmpty = true;
                        $detailObj = $detail;
                        $countItemHasApprovalStatus += 1;
                    }
                    if ($detail->is_approve_by_head === 1) {
                        $thereIsApprovedItem = true;
                    }
                }
            @endphp
            @if ($user->is_gm === 1 && $purchaseRequest->status === 7 && $isApproveNotEmpty && $purchaseRequest->is_cancel === 0)
                @if ($count === $countItemHasApprovalStatus)
                    <div class="row px-4 d-flex justify-content-center">
                        <div class="col-auto me-2">
                            <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                class="btn btn-danger">Reject</button>
                        </div>
                        <div
                            class="col-auto {{ ($count == 1 && !$detailObj->is_approve_by_head) || !$thereIsApprovedItem ? 'd-none' : '' }}">
                            @include('partials.approve-pr-confirmation-modal', [
                                'title' => 'Approve confirmation',
                                'body' =>
                                    'Are you sure want to approve <strong>' .
                                    $purchaseRequest->doc_num .
                                    '</strong>?',
                                'confirmButton' => [
                                    'id' => 'btn6',
                                    'class' => 'btn btn-success',
                                    'onclick' =>
                                        'addAutograph(6, ' . $purchaseRequest->id . ', ' . $user->id . ')',
                                    'text' => 'Approve',
                                ],
                            ])
                            <button data-bs-toggle="modal" data-bs-target="#approve-pr-confirmation-modal"
                                class="btn btn-success">Approve</button>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif

    {{-- PURCHASER AUTOGRAPH --}}
    <div class="col my-2">
        <h2>Purchaser</h2>
        <div class="autograph-box container" id="autographBox5"></div>
        <div class="container mt-2" id="autographuser5"></div>
        @php
            $showApprovalButtons = false;
            if ($purchaseRequest->to_department === 'Computer') {
                $user->email === 'vicky@daijo.co.id' ? ($showApprovalButtons = true) : ($showApprovalButtons = false);
            } elseif ($purchaseRequest->to_department === 'Purchasing') {
                $user->department->name === 'PURCHASING'
                    ? ($showApprovalButtons = true)
                    : ($showApprovalButtons = false);
            } else {
                $user->email === 'nur@daijo.co.id' || $user->email === 'ani_apriani@daijo.co.id'
                    ? ($showApprovalButtons = true)
                    : ($showApprovalButtons = false);
            }
            $showApprovalButtons = $showApprovalButtons && !$purchaseRequest->autograph_5;
        @endphp
        @if ($showApprovalButtons && $purchaseRequest->status === 6 && $purchaseRequest->is_cancel === 0)
            <div class="row px-4 d-flex justify-content-center">
                <div class="col-auto me-2">
                    <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                        class="btn btn-danger">Reject</button>
                </div>
                <div class="col-auto">
                    @include('partials.approve-pr-confirmation-modal', [
                        'title' => 'Approve confirmation',
                        'body' =>
                            'Are you sure want to approve <strong>' . $purchaseRequest->doc_num . '</strong>?',
                        'confirmButton' => [
                            'id' => 'btn5',
                            'class' => 'btn btn-success',
                            'onclick' => 'addAutograph(5, ' . $purchaseRequest->id . ', ' . $user->id . ')',
                            'text' => 'Approve',
                        ],
                    ])
                    <button data-bs-toggle="modal" data-bs-target="#approve-pr-confirmation-modal"
                        class="btn btn-success">Approve</button>
                </div>
            </div>
        @endif
    </div>

    {{-- VERIFICATOR AUTOGRAPH --}}
    <div
        class="col my-2 {{ $purchaseRequest->to_department === 'Computer' ||
        $purchaseRequest->to_department === 'Personnel' ||
        ($purchaseRequest->from_department === 'COMPUTER' && $purchaseRequest->to_department === 'Maintenance') ||
        ($purchaseRequest->from_department === 'PERSONALIA' && $purchaseRequest->to_department === 'Maintenance')
            ? ''
            : 'd-none' }}">
        <h2>Verificator</h2>
        <div class="autograph-box container" id="autographBox3"></div>
        <div class="container mt-2 border-1" id="autographuser3"></div>
        @php
            $detailObj = null;
            $count = 0;
            $isApproveNotEmpty = null;
            $countItemHasApprovalStatus = 0;
            $thereIsApprovedItem = false;
            foreach ($purchaseRequest->itemDetail as $detail) {
                if ($detail->is_approve_by_head != 0) {
                    $count += 1;
                }
                if ($detail->is_approve_by_verificator !== null) {
                    $isApproveNotEmpty = true;
                    $detailObj = $detail;
                    $countItemHasApprovalStatus += 1;
                }
                if ($detail->is_approve_by_verificator === 1) {
                    $thereIsApprovedItem = true;
                }
            }
        @endphp
        @if (
            $user->department->name == 'PERSONALIA' &&
                $user->is_head == 1 &&
                $purchaseRequest->status == 2 &&
                $isApproveNotEmpty &&
                $purchaseRequest->is_cancel === 0)
            @if ($count === $countItemHasApprovalStatus)
                <div class="row px-4 d-flex justify-content-center">
                    <div
                        class="col-auto me-2 {{ ($count == 1 && $detailObj->is_approve_by_verificator) || $thereIsApprovedItem ? 'd-none' : '' }}">
                        <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                            class="btn btn-danger">Reject</button>
                    </div>
                    <div
                        class="col-auto {{ ($count == 1 && !$detailObj->is_approve_by_verificator) || !$thereIsApprovedItem ? 'd-none' : '' }}">
                        @include('partials.approve-pr-confirmation-modal', [
                            'title' => 'Approve confirmation',
                            'body' =>
                                'Are you sure want to approve <strong>' . $purchaseRequest->doc_num . '</strong>?',
                            'confirmButton' => [
                                'id' => 'btn3',
                                'class' => 'btn btn-success',
                                'onclick' => 'addAutograph(3, ' . $purchaseRequest->id . ', ' . $user->id . ')',
                                'text' => 'Approve',
                            ],
                        ])
                        <button data-bs-toggle="modal" data-bs-target="#approve-pr-confirmation-modal"
                            class="btn btn-success">Approve</button>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- DIRECTOR AUTOGRAPH --}}
    <div class="col my-2">
        <h2>Director</h2>
        <div class="autograph-box container" id="autographBox4"></div>
        <div class="container mt-2 border-1" id="autographuser4"></div>
        @php
            $detailObj = null;
            $count = 0;
            $isApproveNotEmpty = null;
            $countItemHasApprovalStatus = 0;
            $thereIsApprovedItem = false;
            foreach ($purchaseRequest->itemDetail as $detail) {
                if ($detail->is_approve_by_verificator != 0) {
                    $count += 1;
                } elseif ($detail->is_approve_by_gm) {
                    $count += 1;
                }
                if ($detail->is_approve !== null) {
                    $isApproveNotEmpty = true;
                    $detailObj = $detail;
                    $countItemHasApprovalStatus += 1;
                }
                if ($detail->is_approve === 1) {
                    $thereIsApprovedItem = true;
                }
            }
        @endphp
        @if (
            $user->specification->name == 'DIRECTOR' &&
                $purchaseRequest->status == 3 &&
                $isApproveNotEmpty &&
                $purchaseRequest->is_cancel === 0)
            @if ($count === $countItemHasApprovalStatus)
                <div class="row px-4 d-flex justify-content-center">
                    <div
                        class="col-auto me-2 {{ ($count === 1 && $detailObj->is_approve) || $thereIsApprovedItem ? 'd-none' : '' }}">
                        <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                            class="btn btn-danger">Reject</button>
                    </div>
                    <div
                        class="col-auto {{ ($count === 1 && !$detailObj->is_approve) || !$thereIsApprovedItem ? 'd-none' : '' }}">
                        @include('partials.approve-pr-confirmation-modal', [
                            'title' => 'Approve confirmation',
                            'body' =>
                                'Are you sure want to approve <strong>' . $purchaseRequest->doc_num . '</strong>?',
                            'confirmButton' => [
                                'id' => 'btn4',
                                'class' => 'btn btn-success',
                                'onclick' => 'addAutograph(4, ' . $purchaseRequest->id . ', ' . $user->id . ')',
                                'text' => 'Approve',
                            ],
                        ])
                        <button data-bs-toggle="modal" data-bs-target="#approve-pr-confirmation-modal"
                            class="btn btn-success">Approve</button>

                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
