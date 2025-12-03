@php
    if (!function_exists('formatFileSize')) {
        function formatFileSize($bytes)
        {
            if ($bytes < 1024) {
                return $bytes . ' bytes';
            } elseif ($bytes < 1024 * 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            } elseif ($bytes < 1024 * 1024 * 1024) {
                return number_format($bytes / (1024 * 1024), 2) . ' MB';
            } else {
                return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
            }
        }
    }
@endphp

<div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 sm:px-5 sm:py-4">
    <h4 class="text-sm font-semibold text-slate-900 mb-3">
        Files
    </h4>

    @if ($files->isEmpty())
        <p class="text-xs text-slate-500">
            No files were uploaded.
        </p>
    @else
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
            @foreach ($files as $file)
                @php
                    $filename = basename($file->name);
                    $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                @endphp

                <div class="flex items-stretch gap-2" x-data="{ confirmDelete: false }">
                    {{-- File card --}}
                    <a href="{{ asset('storage/files/' . $filename) }}" download="{{ $filename }}" class="flex-1">
                        <div
                            class="h-full w-full rounded-xl border border-slate-200 bg-slate-50/60
                                    px-3 py-2 shadow-sm hover:bg-slate-100 transition-colors">
                            <div class="flex items-center gap-2 min-h-[72px]">
                                {{-- Icon --}}
                                <div class="flex-shrink-0">
                                    @if ($extension === 'pdf')
                                        <img src="{{ asset('image/ic-pdf.png') }}" alt="pdf icon"
                                            class="h-10 w-10 object-contain">
                                    @elseif(in_array($extension, ['xls', 'xlsx', 'csv']))
                                        <img src="{{ asset('image/ic-xls.png') }}" alt="excel icon"
                                            class="h-10 w-10 object-contain">
                                    @elseif(in_array($extension, ['png', 'jpeg', 'jpg', 'webp']))
                                        <img src="{{ asset('image/ic-image.png') }}" alt="image icon"
                                            class="h-10 w-10 object-contain">
                                    @elseif(in_array($extension, ['doc', 'docx']))
                                        <img src="{{ asset('image/ic-doc.png') }}" alt="doc icon"
                                            class="h-10 w-10 object-contain">
                                    @else
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-200 text-[10px] font-semibold text-slate-600">
                                            {{ strtoupper($extension) }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Name --}}
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-semibold text-slate-800 line-clamp-3">
                                        {{ $filenameWithoutExtension }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 text-[11px] text-slate-500">
                                {{ formatFileSize($file->size) }}
                            </div>
                        </div>
                    </a>

                    {{-- Delete button + modal per file --}}
                    @if ($showDeleteButton)
                        <div class="flex items-start">
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-rose-200
                                           bg-rose-50 px-2.5 py-2 text-[11px] font-semibold text-rose-600
                                           shadow-sm hover:bg-rose-100 focus:outline-none focus:ring-2
                                           focus:ring-rose-400 focus:ring-offset-1"
                                @click="confirmDelete = true">
                                <i class='bx bxs-trash-alt bx-xs'></i>
                            </button>

                            {{-- Confirm delete modal --}}
                            <div x-show="confirmDelete" x-cloak
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                                @keydown.escape.window="confirmDelete = false">
                                <div class="absolute inset-0" @click="confirmDelete = false"></div>

                                <div
                                    class="relative z-10 w-full max-w-sm mx-4 rounded-2xl bg-white
                                            shadow-xl border border-slate-100">
                                    <form x-ref="deleteForm" action="{{ route('file.delete', $file->id) }}"
                                        method="post" class="flex flex-col">
                                        @csrf
                                        @method('DELETE')

                                        {{-- Header --}}
                                        <div class="px-5 pt-5 pb-2 flex items-start justify-between gap-3">
                                            <h5 class="text-sm font-semibold text-slate-900">
                                                Confirm Delete
                                            </h5>
                                            <button type="button"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400
                                                           hover:bg-slate-100 hover:text-slate-600 focus:outline-none
                                                           focus:ring-2 focus:ring-rose-500 focus:ring-offset-1"
                                                @click="confirmDelete = false">
                                                <span class="sr-only">Close</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    class="h-4 w-4" fill="none" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.6" d="M6 6l12 12M18 6L6 18" />
                                                </svg>
                                            </button>
                                        </div>

                                        {{-- Body --}}
                                        <div class="px-5 pt-1 pb-4 text-sm text-slate-600 space-y-2">
                                            <p>Are you sure you want to delete this file?</p>
                                            <p class="text-[11px] text-slate-400">
                                                {{ $filename }}
                                            </p>
                                        </div>

                                        {{-- Footer --}}
                                        <div
                                            class="px-5 pb-5 pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                                            <button type="button"
                                                class="inline-flex items-center rounded-md border border-slate-300
                                                           bg-white px-3 py-1.5 text-xs font-medium text-slate-700
                                                           shadow-sm hover:bg-slate-50 focus:outline-none
                                                           focus:ring-2 focus:ring-slate-300 focus:ring-offset-1"
                                                @click="confirmDelete = false">
                                                No
                                            </button>

                                            <button type="submit"
                                                class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5
                                                           text-xs font-semibold text-white shadow-sm hover:bg-rose-700
                                                           focus:outline-none focus:ring-2 focus:ring-rose-500
                                                           focus:ring-offset-1"
                                                @click.prevent="$refs.deleteForm.submit()">
                                                Yes, Delete
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
