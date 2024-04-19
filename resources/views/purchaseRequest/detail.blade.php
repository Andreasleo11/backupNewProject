@extends('layouts.app')
@push('extraCss')
    <style>
        .autograph-box {
            width: 200px;
            /* Adjust the width as needed */
            height: 100px;
            /* Adjust the height as needed */
            background-size: contain;
            background-repeat: no-repeat;
            border: 1px solid #ccc;
            /* Add border for better visibility */
        }
    </style>

    <script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>

    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css"
        rel="stylesheet" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js">
    </script>
@endpush

@section('content')
    <section class="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a
                        href="{{ auth()->user()->department->name === 'DIRECTOR' ? route('director.pr.index') : route('purchaserequest.home') }}">Purchase
                        Requests</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </section>

    <div class="text-end container mb-5">
        @if (Auth::user()->id == $userCreatedBy->id)
            <button class="btn btn-outline-primary" data-bs-target="#upload-files-modal" data-bs-toggle="modal">
                <i class='bx bx-upload'></i> Upload
            </button>

            @include('partials.upload-files-modal', ['doc_id' => $purchaseRequest->doc_num])
        @endif
    </div>

    <div class="mt-4">
        @include('partials.alert-success-error')
    </div>

    <section aria-label="autographs" class="container">

        @include('partials.reject-pr-confirmation', $purchaseRequest)

        @php
            $user = Auth::user();
        @endphp

        <div class="row text-center">
            <div class="col">
                <h2>Preparation</h2>
                <div class="autograph-box container" id="autographBox1"></div>
                <div class="container mt-2" id="autographuser1"></div>
            </div>

            <div class="col">
                <h2>Purchaser</h2>
                <div class="autograph-box container" id="autographBox5"></div>
                <div class="container mt-2" id="autographuser5"></div>
                @if (Auth::check() && (Auth::user()->specification->name == 'PURCHASER' && $purchaseRequest->status == 1))
                    <div class="row px-4 d-flex justify-content-center">
                        <div class="col-auto me-2">
                            <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                class="btn btn-danger">Reject</button>
                        </div>
                        <div class="col-auto">
                            <button id="btn5" class="btn btn-success"
                                onclick="addAutograph(5, {{ $purchaseRequest->id }})">Approve</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col">
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
                @if (Auth::check() &&
                        Auth::user()->department == $userCreatedBy->department &&
                        Auth::user()->is_head == 1 &&
                        $purchaseRequest->status == 6 &&
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
                                <button id="btn2" class="btn btn-success"
                                    onclick="addAutograph(2, {{ $purchaseRequest->id }})">Approve</button>
                            </div>
                        </div>
                    @endif
                @endif
                {{-- @dd($count) --}}
            </div>

            <div class="col">
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
                @if (Auth::check() &&
                        Auth::user()->department->name == 'HRD' &&
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
                                <button id="btn3" class="btn btn-success"
                                    onclick="addAutograph(3, {{ $purchaseRequest->id }})">Approve</button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="col">
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
                @if (Auth::check() &&
                        Auth::user()->department->name == 'DIRECTOR' &&
                        $purchaseRequest->status == 3 &&
                        $isApproveNotEmpty)
                    @if ($count === $countItemHasApprovalStatus)
                        <div class="row px-4 d-flex justify-content-center">
                            <div
                                class="col-auto me-2 {{ ($count === 1 && $detailObj->is_approve) || $thereIsApprovedItem ? 'd-none' : '' }}">
                                <button data-bs-toggle="modal" data-bs-target="#reject-pr-confirmation"
                                    class="btn btn-danger">Reject</button>
                            </div>
                            <div
                                class="col-auto {{ ($count === 1 && !$detailObj->is_approve) || !$thereIsApprovedItem ? 'd-none' : '' }}">
                                <button id="btn4" class="btn btn-success"
                                    onclick="addAutograph(4, {{ $purchaseRequest->id }}, {{ $user->id }})">Approve</button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </section>

    <section aria-label="pr-header-body" class="container mt-5">
        <div class="card">
            <div
                class="form-check form-switch mt-2 me-2 text-end {{ ($purchaseRequest->status == 1 && $user->specification->name == 'PURCHASER') ||
                ($purchaseRequest->status == 6 && $user->is_head == 1) ||
                ($purchaseRequest->status == 2 && $user->department->name == 'HRD')
                    ? ''
                    : 'd-none' }}">
                <input type="checkbox" class="btn-check" id="toggle-edit" autocomplete="off">
                <label class="btn btn-outline-secondary" id="edit-mode-label" for="toggle-edit">
                    Edit Mode Off</label>
            </div>
            <div class="mt-4 text-center">
                <span class="h1 fw-semibold">Purchase Requisition</span> <br>
                <div class="fs-6 mt-2">
                    <span class="fs-6 text-secondary">Created By : </span> {{ $userCreatedBy->name }} <br>
                    <span class="fs-6 text-secondary">From Department : </span> {{ $userCreatedBy->department->name }}
                </div>
            </div>
            <hr>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderlesss">
                        <tbody>
                            <tr>
                                <th>Date PR</th>
                                <td>: {{ $purchaseRequest->date_pr }}</td>
                                <th>Date Required</th>
                                <td>:
                                    <a href="" class="header-editable" data-type="text" data-name="date_required"
                                        data-pk="{{ $purchaseRequest->id }}">{{ $purchaseRequest->date_required }}</a><span
                                        id="span-date-required">{{ $purchaseRequest->date_required }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>To Department</th>
                                <td>: {{ $purchaseRequest->to_department }}</td>
                                <th>PR No</th>
                                <td>: {{ $purchaseRequest->pr_no }}</td>
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td>: <a href="" class="header-editable" data-type="text" data-name="supplier"
                                        data-pk="{{ $purchaseRequest->id }}">{{ $purchaseRequest->supplier }}</a> <span
                                        id="span-supplier"> {{ $purchaseRequest->supplier }}</span></td>
                                <th>Remark</th>
                                <td style="width: 40%">: <a href="" class="header-editable" data-type="textarea"
                                        data-name="remark"
                                        data-pk="{{ $purchaseRequest->id }}">{{ $purchaseRequest->remark }}</a><span
                                        id="span-remark">{{ $purchaseRequest->remark }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover text-center table-striped mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">No</th>
                                <th rowspan="2" class="align-middle">Item Name</th>
                                <th rowspan="2" class="align-middle">Quantity</th>
                                <th rowspan="2" class="align-middle">Purpose</th>
                                <th colspan="2" class="align-middle">Unit Price</th>
                                <th rowspan="2" class="align-middle">Subtotal</th>
                                @if (
                                    $user->department->name === 'DIRECTOR' ||
                                        $user->specification->name == 'VERIFICATOR' ||
                                        ($user->department == $userCreatedBy->department && $user->is_head == 1))
                                    <th rowspan="2" class="align-middle">Is Approve</th>
                                @endif
                            </tr>
                            <tr>
                                <th>Before</th>
                                <th>Current</th>
                            </tr>
                        </thead>
                        @php
                            $totalall = 0; // Initialize the variable
                        @endphp
                        <tbody>
                            @forelse($purchaseRequest->itemDetail as $detail)
                                @if ($user->department->name == 'DIRECTOR')
                                    @if ($detail->is_approve || ($detail->is_approve_by_verificator && $detail->is_approve_by_head))
                                        <tr
                                            class="{{ $detail->is_approve !== null ? ($detail->is_approve ? 'table-success' : 'table-danger') : '' }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $detail->item_name }}</td>
                                            <td>{{ $detail->quantity }}</td>
                                            <td>{{ $detail->purpose }}</td>
                                            <td> @currency($detail->master->price) </td>
                                            <td> @currency($detail->price) </td>
                                            <td> @currency($detail->quantity * $detail->price) </td>
                                            @php
                                                if ($purchaseRequest->status == 6 || $purchaseRequest->status == 2) {
                                                    if ($detail->is_approve_by_head !== 0) {
                                                        $totalall += $detail->quantity * $detail->price; // Update the total
                                                    }
                                                } elseif (
                                                    $purchaseRequest->status == 2 ||
                                                    $purchaseRequest->status == 3
                                                ) {
                                                    if ($detail->is_approve_by_verificator !== 0) {
                                                        $totalall += $detail->quantity * $detail->price; // Update the total
                                                    }
                                                } elseif (
                                                    $purchaseRequest->status == 3 ||
                                                    $purchaseRequest->status == 4
                                                ) {
                                                    if ($detail->is_approve !== 0) {
                                                        $totalall += $detail->quantity * $detail->price; // Update the total
                                                    }
                                                }
                                            @endphp

                                            @if ($user->department == $userCreatedBy->department && $user->is_head == 1)
                                                <td>
                                                    @if ($detail->is_approve_by_head === null)
                                                        <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'head']) }}"
                                                            class="btn btn-danger">Reject</a>
                                                        <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'head']) }}"
                                                            class="btn btn-success">Approve</a>
                                                    @else
                                                        {{ $detail->is_approve_by_head == 1 ? 'Yes' : 'No' }}
                                                    @endif
                                                </td>
                                            @elseif ($user->specification->name === 'VERIFICATOR')
                                                <td>
                                                    @if ($detail->is_approve_by_verificator === null)
                                                        <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'verificator']) }}"
                                                            class="btn btn-danger">Reject</a>
                                                        <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'verificator']) }}"
                                                            class="btn btn-success">Approve</a>
                                                    @else
                                                        {{ $detail->is_approve_by_verificator == 1 ? 'Yes' : 'No' }}
                                                    @endif
                                                </td>
                                            @elseif ($user->department->name === 'DIRECTOR')
                                                <td>
                                                    @if ($detail->is_approve === null)
                                                        <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'director']) }}"
                                                            class="btn btn-danger">Reject</a>
                                                        <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'director']) }}"
                                                            class="btn btn-success">Approve</a>
                                                    @else
                                                        {{ $detail->is_approve == 1 ? 'Yes' : 'No' }}
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                @elseif ($user->specification->name == 'VERIFICATOR')
                                    @if ($detail->is_approve_by_head || $detail->is_approve_by_verificator)
                                        <tr
                                            class="{{ $detail->is_approve_by_verificator !== null ? ($detail->is_approve_by_verificator ? 'table-success' : 'table-danger') : '' }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td><a href="" class="editable" data-type="text"
                                                    data-name="item_name"
                                                    data-pk="{{ $detail->id }}">{{ $detail->item_name }}</a><span
                                                    id="span-item-name-{{ $detail->id }}">{{ $detail->item_name }}</span>
                                            </td>

                                            <td><a href="" class="editable" data-type="text" data-name="quantity"
                                                    data-pk="{{ $detail->id }}">{{ $detail->quantity }}</a><span
                                                    id="span-quantity-{{ $detail->id }}">{{ $detail->quantity }}</span>
                                            </td>
                                            <td><a href="" class="editable" data-type="text" data-name="purpose"
                                                    data-pk="{{ $detail->id }}">{{ $detail->purpose }}</a><span
                                                    id="span-purpose-{{ $detail->id }}">{{ $detail->purpose }}</span>
                                            </td>
                                            <td> @currency($detail->master->price ?? 0) </td>
                                            <td> <a href="" class="editable" data-type="text" data-name="price"
                                                    data-pk="{{ $detail->id }}">@currency($detail->price) </a><span
                                                    id="span-price-{{ $detail->id }}">@currency($detail->price)</span>
                                            </td>
                                            @php
                                                if ($purchaseRequest->status == 6 || $purchaseRequest->status == 2) {
                                                    if ($detail->is_approve_by_head !== 0) {
                                                        $totalall += $detail->quantity * $detail->price; // Update the total
                                                    }
                                                } elseif (
                                                    $purchaseRequest->status == 2 ||
                                                    $purchaseRequest->status == 3
                                                ) {
                                                    if ($detail->is_approve_by_verificator !== 0) {
                                                        $totalall += $detail->quantity * $detail->price; // Update the total
                                                    }
                                                } elseif (
                                                    $purchaseRequest->status == 3 ||
                                                    $purchaseRequest->status == 4
                                                ) {
                                                    if ($detail->is_approve !== 0) {
                                                        $totalall += $detail->quantity * $detail->price; // Update the total
                                                    }
                                                }
                                            @endphp

                                            @if ($user->department == $userCreatedBy->department && $user->is_head == 1)
                                                <td>
                                                    @if ($detail->is_approve_by_head === null)
                                                        <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'head']) }}"
                                                            class="btn btn-danger">Reject</a>
                                                        <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'head']) }}"
                                                            class="btn btn-success">Approve</a>
                                                    @else
                                                        {{ $detail->is_approve_by_head == 1 ? 'Yes' : 'No' }}
                                                    @endif
                                                </td>
                                            @elseif ($user->specification->name == 'VERIFICATOR')
                                                <td>
                                                    @if ($detail->is_approve_by_verificator === null)
                                                        <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'verificator']) }}"
                                                            class="btn btn-danger">Reject</a>
                                                        <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'verificator']) }}"
                                                            class="btn btn-success">Approve</a>
                                                    @else
                                                        {{ $detail->is_approve_by_verificator == 1 ? 'Yes' : 'No' }}
                                                    @endif
                                                </td>
                                            @elseif ($user->department->name === 'DIRECTOR')
                                                <td>
                                                    @if ($detail->is_approve === null)
                                                        <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'director']) }}"
                                                            class="btn btn-danger">Reject</a>
                                                        <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'director']) }}"
                                                            class="btn btn-success">Approve</a>
                                                    @else
                                                        {{ $detail->is_approve == 1 ? 'Yes' : 'No' }}
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                @else
                                    {{-- @dd($detail->is_approve_by_head === 1) --}}
                                    <tr
                                        class="
                                            @if ($detail->is_approve === 1 || $detail->is_approve_by_verificator === 1 || $detail->is_approve_by_head === 1) table-success
                                            @elseif ($detail->is_approve === 0 || $detail->is_approve_by_verificator === 0 || $detail->is_approve_by_head === 0)
                                                table-danger @endif
                                        ">
                                        <td>{{ $loop->iteration }}</td>
                                        <td><a href="" class="editable" data-type="text" data-name="item_name"
                                                data-pk="{{ $detail->id }}">{{ $detail->item_name }}</a><span
                                                id="span-item-name-{{ $detail->id }}">{{ $detail->item_name }}</span>
                                        </td>

                                        <td><a href="" class="editable" data-type="text" data-name="quantity"
                                                data-pk="{{ $detail->id }}">{{ $detail->quantity }}</a><span
                                                id="span-quantity-{{ $detail->id }}">{{ $detail->quantity }}</span>
                                        </td>
                                        <td><a href="" class="editable" data-type="text" data-name="purpose"
                                                data-pk="{{ $detail->id }}">{{ $detail->purpose }}</a><span
                                                id="span-purpose-{{ $detail->id }}">{{ $detail->purpose }}</span></td>
                                        <td> @currency($detail->master->price ?? 0) </td>
                                        <td> <a href="" class="editable" data-type="text" data-name="price"
                                                data-pk="{{ $detail->id }}">@currency($detail->price) </a><span
                                                id="span-price-{{ $detail->id }}">@currency($detail->price)</span>
                                        </td>
                                        <td> @currency($detail->quantity * $detail->price) </td>
                                        @php
                                            if ($purchaseRequest->status == 6 || $purchaseRequest->status == 2) {
                                                if ($detail->is_approve_by_head !== 0) {
                                                    $totalall += $detail->quantity * $detail->price; // Update the total
                                                }
                                            } elseif ($purchaseRequest->status == 2 || $purchaseRequest->status == 3) {
                                                if ($detail->is_approve_by_verificator !== 0) {
                                                    $totalall += $detail->quantity * $detail->price; // Update the total
                                                }
                                            } elseif ($purchaseRequest->status == 3 || $purchaseRequest->status == 4) {
                                                if ($detail->is_approve === 1) {
                                                    $totalall += $detail->quantity * $detail->price; // Update the total
                                                }

                                            } elseif ($purchaseRequest->status == 5) {
                                                $totalall += $detail->quantity * $detail->price; // Update the total
                                            }
                                        @endphp

                                        @if ($user->department == $userCreatedBy->department && $user->is_head == 1)
                                            <td>
                                                @if ($detail->is_approve_by_head === null)
                                                    <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'head']) }}"
                                                        class="btn btn-danger">Reject</a>
                                                    <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'head']) }}"
                                                        class="btn btn-success">Approve</a>
                                                @else
                                                    {{ $detail->is_approve_by_head == 1 ? 'Yes' : 'No' }}
                                                @endif
                                            </td>
                                        @elseif ($user->specification->name == 'VERIFICATOR')
                                            <td>
                                                @if ($detail->is_approve_by_verificator === null)
                                                    <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'verificator']) }}"
                                                        class="btn btn-danger">Reject</a>
                                                    <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'verificator']) }}"
                                                        class="btn btn-success">Approve</a>
                                                @else
                                                    {{ $detail->is_approve_by_verificator == 1 ? 'Yes' : 'No' }}
                                                @endif
                                            </td>
                                        @elseif ($user->department->name === 'DIRECTOR')
                                            <td>
                                                @if ($detail->is_approve === null)
                                                    <a href="{{ route('purchaserequest.detail.reject', ['id' => $detail->id, 'type' => 'director']) }}"
                                                        class="btn btn-danger">Reject</a>
                                                    <a href="{{ route('purchaserequest.detail.approve', ['id' => $detail->id, 'type' => 'director']) }}"
                                                        class="btn btn-success">Approve</a>
                                                @else
                                                    {{ $detail->is_approve == 1 ? 'Yes' : 'No' }}
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7">No Data</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-right"><strong>Total</strong></td>
                                <td class="table-active fw-semibold">@currency($totalall)</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="uploaded">
        @include('partials.uploaded-section', [
            'showDeleteButton' => Auth::user()->id == $userCreatedBy->id,
        ])
    </section>
@endsection

@push('extraJs')
    <script>
        // Get references to the checkbox and label elements
        let checkbox = document.getElementById('toggle-edit');
        let label = document.getElementById('edit-mode-label');
        // Get references to all span elements
        const allSpanElements = document.querySelectorAll('[id^="span-"]');

        const editMode = "{{ session('edit_mode') }}";
        if (editMode) {
            label.textContent = 'Edit Mode On';
            label.classList.remove('btn-outline-secondary');
            label.classList.add('btn-primary');
            // Hide all span elements
            allSpanElements.forEach(span => {
                span.classList.add('d-none');
            });
            $('.header-editable, .editable').toggle();
            // Set checkbox state based on editMode
            checkbox.checked = true;
        }

        // Add event listener to the checkbox
        checkbox.addEventListener('change', function() {
            const isChecked = checkbox.checked;
            // Update the label text based on checkbox state
            if (isChecked) {
                label.textContent = 'Edit Mode On';
                label.classList.remove('btn-outline-secondary');
                label.classList.add('btn-primary');
                // Hide all span elements
                allSpanElements.forEach(span => {
                    span.classList.add('d-none');
                });
            } else {
                label.textContent = 'Edit Mode Off';
                label.classList.remove('btn-primary');
                label.classList.add('btn-outline-secondary');
                // Show all span elements
                allSpanElements.forEach(span => {
                    span.classList.remove('d-none');
                });
            }

            // Update session state on the server
            updateSession(isChecked);
        });

        // Function to update session state on the server
        function updateSession(isChecked) {
            fetch('/update-edit-mode', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        isChecked: isChecked
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to update session state');
                    }
                })
                .catch(error => {
                    console.error('Error updating session state:', error);
                });
        }

        $.fn.editable.defaults.mode = 'inline';
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // Overwrite the global templates
        $.fn.editableform.buttons =
            '<button type="submit" class="btn btn-primary editable-submit">OK</button>' +
            '<button type="button" class="btn btn-secondary editable-cancel">Cancel</button>';

        // Select the specific <td> containing the date_required field
        const dateRequiredTd = $('td:has(#span-date-required)');

        // Apply the editable function to the dateRequiredTd element
        dateRequiredTd.find('.header-editable').editable({
            url: '/purchaserequest/update',
            success: function(response, newValue) {
                // Reload the page on success
                window.location.reload();
            },
            validate: function(value) {
                // If the input is empty, return required field message
                if ($.trim(value) == '') {
                    return 'This field is required';
                }

                // Check if the input matches the YYYY-MM-DD format
                const regexPattern = /^\d{4}-\d{2}-\d{2}$/;
                if (!regexPattern.test(value)) {
                    return 'Invalid date format. Please enter date in YYYY-MM-DD format.';
                }

                // Extract year, month, and day components from the input date string
                const [year, month, day] = value.split('-').map(Number);

                // Check if the month and day values are valid
                if (month < 1 || month > 12 || day < 1 || day > 31) {
                    return 'Invalid date';
                }

                // Check for months with less than 31 days
                if ([4, 6, 9, 11].includes(month) && day === 31) {
                    return 'Invalid date for this month';
                }

                // Check for February
                if (month === 2) {
                    // Check for February 29 in leap years
                    if (day === 29) {
                        if (!((year % 4 === 0 && year % 100 !== 0) || year % 400 === 0)) {
                            return 'Invalid date (not a leap year)';
                        }
                    }
                    // Check for February 30 or 31
                    else if (day === 30 || day === 31) {
                        return 'Invalid date for February';
                    }
                }
            }
        });

        $('.editable').editable({
            url: '/purchaserequest/detail/update',
            success: function(response, newValue) {
                // Reload the page on success
                window.location.reload();
            },
            validate: function(value) {
                // Remove leading/trailing spaces
                value = $.trim(value);

                // Check if the field is required
                if (value === '') {
                    return 'This field is required';
                }

                // Check if the length exceeds 255 characters
                if (value.length > 255) {
                    return 'Maximum length exceeded (255 characters)';
                }

                // Remove currency symbol and comma separators
                const numericValue = parseFloat(value.replace(/\D/g, ''));
                console.log(numericValue);

                // Check if the parsed numeric value is a valid number
                if (!isNaN(numericValue)) {

                    // Check if the numeric value exceeds the maximum allowed value
                    if (numericValue > 999999999) {
                        return 'Field cannot be more than 999,999,999!';
                    }
                }

                console.log(value);

            }
        });

        $('.header-editable').editable({
            url: '/purchaserequest/update',
            success: function(response, newValue) {
                // Reload the page on success
                window.location.reload();
            },
            validate: function(value) {
                // Remove leading/trailing spaces
                value = $.trim(value);
                if (value == '') {
                    return 'This field is required';
                }

                // Check if the length exceeds 150 characters
                if (value.length > 150) {
                    return 'Maximum length exceeded (150 characters)';
                }

            }
        });

        $('#toggle-edit').click(function(e) {
            e.stopPropagation();
            $('.header-editable, .editable').toggle();
        });

        $('.header-editable, .editable').toggle();

        // format the price input
        $(document).ready(function() {
            // Attach input event listener using event delegation
            $(document).on('click', 'a[data-name="price"].editable-click', function() {
                // Find the closest editable container and then find the input inside it
                var inputField = $(this).siblings('.editable-container').find('.editable-input input');
                // Check if the input field is found
                if (inputField.length > 0) {
                    // // Input field found, log it to the console
                    // console.log(inputField);
                    inputField.on('input', function() {
                        formatPrice(this);
                    });
                }
            });
        });

        function formatPrice(input) {
            let price = input.value.replace(/\D/g, ''); // Remove non-digit characters
            price = parseInt(price); // Convert string to integer
            if (!isNaN(price)) {
                // Format the price with thousand separators and add currency symbol
                const formattedPrice = 'Rp. ' + price.toLocaleString('id-ID');
                input.value = formattedPrice;
            } else {
                input.value = ''; // Clear the input if it's not a valid number
            }
        }
    </script>

    {{-- autograph script --}}
    <script>
        // Function to add autograph to the specified box
        function addAutograph(section, prId) {
            // Get the div element
            var autographBox = document.getElementById('autographBox' + section);

            console.log('Section:', section);
            console.log('Report ID:', prId);
            var username = '{{ Auth::check() ? Auth::user()->name : '' }}';
            var imageUrl = '{{ asset(':path') }}'.replace(':path', username + '.png');
            console.log('username :', username);
            console.log('image path :', imageUrl);

            autographBox.style.backgroundImage = "url('" + imageUrl + "')";

            // Make an AJAX request to save the image path
            fetch('/save-signature-path/' + prId + '/' + section, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        imagePath: imageUrl,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data.message);
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                });

            checkAutographStatus(prId);
        }


        function checkAutographStatus(reportId) {
            // Assume you have a variable from the server side indicating the autograph status
            var autographs = {
                autograph_1: '{{ $purchaseRequest->autograph_1 ?? null }}',
                autograph_2: '{{ $purchaseRequest->autograph_2 ?? null }}',
                autograph_3: '{{ $purchaseRequest->autograph_3 ?? null }}',
                autograph_4: '{{ $purchaseRequest->autograph_4 ?? null }}',
                autograph_5: '{{ $purchaseRequest->autograph_5 ?? null }}',
            };

            var autographNames = {
                autograph_name_1: '{{ $purchaseRequest->autograph_user_1 ?? null }}',
                autograph_name_2: '{{ $purchaseRequest->autograph_user_2 ?? null }}',
                autograph_name_3: '{{ $purchaseRequest->autograph_user_3 ?? null }}',
                autograph_name_4: '{{ $purchaseRequest->autograph_user_4 ?? null }}',
                autograph_name_5: '{{ $purchaseRequest->autograph_user_5 ?? null }}',
            };

            // Loop through each autograph status and update the UI accordingly
            for (var i = 1; i <= 5; i++) {
                var autographBox = document.getElementById('autographBox' + i);
                var autographInput = document.getElementById('autographInput' + i);
                var autographNameBox = document.getElementById('autographuser' + i);
                var btnId = document.getElementById('btn' + i);


                // Check if autograph status is present in the database
                if (autographs['autograph_' + i]) {

                    if (btnId) {
                        // console.log(btnId);
                        btnId.style.display = 'none';
                    }

                    // Construct URL based on the current location
                    var url = '/' + autographs['autograph_' + i];

                    // Update the background image using the URL
                    autographBox.style.backgroundImage = "url('" + url + "')";

                    var autographName = autographNames['autograph_name_' + i];
                    autographNameBox.textContent = autographName;
                    autographNameBox.style.display = 'block';
                }
            }
        }

        // Call the function to check autograph status on page load
        window.onload = function() {
            checkAutographStatus({{ $purchaseRequest->id }});
        };
    </script>
@endpush
