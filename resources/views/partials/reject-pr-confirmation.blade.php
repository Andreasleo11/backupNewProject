<div class="modal fade" id="reject-pr-confirmation">
    <div class="modal-dialog modal-dialog">
        <form action="{{ route('purchaserequest.reject', $purchaseRequest->id) }}" method="get">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="pr-modal">Confirmation Reject</h1>
                </div>
                <div class="modal-body text-start">
                    <div class="text-center" id="prompt">
                        <label for="description" class="form-label">Are you sure you want to reject <span
                                class="fw-semibold">{{ $purchaseRequest->doc_num }}</span>?</label>
                    </div>
                    <div class="description-form-group d-none">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" class="form-control" placeholder="Tell us why you rejecting this report..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="btnNo" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button id="btnYes" type="button" class="btn btn-primary">Yes</button>
                    <button id="btnConfirm" type="submit" class="btn btn-success d-none">Confirm</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const modalTitle = document.querySelector('.modal-title#pr-modal');
    const formGroup = document.querySelector('.description-form-group');
    const divPrompt = document.getElementById('prompt');
    const btnYes = document.getElementById('btnYes');
    const btnConfirm = document.getElementById('btnConfirm');
    const btnNo = document.getElementById('btnNo');

    btnYes.addEventListener('click', function() {
        modalTitle.textContent = 'Reject';
        divPrompt.classList.add('d-none');
        formGroup.classList.remove('d-none');
        btnYes.classList.add('d-none');
        btnConfirm.classList.remove('d-none');
        btnNo.textContent = 'Close';
    });


    function resetState() {
        modalTitle.textContent = 'Reject Confirmation';
        divPrompt.classList.remove('d-none');
        formGroup.classList.add('d-none');
        btnYes.classList.remove('d-none');
        btnConfirm.classList.add('d-none');
        btnNo.textContent = 'No';
    }

    btnNo.addEventListener('click', resetState);
</script>
