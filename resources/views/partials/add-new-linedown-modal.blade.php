<div class="modal" tabindex="-1" class="modal fade" id="add-new-line" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('addline')}}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="line_code" class="form-label">Line Code: </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="line_code" class="form-control" id="line_code">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="date_down" class="form-label">Date Down:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="date" name="date_down" class="form-control" id="date_down">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="date_prediction" class="form-label">Date Prediction:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="date" name="date_prediction" class="form-control" id="date_prediction">
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
