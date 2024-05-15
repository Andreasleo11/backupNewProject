<div class="row text-center">
    {{-- PREPARATION AUTOGRAPH --}}
    <div class="col my-2">
        <h2>Preparation</h2>
        <div class="autograph-box container" id="autographBox1"></div>
        <div class="container mt-2" id="autographuser1"></div>
    </div>

    @if ($purchaseRequest->is_import === 0)
        {{-- DEPT HEAD 2 AUTOGRAPH --}}
        <div class="col my-2">
            <h2>Dept Head 2</h2>
            <div class="autograph-box container" id="autographBox7"></div>
            <div class="container mt-2 border-1" id="autographuser7"></div>
            {{-- @php
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
        @endphp --}}
            @if (Auth::user()->department->name === $purchaseRequest->from_department &&
                    Auth::user()->is_head == 1 &&
                    Auth::user()->specification->name === 'DESIGN' &&
                    $purchaseRequest->status == 1 &&
                    $purchaseRequest->autograph_7 === null)
                {{-- @if ($count === $countItemHasApprovalStatus) --}}
                <div class="row px-4 d-flex justify-content-center">
                    <div class="col-auto me-2 ">
                        <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                            class="btn btn-danger">Reject</button>
                    </div>
                    <div class="col-auto ">
                        @include('partials.approve-pr-confirmation-modal', [
                            'title' => 'Approve confirmation',
                            'body' =>
                                'Are you sure want to approve <strong>' . $purchaseRequest->doc_num . '</strong>?',
                            'confirmButton' => [
                                'id' => 'btn7',
                                'class' => 'btn btn-success',
                                'onclick' => 'addAutograph(7, ' . $purchaseRequest->id . ', ' . $user->id . ')',
                                'text' => 'Approve',
                            ],
                        ])
                        <button data-bs-toggle="modal" data-bs-target="#approve-pr-confirmation-modal"
                            class="btn btn-success">Approve</button>
                    </div>
                </div>
                {{-- @endif --}}
            @endif
        </div>
    @endif


    {{-- DEPT HEAD AUTOGRAPH --}}
    <div class="col my-2">
        <h2>Dept Head</h2>
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
        @endphp
        @if (Auth::user()->department->name === $purchaseRequest->from_department &&
                Auth::user()->is_head == 1 &&
                $purchaseRequest->status == 1 &&
                $isApproveNotEmpty)
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



    {{-- PURCHASER AUTOGRAPH --}}
    <div class="col my-2">
        <h2>Purchaser</h2>
        <div class="autograph-box container" id="autographBox5"></div>
        <div class="container mt-2" id="autographuser5"></div>
        @if (Auth::user()->specification->name == 'PURCHASER' && $purchaseRequest->status === 6)
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

    {{-- GM AUTOGRAPH --}}
    @if ($purchaseRequest->from_department !== 'MOULDING')
        <div
            class="col {{ $purchaseRequest->type === 'factory' || ($purchaseRequest->to_department === 'Computer' && $purchaseRequest->type === 'factory') ? '' : 'd-none' }}">
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
            @if (Auth::user()->is_gm === 1 && $purchaseRequest->status === 7 && $isApproveNotEmpty)
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

    {{-- VERIFICATOR AUTOGRAPH --}}
    <div
        class="col {{ ($purchaseRequest->to_department === 'Computer' && $purchaseRequest->type === 'factory') || $purchaseRequest->type === 'office' ? '' : 'd-none' }}">
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
        @if (Auth::user()->department->name == 'HRD' &&
                Auth::user()->is_head == 1 &&
                $purchaseRequest->status == 2 &&
                $isApproveNotEmpty)
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
        @if (Auth::user()->department->name == 'DIRECTOR' && $purchaseRequest->status == 3 && $isApproveNotEmpty)
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
