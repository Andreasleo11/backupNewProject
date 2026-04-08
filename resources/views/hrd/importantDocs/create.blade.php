@extends('new.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-4" x-data="{ 
        typeId: '{{ old('type_id', '') }}',
        get isOther() { return this.typeId === '1' },
        selectedFiles: []
    }">
        {{-- Slim Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Create Important Document</h1>
                <nav class="text-[10px] font-bold uppercase tracking-widest text-slate-400" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1.5 p-0 m-0">
                        <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a></li>
                        <li>/</li>
                        <li><a href="{{ route('hrd.importantDocs.index') }}" class="hover:text-indigo-600 transition-colors">Important Documents</a></li>
                        <li>/</li>
                        <li class="text-slate-500">New Document</li>
                    </ol>
                </nav>
            </div>
            
            <a href="{{ route('hrd.importantDocs.index') }}" 
               class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                <i class="bx bx-arrow-back mr-1.5"></i> Back to List
            </a>
        </div>

        {{-- Main Form Card --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Document Details</h2>
                <p class="text-xs text-slate-500 mt-1 italic italic">Fields marked with <span class="text-rose-500 font-bold">*</span> are mandatory.</p>
            </div>

            <form action="{{ route('hrd.importantDocs.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- Row 1: Category & Document ID --}}
                    <div class="space-y-1.5">
                        <label for="typeSelect" class="text-xs font-bold text-slate-700 uppercase tracking-tight">
                            Category <span class="text-rose-500">*</span>
                        </label>
                        <select id="typeSelect" name="type_id" required x-model="typeId"
                                class="w-full px-4 py-2.5 rounded-lg border-slate-200 bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all @error('type_id') border-rose-500 @enderror">
                            <option value="" disabled selected>Select Category</option>
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
                        <input type="text" id="document_id" name="document_id" value="{{ old('document_id') }}"
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
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               placeholder="e.g. KITAS Raymond Lay"
                               class="w-full px-4 py-2.5 rounded-lg border-slate-200 bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all @error('name') border-rose-500 @enderror">
                        @error('name') <p class="text-[10px] font-bold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Row 3: Expiry Date & File Upload --}}
                    <div class="space-y-1.5">
                        <label for="expired_date" class="text-xs font-bold text-slate-700 uppercase tracking-tight">
                            Expiry Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="expired_date" name="expired_date" value="{{ old('expired_date') }}" required
                               class="w-full px-4 py-2.5 rounded-lg border-slate-200 bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all @error('expired_date') border-rose-500 @enderror">
                        @error('expired_date') <p class="text-[10px] font-bold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="fileInput" class="text-xs font-bold text-slate-700 uppercase tracking-tight">
                            Attach Files
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
                                    <span class="text-[10px] font-black uppercase text-indigo-700 tracking-widest">Ready to Upload</span>
                                    <button type="button" @click="selectedFiles = []; $refs.fileInput.value = ''" 
                                            class="text-[10px] font-bold text-rose-500 hover:text-rose-700">Clear All</button>
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
                                  class="w-full px-4 py-2.5 rounded-lg border-slate-200 bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all @error('description') border-rose-500 @enderror">{{ old('description') }}</textarea>
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="reset" 
                            class="inline-flex h-10 items-center justify-center rounded-xl bg-slate-100 px-6 text-xs font-bold text-slate-600 hover:bg-slate-200 transition-all">
                        Clear Form
                    </button>
                    <button type="submit" 
                            class="inline-flex h-10 items-center justify-center rounded-xl bg-indigo-600 px-8 text-xs font-bold text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                        <i class="bx bx-check-circle mr-1.5 text-base"></i>
                        Save Document
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
