<div class="modal fade" tabindex="-1" class="modal fade" id="edit-defect-category-modal-{{ $defectCategory->id }}" aria-labelledby="editDefectCategoryModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" action="{{ route('qaqc.defectcategory.update', $defectCategory->id) }}">
              @csrf
              @method('PUT')
              <div class="modal-header">
                <h5 class="modal-title">Edit Defect Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <div class="form-group mb-3">
                      <label class="form-label">Name</label>
                      <input type="text" class="form-control" name="name" value="{{ $defectCategory->name }}">
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save</button>
              </div>
          </form>
        </div>
    </div>
  </div>
