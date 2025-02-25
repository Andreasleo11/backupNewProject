<div class="modal" tabindex="-1" class="modal fade" id="add-new-employee" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('addemployee') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="NIK" class="form-label">NIK </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="NIK" class="form-control" id="NIK">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="Nama" class="form-label">Nama:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="Nama" class="form-control" id="Nama">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="Gender" class="form-label">Gender:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="Gender" class="form-control" id="Gender">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="Dept" class="form-label">Dept:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="Dept" class="form-control" id="Dept">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="start_date" class="form-label"> Start Date:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="date" name="start_date" class="form-control" id="start_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="status" class="form-label"> Status:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="status" class="form-control" id="status">
                            </div>
                        </div>
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
