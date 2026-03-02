<div class="modal fade" id="edit-discipline-yayasan-modal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="edit-form" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Lembar Penilaian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Loading Spinner -->
                <div id="edit-yayasan-modal-loader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Fetching data...</p>
                </div>
                <!-- Form Content -->
                <div class="modal-body" id="edit-yayasan-modal-content" style="display: none;">
                    <div class="text-center mb-4">
                        <h1 class="mb-0 text-lg font-semibold">Beri nilai mulai dari A hingga E</h1>
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
                                    <td id="employee-alpha">0</td>
                                    <td id="employee-telat">0</td>
                                    <td id="employee-izin">0</td>
                                    <td id="employee-sakit">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 3-column grid layout -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kemampuan_kerja" class="form-label">Kemampuan Kerja:</label>
                                <input type="text" maxlength="1" name="kemampuan_kerja" class="form-control"
                                    id="kemampuan_kerja" pattern="[A-E]" required>
                            </div>
                            <div class="form-group mt-3">
                                <label for="kecerdasan_kerja" class="form-label">Kecerdasan Kerja:</label>
                                <input type="text" maxlength="1" name="kecerdasan_kerja" class="form-control"
                                    id="kecerdasan_kerja" pattern="[A-E]" required>
                            </div>
                            <div class="form-group mt-3">
                                <label for="qualitas_kerja" class="form-label">Kualitas Kerja:</label>
                                <input type="text" maxlength="1" name="qualitas_kerja" class="form-control"
                                    id="qualitas_kerja" pattern="[A-E]" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="disiplin_kerja" class="form-label">Disiplin Kerja:</label>
                                <input type="text" maxlength="1" name="disiplin_kerja" class="form-control"
                                    id="disiplin_kerja" pattern="[A-E]" required>
                            </div>
                            <div class="form-group mt-3">
                                <label for="kepatuhan_kerja" class="form-label">Kepatuhan Kerja:</label>
                                <input type="text" maxlength="1" name="kepatuhan_kerja" class="form-control"
                                    id="kepatuhan_kerja" pattern="[A-E]" required>
                            </div>
                            <div class="form-group mt-3">
                                <label for="lembur" class="form-label">Lembur:</label>
                                <input type="text" maxlength="1" name="lembur" class="form-control" id="lembur"
                                    pattern="[A-E]" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="efektifitas_kerja" class="form-label">Efektifitas Kerja:</label>
                                <input type="text" maxlength="1" name="efektifitas_kerja" class="form-control"
                                    id="efektifitas_kerja" pattern="[A-E]" required>
                            </div>
                            <div class="form-group mt-3">
                                <label for="relawan" class="form-label">Ringan Tangan:</label>
                                <input type="text" maxlength="1" name="relawan" class="form-control" id="relawan"
                                    pattern="[A-E]" required>
                            </div>
                            <div class="form-group mt-3">
                                <label for="integritas" class="form-label">Integritas:</label>
                                <input type="text" maxlength="1" name="integritas" class="form-control"
                                    id="integritas" pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" id="edit-yayasan-modal-footer" style="display: none;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('edit-form');
        const inputs = form.querySelectorAll('.form-control[pattern]');

        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-E]/g, '');
                if (this.value && /^[A-E]$/.test(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });

        form.addEventListener('submit', function(event) {
            let isValid = true;
            inputs.forEach(input => {
                if (!input.value || !/^[A-E]$/.test(input.value)) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                }
            });

            if (!isValid || form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
</script>

<style>
    #edit-yayasan-modal-content .is-valid,
    #edit-yayasan-modal-content .is-invalid {
        border-width: 2px;
    }
</style>
