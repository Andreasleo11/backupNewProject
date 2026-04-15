@php
    $diffDays = $today->diffInDays($importantDoc->expired_date, false);
    $statusClass = match (true) {
        $diffDays < 0 => 'bg-rose-100 text-rose-700 border-rose-200',
        $diffDays <= $thresholdDays => 'bg-amber-100 text-amber-700 border-amber-200',
        default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
    };
    $statusLabel = match (true) {
        $diffDays < 0 => 'Expired Document',
        $diffDays === 0 => 'Expires Today',
        $diffDays <= $thresholdDays => 'Expiring Soon',
        default => 'Active & Valid',
    };
@endphp

<div class="space-y-4">
    {{-- Current Status Card --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div
            class="{{ $statusClass }} px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="h-14 w-14 rounded-2xl bg-white/50 backdrop-blur-md border border-current/20 flex items-center justify-center text-current shadow-sm">
                    <i class="bx bx-file text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold">{{ $importantDoc->name }}</h2>
                    <p class="text-[10px] uppercase font-black tracking-widest opacity-70">
                        {{ $importantDoc->type->name }}</p>
                </div>
            </div>
            <div class="flex flex-col items-end">
                <span class="text-[11px] font-black uppercase tracking-tighter opacity-60">Status</span>
                <span class="text-xl font-black italic uppercase tracking-tight">{{ $statusLabel }}</span>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Info Column 1 --}}
            <div class="space-y-4">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Document
                        Number</span>
                    <p class="text-sm font-bold text-slate-700">{{ $importantDoc->document_id ?: 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Expiry
                        Date</span>
                    <p class="text-sm font-bold text-slate-700">
                        {{ optional($importantDoc->expired_date)->format('d F Y') }}</p>
                    <p class="text-[10px] font-medium text-slate-400 italic">
                        @if ($diffDays < 0)
                            Passed {{ abs($diffDays) }} days ago
                        @elseif($diffDays == 0)
                            Expires Today
                        @else
                            {{ $diffDays }} days remaining
                        @endif
                    </p>
                </div>
            </div>

            {{-- Info Column 2: Description --}}
            <div class="md:col-span-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Notes /
                    Description</span>
                <div
                    class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600 leading-relaxed border border-slate-100 min-h-[100px]">
                    {{ $importantDoc->description ?: 'No description provided for this document.' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Attachments Gallery --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Attachments</h2>
            <span
                class="text-[10px] font-bold text-slate-400 bg-white border border-slate-200 px-2 py-0.5 rounded-full">{{ $importantDoc->files->count() }}
                Files</span>
        </div>

        @if ($importantDoc->files->isEmpty())
            <div class="p-8 text-center">
                <i class="bx bx-paperclip text-3xl text-slate-300 mb-2"></i>
                <p class="text-xs text-slate-500">No attachments found.</p>
            </div>
        @else
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($importantDoc->files as $file)
                    @php
                        $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                        $icon = match (true) {
                            in_array($extension, ['pdf']) => 'bxs-file-pdf text-rose-500',
                            in_array($extension, ['xls', 'xlsx', 'csv']) => 'bxs-spread-sheet text-emerald-500',
                            in_array($extension, ['png', 'jpg', 'jpeg']) => 'bxs-image text-indigo-500',
                            in_array($extension, ['doc', 'docx']) => 'bxs-file-doc text-blue-500',
                            default => 'bxs-file text-slate-400',
                        };
                    @endphp

                    <div
                        class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/50 group hover:border-indigo-200 transition-all">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <i class="bx {{ $icon }} text-2xl"></i>
                            <div class="truncate">
                                <p class="text-[11px] font-bold text-slate-700 truncate">{{ $file->name }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $extension }}</p>
                            </div>
                        </div>
                        <a href="{{ asset('storage/importantDocuments/' . $file->name) }}" target="_blank"
                            class="h-8 w-8 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-xs">
                            <i class="bx bx-download text-sm"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Simplified Quick Actions (Only shown in Sidebar context usually, or shared) --}}
    <div class="flex items-center justify-between pt-2">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">Updated:
            {{ $importantDoc->updated_at->diffForHumans() }}</span>
        <div class="flex items-center gap-2">
            <a href="{{ route('hrd.importantDocs.edit', $importantDoc->id) }}"
                class="inline-flex h-8 items-center px-3 rounded-lg text-xs font-bold text-indigo-600 hover:bg-indigo-50 transition-all">
                <i class="bx bx-edit mr-1"></i> Edit
            </a>
        </div>
    </div>
</div>
