@extends('new.layouts.app')

@php
    /** @var \App\DataTables\Admin\EvaluationDataWeeklyManagementDataTable $dataTable */
    $pageTitle = 'Manajemen Data P&E Mingguan';
    $pageDescription =
        'Kelola database Master Employee Evaluation mingguan. Unggah dari sumber eksternal, pantau absensi mingguan, dan kelola data operasional dengan cepat.';
    $pageIcon = 'bx-calendar-week';
@endphp

@push('styles')
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.tailwindcss.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        }

        .dt-buttons .btn {
            @apply px-4 py-2 text-sm font-semibold rounded-xl border transition-all;
        }

        #evaluation-data-weekly-management-table_wrapper {
            @apply w-full;
        }

        #evaluation-data-weekly-management-table th {
            @apply bg-slate-50 text-slate-600 font-bold tracking-wider py-4 uppercase text-xs;
        }

        /* Drag and Drop Upload Area */
        .drop-zone {
            transition: all 0.3s ease;
        }

        .drop-zone.drag-active {
            border-color: #f59e0b;
            /* Amber */
            background-color: #fffbeb;
            transform: scale(1.02);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="evaluationDataWeeklyAdmin()">

        {{-- Header Layout --}}
        <div class="relative overflow-hidden rounded-2xl bg-amber-900 shadow-xl mb-8">
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 blend-overlay">
            </div>
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-amber-500 blur-3xl opacity-30"></div>
            <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-orange-500 blur-3xl opacity-30"></div>

            <div class="relative p-8 sm:p-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="max-w-3xl">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-200 border border-amber-500/30 mb-4 text-xs font-semibold tracking-wide uppercase">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-400"></span>
                        </span>
                        Administration Zone
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-white mb-2 tracking-tight">Manajemen Data <span
                            class="text-amber-300">Mingguan</span></h1>
                    <p class="text-amber-100/80 text-lg max-w-2xl leading-relaxed">{{ $pageDescription }}</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto shrink-0">
                    <button @click="openUploadModal"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-amber-900 hover:bg-amber-50 font-bold rounded-xl transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 border border-white/20 whitespace-nowrap">
                        <i class='bx bx-cloud-upload text-xl'></i>
                        <span>Unggah Data Mingguan</span>
                    </button>
                    <button @click="confirmTruncate"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-500/20 text-red-200 hover:bg-rose-600 hover:text-white font-bold rounded-xl transition-all border border-rose-500/30 whitespace-nowrap">
                        <i class='bx bx-trash-alt text-xl'></i>
                        <span>Flush Tabel</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Main DataTable Card --}}
        <div class="glass-card rounded-2xl shadow-xl border border-slate-200/60 overflow-hidden relative z-10">
            <div class="p-6">
                {!! $dataTable->table(['class' => 'w-full w-100 align-middle whitespace-nowrap']) !!}
            </div>
        </div>

        {{-- Upload Modal (AlpineJS) --}}
        <div x-show="uploadModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div x-show="uploadModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="closeUploadModal"></div>

            <!-- Modal Panel -->
            <div x-show="uploadModalOpen" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col items-center">

                <div class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 cursor-pointer p-2 bg-slate-100 rounded-full z-20"
                    @click="closeUploadModal">
                    <i class="bx bx-x text-xl"></i>
                </div>

                <!-- Step 1: Upload -->
                <div x-show="step === 1" class="w-full p-8 flex flex-col items-center">
                    <div
                        class="h-16 w-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-3xl mb-4 shadow-sm">
                        <i class="bx bx-calendar-week"></i>
                    </div>

                    <h3 class="text-xl font-bold text-slate-800 mb-2">Unggah Data Mingguan</h3>
                    <p class="text-slate-500 text-sm text-center mb-6">File yang didukung: <b>.xlsx, .xls, .csv</b>.
                        Pastikan format kolom sesuai dengan template standar sistem.</p>

                    <form @submit.prevent="submitUpload" class="w-full">
                        <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 mb-6 drop-zone relative cursor-pointer group"
                            :class="{ 'drag-active': isDragging }" @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop">

                            <input type="file" x-ref="fileInput" @change="handleFileSelect" accept=".xlsx,.xls,.csv"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                            <div class="text-center flex flex-col items-center">
                                <i class="bx"
                                    :class="selectedFile ? 'bx-check-circle text-emerald-500 bx-tada text-5xl' :
                                        'bx-cloud-upload text-amber-400 group-hover:text-amber-500 text-5xl'"
                                    class="mb-3 transition-colors"></i>

                                <h4 x-show="!selectedFile" class="text-slate-700 font-bold text-base mb-1">Tarik & Lepas
                                    File</h4>
                                <p x-show="!selectedFile" class="text-slate-400 text-sm">atau klik untuk memilih dari
                                    komputer</p>

                                <h4 x-show="selectedFile" class="text-emerald-700 font-bold text-base mb-1"
                                    x-text="selectedFile?.name"></h4>
                                <p x-show="selectedFile" class="text-emerald-600/80 text-sm"
                                    x-text="(selectedFile?.size / 1024).toFixed(1) + ' KB'"></p>
                            </div>
                        </div>

                        <button type="submit" :disabled="!selectedFile || isUploading"
                            class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="isUploading"
                                class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-solid border-white border-r-transparent"></span>
                            <span x-text="isUploading ? 'Memindai Integritas...' : 'Lanjut ke Preview'"></span>
                        </button>
                    </form>
                </div>

                <!-- Step 2: Preview / Integrity Report -->
                <div x-show="step === 2" x-cloak class="w-full">
                    <div class="bg-amber-50 p-6 border-b border-amber-100 flex items-center gap-4">
                        <div
                            class="h-12 w-12 bg-white text-amber-600 rounded-xl flex items-center justify-center text-2xl shadow-sm border border-amber-100 shrink-0">
                            <i class="bx bx-shield-check"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-amber-900 text-lg">Integrity Guard Report (Weekly)</h3>
                            <p class="text-amber-600/70 text-sm">Analisis data mingguan sebelum proses impor</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Stats Grid -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-center">
                                <div class="text-emerald-600 font-black text-2xl mb-1" x-text="scanResult?.new"></div>
                                <div class="text-emerald-700 text-xs font-bold uppercase tracking-wider">Data Baru</div>
                            </div>
                            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
                                <div class="text-amber-600 font-black text-2xl mb-1" x-text="scanResult?.updates"></div>
                                <div class="text-amber-700 text-xs font-bold uppercase tracking-wider">Perbaruan</div>
                            </div>
                            <div class="bg-rose-50 border border-rose-100 rounded-xl p-4 text-center">
                                <div class="text-rose-600 font-black text-2xl mb-1" x-text="scanResult?.errors.length">
                                </div>
                                <div class="text-rose-700 text-xs font-bold uppercase tracking-wider">Masalah</div>
                            </div>
                        </div>

                        <!-- Errors Section -->
                        <div x-show="scanResult?.errors.length > 0"
                            class="max-h-48 overflow-y-auto rounded-xl border border-rose-100 bg-rose-50/50 p-4">
                            <h4 class="text-rose-800 font-bold text-sm mb-2 flex items-center gap-2">
                                <i class="bx bx-error-circle"></i>
                                Daftar Masalah yang Ditemukan:
                            </h4>
                            <ul class="text-rose-600 text-xs space-y-1 ml-6 list-disc">
                                <template x-for="error in scanResult?.errors">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </div>

                        <div x-show="scanResult?.errors.length === 0"
                            class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-700 text-sm">
                            <i class="bx bxs-check-shield text-xl"></i>
                            <span>Integritas data valid. Siap untuk proses impor ke database.</span>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <button @click="step = 1"
                                class="flex-1 py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all">
                                Ulangi Upload
                            </button>
                            <button @click="commitUpload" :disabled="isUploading"
                                class="flex-[2] py-3 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition-all shadow-lg flex items-center justify-center gap-2">
                                <span x-show="isUploading"
                                    class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-solid border-white border-r-transparent"></span>
                                <span x-text="isUploading ? 'Menyimpan...' : 'Finalize & Import'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>

    {!! $dataTable->scripts() !!}

    <script>
        // Global Delete Function used by DataTable action button
        window.deleteRow = function(url) {
            Swal.fire({
                title: 'Hapus Data Mingguan?',
                text: "Data absen mingguan ini akan terhapus.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(url)
                        .then(res => {
                            window.LaravelDataTables['evaluation-data-weekly-management-table'].ajax
                            .reload();
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    title: 'Berhasil',
                                    message: res.data.message,
                                    type: 'success'
                                }
                            }));
                        })
                        .catch(err => {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    title: 'Error',
                                    message: 'Gagal menghapus data.',
                                    type: 'error'
                                }
                            }));
                        });
                }
            });
        };

        document.addEventListener('alpine:init', () => {
            Alpine.data('evaluationDataWeeklyAdmin', () => ({
                uploadModalOpen: false,
                step: 1,
                isDragging: false,
                isUploading: false,
                selectedFile: null,
                scanResult: null,

                openUploadModal() {
                    this.uploadModalOpen = true;
                    this.step = 1;
                    this.selectedFile = null;
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                },

                closeUploadModal() {
                    this.uploadModalOpen = false;
                },

                handleDrop(e) {
                    this.isDragging = false;
                    if (e.dataTransfer.files.length > 0) {
                        this.selectedFile = e.dataTransfer.files[0];
                        this.$refs.fileInput.files = e.dataTransfer.files;
                    }
                },

                handleFileSelect(e) {
                    if (e.target.files.length > 0) {
                        this.selectedFile = e.target.files[0];
                    }
                },

                submitUpload() {
                    if (!this.selectedFile) return;
                    this.isUploading = true;

                    let formData = new FormData();
                    formData.append('file', this.selectedFile);

                    axios.post('{{ route('admin.evaluation-data-weekly.upload') }}', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then(res => {
                            this.isUploading = false;
                            this.scanResult = res.data.report;
                            this.step = 2;
                        })
                        .catch(err => {
                            this.isUploading = false;
                            let msg = err.response?.data?.message ||
                                'Terjadi kesalahan saat memindai file.';
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    title: 'Scan Gagal',
                                    message: msg,
                                    type: 'error'
                                }
                            }));
                        });
                },

                commitUpload() {
                    this.isUploading = true;
                    axios.post('{{ route('admin.evaluation-data-weekly.commit') }}', {
                            temp_path: this.scanResult.temp_path
                        })
                        .then(res => {
                            this.isUploading = false;
                            this.closeUploadModal();
                            window.LaravelDataTables['evaluation-data-weekly-management-table'].ajax
                                .reload();
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.data.message,
                                icon: 'success',
                                confirmButtonColor: '#f59e0b'
                            });
                        })
                        .catch(err => {
                            this.isUploading = false;
                            let msg = err.response?.data?.message ||
                                'Terjadi kesalahan saat menyimpan data.';
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    title: 'Gagal',
                                    message: msg,
                                    type: 'error'
                                }
                            }));
                        });
                },

                confirmTruncate() {
                    Swal.fire({
                        title: 'Flush Data Mingguan?',
                        html: "<p class='text-rose-600 font-bold'>Aksi ini akan mengkosongkan seluruh log mingguan di database!</p>",
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'SAYA YAKIN, FLUSH TABEL',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            axios.delete('{{ route('admin.evaluation-data-weekly.truncate') }}')
                                .then(res => {
                                    window.LaravelDataTables[
                                            'evaluation-data-weekly-management-table'].ajax
                                        .reload();
                                    Swal.fire('Terhapus!', res.data.message, 'success');
                                })
                                .catch(err => {
                                    window.dispatchEvent(new CustomEvent('toast', {
                                        detail: {
                                            title: 'Error',
                                            message: 'Gagal membersihkan tabel.',
                                            type: 'error'
                                        }
                                    }));
                                });
                        }
                    });
                }
            }));
        });
    </script>
@endpush
