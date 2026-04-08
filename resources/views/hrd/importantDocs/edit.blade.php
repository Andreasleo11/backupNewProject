@extends('new.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-4" x-data="{ 
        typeId: '{{ old('type_id', $importantDoc->type_id) }}',
        get isOther() { return this.typeId === '1' },
        selectedFiles: []
    }">
        {{-- Slim Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Document</h1>
                <nav class="text-[10px] font-bold uppercase tracking-widest text-slate-400" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1.5 p-0 m-0">
                        <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a></li>
                        <li>/</li>
                        <li><a href="{{ route('hrd.importantDocs.index') }}" class="hover:text-indigo-600 transition-colors">Important Documents</a></li>
                        <li>/</li>
                        <li><a href="{{ route('hrd.importantDocs.detail', $importantDoc->id) }}" class="hover:text-indigo-600 transition-colors tracking-tight truncate max-w-[150px] inline-block align-bottom">{{ $importantDoc->name }}</a></li>
                        <li>/</li>
                        <li class="text-slate-500">Edit</li>
                    </ol>
                </nav>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('hrd.importantDocs.detail', $importantDoc->id) }}" 
                   class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                    View Details
                </a>
                <a href="{{ route('hrd.importantDocs.index') }}" 
                   class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                    <i class="bx bx-arrow-back mr-1.5"></i> Back
                </a>
            </div>
        </div>

        {{-- Current Status Badge & Title Context --}}
        @php
            $diffDays = $today->diffInDays($importantDoc->expired_date, false);
            $statusClass = match(true) {
                $diffDays < 0 => 'bg-rose-100 text-rose-700 border-rose-200',
                $diffDays <= $thresholdDays => 'bg-amber-100 text-amber-700 border-amber-200',
                default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            };
            $statusLabel = match(true) {
                $diffDays < 0 => 'Expired ' . abs($diffDays) . 'd ago',
                $diffDays === 0 => 'Expires Today',
                $diffDays <= $thresholdDays => 'Expiring in ' . $diffDays . 'd',
                default => 'Active & Healthy',
            };
        @endphp

        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                    <i class="bx bx-file text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ $importantDoc->name }}</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $importantDoc->type->name }}</p>
                </div>
            </div>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-bold border {{ $statusClass }}">
                <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current animate-pulse"></span>
                {{ $statusLabel }}
            </span>
        </div>

        {{-- Main Form Card --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Update Information</h2>
            </div>

            <form action="{{ route('hrd.importantDocs.update', $importantDoc->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- Row 1: Category & Document ID --}}
                    <div class="space-y-1.5">
                        <label for="typeSelect" class="text-xs font-bold text-slate-700 uppercase tracking-tight">
                            Category <span class="text-rose-500">*</span>
                        </label>
                        <select id="typeSelect" name="type_id" required x-model="typeId"
                                class="w-full px-4 py-2.5 rounded-lg border-slate-200 bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all @error('type_id') border-rose-500 @enderror">
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('type_id') <p class="text-[10px] font-bold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="document_id" class="text-xs font-bold text-slate-700 uppercase tracking-tight">
                            Document ID/Number
                        </label>
                        <input type="text" id="document_id" name="document_id" value="{{ old('document_id', $importantDoc->document_id) }}"
                               placeholder="e.g. 90S/A8D.89OU"
                               class="w-full px-4 py-2.5 rounded-lg border-slate-200 bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all @error('document_id') border-rose-500 @enderror">
                        @error('document_id') <p class="text-[10px] font-bold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Conditional Row: Other Type Name --}}
                    <div class="col-span-full space-y-1.5" x-show="isOther" x-transition x-cloak>
                        <label for="otherInput" class="text-xs font-bold text-indigo-700 uppercase tracking-tight">
                            Specify Other Category <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="otherInput" name="other" value="{{ old('other') }}"
                               :required="isOther"
                               class="w-full px-4 py-2.5 rounded-lg border-indigo-200 bg-indigo-50/30 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all">
                    </div>

                    {{-- Row 2: Document Name --}}
                    <div class="col-span-full space-y-1.5">
                        <label for="name" class="text-xs font-bold text-slate-700 uppercase tracking-tight">
                            Document Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $importantDoc->name) }}" required
                               placeholder="e.g. KITAS Raymond Lay"
                               class="w-full px-4 py-2.5 rounded-lg border-slate-200 bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all @error('name') border-rose-500 @enderror">
                        @error('name') <p class="text-[10px] font-bold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Row 3: Expiry Date & File Upload --}}
                    <div class="space-y-1.5">
                        <label for="expired_date" class="text-xs font-bold text-slate-700 uppercase tracking-tight">
                            Expiry Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="expired_date" name="expired_date" value="{{ old('expired_date', optional($importantDoc->expired_date)->format('Y-m-d')) }}" required
                               class="w-full px-4 py-2.5 rounded-lg border-slate-200 bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all @error('expired_date') border-rose-500 @enderror">
                        @error('expired_date') <p class="text-[10px] font-bold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="fileInput" class="text-xs font-bold text-slate-700 uppercase tracking-tight">
                            Add New Attachments
                        </label>
                        <input type="file" id="fileInput" name="files[]" multiple x-ref="fileInput"
                               @change="selectedFiles = Array.from($event.target.files)"
                               class="block w-full text-xs text-slate-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-lg file:border-0
                                      file:text-[10px] file:font-bold file:uppercase
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100 transition-all">
                        
                        {{-- Staging Area --}}
                        <template x-if="selectedFiles.length > 0">
                            <div class="mt-3 p-3 rounded-xl border border-indigo-100 bg-indigo-50/30 space-y-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-black uppercase text-indigo-700 tracking-widest">New Files Staged</span>
                                    <button type="button" @click="selectedFiles = []; $refs.fileInput.value = ''" 
                                            class="text-[10px] font-bold text-rose-500 hover:text-rose-700">Clear Selection</button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="file in selectedFiles" :key="file.name">
                                        <div class="flex items-center gap-2 p-2 bg-white rounded-lg border border-indigo-50 shadow-sm animate-in slide-in-from-bottom-1 duration-200">
                                            <i class="bx bx-paperclip text-indigo-400"></i>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold text-slate-700 truncate capitalize" x-text="file.name"></p>
                                                <p class="text-[8px] font-bold text-slate-400 uppercase" x-text="(file.size / 1024).toFixed(1) + ' KB'"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Row 4: Description --}}
                    <div class="col-span-full space-y-1.5">
                        <label for="description" class="text-xs font-bold text-slate-700 uppercase tracking-tight">
                            Notes <span class="text-slate-400 text-[10px] lowercase italic">(Optional)</span>
                        </label>
                        <textarea id="description" name="description" rows="3"
                                  placeholder="Add any relevant notes or details about this document..."
                                  class="w-full px-4 py-2.5 rounded-lg border-slate-200 bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all @error('description') border-rose-500 @enderror">{{ old('description', $importantDoc->description) }}</textarea>
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('hrd.importantDocs.index') }}" 
                       class="inline-flex h-10 items-center justify-center rounded-xl px-6 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-all">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex h-10 items-center justify-center rounded-xl bg-indigo-600 px-8 text-xs font-bold text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                        <i class="bx bx-save mr-1.5 text-base"></i>
                        Update Document
                    </button>
                </div>
            </form>
        </div>

        {{-- Existing Attachments Gallery --}}
        @if ($importantDoc->files->isNotEmpty())
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Current Attachments</h2>
                    <span class="text-[10px] font-bold text-slate-400 bg-white border border-slate-200 px-2 py-0.5 rounded-full">{{ $importantDoc->files->count() }} Files</span>
                </div>

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
                        @endphp
                        
                        <div class="group relative flex flex-col items-center p-4 rounded-xl border border-slate-100 bg-slate-50/30 hover:bg-white hover:border-indigo-100 hover:shadow-md transition-all">
                            <i class="bx {{ $icon }} text-3xl mb-2"></i>
                            <div class="text-center min-w-0 w-full">
                                <p class="text-[11px] font-bold text-slate-700 truncate px-2" title="{{ $file->name }}">{{ $file->name }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase">{{ strtoupper($extension) }}</p>
                            </div>

                            {{-- Absolute Delete Overlay --}}
                            <form method="POST" action="{{ route('hrd.importantDocs.file.delete', $file->id) }}"
                                  onsubmit="return confirm('Delete this file permanently?')"
                                  class="absolute top-2 right-2 transition-all">
                                @csrf @method('DELETE')
                                <button type="submit" class="h-7 w-7 rounded-lg bg-white border border-rose-100 text-rose-500 hover:bg-rose-700 hover:text-white transition-all shadow-sm flex items-center justify-center" title="Remove Attachment">
                                    <i class="bx bx-trash text-sm"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
