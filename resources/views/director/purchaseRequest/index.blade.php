@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="row d-flex">
        <div class="col">
            <h1 class="h1">Purchase Requisition List</h1>
        </div>
        <div class="col-auto">
            @if (Auth::user()->department->name !== 'MANAGEMENT')
                <a href="{{ route('purchaserequest.create') }}" class="btn btn-primary">Create PR </a>
            @endif
        </div>
    </div>
    <section class="content">
        <div class="card mt-5">
            <div class="card-body pe-4">
                <div class="table-responsive p-4">
                    <div class="mb-3 row d-flex">
                        <div class="col">
                            <button id="approve-selected-btn" data-approve-url="{{ route('director.pr.approveSelected') }}"
                                class="btn btn-primary">Approve Selected</button>
                            @include('partials.approve-confirmation-modal')
                            <button id="reject-selected-btn" data-reject-url="{{ route('director.pr.rejectSelected') }}"
                                class="btn btn-danger">Reject Selected</button>
                            @include('partials.info-modal')
                            @include('partials.reject-selected-modal')
                            <input type="hidden" id="selected-report-ids" name="selected_report_ids">
                        </div>
                        <div class="col-auto">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="form-label">Filter by status</div>
                                </div>
                                <div class="col-auto">
                                    <select name="filter_status" id="status-filter" class="form-select">
                                        <option value="" selected>All</option>
                                        <option value="3">Waiting</option>
                                        <option value="4">Approved</option>
                                        <option value="5">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}

    <script type="module">
        $(function() {
            let dataTable = window.LaravelDataTables["purchaserequest-table"];
            $('#status-filter').change(function() {
                let status = $(this).val();
                console.log("Selected status:", status); // Output the selected status to console

                dataTable.column(7).search(status).draw(); // Filter by status column
            });
        });
    </script>

    <script>
        const rejectSelectedButton = document.getElementById('reject-selected-btn');
        const approveSelectedButton = document.getElementById('approve-selected-btn');

        // logic for check all
        document.addEventListener('DOMContentLoaded', function() {
            var checkInterval = setInterval(function() {
                var thElement = document.querySelector('th.check_all');
                if (thElement) {
                    clearInterval(checkInterval);

                    var input = document.createElement('input');
                    input.style.marginLeft = '10px';
                    input.setAttribute('type', 'checkbox');
                    input.setAttribute('class', 'form-check-input');
                    thElement.appendChild(input);

                    var isChecked = false;

                    const checkAllCheckbox = document.querySelectorAll('thead input[type="checkbox"]');

                    checkAllCheckbox.forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {
                            var checkboxes = document.querySelectorAll(
                                'tbody input[type="checkbox"]');
                            checkboxes.forEach(function(checkbox) {
                                checkbox.checked = !isChecked;
                            });

                            isChecked = !isChecked;
                        });
                    });
                }
            }, 100);

            // confirm approve in approve modal
            document.getElementById('confirmApprove').addEventListener('click', function() {
                var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

                if (checkboxes.length > 0) {
                    var ids = [];
                    checkboxes.forEach(function(checkbox) {
                        var userId = checkbox.id.replace('checkbox', '');
                        ids.push(userId);
                    });

                    console.log("ids: " + ids);

                    if (ids.length > 0) {
                        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content');
                        var approveRoute = document.getElementById('approve-selected-btn').getAttribute(
                            'data-approve-url');
                        // console.log(approveRoute);

                        fetch(approveRoute, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                ids: ids
                            }),
                        }).then(response => {
                            if (response.ok) {
                                console.log('Selected records approved successfully');
                                location.reload();
                            } else {
                                console.error('Failed to approve selected records.');
                            }
                        }).catch(error => {
                            console.error('An error occured:', error);
                        });
                    } else {
                        console.warn('No records selected for approval');
                    }
                } else {
                    showInfoModal('Cannot Approve',
                        'You cannot approve because there is no selected purchase request.');
                }
            });

            // confirm reject in reject modal
            document.getElementById('confirmReject').addEventListener('click', function() {
                var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

                var ids = [];
                checkboxes.forEach(function(checkbox) {
                    var userId = checkbox.id.replace('checkbox', '');
                    ids.push(userId);
                });

                // console.log(ids);

                var rejectionReason = document.getElementById('rejectionReason').value;

                if (ids.length > 0) {
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content');
                    var rejectRoute = document.getElementById('reject-selected-btn').getAttribute(
                        'data-reject-url');
                    var rejectionReason = document.getElementById('rejectionReason').value;

                    fetch(rejectRoute, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            ids: ids,
                            rejection_reason: rejectionReason
                        }),
                    }).then(response => {
                        if (response.ok) {
                            console.log('Selected records rejected successfully');
                            location.reload();
                        } else {
                            console.error('Failed to reject selected records.');
                        }
                    }).catch(error => {
                        console.error('An error occured:', error);
                    });
                } else {
                    console.warn('No records selected for rejection');
                }
            });

        });

        function getSelectedReportIds() {
            var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

            var ids = [];
            checkboxes.forEach(function(checkbox) {
                var userId = checkbox.id.replace('checkbox', '');
                ids.push(userId);
            });

            return ids;
        }

        function getSelectedReportIdsWithStatus() {
            var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

            var reportIdWithStatus = [];
            checkboxes.forEach(function(checkbox) {
                var parts = checkbox.id.split('-'); // Split the ID and status using the '-' separator
                var reportId = parseInt(parts[0].replace('checkbox', '')); // Extract the report ID
                var status = parts[1]; // Extract the approval status
                var docNum = parts[2];

                reportIdWithStatus.push({
                    id: reportId,
                    status: status,
                    docNum: docNum
                });
            });

            return reportIdWithStatus;
        }


        var reportIds = [ /* Array of report IDs */ ];
        var approvalStatusMap = {
            /* Map of report IDs to their approval status (e.g., 'approved', 'rejected') */
        };

        approveSelectedButton.addEventListener('click', function() {
            const selectedReportIdsWithStatus = getSelectedReportIdsWithStatus();
            console.log(selectedReportIdsWithStatus);

            reportIds = selectedReportIdsWithStatus.map(report => report.docNum);
            selectedReportIdsWithStatus.forEach(report => {
                approvalStatusMap[report.docNum] = report.status;
            });

            // console.log(reportIds);
            // console.log(approvalStatusMap);

            let hasApprovedOrRejected = false;

            selectedReportIdsWithStatus.forEach(report => {
                if (report.status === '5' || report.status === '4') {
                    hasApprovedOrRejected = true;
                }
            });


            // If any selected report has an approval status of 'approved' or 'rejected', show the modal
            if (hasApprovedOrRejected) {
                showInfoModal('Cannot Approve',
                    'You cannot approve or reject the selected documents because there are already approved or rejected documents among them.'
                );
            } else {
                // Proceed with the approval process
                var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');
                if (checkboxes.length > 0) {
                    showApproveModal()
                } else {
                    showInfoModal('Cannot Approve',
                        'You cannot approve because there is no selected purchase request.');
                }
                console.log('Executing approval process for selected purchase request:',
                    selectedReportIdsWithStatus);
            }


        });

        rejectSelectedButton.addEventListener('click', function() {
            const selectedReportIdsWithStatus = getSelectedReportIdsWithStatus();
            console.log(selectedReportIdsWithStatus);

            reportIds = selectedReportIdsWithStatus.map(report => report.docNum);
            selectedReportIdsWithStatus.forEach(report => {
                approvalStatusMap[report.docNum] = report.status;
            });

            // console.log(reportIds);
            // console.log(approvalStatusMap);

            let hasApprovedOrRejected = false;

            selectedReportIdsWithStatus.forEach(report => {
                if (report.status === '5' || report.status === '4') {
                    hasApprovedOrRejected = true;
                }
            });

            // If any selected report has an approval status of 'approved' or 'rejected', show the modal
            if (hasApprovedOrRejected) {
                showInfoModal('Cannot Reject',
                    'You cannot approve or reject the selected documents because there are already approved or rejected documents among them.'
                );
            } else {
                // Proceed with the rejection process
                var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');
                if (checkboxes.length > 0) {
                    showRejectModal();
                } else {
                    showInfoModal('Cannot Reject',
                        'You cannot reject because there is no selected purchase request.');
                }
                // console.log('Executing rejection process for selected purchase request:',
                //     selectedReportIdsWithStatus);
            }
        });

        function showInfoModal(title, message) {
            const modalTitleElement = document.getElementById('modalTitle');
            const modalBodyElement = document.getElementById('modalBody');
            const modalElement = document.getElementById('info-modal');

            modalTitleElement.innerText = title;
            modalBodyElement.innerText = message;

            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }

        function showRejectModal() {
            const modal = new bootstrap.Modal(document.getElementById('reject-selected-modal'), {
                backdrop: 'static'
            })
            modal.show();
        }

        function showApproveModal() {
            const modal = new bootstrap.Modal(document.getElementById('approve-confirmation-modal'), {
                backdrop: 'static'
            })
            modal.show();
        }
    </script>
@endpush
