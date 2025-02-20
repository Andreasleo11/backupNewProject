<div class="modal fade" id="edit-discipline-yayasan-modal-{{ str_replace(' ', '',$employee->id) }}" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('updateyayasan', $employee->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Lembar Penilaian Untuk {{$employee->karyawan->Nama}} {{$employee->NIK}} {{$employee->department->name}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h1 class="mb-0">Beri nilai mulai dari A hingga E</h1>
                    </div>

                    <!-- Absence Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Alpha</th>
                                    <th>Telat</th>
                                    <th>Izin</th>
                                    <th>Sakit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $employee->Alpha ?? 0 }}</td>
                                    <td>{{ $employee->Telat ?? 0 }}</td>
                                    <td>{{ $employee->Izin ?? 0 }}</td>
                                    <td>{{ $employee->Sakit ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Create a 3-column grid layout -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kemampuan_kerja" class="form-label">Kemampuan Kerja:</label>
                                <input type="text" maxlength="1" name="kemampuan_kerja" class="form-control" id="kemampuan_kerja" pattern="[A-E]" value="{{$employee->kemampuan_kerja}}">
                            </div>
                            <div class="form-group mt-3">
                                <label for="kecerdasan_kerja" class="form-label">Kecerdasan Kerja:</label>
                                <input type="text" maxlength="1" name="kecerdasan_kerja" class="form-control" id="kecerdasan_kerja" pattern="[A-E]" value="{{$employee->kecerdasan_kerja}}">
                            </div>
                            <div class="form-group mt-3">
                                <label for="qualitas_kerja" class="form-label">Kualitas Kerja:</label>
                                <input type="text" maxlength="1" name="qualitas_kerja" class="form-control" id="qualitas_kerja" pattern="[A-E]" value="{{$employee->qualitas_kerja}}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="disiplin_kerja" class="form-label">Disiplin Kerja:</label>
                                <input type="text" maxlength="1" name="disiplin_kerja" class="form-control" id="disiplin_kerja" pattern="[A-E]" value="{{$employee->disiplin_kerja}}">
                            </div>
                            <div class="form-group mt-3">
                                <label for="kepatuhan_kerja" class="form-label">Kepatuhan Kerja:</label>
                                <input type="text" maxlength="1" name="kepatuhan_kerja" class="form-control" id="kepatuhan_kerja" pattern="[A-E]" value="{{$employee->kepatuhan_kerja}}">
                            </div>
                            <div class="form-group mt-3">
                                <label for="lembur" class="form-label">Lembur:</label>
                                <input type="text" maxlength="1" name="lembur" class="form-control" id="lembur" pattern="[A-E]" value="{{$employee->lembur}}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="efektifitas_kerja" class="form-label">Efektifitas Kerja:</label>
                                <input type="text" maxlength="1" name="efektifitas_kerja" class="form-control" id="efektifitas_kerja" pattern="[A-E]" value="{{$employee->efektifitas_kerja}}">
                            </div>
                            <div class="form-group mt-3">
                                <label for="relawan" class="form-label">Ringan Tangan:</label>
                                <input type="text" maxlength="1" name="relawan" class="form-control" id="relawan" pattern="[A-E]" value="{{$employee->relawan}}">
                            </div>
                            <div class="form-group mt-3">
                                <label for="integritas" class="form-label">Integritas:</label>
                                <input type="text" maxlength="1" name="integritas" class="form-control" id="integritas" pattern="[A-E]" value="{{$employee->integritas}}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
