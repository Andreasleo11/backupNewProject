<div class="modal" tabindex="-1" class="modal fade" id="edit-line-modal{{ str_replace(' ', '',$data->line_code) }}" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('editline', $data->line_code)}}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Line</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="line_code" class="form-label">Line Code: </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="line_code" class="form-control" id="line_code" value="{{$data->line_code}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="line_name" class="form-label">Line Name:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="line_name" class="form-control" id="line_name" value="{{$data->line_name}}"> 
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="departement" class="form-label">Department:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="departement" class="form-control" id="departement" value="{{$data->departement}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="daily_minutes" class="form-label">Daily Minutes:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="daily_minutes" class="form-control" id="daily_minutes" value="{{$data->daily_minutes}}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">edit</button>
                </div>
            </form>
        </div>
    </div>
</div>
