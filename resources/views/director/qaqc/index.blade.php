@extends('layouts.app')

@section('content')
<section class="header">
    <div class="d-flex mb-1 row-flex">
        <div class="h2 me-auto">QA & QC Reports</div>
    </div>
</section>

<section>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-5 ">
            <li class="breadcrumb-item"><a href="{{route('director.home')}}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">QA & QC Reports</li>
        </ol>
    </nav>
</section>

@if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<section class="content">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive p-4">
                <div class="mb-3">
                    <button id="approve-selected-btn" data-approve-url="{{ route('director.qaqc.approveSelected') }}" class="btn btn-primary">Approve Selected</button>
                    <button id="reject-selected-btn" data-reject-url="{{ route('director.qaqc.rejectSelected') }}" data-bs-toggle="modal" data-bs-target="#reject-selected-modal" class="btn btn-danger">Reject Selected</button>
                    @include('partials.reject-selected-modal')

                    <button id="reject-selected-btn2" data-reject-url="{{ route('director.qaqc.rejectSelected') }}" data-bs-toggle="modal" data-bs-target="#reject-confirmation-modal" class="btn btn-danger">Reject Selected 2</button>
                    @include('partials.reject-confirmation-modal')
                </div>
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('extraJs')
    {{ $dataTable->scripts() }}

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            var checkInterval = setInterval(function(){
                var thElement = document.querySelector('th.check_all');
                if(thElement){
                    clearInterval(checkInterval);

                    var input = document.createElement('input');
                    input.style.marginLeft= '10px';
                    input.setAttribute('type', 'checkbox');
                    input.setAttribute('class', 'form-check-input');
                    thElement.appendChild(input);

                    var isChecked = false;

                    thElement.addEventListener('click', function(){
                        var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');

                        checkboxes.forEach(function(checkbox){
                            checkbox.checked = !isChecked;
                        });

                        isChecked = !isChecked;
                    });
                }
            }, 100);

            document.getElementById('approve-selected-btn').addEventListener('click', function(){
                var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

                var ids = [];
                checkboxes.forEach(function(checkbox){
                    var userId = checkbox.id.replace('checkbox', '');
                    ids.push(userId);
                });

                if(ids.length > 0){
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var approveRoute = document.getElementById('approve-selected-btn').getAttribute('data-approve-url');
                    console.log(approveRoute);

                    fetch(approveRoute, {
                        method: 'PUT',
                        headers: {
                            'Content-Type' : 'application/json',
                            'X-CSRF-TOKEN' : csrfToken
                        },
                        body: JSON.stringify({ ids: ids}),
                    }).then(response => {
                        if(response.ok){
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
            });

            document.getElementById('confirmReject').addEventListener('click', function(){
                var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

                var ids = [];
                checkboxes.forEach(function(checkbox){
                    var userId = checkbox.id.replace('checkbox', '');
                    ids.push(userId);
                });

                var rejectionReason = document.getElementById('rejectionReason').value;

                if(ids.length > 0){
                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var rejectRoute = document.getElementById('reject-selected-btn').getAttribute('data-reject-url');
                    var rejectionReason = document.getElementById('rejectionReason').value;

                    fetch(rejectRoute, {
                        method: 'PUT',
                        headers: {
                            'Content-Type' : 'application/json',
                            'X-CSRF-TOKEN' : csrfToken
                        },
                        body: JSON.stringify({
                            ids: ids,
                            rejection_reason: rejectionReason
                        }),
                    }).then(response => {
                        if(response.ok){
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

        function getSelectedReportIds(){
            var checkboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');

            var ids = [];
            checkboxes.forEach(function(checkbox){
                var userId = checkbox.id.replace('checkbox', '');
                ids.push(userId);
            });

            return ids;
        }

        // Function to check if any report has been approved
        function hasApprovedReports(selectedReportIds) {
            // Check if any of the selected report IDs have is_approve equal to true
            for (let id of selectedReportIds) {
                // Assuming reportsData is an array containing report objects with their properties including is_approve
                const report = reportsData.find(report => report.id === id);
                if (report && report.is_approve) {
                    return true;
                }
            }
            return false;
        }

        // Function to check if any report has been rejected before
        function hasRejectedReports(selectedReportIds) {
            // Check if any of the selected report IDs have been rejected before
            for (let id of selectedReportIds) {
                // Assuming reportsData is an array containing report objects with their properties including is_rejected
                const report = reportsData.find(report => report.id === id);
                if (report && report.is_rejected) {
                    return true;
                }
            }
            return false;
        }

        // Function to update the modal content based on selected reports
        function updateConfirmationModal(selectedReportIds) {
            const confirmationMessage = document.getElementById('confirmationMessage');
            const checkboxLabel = document.getElementById('confirmationCheckboxLabel');
            const checkbox = document.getElementById('confirmationCheckbox');

            // Check if any report has been approved
            const hasApproved = hasApprovedReports(selectedReportIds);
            // Check if any report has been rejected before
            const hasRejected = hasRejectedReports(selectedReportIds);

            if (hasApproved) {
                const approvedReports = selectedReportIds.filter(id => {
                    const report = reportsData.find(report => report.id === id);
                    return report && report.is_approve;
                });
                // Update confirmation message
                confirmationMessage.innerHTML = `One or more reports (${approvedReports.join(', ')}) have been approved. Please check the confirmation box below to confirm rejection.`;
            } else if (hasRejected) {
                const rejectedReports = selectedReportIds.filter(id => {
                    const report = reportsData.find(report => report.id === id);
                    return report && report.is_rejected;
                });
                // Update confirmation message
                confirmationMessage.innerHTML = `One or more reports (${rejectedReports.join(', ')}) have been rejected before. Please check the confirmation box below to proceed with rejection.`;
            } else {
                // Update confirmation message
                confirmationMessage.innerHTML = 'Are you sure you want to reject the selected reports? Please check the confirmation box below to confirm.';
            }

            // Enable/disable checkbox based on whether any report has been approved or rejected before
            checkboxLabel.style.display = hasApproved || hasRejected ? 'block' : 'none';
            checkbox.disabled = !hasApproved && !hasRejected;
        }

        // Event listener for the "Reject" button click
        document.getElementById('reject-selected-btn2').addEventListener('click', function() {
            // Get the selected report IDs (assuming you have a function to retrieve them)
            const selectedReportIds = getSelectedReportIds();
            console.log(selectedReportIds);

            // Update modal content based on selected reports
            updateConfirmationModal(selectedReportIds);
        });

        // Event listener for the confirmation checkbox change
        document.getElementById('confirmationCheckbox').addEventListener('change', function() {
            // Enable/disable the reason input based on whether the checkbox is checked
            const reasonInput = document.getElementById('reasonInput');
            reasonInput.disabled = !this.checked;
        });

        // Function to handle rejection confirmation modal
        function showRejectionConfirmationModal(reportIds, approvalStatus) {
            const modalElement = document.getElementById('rejectionConfirmationModal');
            const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });

            // Check if any report has been approved or rejected before
            let confirmed = false;
            let confirmedDocNum = '';
            let rejectedDocNum = '';
            reportIds.forEach(id => {
                if (approvalStatus[id] === 'approved') {
                    confirmedDocNum = id;
                } else if (approvalStatus[id] === 'rejected') {
                    rejectedDocNum = id;
                }
            });

            const confirmationMessage = document.getElementById('confirmationMessage');
            const confirmationCheckboxLabel = document.getElementById('confirmationCheckboxLabel');
            const confirmationCheckbox = document.getElementById('confirmationCheckbox');
            const reasonInput = document.getElementById('reasonInput');
            const confirmRejectionButton = document.getElementById('confirmRejectionButton');

            if (confirmedDocNum) {
                // If any report has been approved before
                confirmationMessage.textContent = `The report with document number ${confirmedDocNum} has been approved before. Please confirm your rejection.`;
                confirmationCheckboxLabel.style.display = 'block';
            } else if (rejectedDocNum) {
                // If any report has been rejected before
                confirmationMessage.textContent = `The report with document number ${rejectedDocNum} has been rejected before. Please confirm your rejection.`;
                confirmationCheckboxLabel.style.display = 'block';
            } else {
                // If no reports have been approved or rejected before
                confirmationMessage.textContent = 'Please provide a reason for rejection:';
                confirmationCheckboxLabel.style.display = 'none';
            }

            modal.show();

            confirmRejectionButton.addEventListener('click', function() {
                const reason = reasonInput.value.trim();
                if (confirmed || (confirmationCheckbox.checked && reason !== '')) {
                    // Proceed with rejection
                    console.log('Rejection confirmed with reason:', reason);
                    modal.hide();
                    // Call a function to handle rejection submission
                } else {
                    // Show error message or prompt user to confirm
                    console.log('Please confirm your rejection and provide a reason.');
                }
            });
        }

        // Example usage
        const reportIds = [/* Array of report IDs */];
        const approvalStatus = {
            /* Map of report IDs to their approval status (e.g., 'approved', 'rejected') */
        };
        showRejectionConfirmationModal(reportIds, approvalStatus);

    </script>
@endpush
