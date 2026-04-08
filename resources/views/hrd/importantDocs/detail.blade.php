@extends('new.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-4">
        {{-- Slim Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Document Details</h1>
                <nav class="text-[10px] font-bold uppercase tracking-widest text-slate-400" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1.5 p-0 m-0">
                        <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a></li>
                        <li>/</li>
                        <li><a href="{{ route('hrd.importantDocs.index') }}" class="hover:text-indigo-600 transition-colors">Important Documents</a></li>
                        <li>/</li>
                        <li class="text-slate-500">{{ $importantDoc->name }}</li>
                    </ol>
                </nav>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('hrd.importantDocs.edit', $importantDoc->id) }}" 
                   class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition-all active:scale-95">
                    <i class="bx bx-edit mr-1.5"></i> Edit Document
                </a>
                <a href="{{ route('hrd.importantDocs.index') }}" 
                   class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                    <i class="bx bx-arrow-back mr-1.5"></i> Back to List
                </a>
            </div>
        </div>

        {{-- Current Status Card --}}
        @php
            $diffDays = $today->diffInDays($importantDoc->expired_date, false);
            $statusClass = match(true) {
                $diffDays < 0 => 'bg-rose-100 text-rose-700 border-rose-200',
                $diffDays <= $thresholdDays => 'bg-amber-100 text-amber-700 border-amber-200',
                default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            };
            $statusLabel = match(true) {
                $diffDays < 0 => 'Expired Document',
                $diffDays === 0 => 'Expires Today',
                $diffDays <= $thresholdDays => 'Expiring Soon',
                default => 'Active & Valid',
            };
        @endphp

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="{{ $statusClass }} px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-white/50 backdrop-blur-md border border-current/20 flex items-center justify-center text-current shadow-sm">
                        <i class="bx bx-file text-3xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">{{ $importantDoc->name }}</h2>
                        <p class="text-[10px] uppercase font-black tracking-widest opacity-70">{{ $importantDoc->type->name }}</p>
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
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Document Number</span>
                        <p class="text-sm font-bold text-slate-700">{{ $importantDoc->document_id ?: 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Expiry Date</span>
                        <p class="text-sm font-bold text-slate-700">{{ optional($importantDoc->expired_date)->format('d F Y') }}</p>
                        <p class="text-[10px] font-medium text-slate-400 italic">
                             @if($diffDays < 0)
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
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Notes / Description</span>
                    <div class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600 leading-relaxed border border-slate-100 min-h-[100px]">
                        {{ $importantDoc->description ?: 'No description provided for this document.' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Attachments Gallery --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Document Attachments</h2>
                <span class="text-[10px] font-bold text-slate-400 bg-white border border-slate-200 px-2 py-0.5 rounded-full">{{ $importantDoc->files->count() }} Files</span>
            </div>

            @if ($importantDoc->files->isEmpty())
                <div class="p-12 text-center">
                    <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bx bx-paperclip text-3xl text-slate-300"></i>
                    </div>
                    <p class="text-sm text-slate-500">No attachments found for this document.</p>
                </div>
            @else
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($importantDoc->files as $file)
                        @php
                            $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                            $icon = match(true) {
                                in_array($extension, ['pdf']) => 'bxs-file-pdf text-rose-500',
                                in_array($extension, ['xls','xlsx','csv']) => 'bxs-spread-sheet text-emerald-500',
                                in_array($extension, ['png','jpg','jpeg']) => 'bxs-image text-indigo-500',
                                in_array($extension, ['doc','docx']) => 'bxs-file-doc text-blue-500',
                                default => 'bxs-file text-slate-400',
                            };
                            $isImage = in_array($extension, ['png', 'jpg', 'jpeg']);
                        @endphp
                        
                        <div class="group flex flex-col rounded-xl border border-slate-200 bg-white hover:border-indigo-500 hover:shadow-lg transition-all overflow-hidden">
                            <div class="aspect-video bg-slate-50 flex items-center justify-center relative border-b border-slate-100">
                                <i class="bx {{ $icon }} text-5xl transition-transform group-hover:scale-110"></i>
                                @if($isImage)
                                    <div class="absolute inset-0 bg-indigo-600/0 group-hover:bg-indigo-600/5 transition-colors"></div>
                                @endif
                            </div>
                            
                            <div class="p-3 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold text-slate-700 truncate" title="{{ $file->name }}">{{ $file->name }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">{{ strtoupper($extension) }}</p>
                                </div>
                                <a href="{{ asset('storage/importantDocuments/' . $file->name) }}" target="_blank"
                                   class="h-8 w-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center"
                                   title="Download / View">
                                    <i class="bx bx-download text-base"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Last Updated: {{ $importantDoc->updated_at->format('M d, Y H:i') }}</span>
            <form action="{{ route('hrd.importantDocs.delete', $importantDoc->id) }}" method="POST" onsubmit="return confirm('Delete this document? (This action can be undone by an administrator)')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg px-4 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-all">
                    <i class="bx bx-trash mr-1.5"></i> Delete Document
                </button>
            </form>
        </div>
    </div>
@endsection
