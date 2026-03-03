{{--
    Unified Discipline Edit Modal
    ─────────────────────────────
    One modal for Regular, Yayasan, and Magang evaluations.
    Triggered by the 'edit-discipline-btn' buttons in DisciplineDataTable.

    The button passes:
      - data-id             → evaluation record ID (for AJAX fetch)
      - data-update-url     → full PUT URL (route + id, pre-built in DataTable)

    The JS below:
      1. Listens for the modal trigger button click
      2. Shows the loading spinner
      3. Fetches /evaluation/{id} (getEvaluationData route)
      4. Determines type from the response (employment_scheme)
      5. Shows the correct set of fields, hides the other
      6. Populates the form, sets the form action
      7. Submits via AJAX on Save
--}}
<div class="modal fade" id="edit-discipline-modal" tabindex="-1" aria-labelledby="editDisciplineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="edit-discipline-form" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="editDisciplineModalLabel">Lembar Penilaian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Loading Spinner --}}
                <div id="discipline-modal-loader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Fetching data...</p>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body" id="discipline-modal-content" style="display: none;">
                    <div class="text-center mb-3">
                        <h6 class="text-muted" id="discipline-modal-employee-name"></h6>
                        <small class="badge bg-secondary" id="discipline-modal-type-badge"></small>
                    </div>

                    {{-- Absence summary (read-only) --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered text-center table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Alpha</th><th>Telat</th><th>Izin</th><th>Sakit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="dm-alpha">0</td>
                                    <td id="dm-telat">0</td>
                                    <td id="dm-izin">0</td>
                                    <td id="dm-sakit">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- NEW SYSTEM: 9 fields (Yayasan / Magang) --}}
                    <div id="dm-new-fields" style="display: none;">
                        <p class="text-muted small mb-3">Beri nilai dari A hingga E</p>
                        <div class="row g-3">
                            @foreach ([
                                'kemampuan_kerja'   => 'Kemampuan Kerja',
                                'kecerdasan_kerja'  => 'Kecerdasan Kerja',
                                'qualitas_kerja'    => 'Kualitas Kerja',
                                'disiplin_kerja'    => 'Disiplin Kerja',
                                'kepatuhan_kerja'   => 'Kepatuhan Kerja',
                                'lembur'            => 'Lembur',
                                'efektifitas_kerja' => 'Efektifitas Kerja',
                                'relawan'           => 'Ringan Tangan',
                                'integritas'        => 'Integritas',
                            ] as $field => $label)
                            <div class="col-md-4">
                                <label for="new_{{ $field }}" class="form-label">{{ $label }}</label>
                                <input type="text" maxlength="1" name="{{ $field }}"
                                    id="new_{{ $field }}"
                                    class="form-control text-uppercase discipline-grade-input"
                                    placeholder="A–E" pattern="[A-Ea-e]">
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- OLD SYSTEM: 5 fields (Regular) --}}
                    <div id="dm-old-fields" style="display: none;">
                        <p class="text-muted small mb-3">Beri nilai dari A hingga E</p>
                        <div class="row g-3">
                            @foreach ([
                                'kerajinan_kerja' => 'Kinerja Kerja',
                                'kerapian_kerja'  => 'Kerapian',
                                'prestasi'        => 'Prestasi',
                                'loyalitas'       => 'Loyalitas',
                                'perilaku_kerja'  => 'Etika & Kesopanan',
                            ] as $field => $label)
                            <div class="col-md-4">
                                <label for="old_{{ $field }}" class="form-label">{{ $label }}</label>
                                <input type="text" maxlength="1" name="{{ $field }}"
                                    id="old_{{ $field }}"
                                    class="form-control text-uppercase discipline-grade-input"
                                    placeholder="A–E" pattern="[A-Ea-e]">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer" id="discipline-modal-footer" style="display: none;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const modal        = document.getElementById('edit-discipline-modal');
    const form         = document.getElementById('edit-discipline-form');
    const loader       = document.getElementById('discipline-modal-loader');
    const content      = document.getElementById('discipline-modal-content');
    const footer       = document.getElementById('discipline-modal-footer');
    const newFields    = document.getElementById('dm-new-fields');
    const oldFields    = document.getElementById('dm-old-fields');
    const nameBadge    = document.getElementById('discipline-modal-employee-name');
    const typeBadge    = document.getElementById('discipline-modal-type-badge');

    // ── Grade input validation: force A–E uppercase ──────────
    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('discipline-grade-input')) return;
        e.target.value = e.target.value.toUpperCase().replace(/[^A-E]/g, '');
        const valid = /^[A-E]$/.test(e.target.value);
        e.target.classList.toggle('is-valid', valid);
        e.target.classList.toggle('is-invalid', !valid);
    });

    // ── Trigger: any edit button in DataTable ────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.edit-discipline-btn');
        if (!btn) return;

        const id        = btn.dataset.id;
        const updateUrl = btn.dataset.updateUrl;

        // Reset state
        showLoader();
        form.action = updateUrl;
        form.classList.remove('was-validated');

        // Fetch evaluation data via AJAX
        axios.get('/api/evaluation/' + id)
            .then(({ data }) => {
                populateModal(data);
                hideLoader();
            })
            .catch(() => {
                content.innerHTML = '<div class="alert alert-danger">Failed to load data. Please try again.</div>';
                hideLoader();
            });
    });

    // ── Form submission via AJAX ─────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const inputs = form.querySelectorAll('.discipline-grade-input:not([style*="display: none"] *)');
        let valid = true;

        inputs.forEach(input => {
            if (!/^[A-E]$/.test(input.value)) {
                input.classList.add('is-invalid');
                valid = false;
            }
        });

        if (!valid) {
            form.classList.add('was-validated');
            return;
        }

        const data = new FormData(form);

        axios.post(form.action, data, {
            headers: { 'X-HTTP-Method-Override': 'PUT' }
        }).then(() => {
            bootstrap.Modal.getInstance(modal).hide();
            // Reload the visible DataTable
            document.querySelectorAll('table.dataTable').forEach(t => {
                const dtInstance = $.fn.dataTable.Api ? new $.fn.dataTable.Api(t) : null;
                if (dtInstance) dtInstance.ajax.reload(null, false);
            });
        }).catch(err => {
            console.error('Save failed', err);
        });
    });

    // ── Helpers ──────────────────────────────────────────────

    function showLoader() {
        loader.style.display  = '';
        content.style.display = 'none';
        footer.style.display  = 'none';
    }

    function hideLoader() {
        loader.style.display  = 'none';
        content.style.display = '';
        footer.style.display  = '';
    }

    function populateModal(data) {
        // Employee info
        nameBadge.textContent = data.karyawan?.Nama ?? data.karyawan?.name ?? '—';

        // Absence (read-only)
        document.getElementById('dm-alpha').textContent = data.Alpha ?? 0;
        document.getElementById('dm-telat').textContent = data.Telat ?? 0;
        document.getElementById('dm-izin').textContent  = data.Izin  ?? 0;
        document.getElementById('dm-sakit').textContent = data.Sakit ?? 0;

        // Determine scoring type
        const scheme = data.karyawan?.employment_scheme ?? '';
        const isNew  = scheme.includes('YAYASAN') || scheme.includes('MAGANG');

        typeBadge.textContent = isNew ? scheme : 'Regular';

        if (isNew) {
            newFields.style.display = '';
            oldFields.style.display = 'none';
            // Populate new-system fields
            ['kemampuan_kerja','kecerdasan_kerja','qualitas_kerja','disiplin_kerja',
             'kepatuhan_kerja','lembur','efektifitas_kerja','relawan','integritas']
                .forEach(f => {
                    const el = document.getElementById('new_' + f);
                    if (el) el.value = data[f] ?? '';
                });
        } else {
            newFields.style.display = 'none';
            oldFields.style.display = '';
            // Populate old-system fields
            ['kerajinan_kerja','kerapian_kerja','prestasi','loyalitas','perilaku_kerja']
                .forEach(f => {
                    const el = document.getElementById('old_' + f);
                    if (el) el.value = data[f] ?? '';
                });
        }
    }
})();
</script>

<style>
#discipline-modal-content .is-valid,
#discipline-modal-content .is-invalid {
    border-width: 2px;
}
</style>
