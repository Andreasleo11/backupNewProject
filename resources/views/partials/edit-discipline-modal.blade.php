<div class="modal" tabindex="-1" class="modal fade" id="edit-discipline-modal-{{ str_replace(' ', '', $employee->id) }}"
    aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('editdiscipline', $employee->id) }}" class="needs-validation">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h4 class="modal-title">Discipline Point For <strong> {{ $employee->karyawan->Nama }}
                        </strong>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-4 col-form-label">
                                <label for="kerajinan_kerja" class="form-label">Kerajinan Kerja</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="kerajinan_kerja" class="form-control" id="kerajinan_kerja"
                                    pattern="[A-E]" maxlength="1" value="{{ $employee->kerajinan_kerja }}" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                    <!-- Repeat similar structure for other inputs -->
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-4 col-form-label">
                                <label for="kerapian_kerja" class="form-label">Kerapian Kerja</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="kerapian_kerja" class="form-control" id="kerapian_kerja"
                                    pattern="[A-E]" maxlength="1" value="{{ $employee->kerapian_kerja }}" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-4 col-form-label">
                                <label for="loyalitas" class="form-label">Loyalitas</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="loyalitas" class="form-control" id="loyalitas"
                                    pattern="[A-E]" maxlength="1" value="{{ $employee->loyalitas }}" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-4 col-form-label">
                                <label for="perilaku_kerja" class="form-label">Perilaku Kerja</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="perilaku_kerja" class="form-control" id="perilaku_kerja"
                                    pattern="[A-E]" maxlength="1" value="{{ $employee->perilaku_kerja }}" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-4 col-form-label">
                                <label for="prestasi" class="form-label">Prestasi</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="prestasi" class="form-control" id="prestasi"
                                    pattern="[A-E]" maxlength="1" value="{{ $employee->prestasi }}" required
                                    oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the form element
        var form = document.querySelector('.needs-validation');

        // Get all input elements with class 'form-control'
        const inputs = document.querySelectorAll('.form-control');

        inputs.forEach(input => {
            // Ensure value is uppercase and only A-E
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-E]/g, '');
            });
        });

        // Form submission validation
        form.addEventListener('submit', function(event) {
            // Check if the form is valid using Bootstrap's validation
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }

            let isValid = true;

            inputs.forEach(input => {
                // Check if input is empty or invalid
                if (!input.value || !/^[A-E]$/.test(input.value)) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                } else {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                }
            });

            if (!isValid) {
                event.preventDefault(); // Prevent form submission if any input is invalid
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });
</script>

<style>
    .is-valid,
    .is-invalid {
        border-width: 2px;
    }
</style>
