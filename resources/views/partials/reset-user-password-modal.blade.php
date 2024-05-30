<div class="modal fade" id="resetPasswordConfirmationModal{{ $user->id }}" tabindex="-1" role="dialog"
    aria-labelledby="resetPasswordConfirmationLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordConfirmationLabel">Reset Password Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to reset <strong>{{ $user->email }}</strong> password?
            </div>
            <div class="modal-footer">
                <form action="{{ route('superadmin.users.reset.password', $user->id) }}" method="GET"
                    style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">Reset</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
