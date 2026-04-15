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
    $gridCols = $gridCols ?? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
@endphp

<div class="space-y-4">
    @if ($title)
        <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-800">
            <i class="bi bi-paperclip text-indigo-500"></i> {{ $title }}
        </h3>
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
                        {{-- Download Link --}}
                        <a href="{{ asset('storage/files/' . $file->name) }}" target="_blank"
                            class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition-all hover:bg-indigo-600 hover:text-white hover:shadow-lg hover:shadow-indigo-200"
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
                                    class="flex h-9 w-9 items-center justify-center rounded-xl text-rose-400 transition-all hover:bg-rose-600 hover:text-white hover:shadow-lg hover:shadow-rose-200"
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
</div>
