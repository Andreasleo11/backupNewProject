@extends('layouts.app')

@push('extraCss')
    <style>
        .circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #007bff;
            border: 2px solid #007bff;
            /* This creates the #007bff outline */
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .outline {
            background-color: transparent;
            color: #007bff;
            /* Hide the text inside the circles */
        }
    </style>
@endpush

@section('content')
    @include('partials.alert-success-error')
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-flex-grow-1 border-bottom p-0">
                                    <div class="p-4">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-auto">
                                                <div class="circle">1</div>
                                            </div>
                                            <div class="col">
                                                <div class="progress" role="progressbar" aria-valuenow="100"
                                                    aria-valuemin="0" aria-valuemax="100" style="height: 12px">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                        style="width: 100%"></div>
                                                </div>
                                            </div>

                                            <!-- Circle 2 -->
                                            <div class="col-auto">
                                                <div class="circle">2</div>
                                            </div>
                                            <div class="col">
                                                <div class="progress" role="progressbar" aria-valuenow="50"
                                                    aria-valuemin="0" aria-valuemax="100" style="height: 12px">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                        style="width: 50%"></div>
                                                </div>
                                            </div>

                                            <!-- Circle 3 -->
                                            <div class="col-auto">
                                                <div class="circle outline">3</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-top pt-4 px-4">
                                        <div class="mb-4">
                                            <span class="h3">Add Part Defects</span>
                                            <p class="text-secondary mt-2">You need to add part defects for each of part
                                                details that you have
                                                been added before. Everytime you add, it will stored in the table below.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-3 p-4 border-end">
                                    <h5 class="pb-2">1. Select Part Detail</h5>
                                    <div class="list-group" id="list-tab" role="tablist">
                                        @foreach ($details as $detail)
                                            <a class="list-group-item list-group-item-action @if (session('active_tab') == $detail->id) active @endif"
                                                id="list-detail-{{ $detail->id }}-list" data-bs-toggle="list"
                                                href="#list-detail{{ $detail->id }}" role="tab"
                                                aria-controls="list-detail{{ $detail->id }}">{{ $detail->part_name }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-9 p-4">
                                    @foreach ($details as $index => $detail)
                                        <div class="tab-content" id="nav-tabContent">
                                            <div class="tab-pane fade show @if (session('active_tab') == $detail->id) active @endif"
                                                id="list-detail{{ $detail->id }}" role="tabpanel">
                                                <div class="mb-3 row">
                                                    <div class="col">
                                                        <h5>2. Add Defects for <span
                                                                class="fw-semibold">{{ $detail->part_name }}</span> </h5>
                                                    </div>
                                                    <div class="col-auto">
                                                        <a class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                            data-bs-target="#add-defect-modal-{{ $detail->id }}">+ Add
                                                            Defect</a>
                                                        @include('partials.add-defect-modal')
                                                        <a href="" class="btn btn-outline-secondary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#add-defect-category-modal-{{ $detail->id }}">+
                                                            Add Defect Category</a>
                                                        @include('partials.add-defect-category-modal', [
                                                            'id' => $detail->id,
                                                        ])
                                                    </div>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered table-sm">
                                                        <thead class="text-center align-middle">
                                                            <tr>
                                                                <th class="py-3">#</th>
                                                                <th>Daijo Defect</th>
                                                                <th>Customer Defect</th>
                                                                <th>Supplier Defect</th>
                                                                <th>Remarks</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($detail->defects as $defect)
                                                                <tr class="text-center align-middle">
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    @if ($defect->is_daijo)
                                                                        <td></td>
                                                                        <td>{{ $defect->quantity . ' : ' . $defect->category->name }}
                                                                        </td>
                                                                        <td></td>
                                                                    @elseif($defect->is_customer)
                                                                        <td>{{ $defect->quantity . ' : ' . $defect->category->name }}
                                                                        </td>
                                                                        <td></td>
                                                                        <td></td>
                                                                    @elseif($defect->is_supplier)
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td>{{ $defect->quantity . ' : ' . $defect->category->name }}
                                                                        </td>
                                                                    @endif
                                                                    <td>{{ $defect->remarks ?? '-' }}</td>
                                                                    <td>
                                                                        <form
                                                                            action="{{ route('qaqc.report.deletedefect', $defect->id) }}"
                                                                            method="post">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit"
                                                                                class="btn btn-danger btn-sm">
                                                                                <i class='bx bx-trash-alt'></i>
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="6" class="text-center">No Data</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-between border-top p-3">
                                    <div class="">
                                        <a href="{{ route('qaqc.report.createdetail') }}"
                                            class="btn btn-secondary">Back</a>
                                    </div>

                                    <div class="d-flex">
                                        <form action="{{ route('qaqc.report.redirect.to.index') }}" method="get">
                                            <button type="submit" class="btn btn-success" id="finishBtn">Finish</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('partials.info-modal')
    <button type="button" id="showModalBtn" class="d-none" data-bs-toggle="modal"
        data-bs-target="#info-modal">test</button>
@endsection

@push('extraJs')
    <script>
        let details = {!! json_encode($details) !!};
        console.log(details);

        details.forEach(detail => {
            let totalVerifyQuantity = detail.verify_quantity;
            let defects = detail.defects;
            console.log(defects);

            // Calculate the sum of quantity for all defects of this detail
            let totalDefectQuantity = defects.reduce((total, defect) => total + defect.quantity, 0);

            console.log("total verify quantity: " + totalVerifyQuantity);
            console.log("total defect quantity: " + totalDefectQuantity);

            // Check if the sum of verify_quantity is greater than the sum of defect quantities
            if (totalDefectQuantity > totalVerifyQuantity) {
                // alert('Verify quantity is greater than accumulated defect quantity for detail: ' + detail.id);
                // Update modal content
                document.getElementById('modalTitle').textContent = 'Verify Quantity Alert';
                document.getElementById('modalBody').textContent =
                    'Verify quantity is greater than accumulated defect quantity for ' + detail.part_name;

                // Show Modal
                document.addEventListener('DOMContentLoaded', function() {
                    // Find the button element
                    let showModalBtn = document.getElementById('showModalBtn');

                    // Trigger the button click
                    showModalBtn.click();
                    const finishBtn = document.getElementById('finishBtn');
                    finishBtn.disabled = true;
                });
            } else {
                finishBtn.disabled = false;
            }
        });

        @foreach ($details as $detail)
            const checkCustomerDefect{{ $detail->id }} = document.getElementById(
                'checkCustomerDefect{{ $detail->id }}');
            const checkDaijoDefect{{ $detail->id }} = document.getElementById(
                'checkDaijoDefect{{ $detail->id }}');
            const checkSupplierDefect{{ $detail->id }} = document.getElementById(
                'checkSupplierDefect{{ $detail->id }}');
            const customerDefectGroup{{ $detail->id }} = document.getElementById(
                'customerDefectGroup{{ $detail->id }}');
            const daijoDefectGroup{{ $detail->id }} = document.getElementById(
                'daijoDefectGroup{{ $detail->id }}');
            const supplierDefectGroup{{ $detail->id }} = document.getElementById(
                'supplierDefectGroup{{ $detail->id }}');
            const remarkSelect{{ $detail->id }} = document.getElementById('remark{{ $detail->id }}');
            const otherInput{{ $detail->id }} = document.getElementById(
                'other_remark{{ $detail->id }}');

            checkCustomerDefect{{ $detail->id }}.addEventListener('change', function() {
                if (this.checked) {
                    customerDefectGroup{{ $detail->id }}.style.display = 'block';
                } else {
                    customerDefectGroup{{ $detail->id }}.style.display = 'none';
                }
            });

            checkDaijoDefect{{ $detail->id }}.addEventListener('change', function() {
                if (this.checked) {
                    daijoDefectGroup{{ $detail->id }}.style.display = 'block';
                } else {
                    daijoDefectGroup{{ $detail->id }}.style.display = 'none';
                }
            });

            checkSupplierDefect{{ $detail->id }}.addEventListener('change', function() {
                if (this.checked) {
                    supplierDefectGroup{{ $detail->id }}.style.display = 'block';
                } else {
                    supplierDefectGroup{{ $detail->id }}.style.display = 'none';
                }
            });

            remarkSelect{{ $detail->id }}.addEventListener('change', function() {
                if (this.value === 'other') {
                    otherInput{{ $detail->id }}.style.display = 'block';
                } else {
                    otherInput{{ $detail->id }}.style.display = 'none';
                }
            });
        @endforeach

        // Function to handle tab click event
        function handleTabClick(event) {
            // Extract the detail ID from the clicked tab's ID
            const detailId = event.target.id.split('-')[2];
            console.log(detailId);

            // Make AJAX request to update session variable
            fetch('/update-active-tab', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        detailId: detailId
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to update active tab');
                    }
                    return response.json();
                })
                .then(data => {
                    // Handle successful response if needed
                    console.log('Active tab updated successfully');
                })
                .catch(error => {
                    // Handle error if needed
                    console.error('Error updating active tab:', error.message);
                });
        }

        // Add click event listener to each tab
        document.querySelectorAll('.list-group-item').forEach(tab => {
            tab.addEventListener('click', handleTabClick);
        });
    </script>
@endpush
