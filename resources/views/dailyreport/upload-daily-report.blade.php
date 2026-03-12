@extends('new.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8" x-data="{ dragging: false, fileName: '' }">
    {{-- Header Section --}}
    <div class="relative z-50 rounded-3xl bg-slate-900 shadow-2xl overflow-hidden">
        {{-- Background glow --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
        </div>

        <div class="relative px-8 py-8 flex items-center gap-6">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 text-white shadow-xl shadow-indigo-500/20 shrink-0">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                    Upload Laporan Harian
                </h1>
                <p class="mt-1 max-w-2xl text-sm font-medium text-slate-400">
                    Aman dan mudah. Unggah file Excel (.xlsx) atau CSV untuk dipratinjau sebelum masuk database.
                </p>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div x-data="{ open: true }" x-show="open" x-transition.opacity.duration.300ms class="flex items-start justify-between gap-4 rounded-2xl border border-emerald-200/60 bg-gradient-to-r from-emerald-50 to-green-50 px-6 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-wide text-emerald-800">Berhasil</h3>
                    <p class="text-xs font-medium text-emerald-600/90">{{ session('success') }}</p>
                </div>
            </div>
            <button @click="open = false" type="button" class="text-emerald-500 hover:text-emerald-700 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @elseif (session('error'))
        <div x-data="{ open: true }" x-show="open" x-transition.opacity.duration.300ms class="flex items-start justify-between gap-4 rounded-2xl border border-rose-200/60 bg-gradient-to-r from-rose-50 to-red-50 px-6 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-wide text-rose-800">Terjadi Kesalahan</h3>
                    <p class="text-xs font-medium text-rose-600/90">{{ session('error') }}</p>
                </div>
            </div>
            <button @click="open = false" type="button" class="text-rose-500 hover:text-rose-700 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    {{-- The Upload Card --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40 p-8">
        <form action="{{ route('daily-report.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            {{-- Drag and Drop Zone --}}
            <div>
                <label class="block text-sm font-bold tracking-wide text-slate-700 mb-3">
                    File Laporan (Batas 10MB)
                </label>
                
                <div 
                    class="relative mt-2 flex justify-center rounded-2xl border-2 border-dashed px-6 py-16 transition-colors duration-200 ease-in-out cursor-pointer"
                    :class="dragging ? 'border-indigo-500 bg-indigo-50/50' : 'border-slate-300 hover:border-indigo-400 hover:bg-slate-50'"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="
                        dragging = false;
                        if($event.dataTransfer.files.length > 0) {
                            $refs.fileInput.files = $event.dataTransfer.files;
                            fileName = $event.dataTransfer.files[0].name;
                        }
                    "
                    @click="$refs.fileInput.click()"
                >
                    <div class="text-center">
                        <div 
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-full transition-colors duration-200"
                            :class="dragging ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-400'"
                            x-show="!fileName"
                        >
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>

                        {{-- Display file name when selected --}}
                        <div x-show="fileName" class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600" style="display: none;">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        
                        <div class="mt-4 flex text-sm text-slate-600 justify-center">
                            <span class="relative rounded-md font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                                <span x-show="!fileName">Klik untuk mengunggah</span>
                                <span x-show="fileName" x-text="fileName" class="text-emerald-700"></span>
                            </span>
                            <p class="pl-1" x-show="!fileName">atau drag and drop</p>
                        </div>
                        <p class="mt-2 text-xs text-slate-500" x-show="!fileName">Format: .xlsx, .csv (Template Standard)</p>
                    </div>

                    {{-- Hidden traditional input --}}
                    <input 
                        type="file" 
                        name="report_file" 
                        id="report_file" 
                        accept=".xlsx,.csv,.txt" 
                        required 
                        class="hidden" 
                        x-ref="fileInput"
                        @change="fileName = $event.target.files[0]?.name || ''"
                    >
                </div>

                @error('report_file')
                    <p class="mt-2 text-xs font-semibold text-rose-600 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Submit Footer --}}
            <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                <p class="text-[11px] font-medium text-slate-500 flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    Aman. Tinjau hasil terlebih dahulu sebelum tersimpan.
                </p>

                <button
                    type="submit"
                    :disabled="!fileName"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none hover:-translate-y-0.5"
                >
                    <span>Lanjutkan & Pratinjau</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
