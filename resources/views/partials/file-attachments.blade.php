@php
    if (!function_exists('formatFileSize')) {
        function formatFileSize($bytes)
        {
            if ($bytes < 1024) {
                return $bytes . ' B';
            } elseif ($bytes < 1024 * 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            } elseif ($bytes < 1024 * 1024 * 1024) {
                return number_format($bytes / (1024 * 1024), 2) . ' MB';
            } else {
                return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
            }
        }
    }

    $title = $title ?? 'Related Documents';
    $showDelete = $showDelete ?? false;
    $showUpload = $showUpload ?? false;
    $gridCols = $gridCols ?? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
@endphp

<div x-data="{ previewOpen: false, previewUrl: '', previewType: '' }" class="space-y-4">
    @if ($title || $showUpload)
        <div class="flex items-center justify-between">
            @if ($title)
                <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-800">
                    <i class="bi bi-paperclip text-indigo-500"></i> {{ $title }}
                </h3>
            @endif
            @if ($showUpload)
                <button type="button" 
                    x-data
                    @click="$dispatch('open-upload-modal')"
                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 hover:border-indigo-150 hover:bg-indigo-50/30 text-indigo-600 text-xs font-semibold rounded-lg shadow-xs transition duration-200">
                    <i class="bi bi-plus-lg"></i> Add File
                </button>
            @endif
        </div>
    @endif

    @if ($files->isEmpty())
        <div
            class="flex flex-col items-center justify-center py-8 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50">
            <i class="bi bi-folder2-open text-3xl text-slate-200 mb-2"></i>
            <p class="text-xs font-medium text-slate-400">No documents attached.</p>
        </div>
    @else
        <div class="grid {{ $gridCols }} gap-4">
            @foreach ($files as $file)
                @php
                    $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                @endphp
                <div
                    class="group flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-3 shadow-sm transition-all duration-300 hover:border-indigo-100 hover:shadow-md hover:shadow-indigo-500/5 hover:-translate-y-0.5">
                    <div class="flex items-center gap-3 overflow-hidden">
                        {{-- File Icon --}}
                        <div
                            class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-slate-50 shadow-inner group-hover:bg-indigo-50 transition-colors">
                            @if (Str::contains($file->mime_type, 'image') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                <i class="bi bi-file-earmark-image text-xl text-purple-500"></i>
                            @elseif(Str::contains($file->mime_type, 'pdf') || $extension === 'pdf')
                                <i class="bi bi-file-earmark-pdf text-xl text-rose-500"></i>
                            @elseif(Str::contains($file->mime_type, 'spreadsheet') ||
                                    Str::contains($file->mime_type, 'excel') ||
                                    in_array($extension, ['xls', 'xlsx', 'csv']))
                                <i class="bi bi-file-earmark-spreadsheet text-xl text-emerald-500"></i>
                            @elseif(Str::contains($file->mime_type, 'word') || in_array($extension, ['doc', 'docx']))
                                <i class="bi bi-file-earmark-word text-xl text-blue-500"></i>
                            @else
                                <i class="bi bi-file-earmark-text text-xl text-slate-400"></i>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition-colors"
                                title="{{ $file->name }}">
                                {{ $file->name }}
                            </p>
                            <p class="text-[10px] font-medium text-slate-400">
                                {{ formatFileSize($file->size) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 pl-2">
                        {{-- Preview Button --}}
                        @if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <button type="button"
                                @click="previewUrl = '{{ asset('storage/files/' . $file->name) }}'; previewType = '{{ $extension }}'; previewOpen = true"
                                class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-450 transition-all hover:bg-indigo-50 hover:text-indigo-650"
                                title="Preview File">
                                <i class="bi bi-eye"></i>
                            </button>
                        @endif

                        {{-- Download Link --}}
                        <a href="{{ asset('storage/files/' . $file->name) }}" target="_blank"
                            class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-450 transition-all hover:bg-indigo-50 hover:text-indigo-650"
                            title="Download / View">
                            <i class="bi bi-download"></i>
                        </a>

                        {{-- Delete Button --}}
                        @if ($showDelete)
                            <form action="{{ route('file.destroy', $file->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this file?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="flex h-9 w-9 items-center justify-center rounded-xl text-rose-400 transition-all hover:bg-rose-50 hover:text-rose-600"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Document Preview Modal --}}
    <div x-show="previewOpen" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;" 
         x-cloak>
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs" @click="previewOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-5xl rounded-2xl bg-white shadow-2xl border border-slate-100 flex flex-col h-[85vh] overflow-hidden" 
                 @click.outside="previewOpen = false">
                <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Document Preview</h3>
                    <button type="button" @click="previewOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>
                <div class="flex-1 bg-slate-50 relative p-4 flex items-center justify-center">
                    <template x-if="previewType === 'pdf'">
                        <iframe :src="previewUrl" class="w-full h-full rounded-lg border-0 shadow-sm"></iframe>
                    </template>
                    <template x-if="previewType !== 'pdf'">
                        <img :src="previewUrl" class="max-w-full max-h-full object-contain rounded-lg shadow-md">
                    </template>
                </div>
                <div class="px-6 py-3 border-t border-slate-100 flex justify-end">
                    <a :href="previewUrl" download class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-650 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition" target="_blank">
                        <i class="bi bi-download"></i> Open in New Tab
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
