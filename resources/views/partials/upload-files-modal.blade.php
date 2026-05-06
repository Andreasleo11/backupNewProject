{{-- Premium Upload Files Modal --}}
<template x-teleport="body">
    <div x-data="{
    open: false,
    docId: '{{ $doc_id ?? '' }}',
    isDragging: false,
    files: [],
    uploading: false,

    init() {
        this.$watch('open', value => {
            if (value) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        });
    },

    handleDrop(e) {
        this.isDragging = false;
        const droppedFiles = e.dataTransfer.files;
        this.addFiles(droppedFiles);
    },

    handleFileSelect(e) {
        const selectedFiles = e.target.files;
        this.addFiles(selectedFiles);
        // Reset input so same file can be selected again if needed
        e.target.value = '';
    },

    addFiles(fileList) {
        // Convert FileList to Array
        for (let i = 0; i < fileList.length; i++) {
            const file = fileList[i];
            // Prevent duplicates (simple check by name and size)
            if (!this.files.some(f => f.name === file.name && f.size === file.size)) {
                this.files.push(file);
            }
        }
        this.updateInput();
    },

    removeFile(index) {
        this.files.splice(index, 1);
        this.updateInput();
    },

    updateInput() {
        const dt = new DataTransfer();
        this.files.forEach(f => dt.items.add(f));
        // Sync to the actual form input
        this.$refs.uploadInput.files = dt.files;
    },

    submitForm() {
        if (this.files.length === 0) return;
        this.uploading = true;
        this.$refs.uploadForm.submit();
    },

    getFileIcon(file) {
        if (file.type.includes('pdf')) return 'bi-file-earmark-pdf text-rose-500';
        if (file.type.includes('spreadsheet') || file.type.includes('excel')) return 'bi-file-earmark-spreadsheet text-emerald-500';
        if (file.type.includes('word') || file.type.includes('document')) return 'bi-file-earmark-word text-blue-500';
        if (file.type.includes('image')) return 'bi-file-earmark-image text-purple-500';
        return 'bi-file-earmark-text text-slate-400';
    },

    formatSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}" x-show="open" x-cloak 
    @open-upload-modal.window="
        open = true; 
        if ($event.detail && $event.detail.docId) { 
            docId = $event.detail.docId; 
        }
    "
    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
    @keydown.escape.window="open = false">

    {{-- Overlay Close --}}
    <div class="absolute inset-0" @click="open = false"></div>

    {{-- Modal Content --}}
    <div class="relative w-full max-w-lg mx-4 transform transition-all" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Upload Documents</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Attach relevant files to this request</p>
                </div>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <form x-ref="uploadForm" action="{{ route('file.upload') }}" method="post" enctype="multipart/form-data"
                class="flex flex-col">
                @csrf
                <input type="hidden" name="doc_num" :value="docId">
                {{-- Hidden real input --}}
                <input type="file" x-ref="uploadInput" name="files[]" class="hidden" multiple>

                {{-- Body --}}
                <div class="p-6 space-y-6">

                    {{-- Drop Zone --}}
                    <div class="relative group cursor-pointer" @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop($event)"
                        @click="$refs.dummyInput.click()">

                        <input type="file" x-ref="dummyInput" class="hidden" multiple
                            @change="handleFileSelect($event)">

                        <div class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-xl transition-all duration-300 bg-slate-50/50"
                            :class="isDragging ? 'border-indigo-500 bg-indigo-50/30 scale-[1.02]' :
                                'border-slate-300 hover:border-indigo-400 hover:bg-slate-50'">

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-100 mb-3 group-hover:scale-110 transition-transform duration-300">
                                <i class="bi bi-cloud-arrow-up text-2xl"
                                    :class="isDragging ? 'text-indigo-600' : 'text-slate-400 group-hover:text-indigo-500'"></i>
                            </div>

                            <p class="text-sm font-semibold text-slate-700"
                                :class="isDragging ? 'text-indigo-700' : ''">
                                <span class="text-indigo-600 hover:underline">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-slate-400 mt-1">PDF, Excel, Images (Max 10MB)</p>
                        </div>
                    </div>

                    {{-- File List --}}
                    <template x-if="files.length > 0">
                        <div class="space-y-3" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translateY-4"
                            x-transition:enter-end="opacity-100 translateY-0">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Selected Files (<span
                                    x-text="files.length"></span>)</h4>

                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-2 pr-1">
                                <template x-for="(file, index) in files" :key="index">
                                    <div
                                        class="flex items-center gap-3 p-2 rounded-lg border border-slate-100 bg-white shadow-sm group hover:border-indigo-100 transition-colors">
                                        {{-- Preview / Icon --}}
                                        <div
                                            class="h-10 w-10 flex-shrink-0 rounded-lg bg-slate-50 flex items-center justify-center overflow-hidden border border-slate-100">
                                            <template x-if="file.type.startsWith('image/')">
                                                <img :src="URL.createObjectURL(file)"
                                                    class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!file.type.startsWith('image/')">
                                                <i class="bi text-lg" :class="getFileIcon(file)"></i>
                                            </template>
                                        </div>

                                        {{-- Details --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-700 truncate" x-text="file.name">
                                            </p>
                                            <p class="text-[10px] text-slate-400" x-text="formatSize(file.size)"></p>
                                        </div>

                                        {{-- Remove --}}
                                        <button type="button" @click.stop="removeFile(index)"
                                            class="h-8 w-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-all opacity-0 group-hover:opacity-100 focus:opacity-100"
                                            title="Remove file">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button type="button" @click="open = false"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-200/50 transition-all">
                        Cancel
                    </button>

                    <button type="button" @click="submitForm()" :disabled="files.length === 0 || uploading"
                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none transition-all">
                        <template x-if="!uploading">
                            <span>Upload Files</span>
                        </template>
                        <template x-if="uploading">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Uploading...
                            </span>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
