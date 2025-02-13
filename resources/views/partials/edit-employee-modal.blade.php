<div class="modal" tabindex="-1" class="modal fade" id="edit-employee-modal{{$data->id}}" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('editemployee', $data->id)}}">
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
                                <label for="Nama" class="form-label">Nama : </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="Nama" class="form-control" id="Nama" value="{{$data->Nama}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="Dept" class="form-label">Dept :</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="Dept" class="form-control" id="Dept" value="{{$data->Dept}}"> 
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="status" class="form-label">Status :</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="status" class="form-control" id="status" value="{{$data->status}}">
                            </div>
                        </div>
                    </div>
                     <!-- New End Date Field -->
                     <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="end_date" class="form-label">End Date :</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="date" name="end_date" class="form-control" id="end_date" value="{{$data->end_date}}">
                            </div>
                        </div>
                    </div>
                    <!-- New Jatah Cuti Tahun Field -->
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="jatah_cuti_taun" class="form-label">Jatah Cuti Tahun :</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="number" name="jatah_cuti_taun" class="form-control" id="jatah_cuti_taun" value="{{$data->jatah_cuti_taun}}">
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
