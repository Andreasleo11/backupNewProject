<div class="modal fade" id="edit-discipline-magang-modal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="edit-discipline-magang-form" class="needs-validation">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Discipline Point For <strong id="edit-magang-employee-name"></strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Loading Spinner -->
                <div id="edit-magang-modal-loader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Fetching data...</p>
                </div>
                <!-- Form Content -->
                <div class="modal-body" id="edit-magang-modal-content" style="display: none;">
                    <div class="text-center mb-4">
                        <h1 class="mb-0 text-lg font-semibold">Beri nilai mulai dari A hingga E</h1>
                    </div>
                    
                    <div class="form-group mt-4">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label text-sm">
                                <label for="kemampuan_kerja" class="form-label mb-0"> Kemampuan Kerja : </label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" maxlength="1" name="kemampuan_kerja" class="form-control"
                                    id="kemampuan_kerja" pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label text-sm">
                                <label for="kecerdasan_kerja" class="form-label mb-0"> Kecerdasan Kerja :</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" maxlength="1" name="kecerdasan_kerja" class="form-control"
                                    id="kecerdasan_kerja" pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label text-sm">
                                <label for="qualitas_kerja" class="form-label mb-0">Qualitas Kerja:</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" maxlength="1" name="qualitas_kerja" class="form-control"
                                    id="qualitas_kerja" pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label text-sm">
                                <label for="disiplin_kerja" class="form-label mb-0">Disiplin Kerja:</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" maxlength="1" name="disiplin_kerja" class="form-control"
                                    id="disiplin_kerja" pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label text-sm">
                                <label for="kepatuhan_kerja" class="form-label mb-0">Kepatuhan Kerja :</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" maxlength="1" name="kepatuhan_kerja" class="form-control"
                                    id="kepatuhan_kerja" pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label text-sm">
                                <label for="lembur" class="form-label mb-0">Lembur :</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" maxlength="1" name="lembur" class="form-control" id="lembur"
                                    pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label text-sm">
                                <label for="efektifitas_kerja" class="form-label mb-0">Efektifitas Kerja :</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" maxlength="1" name="efektifitas_kerja" class="form-control"
                                    id="efektifitas_kerja" pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label text-sm">
                                <label for="relawan" class="form-label mb-0">Relawan :</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" maxlength="1" name="relawan" class="form-control"
                                    id="relawan" pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label text-sm">
                                <label for="integritas" class="form-label mb-0">Integritas :</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" maxlength="1" name="integritas" class="form-control"
                                    id="integritas" pattern="[A-E]" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="edit-magang-modal-footer" style="display: none;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('edit-discipline-magang-modal');
        const form = document.getElementById('edit-discipline-magang-form');
        const loader = document.getElementById('edit-magang-modal-loader');
        const content = document.getElementById('edit-magang-modal-content');
        const footer = document.getElementById('edit-magang-modal-footer');

        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');

            // Reset UI
            form.reset();
            content.style.display = 'none';
            footer.style.display = 'none';
            loader.style.display = 'block';
            document.getElementById('edit-magang-employee-name').textContent = '';

            // Set action dynamically to Magang Update Route
            form.action = `/discipline/magang/update/${id}`;

            axios.get(`/evaluationDatas/${id}`)
                .then(response => {
                    const data = response.data;
                    document.getElementById('edit-magang-employee-name').textContent = data.karyawan?.Nama || data.karyawan?.name || 'Unknown';

                    const fields = [
                        'kemampuan_kerja', 'kecerdasan_kerja', 'qualitas_kerja', 'disiplin_kerja',
                        'kepatuhan_kerja', 'lembur', 'efektifitas_kerja', 'relawan', 'integritas'
                    ];

                    fields.forEach(field => {
                        const input = document.getElementById(field);
                        if (input) {
                            input.value = data[field] || 'C';
                            input.classList.remove('is-invalid', 'is-valid');
                        }
                    });

                    loader.style.display = 'none';
                    content.style.display = 'block';
                    footer.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error fetching magang data:', error);
                    loader.innerHTML = '<div class="alert alert-danger">Failed to load data. Please try again.</div>';
                });
        });

        const inputs = document.querySelectorAll('#edit-magang-modal-content .form-control');

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
    #edit-magang-modal-content .is-valid,
    #edit-magang-modal-content .is-invalid {
        border-width: 2px;
    }
</style>
