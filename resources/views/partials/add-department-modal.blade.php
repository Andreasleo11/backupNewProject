<div class="modal fade" tabindex="-1" id="add-department-modal" aria-labelledby="addDepartmentModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('superadmin.departments.store')}}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="inputName" class="form-label">Name</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control" id="inputName">
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
