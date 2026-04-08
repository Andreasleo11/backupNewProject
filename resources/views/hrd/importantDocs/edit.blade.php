@extends('new.layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-6">
        {{-- Header --}}
        <section class="mb-4">
            <h1 class="text-xl font-semibold text-slate-900">
                Edit Important Document
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Perbarui informasi dokumen penting. Pastikan data sesuai sebelum menyimpan perubahan.
            </p>
        </section>

        {{-- Breadcrumb --}}
        <section class="mb-4">
            <nav class="text-sm" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-1 text-slate-500">
                    <li>
                        <a href="{{ route('home') }}" class="hover:text-slate-700 hover:underline">
                            Home
                        </a>
                    </li>
                    <li class="text-slate-400">/</li>
                    <li>
                        <a href="{{ route('hrd.importantDocs.index') }}" class="hover:text-slate-700 hover:underline">
                            Important Documents
                        </a>
                    </li>
                    <li class="text-slate-400">/</li>
                    <li class="font-medium text-slate-700">
                        Edit
                    </li>
                </ol>
            </nav>
        </section>

        {{-- Main Form Card --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm mb-5">
            <div class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-800">
                    Important Document Form
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Mengedit: <span class="font-medium text-slate-700">{{ $importantDoc->name }}</span>
                    @if ($importantDoc->document_id)
                        <span class="text-slate-400">• ID: {{ $importantDoc->document_id }}</span>
                    @endif
                </p>
            </div>

            <div class="px-6 py-6">
                <form action="{{ route('hrd.importantDocs.update', $importantDoc->id) }}" method="POST"
                    enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div class="space-y-1">
                        <label for="name" class="block text-sm font-medium text-slate-700">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $importantDoc->name) }}"
                            required placeholder="Insert name of the document"
                            class="block w-full rounded-md border border-slate-300 bg-white
                                   px-3 py-2.5 text-sm shadow-sm
                                   focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                                   @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">

                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="space-y-1">
                        <label for="type_id" class="block text-sm font-medium text-slate-700">
                            Type <span class="text-red-500">*</span>
                        </label>

                        <select id="type_id" name="type_id" required
                            class="block w-full rounded-md border border-slate-300 bg-white
                                   px-3 py-2.5 text-sm shadow-sm
                                   focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                                   @error('type_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}"
                                    {{ (string) old('type_id', $importantDoc->type_id) === (string) $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('type_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Document ID --}}
                    <div class="space-y-1">
                        <label for="document_id" class="block text-sm font-medium text-slate-700">
                            Document ID <span class="text-slate-400 text-xs">(Optional)</span>
                        </label>
                        <input type="text" id="document_id" name="document_id"
                            value="{{ old('document_id', $importantDoc->document_id) }}"
                            class="block w-full rounded-md border border-slate-300 bg-white
                                   px-3 py-2.5 text-sm shadow-sm
                                   focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                                   @error('document_id') border-red-500 @enderror">

                        @error('document_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="space-y-1">
                        <label for="description" class="block text-sm font-medium text-slate-700">
                            Description <span class="text-slate-400 text-xs">(Optional)</span>
                        </label>
                        <textarea id="description" name="description" rows="3"
                            class="block w-full rounded-md border border-slate-300 bg-white
                                   px-3 py-2.5 text-sm shadow-sm
                                   focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                                   @error('description') border-red-500 @enderror">{{ old('description', $importantDoc->description) }}</textarea>

                        @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Expired Date --}}
                    <div class="space-y-1">
                        <label for="expired_date" class="block text-sm font-medium text-slate-700">
                            Expired Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="expired_date" name="expired_date"
                            value="{{ old('expired_date', optional($importantDoc->expired_date)->format('Y-m-d')) }}"
                            required
                            class="block w-full rounded-md border border-slate-300 bg-white
                                   px-3 py-2.5 text-sm shadow-sm
                                   focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500
                                   @error('expired_date') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">

                        @error('expired_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Upload New Files --}}
                    <div class="space-y-1">
                        <label for="fileInput" class="block text-sm font-medium text-slate-700">
                            Add New File(s) <span class="text-slate-400 text-xs">(Optional)</span>
                        </label>
                        <input type="file" id="fileInput" name="files[]" multiple
                            class="block px-3 py-2.5 w-full text-sm text-slate-700
                                      file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5
                                      file:text-sm file:font-medium file:text-indigo-700
                                      hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-slate-500">
                            File baru akan ditambahkan ke lampiran yang sudah ada.
                        </p>

                        @error('files.*')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                        <a href="{{ route('hrd.importantDocs.detail', $importantDoc->id) }}"
                            class="inline-flex items-center rounded-md border border-slate-200 bg-white
                                  px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50
                                  focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                            Cancel
                        </a>

                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5
                                       text-xs font-semibold text-white shadow-sm hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Existing Attachments Card --}}
        @if ($importantDoc->files->isNotEmpty())
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800">Current Attachments</h2>
                    <span class="text-xs text-slate-500">{{ $importantDoc->files->count() }} file(s)</span>
                </div>

                <div class="px-4 py-4 space-y-2">
                    @foreach ($importantDoc->files as $file)
                        @php
                            $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                            $label = match(true) {
                                in_array($extension, ['pdf'])             => 'PDF',
                                in_array($extension, ['xls','xlsx','csv'])=> 'Sheet',
                                in_array($extension, ['png','jpg','jpeg'])=> 'Image',
                                in_array($extension, ['doc','docx'])      => 'Doc',
                                default                                   => strtoupper($extension ?: 'FILE'),
                            };
                        @endphp

                        <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-white border border-slate-200 text-xs font-semibold text-slate-600">
                                    {{ $label }}
                                </div>
                                <p class="truncate text-sm font-medium text-slate-800" title="{{ $file->name }}">
                                    {{ $file->name }}
                                </p>
                            </div>

                            {{-- Per-file delete form --}}
                            <form method="POST" action="{{ route('hrd.importantDocs.file.delete', $file->id) }}"
                                onsubmit="return confirm('Delete this file attachment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center rounded-md border border-rose-200 bg-rose-50
                                           px-2.5 py-1 text-xs font-medium text-rose-700 hover:bg-rose-100
                                           focus:outline-none focus:ring-2 focus:ring-rose-300 focus:ring-offset-1">
                                    <i class="bx bx-trash-alt mr-1"></i> Remove
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
