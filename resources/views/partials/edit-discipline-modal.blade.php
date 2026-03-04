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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl overflow-hidden glass-card">
            <form method="POST" id="edit-discipline-form" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                {{-- Premium Header --}}
                <div class="modal-header border-b border-slate-100 bg-slate-50/50 relative px-6 py-5">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                            <i class="bx bx-edit text-xl"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-slate-800 m-0" id="editDisciplineModalLabel">Lembar Penilaian</h5>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Berikan nilai performa karyawan</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close text-reset bg-slate-200 hover:bg-slate-300 rounded-lg p-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Loading Spinner --}}
                <div id="discipline-modal-loader" class="text-center py-10">
                    <div class="spinner-border text-indigo-500" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 font-medium">Mengambil data...</p>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-6" id="discipline-modal-content" style="display: none;">
                    
                    {{-- Employee Summary Container --}}
                    <div class="bg-slate-50 rounded-2xl border border-slate-100 p-4 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 font-bold text-lg shadow-sm">
                                <i class="bx bx-user"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-slate-800 m-0 text-lg" id="discipline-modal-employee-name"></h6>
                                <span class="badge bg-indigo-100 text-indigo-700 border border-indigo-200 mt-1" id="discipline-modal-type-badge"></span>
                            </div>
                        </div>
                        
                        {{-- Absence Mini-Cards --}}
                        <div class="flex items-center gap-2">
                            <div class="text-center px-3 py-1.5 bg-white rounded-lg border border-slate-200 shadow-sm min-w-[60px]">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Alpha</div>
                                <div class="font-bold text-rose-600" id="dm-alpha">0</div>
                            </div>
                            <div class="text-center px-3 py-1.5 bg-white rounded-lg border border-slate-200 shadow-sm min-w-[60px]">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Telat</div>
                                <div class="font-bold text-amber-600" id="dm-telat">0</div>
                            </div>
                            <div class="text-center px-3 py-1.5 bg-white rounded-lg border border-slate-200 shadow-sm min-w-[60px]">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Izin</div>
                                <div class="font-bold text-sky-600" id="dm-izin">0</div>
                            </div>
                            <div class="text-center px-3 py-1.5 bg-white rounded-lg border border-slate-200 shadow-sm min-w-[60px]">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Sakit</div>
                                <div class="font-bold text-indigo-600" id="dm-sakit">0</div>
                            </div>
                        </div>
                    </div>

                    {{-- NEW SYSTEM: 9 fields (Yayasan / Magang) --}}
                    <div id="dm-new-fields" style="display: none;">
                        <h6 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="bx bx-check-double text-indigo-500"></i> Kriteria Penilaian Dasar
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
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
                            <div>
                                <label for="new_{{ $field }}" class="form-label text-xs font-semibold text-slate-600">{{ $label }}</label>
                                <input type="text" maxlength="1" name="{{ $field }}"
                                    id="new_{{ $field }}"
                                    class="form-control text-center font-bold text-indigo-700 text-lg uppercase discipline-grade-input focus:ring-2 focus:ring-indigo-500/20 py-2"
                                    placeholder="A–E" pattern="[A-Ea-e]" autocomplete="off">
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- OLD SYSTEM: 5 fields (Regular) --}}
                    <div id="dm-old-fields" style="display: none;">
                        <h6 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="bx bx-check-double text-indigo-500"></i> Kriteria Penilaian Khusus
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 border border-slate-100 rounded-xl p-4 bg-white shadow-sm">
                            @foreach ([
                                'kerajinan_kerja' => 'Kinerja Kerja',
                                'kerapian_kerja'  => 'Kerapian',
                                'prestasi'        => 'Prestasi',
                                'loyalitas'       => 'Loyalitas',
                                'perilaku_kerja'  => 'Etika & Kesopanan',
                            ] as $field => $label)
                            <div>
                                <label for="old_{{ $field }}" class="form-label text-xs font-semibold text-slate-600">{{ $label }}</label>
                                <input type="text" maxlength="1" name="{{ $field }}"
                                    id="old_{{ $field }}"
                                    class="form-control text-center font-bold text-indigo-700 text-lg uppercase discipline-grade-input focus:ring-2 focus:ring-indigo-500/20 py-2"
                                    placeholder="A–E" pattern="[A-Ea-e]" autocomplete="off">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-t border-slate-100 bg-slate-50/50 px-6 py-4" id="discipline-modal-footer" style="display: none;">
                    <button type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 transition-all shadow-sm flex items-center gap-2">
                        <i class="bx bx-save"></i> Simpan Nilai
                    </button>
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
        axios.get('/evaluation/' + id + '/data')
            .then(({ data }) => {
                populateModal(data);
                hideLoader();
            })
            .catch((err) => {
                hideLoader();
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        title: 'Gagal Memuat Data',
                        message: 'Terjadi kesalahan saat mengambil evaluasi.',
                        type: 'error'
                    }
                }));
                // Fallback direct window object if Alpine listener missed it
                if (window.toastManager) window.toastManager.show({ title: 'Gagal', message: 'Data evaluasi tidak ditemukan.', type: 'error'});

                bootstrap.Modal.getInstance(modal).hide();
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
        }).then(({ data }) => {
            bootstrap.Modal.getInstance(modal).hide();
            
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    title: 'Berhasil',
                    message: data.message || 'Nilai evaluasi berhasil disimpan.',
                    type: 'success'
                }
            }));
            // Fallback just in case
            if (window.toastManager && !window.Alpine) window.toastManager.show({title: 'Berhasil', message: 'Tersimpan', type: 'success'});

            // Reload the visible DataTable & update status chips defined in index.blade.php
            if (typeof window.reloadEvaluationTables === 'function') {
                window.reloadEvaluationTables();
            } else {
                document.querySelectorAll('table.dataTable').forEach(t => {
                    const dtInstance = $.fn.dataTable.Api ? new $.fn.dataTable.Api(t) : null;
                    if (dtInstance) dtInstance.ajax.reload(null, false);
                });
            }
        }).catch(err => {
            console.error('Save failed', err);
            console.error('Save failed', err);
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    title: 'Gagal Menyimpan',
                    message: 'Terjadi kesalahan saat menyimpan nilai. Silahkan coba lagi.',
                    type: 'error'
                }
            }));
            form.classList.remove('was-validated');
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
