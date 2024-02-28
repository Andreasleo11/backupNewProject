<div class="modal fade" id="reject-selected-modal">
    <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                @method('PUT')
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Reason rejecting these documents</h1>
                </div>
                <div class="modal-body">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control" id="rejectionReason" placeholder="Tell us why you rejecting this report..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmReject">Confirm</button>
                </div>
            </div>
    </div>
</div>
