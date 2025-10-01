<div class="modal" tabindex="-1" class="modal fade" id="edit-user-modal{{ $user->id }}"
    aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('superadmin.users.update', $user->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="inputEmail" class="form-label">Email</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="email" name="email" class="form-control" id="inputEmail"
                                    value="{{ $user->email }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="inputName" class="form-label">Name</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control" id="inputName"
                                    value="{{ $user->name }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="inputRole" class="form-label">Role</label>
                            </div>
                            <div class="col-sm-9">
                                <select id="inputRole" name="role" class="form-select">
                                    @foreach ($roles as $role)
                                        @if ($role->id === $user->role_id)
                                            <option value="{{ $role->id }}" selected>{{ $role->name }}</option>
                                        @else
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endif
                                    @endforeach
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
                                <select id="inputDepartment" name="department" class="form-select">
                                    @foreach ($departments as $department)
                                        @if ($department->id === $user->department_id)
                                            <option value="{{ $department->id }}" selected>{{ $department->name }}
                                            </option>
                                        @else
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="inputSpecification" class="form-label">Specification</label>
                            </div>
                            <div class="col-sm-9">
                                <select id="inputSpecification" name="specification" class="form-select">
                                    @foreach ($specifications as $specification)
                                        @if ($specification->id == $user->specification_id)
                                            <option value="{{ $specification->id }}" selected>
                                                {{ $specification->name }}
                                            </option>
                                        @else
                                            <option value="{{ $specification->id }}">{{ $specification->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
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
