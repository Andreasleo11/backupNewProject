{{--
    Unified Discipline Edit Modal (AlpineJS + Tailwind)
    ───────────────────────────────────────────────────
    Triggered via `window.dispatchEvent(new CustomEvent('open-evaluate-modal', {detail: {id, url}}))`
--}}
<div x-data="evaluationModal()" 
     x-show="isOpen" 
     @open-evaluate-modal.window="openModal($event.detail.id, $event.detail.url)"
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-[1050] flex items-center justify-center p-4 sm:p-6"
     style="display: none;"
     role="dialog"
     aria-modal="true">
     
    <!-- Backdrop -->
    <div x-show="isOpen" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" 
         @click="closeModal()"></div>

    <!-- Modal Panel -->
    <div x-show="isOpen" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
         style="max-height: calc(100dvh - 2.5rem);"
         class="relative w-full max-w-4xl flex flex-col transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all border border-slate-100">
         
        <form @submit.prevent="submitForm" class="flex flex-col h-full overflow-hidden w-full">
            
            {{-- Premium Header --}}
            <div class="relative px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex-none">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                            <i class="bx bx-edit text-xl"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-slate-800 m-0 text-lg">Lembar Penilaian</h5>
                            <p class="text-xs text-slate-500 m-0 mt-0.5">Berikan nilai performa karyawan</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showGuide = !showGuide" :class="showGuide ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-500 hover:bg-indigo-100'" class="border-none rounded-lg p-2 transition-colors focus:outline-none">
                            <i class="bx bx-info-circle text-2xl"></i>
                        </button>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-500 bg-slate-100 border-none hover:bg-slate-200 rounded-lg p-2 transition-colors focus:outline-none">
                            <i class="bx bx-x text-2xl"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Body (Scrollable) --}}
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                
                {{-- Loading Spinner --}}
                <div x-show="isLoading" class="text-center py-10">
                    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-indigo-500 border-r-transparent align-[-0.125em]" role="status">
                        <span class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]">Loading...</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 font-medium">Mengambil data...</p>
                </div>

                {{-- Content --}}
                <div x-show="!isLoading" style="display: none;">
                    
                    {{-- Employee Summary Container --}}
                    <div class="bg-slate-50 rounded-2xl border border-slate-100 p-4 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 font-bold text-lg shadow-sm shrink-0">
                                <i class="bx bx-user"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-slate-800 m-0 text-lg" x-text="record.name"></h6>
                                <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200" x-text="record.typeBadge"></span>
                            </div>
                        </div>
                        
                        {{-- Absence Mini-Cards --}}
                        <div class="flex items-center gap-2">
                            <div class="text-center px-3 py-1.5 bg-white rounded-lg border border-slate-200 shadow-sm min-w-[60px]">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Alpha</div>
                                <div class="font-bold text-rose-600" x-text="record.alpha">0</div>
                            </div>
                            <div class="text-center px-3 py-1.5 bg-white rounded-lg border border-slate-200 shadow-sm min-w-[60px]">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Telat</div>
                                <div class="font-bold text-amber-600" x-text="record.telat">0</div>
                            </div>
                            <div class="text-center px-3 py-1.5 bg-white rounded-lg border border-slate-200 shadow-sm min-w-[60px]">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Izin</div>
                                <div class="font-bold text-sky-600" x-text="record.izin">0</div>
                            </div>
                            <div class="text-center px-3 py-1.5 bg-white rounded-lg border border-slate-200 shadow-sm min-w-[60px]">
                                <div class="text-[10px] uppercase font-bold text-slate-400">Sakit</div>
                                <div class="font-bold text-indigo-600" x-text="record.sakit">0</div>
                            </div>
                        </div>
                    </div>

                    {{-- Grading Guide Popover (Collapsible) --}}
                    <div x-show="showGuide" x-collapse style="display: none;" class="mb-6">
                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 shadow-sm text-sm text-indigo-900 flex flex-col gap-4">
                            
                            {{-- Unified Absence Header --}}
                            <div>
                                <h6 class="font-bold mb-2 text-indigo-800 flex items-center gap-2"><i class='bx bx-time'></i> Penilaian Absensi (Otomatis)</h6>
                                <p class="mb-0 text-indigo-700">Total Poin Kehadiran Maksimal: <strong class="text-indigo-900">40</strong></p>
                                <ul class="list-disc pl-5 mt-1 space-y-0.5 text-indigo-700">
                                    <li>1 Alpha = <strong>-10 Poin</strong></li>
                                    <li>1 Izin = <strong>-2 Poin</strong></li>
                                    <li>1 Sakit = <strong>-1 Poin</strong></li>
                                    <li>1 Terlambat = <strong>-0.5 Poin</strong></li>
                                </ul>
                            </div>

                            <hr class="border-indigo-200/50 my-1">

                            {{-- NEW SYSTEM: Yayasan / Magang --}}
                            <div x-show="record.isNewSystem" style="display: none;">
                                <h6 class="font-bold mb-2 text-indigo-800 flex items-center gap-2"><i class='bx bx-bar-chart-alt-2'></i> Kriteria Nilai Alphabet (Sistem Baru)</h6>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                                    <div class="bg-white/60 p-3 rounded border border-indigo-100/50 text-xs">
                                        <p class="font-bold text-slate-700 mb-1">Kemampuan Kerja</p>
                                        <p class="mb-0">A=17, B=14, C=11, D=8, E=0</p>
                                    </div>
                                    <div class="bg-white/60 p-3 rounded border border-indigo-100/50 text-xs">
                                        <p class="font-bold text-slate-700 mb-1">Kecerdasan Kerja</p>
                                        <p class="mb-0">A=16, B=13, C=10, D=7, E=0</p>
                                    </div>
                                    <div class="bg-white/60 p-3 rounded border border-indigo-100/50 text-xs">
                                        <p class="font-bold text-slate-700 mb-1">Kualitas Kerja</p>
                                        <p class="mb-0">A=11, B=9, C=7, D=4, E=0</p>
                                    </div>
                                    <div class="bg-white/60 p-3 rounded border border-indigo-100/50 text-xs">
                                        <p class="font-bold text-slate-700 mb-1">Disiplin Kerja & Integritas</p>
                                        <p class="mb-0">A=8, B=6, C=5, D=3, E=0</p>
                                    </div>
                                    <div class="bg-white/60 p-3 rounded border border-indigo-100/50 text-xs sm:col-span-2">
                                        <p class="font-bold text-slate-700 mb-1">Kepatuhan, Lembur, Efektifitas & Relawan</p>
                                        <p class="mb-0">A=10, B=8, C=6, D=4, E=0</p>
                                    </div>
                                </div>
                            </div>

                            {{-- OLD SYSTEM: Regular --}}
                            <div x-show="!record.isNewSystem" style="display: none;">
                                <h6 class="font-bold mb-2 text-indigo-800 flex items-center gap-2"><i class='bx bx-bar-chart-alt-2'></i> Kriteria Nilai Alphabet (Sistem Lama)</h6>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                                    <div class="bg-white/60 p-3 rounded border border-indigo-100/50 text-xs">
                                        <p class="font-bold text-slate-700 mb-1">Prestasi</p>
                                        <p class="mb-0">A=20, B=15, C=10, D=5, E=0</p>
                                    </div>
                                    <div class="bg-white/60 p-3 rounded border border-indigo-100/50 text-xs">
                                        <p class="font-bold text-slate-700 mb-1">Lainnya (Kerajinan, Kerapian, Loyalitas, Perilaku)</p>
                                        <p class="mb-0">A=10, B=7.5, C=5, D=2.5, E=0</p>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-indigo-200/50 my-1">
                            
                            {{-- Grade Target --}}
                            <div>
                                <h6 class="font-bold mb-2 text-indigo-800 flex items-center gap-2"><i class='bx bx-target-lock'></i> Target Grade Akhir</h6>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded font-semibold border border-green-200">A : 91 - 100</span>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded font-semibold border border-blue-200">B : 71 - 90</span>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded font-semibold border border-yellow-200">C : 61 - 70</span>
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded font-semibold border border-red-200">D : < 60</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- NEW SYSTEM: 9 fields (Yayasan / Magang) --}}
                    <div x-show="record.isNewSystem" style="display: none;">
                        <h6 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="bx bx-check-double text-indigo-500"></i> Kriteria Penilaian Dasar
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            <template x-for="(label, field) in newFieldsConfig" :key="field">
                                <div>
                                    <label :for="'new_'+field" class="block text-xs font-semibold text-slate-600 mb-1" x-text="label"></label>
                                    <input type="text" maxlength="1" :name="field" :id="'new_'+field"
                                        x-model="form[field]"
                                        @input="validateInput(field)"
                                        :class="{'border-rose-400 focus:ring-rose-500/20': errors[field], 'border-slate-300 focus:ring-indigo-500/20': !errors[field]}"
                                        class="block w-full text-center font-bold text-indigo-700 text-lg uppercase rounded-lg border py-2 shadow-sm focus:border-indigo-500 focus:ring-2 transition-colors outline-none"
                                        placeholder="A–E" autocomplete="off">
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- OLD SYSTEM: 5 fields (Regular) --}}
                    <div x-show="!record.isNewSystem" style="display: none;">
                        <h6 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="bx bx-check-double text-indigo-500"></i> Kriteria Penilaian Khusus
                        </h6>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 border border-slate-100 rounded-xl p-4 bg-white shadow-sm">
                            <template x-for="(label, field) in oldFieldsConfig" :key="field">
                                <div>
                                    <label :for="'old_'+field" class="block text-xs font-semibold text-slate-600 mb-1" x-text="label"></label>
                                    <input type="text" maxlength="1" :name="field" :id="'old_'+field"
                                        x-model="form[field]"
                                        @input="validateInput(field)"
                                        :class="{'border-rose-400 focus:ring-rose-500/20': errors[field], 'border-slate-300 focus:ring-indigo-500/20': !errors[field]}"
                                        class="block w-full text-center font-bold text-indigo-700 text-lg uppercase rounded-lg border py-2 shadow-sm focus:border-indigo-500 focus:ring-2 transition-colors outline-none"
                                        placeholder="A–E" autocomplete="off">
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div x-show="!isLoading" class="border-t border-slate-100 bg-slate-50/50 px-6 py-4 flex justify-end gap-3 flex-none" style="display: none;">
                <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                <button type="submit" :disabled="isSubmitting" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 transition-all shadow-sm flex items-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed">
                    <span x-show="isSubmitting" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-solid border-white border-r-transparent mr-1"></span>
                    <i x-show="!isSubmitting" class="bx bx-save"></i> 
                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Nilai'"></span>
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('evaluationModal', () => ({
        isOpen: false,
        isLoading: false,
        isSubmitting: false,
        showGuide: false,
        updateUrl: '',
        
        record: {
            id: null,
            name: '',
            typeBadge: '',
            isNewSystem: false,
            alpha: 0,
            telat: 0,
            izin: 0,
            sakit: 0
        },
        
        form: {},
        errors: {},
        
        newFieldsConfig: {
            'kemampuan_kerja': 'Kemampuan Kerja',
            'kecerdasan_kerja': 'Kecerdasan Kerja',
            'qualitas_kerja': 'Kualitas Kerja',
            'disiplin_kerja': 'Disiplin Kerja',
            'kepatuhan_kerja': 'Kepatuhan Kerja',
            'lembur': 'Lembur',
            'efektifitas_kerja': 'Efektifitas Kerja',
            'relawan': 'Ringan Tangan',
            'integritas': 'Integritas'
        },
        
        oldFieldsConfig: {
            'kerajinan_kerja': 'Kinerja Kerja',
            'kerapian_kerja': 'Kerapian',
            'prestasi': 'Prestasi',
            'loyalitas': 'Loyalitas',
            'perilaku_kerja': 'Etika & Kesopanan'
        },

        openModal(id, url) {
            this.isOpen = true;
            this.isLoading = true;
            this.updateUrl = url;
            this.form = {};
            this.errors = {};
            
            // Lock body scroll
            document.body.style.overflow = 'hidden';

            axios.get('/evaluation/' + id + '/data')
                .then(({ data }) => {
                    // Populate headers
                    this.record.name = data.karyawan?.Nama ?? data.karyawan?.name ?? '—';
                    this.record.alpha = data.Alpha ?? 0;
                    this.record.telat = data.Telat ?? 0;
                    this.record.izin = data.Izin ?? 0;
                    this.record.sakit = data.Sakit ?? 0;

                    const scheme = data.karyawan?.employment_scheme ?? '';
                    this.record.isNewSystem = scheme.includes('YAYASAN') || scheme.includes('MAGANG');
                    this.record.typeBadge = this.record.isNewSystem ? scheme : 'Regular';

                    // Populate forms
                    const fields = this.record.isNewSystem ? Object.keys(this.newFieldsConfig) : Object.keys(this.oldFieldsConfig);
                    fields.forEach(f => {
                        this.form[f] = data[f] ?? '';
                    });

                    this.isLoading = false;
                })
                .catch((err) => {
                    this.isOpen = false;
                    document.body.style.overflow = '';
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            title: 'Gagal Memuat Data',
                            message: 'Terjadi kesalahan saat mengambil evaluasi.',
                            type: 'error'
                        }
                    }));
                });
        },

        closeModal() {
            this.isOpen = false;
            // Restore body scroll
            document.body.style.overflow = '';
        },
        
        validateInput(field) {
            let val = this.form[field] || '';
            val = val.toUpperCase().replace(/[^A-E]/g, '');
            this.form[field] = val;
            
            this.errors[field] = !/^[A-E]$/.test(val);
        },

        submitForm() {
            if (this.isSubmitting) return;
            
            // Validate all current fields
            const fields = this.record.isNewSystem ? Object.keys(this.newFieldsConfig) : Object.keys(this.oldFieldsConfig);
            let isValid = true;
            
            fields.forEach(f => {
                this.validateInput(f);
                if (this.errors[f]) isValid = false;
            });
            
            if (!isValid) return;

            this.isSubmitting = true;
            
            // Build Form Data matching backend expectation
            const formData = new FormData();
            fields.forEach(f => {
                formData.append(f, this.form[f]);
            });

            axios.post(this.updateUrl, formData, {
                headers: { 'X-HTTP-Method-Override': 'PUT' }
            }).then(({ data }) => {
                this.isSubmitting = false;
                this.closeModal();
                
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        title: 'Berhasil',
                        message: data.message || 'Nilai evaluasi berhasil disimpan.',
                        type: 'success'
                    }
                }));

                // Reload the visible DataTable & update status chips defined in index.blade.php
                if (typeof window.reloadEvaluationTables === 'function') {
                    window.reloadEvaluationTables();
                } else {
                    document.querySelectorAll('table.dataTable').forEach(t => {
                        const dtInstance = typeof $.fn?.dataTable?.Api === 'function' ? new $.fn.dataTable.Api(t) : null;
                        if (dtInstance) dtInstance.ajax.reload(null, false);
                    });
                }
            }).catch(err => {
                this.isSubmitting = false;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        title: 'Gagal Menyimpan',
                        message: 'Terjadi kesalahan saat menyimpan nilai. Silahkan coba lagi.',
                        type: 'error'
                    }
                }));
            });
        }
    }));
});
</script>
