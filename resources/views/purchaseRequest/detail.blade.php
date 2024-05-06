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

    @php
        $user = Auth::user();
    @endphp

    <section aria-label="autographs" class="container">

        @include('partials.reject-pr-confirmation', $purchaseRequest)

        @include('partials.pr-detail-autographs')

    </section>

    <section aria-label="pr-header-body" class="container mt-5">
        <div class="card">
            <div class="d-flex flex-row-reverse mb-3">

                <div
                    class="p-2 {{ ($purchaseRequest->user_id_create === $user->id && $purchaseRequest->status === 1) ||
                    ($purchaseRequest->status === 1 && $user->is_head) ||
                    ($purchaseRequest->status === 6 && $user->specification->name === 'PURCHASER') ||
                    ($purchaseRequest->status === 2 && $user->department->name === 'HRD')
                        ? ''
                        : 'd-none' }}">
                    @include('partials.edit-purchase-request-modal', [
                        'pr' => $purchaseRequest,
                        'details' => $filteredItemDetail,
                    ])
                    <button data-bs-target="#edit-purchase-request-modal-{{ $purchaseRequest->id }}" data-bs-toggle="modal"
                        class="btn btn-primary"><i class='bx bx-edit'></i> Edit</button>
                </div>
            </div>

            <div class="text-center">
                <span class="h1 fw-semibold">Purchase Requisition</span> <br>
                <div class="fs-6 mt-2">
                    <span class="fs-6 text-secondary">Created By : </span> {{ $userCreatedBy->name }} <br>
                    <span class="fs-6 text-secondary">From Department : </span> {{ $userCreatedBy->department->name }}
                    <br>
                    <span class="fs-6 text-secondary">Doc num : </span> {{ $purchaseRequest->doc_num }}
                    <div class="mt-2">
                        @include('partials.pr-status-badge', ['pr' => $purchaseRequest])
                    </div>
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
                                <td>: {{ $purchaseRequest->date_required }}
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
                                <td>: {{ $purchaseRequest->supplier }}</td>
                                <th>PIC</th>
                                <td>: {{ $purchaseRequest->pic }}</td>

                                </td>
                            </tr>
                            <tr>
                                <th style="width: 15%">Remark</th>
                                <td colspan="3" style="width: 35%; word-wrap: break-word; word-break: break-all;">:
                                    {{ $purchaseRequest->remark }}
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
                            @forelse($filteredItemDetail as $detail)
                                <tr
                                    class=" @if ($detail->is_approve === 1) table-success
                                            @elseif($detail->is_approve === 0)
                                                table-danger text-decoration-line-through
                                            @elseif($detail->is_approve === null)
                                                @if (Auth::user()->department->name === 'DIRECTOR')
                                                @elseif ($detail->is_approve_by_verificator === 1)
                                                    table-success
                                                @elseif($detail->is_approve_by_verificator === 0)
                                                    table-danger text-decoration-line-through
                                                @elseif(Auth::user()->specification->name === 'VERIFICATOR')
                                                @else
                                                    @if ($detail->is_approve_by_head === 1)
                                                        table-success
                                                    @elseif($detail->is_approve_by_head === 0)
                                                        table-danger text-decoration-line-through @endif
                                                @endif
                                            @endif ">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $detail->item_name }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td>{{ $detail->purpose }}</td>
                                    <td> @currency($detail->master->price ?? 0) </td>
                                    <td> @currency($detail->price)</td>
                                    <td> @currency($detail->quantity * $detail->price) </td>

                                    {{-- Logic for total --}}
                                    @php
                                        if ($purchaseRequest->status === 6) {
                                            if (!is_null($detail->is_approve_by_head)) {
                                                if ($detail->is_approve_by_head) {
                                                    $totalall += $detail->quantity * $detail->price;
                                                }
                                            } else {
                                                $totalall += $detail->quantity * $detail->price;
                                            }
                                        } elseif ($purchaseRequest->status === 2) {
                                            if (!is_null($detail->is_approve_by_verificator)) {
                                                if ($detail->is_approve_by_verificator) {
                                                    $totalall += $detail->quantity * $detail->price;
                                                }
                                            } else {
                                                if ($detail->is_approve_by_head) {
                                                    $totalall += $detail->quantity * $detail->price;
                                                }
                                            }
                                        } elseif ($purchaseRequest->status === 3) {
                                            if (!is_null($detail->is_approve)) {
                                                if ($detail->is_approve) {
                                                    $totalall += $detail->quantity * $detail->price;
                                                }
                                            } else {
                                                if ($detail->is_approve_by_verificator) {
                                                    $totalall += $detail->quantity * $detail->price;
                                                } elseif ($detail->is_approve_by_gm) {
                                                    $totalall += $detail->quantity * $detail->price;
                                                }
                                            }
                                        } elseif ($purchaseRequest->status === 4) {
                                            if ($detail->is_approve) {
                                                $totalall += $detail->quantity * $detail->price;
                                            }
                                        } elseif ($purchaseRequest->status === 1) {
                                            if (!is_null($detail->is_approve_by_head)) {
                                                if ($detail->is_approve_by_head) {
                                                    $totalall += $detail->quantity * $detail->price;
                                                }
                                            } else {
                                                $totalall += $detail->quantity * $detail->price;
                                            }
                                        } else {
                                            $totalall += 0;
                                        }
                                    @endphp

                                    {{-- Button approve reject per item --}}
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
                            @empty
                                <tr>
                                    <td colspan="7">No Data</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-right"><strong>Total</strong></td>
                                <td class="table-active fw-semibold">@currency($totalall ?? 0)</td>
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
                    // Approve all detail items if the user is GM
                    @if (Auth::user()->is_gm)
                        approveAllDetailItems(prId, 'GM');
                    @endif
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
                autograph_6: '{{ $purchaseRequest->autograph_6 ?? null }}',
            };

            var autographNames = {
                autograph_name_1: '{{ $purchaseRequest->autograph_user_1 ?? null }}',
                autograph_name_2: '{{ $purchaseRequest->autograph_user_2 ?? null }}',
                autograph_name_3: '{{ $purchaseRequest->autograph_user_3 ?? null }}',
                autograph_name_4: '{{ $purchaseRequest->autograph_user_4 ?? null }}',
                autograph_name_5: '{{ $purchaseRequest->autograph_user_5 ?? null }}',
                autograph_name_6: '{{ $purchaseRequest->autograph_user_6 ?? null }}',
            };

            // Loop through each autograph status and update the UI accordingly
            for (var i = 1; i <= 6; i++) {
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

        function approveAllDetailItems(prId, type) {
            // Make an AJAX request to save the image path
            fetch(`/approveAllDetailItems/${prId}/${type}`)
                .then(response => response.json())
                .then(data => {
                    console.log(data.message);
                })
                .catch(error => {
                    console.error(error);
                });
        }

        // Call the function to check autograph status on page load
        window.onload = function() {
            checkAutographStatus({{ $purchaseRequest->id }});
        };
    </script>
@endpush
