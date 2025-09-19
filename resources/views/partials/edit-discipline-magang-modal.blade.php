<div class="modal" tabindex="-1" class="modal fade"
  id="edit-discipline-magang-modal-{{ str_replace(' ', '', $employee->id) }}"
  aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('updatemagang', $employee->id) }}">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Discipline Point For {{ $employee->karyawan->Nama }}
            {{ $employee->id }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-4">
            <h1 class="mb-0">Beri nilai mulai dari A hingga E</h1>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="kemampuan_kerja" class="form-label"> Kemampuan Kerja : </label>
              </div>
              <div class="col-sm-9">
                <input type="text" maxlength="1" name="kemampuan_kerja" class="form-control"
                  id="kemampuan_kerja" pattern="[A-E]" value="{{ $employee->kemampuan_kerja }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="kecerdasan_kerja" class="form-label"> Kecerdasan Kerja :</label>
              </div>
              <div class="col-sm-9">
                <input type="text" maxlength="1" name="kecerdasan_kerja" class="form-control"
                  id="kecerdasan_kerja" pattern="[A-E]" value="{{ $employee->kecerdasan_kerja }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="qualitas_kerja" class="form-label">Qualitas Kerja:</label>
              </div>
              <div class="col-sm-9">
                <input type="text" maxlength="1" name="qualitas_kerja" class="form-control"
                  id="qualitas_kerja" pattern="[A-E]" value="{{ $employee->qualitas_kerja }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="disiplin_kerja" class="form-label">Disiplin Kerja:</label>
              </div>
              <div class="col-sm-9">
                <input type="text" maxlength="1" name="disiplin_kerja" class="form-control"
                  id="disiplin_kerja" pattern="[A-E]" value="{{ $employee->disiplin_kerja }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="kepatuhan_kerja" class="form-label">Kepatuhan Kerja :</label>
              </div>
              <div class="col-sm-9">
                <input type="text" maxlength="1" name="kepatuhan_kerja" class="form-control"
                  id="kepatuhan_kerja" pattern="[A-E]" value="{{ $employee->kepatuhan_kerja }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="lembur" class="form-label">Lembur :</label>
              </div>
              <div class="col-sm-9">
                <input type="text" maxlength="1" name="lembur" class="form-control"
                  id="lembur" pattern="[A-E]" value="{{ $employee->lembur }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="efektifitas_kerja" class="form-label">Efektifitas Kerja :</label>
              </div>
              <div class="col-sm-9">
                <input type="text" maxlength="1" name="efektifitas_kerja"
                  class="form-control" id="efektifitas_kerja" pattern="[A-E]"
                  value="{{ $employee->efektifitas_kerja }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="relawan" class="form-label">Relawan :</label>
              </div>
              <div class="col-sm-9">
                <input type="text" maxlength="1" name="relawan" class="form-control"
                  id="relawan" pattern="[A-E]" value="{{ $employee->relawan }}">
              </div>
            </div>
          </div>
          <div class="form-group mt-4">
            <div class="row">
              <div class="col-sm-3 col-form-label">
                <label for="integritas" class="form-label">Integritas :</label>
              </div>
              <div class="col-sm-9">
                <input type="text" maxlength="1" name="integritas" class="form-control"
                  id="integritas" pattern="[A-E]" value="{{ $employee->integritas }}">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
