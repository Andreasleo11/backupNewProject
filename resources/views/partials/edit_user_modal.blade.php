<div class="modal" tabindex="-1" class="modal fade" id="edit-modal" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                      <label for="inputName" class="form-label">Name</label>
                      <input type="text" class="form-control" id="inputName">
                    </div>
                    <div class="mb-3">
                      <label for="inputEmail" class="form-label">Email address</label>
                      <input type="email" class="form-control" id="inputEmail">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadioAdmin" value="option1">
                            <label class="form-check-label" for="inline_radio_admin">Super Admin</label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inline_radio_manager" value="option2">
                            <label class="form-check-label" for="inline_radio_manager">Staff</label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inline_radio_other" value="option3">
                            <label class="form-check-label" for="inline_radio_other">User</label>
                          </div>
                    </div>
                  </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
              </div>
        </div>
    </div>
</div>    