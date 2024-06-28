<div class="modal fade" id="cancel-confirmation-modal-{{ $pr->id }}">
    <div class="modal-dialog">
        <div class="modal-content text-start">
            <form action="{{ route('purchaserequest.cancel', $pr->id) }}" method="post">
                @csrf
                @method('put')
                <div class="modal-header">
                    <h5 class="modal-title">Cancel PR Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <label for="description" class="form-label fw-bold fs-6">Reason (Description)</label>
                    <textarea name="description" id="description" cols="30" rows="5" class="form-control"
                        placeholder="Tell us why you cancel this purchase request..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
