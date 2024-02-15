<div class="modal" tabindex="-1" class="modal fade" id="add-user-modal" aria-labelledby="addUserModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('superadmin.users.store')}}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="inputEmail" class="form-label">Email</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="email" name="email" class="form-control" id="inputEmail">
                            </div>
                        </div>
                    </div>
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
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="inputRole" class="form-label">Role</label>
                            </div>
                            <div class="col-sm-9">
                                <select id="inputRole" name="role_id" class="form-control">
                                    <option value="3">User</option>
                                    <option value="2">Staff</option>
                                    <option value="1">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="inputDepartment" class="form-label">Department</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="department" class="form-control" id="inputDepartment">
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
