<div class="modal" tabindex="-1" class="modal fade" id="edit-discipline-modal-{{ str_replace(' ', '',$employee->id) }}" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('editdiscipline', $employee->id)}}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Discipline Point For {{$employee->karyawan->Nama}} </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="kerajinan_kerja" class="form-label"> Kerajinan Kerja : </label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="kerajinan_kerja" class="form-control" id="kerajinan_kerja" value="{{$employee->kerajinan_kerja}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="kerapian_pakaian" class="form-label">Kerapian Pakaian:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="kerapian_pakaian" class="form-control" id="kerapian_pakaian" value="{{$employee->kerapian_pakaian}}"> 
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="kerapian_rambut" class="form-label">Kerapian Rambut:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="kerapian_rambut" class="form-control" id="kerapian_rambut" value="{{$employee->kerapian_rambut}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="kerapian_sepatu" class="form-label">Kerapian Sepatu:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="kerapian_sepatu" class="form-control" id="kerapian_sepatu" value="{{$employee->kerapian_sepatu}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="prestasi" class="form-label">Prestasi :</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="prestasi" class="form-control" id="prestasi" value="{{$employee->prestasi}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="row">
                            <div class="col-sm-3 col-form-label">
                                <label for="loyalitas" class="form-label">Loyalitas:</label>
                            </div>
                            <div class="col-sm-9">
                                <input type="text" name="loyalitas" class="form-control" id="loyalitas" value="{{$employee->loyalitas}}">
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
