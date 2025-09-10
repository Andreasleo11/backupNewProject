<div class="modal fade" tabindex="-1" id="edit-department-modal-{{ $department->id }}"
  aria-labelledby="editDepartmentModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('superadmin.departments.update', $department->id) }}">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="inputName" class="form-label">Name</label>
              </div>
              <div class="col-sm-9">
                <input type="text" name="name" class="form-control" id="inputName"
                  value="{{ $department->name }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="inputDeptNo" class="form-label">Dept No</label>
              </div>
              <div class="col-sm-9">
                <input type="text" name="dept_no" class="form-control" id="inputDeptNo"
                  value="{{ $department->dept_no }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="col">
              <label for="inputDeptNo" class="form-label me-5">At Office</label>

              <div class="form-check form-check-inline ms-2">
                <input class="form-check-input" type="radio" name="is_office" value="1"
                  {{ $department->is_office ? 'checked' : '' }}>
                <label class="form-check-label">Yes</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="is_office" value="0"
                  {{ !$department->is_office ? 'checked' : '' }}>
                <label class="form-check-label">No</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
