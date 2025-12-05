{{-- Upload Files Modal (Tailwind + Alpine) --}}
<div x-show="openUploadFiles" x-cloak x-data class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    @keydown.escape.window="openUploadFiles = false">
    {{-- Overlay klik luar untuk close --}}
    <div class="absolute inset-0" @click="openUploadFiles = false"></div>

    <div class="relative z-10 w-full max-w-md mx-4 rounded-2xl bg-white shadow-xl border border-slate-100">
        <form x-ref="uploadForm" action="{{ route('file.upload') }}" method="post" enctype="multipart/form-data"
            id="form-upload" class="flex flex-col">
            @csrf

            {{-- Header --}}
            <div class="flex items-start justify-between px-5 pt-5 pb-2">
                <h5 class="text-sm font-semibold text-slate-900">
                    Upload Files
                </h5>
                <button type="button"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400
                               hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-1"
                    @click="openUploadFiles = false">
                    <span class="sr-only">Close</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 pt-1 pb-4">
                <div class="text-center py-4 rounded-xl bg-slate-50 border border-dashed border-slate-200">
                    <p class="text-[11px] text-slate-500 mb-3">
                        Upload files for this report. PDF, images, Excel files are allowed.
                    </p>

                    <input type="hidden" name="doc_num" value="{{ $doc_id }}">

                    <button type="button"
                        class="inline-flex items-center rounded-md border border-indigo-600 bg-white
                                   px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        @click="$refs.uploadInput.click()">
                        Browse files
                    </button>

                    <input type="file" x-ref="uploadInput" name="files[]" class="hidden" multiple
                        @change="$refs.uploadForm.submit()">
                </div>
            </div>

            {{-- Footer (optional, sekarang auto-submit jadi bisa kosong atau isi hint) --}}
            <div class="px-5 pb-4 pt-1 text-[11px] text-slate-400">
                File akan langsung di-upload setelah dipilih.
            </div>
        </form>
    </div>
</div>
