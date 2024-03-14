<div class="modal" tabindex="-1" id="add-defect-category-modal-{{ $detail->id }}" aria-labelledby="addDefectCategoryModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" action="{{ route('qaqc.defectcategory.store') }}">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title">Add New Defect Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <div class="form-group mb-3">
                      <label class="form-label">Name</label>
                      <input type="text" class="form-control" name="name">
                      <div class="form-text">Eg. Nubmark, Scratch, Bubble, ...</div>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Add</button>
              </div>
          </form>
        </div>
    </div>
  </div>
