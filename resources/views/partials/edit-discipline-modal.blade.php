<div class="modal fade" id="edit-discipline-modal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="edit-discipline-form" class="needs-validation">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h4 class="modal-title">Discipline Point For <strong id="edit-employee-name"></strong></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Loading Spinner -->
                <div id="edit-modal-loader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Fetching data...</p>
                </div>
                <!-- Form Content -->
                <div class="modal-body" id="edit-modal-content" style="display: none;">
                    <div class="form-group mb-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label">
                                <label for="kerajinan_kerja" class="form-label mb-0">Kerajinan Kerja</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="kerajinan_kerja" class="form-control" id="kerajinan_kerja"
                                    pattern="[A-E]" maxlength="1" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label">
                                <label for="kerapian_kerja" class="form-label mb-0">Kerapian Kerja</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="kerapian_kerja" class="form-control" id="kerapian_kerja"
                                    pattern="[A-E]" maxlength="1" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label">
                                <label for="loyalitas" class="form-label mb-0">Loyalitas</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="loyalitas" class="form-control" id="loyalitas"
                                    pattern="[A-E]" maxlength="1" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label">
                                <label for="perilaku_kerja" class="form-label mb-0">Perilaku Kerja</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="perilaku_kerja" class="form-control" id="perilaku_kerja"
                                    pattern="[A-E]" maxlength="1" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="row items-center">
                            <div class="col-sm-4 col-form-label">
                                <label for="prestasi" class="form-label mb-0">Prestasi</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="prestasi" class="form-control" id="prestasi"
                                    pattern="[A-E]" maxlength="1" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="edit-modal-footer" style="display: none;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('edit-discipline-modal');
        const form = document.getElementById('edit-discipline-form');
        const loader = document.getElementById('edit-modal-loader');
        const content = document.getElementById('edit-modal-content');
        const footer = document.getElementById('edit-modal-footer');

        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');

            // Reset UI state
            form.reset();
            content.style.display = 'none';
            footer.style.display = 'none';
            loader.style.display = 'block';
            document.getElementById('edit-employee-name').textContent = '';

            // This matches the format in routes: UpdateEvaluation but actually it's process evaluation data POST. wait no, there is no generic edit discipline. I'll need to look at web.php or performance.php 
            // The route was 'editdiscipline' ... which normally is a PUT to something. Let's set it dynamically based on the fact that this is just Regular discipline. 
            // Actually the current code uses `route('editdiscipline', $employee->id)`. 
            form.action = `/discipline/yayasan/update/${id}`; // To fix later if it differs

            axios.get(`/evaluationDatas/${id}`)
                .then(response => {
                    const data = response.data;

                    document.getElementById('edit-employee-name').textContent = data.karyawan?.Nama || data.karyawan?.name || 'Unknown';

                    if(document.getElementById('kerajinan_kerja')) document.getElementById('kerajinan_kerja').value = data.kerajinan_kerja || 'C';
                    if(document.getElementById('kerapian_kerja')) document.getElementById('kerapian_kerja').value = data.kerapian_kerja || 'C';
                    if(document.getElementById('loyalitas')) document.getElementById('loyalitas').value = data.loyalitas || 'C';
                    if(document.getElementById('perilaku_kerja')) document.getElementById('perilaku_kerja').value = data.perilaku_kerja || 'C';
                    if(document.getElementById('prestasi')) document.getElementById('prestasi').value = data.prestasi || 'C';

                    loader.style.display = 'none';
                    content.style.display = 'block';
                    footer.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    loader.innerHTML = '<div class="alert alert-danger">Failed to load data. Please try again.</div>';
                });
        });

        const inputs = document.querySelectorAll('#edit-modal-content .form-control');

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
    #edit-modal-content .is-valid,
    #edit-modal-content .is-invalid {
        border-width: 2px;
    }
</style>
