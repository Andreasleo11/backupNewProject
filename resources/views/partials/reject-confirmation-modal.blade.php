<!-- Rejection Confirmation Modal -->
<div class="modal fade" id="rejection-confirmation-modal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectionConfirmationModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage"></p>
                <div class="form-check mb-3" id="confirmationCheckboxLabel" style="display: none;">
                    <input class="form-check-input" type="checkbox" id="confirmationCheckbox">
                    <label class="form-check-label" for="confirmationCheckbox">
                        I confirm my rejection of the selected reports
                    </label>
                </div>
                <div class="mb-3">
                    <label for="reasonInput" class="form-label">Reason for Rejection:</label>
                    <textarea class="form-control" id="reasonInput" rows="3" disabled></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmRejectionButton">Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>
