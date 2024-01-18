<div class="modal" tabindex="-1" class="modal fade" id="add-important-doc-modal" aria-labelledby="addImportantDocModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Important Doc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                      <label for="inputName" class="form-label">Name</label>
                      <input type="text" class="form-control" id="inputName">
                    </div>
                    <div class="mb-3">
                      <label for="inputType" class="form-label">Type</label>
                      <input type="text" class="form-control" id="inputType">
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <label for="expiredDate">Expired Date</label>
                        <input type="date" id="expiredDate" name="expiredDate">
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
