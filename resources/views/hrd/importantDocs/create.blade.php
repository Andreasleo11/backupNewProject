@extends('new.layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-6">
        {{-- Header --}}
        <section class="mb-4">
            <h1 class="text-xl font-semibold text-slate-900">
                Create Important Document
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Lengkapi form di bawah untuk menambahkan dokumen penting baru ke dalam daftar.
            </p>
        </section>

        {{-- Breadcrumb --}}
        <section class="mb-4">
            <nav class="text-sm" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-1 text-slate-500">
                    <li>
                        <a href="{{ route('hrd') }}" class="hover:text-slate-700 hover:underline">
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
                        Create
                    </li>
                </ol>
            </nav>
        </section>

        {{-- Card --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-800">
                    Important Document Form
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Field dengan tanda <span class="text-red-500">*</span> wajib diisi.
                </p>
            </div>

            <div class="px-4">
                <form action="{{ route('hrd.importantDocs.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-5">
                    @csrf

                    {{-- Type --}}
                    <div class="space-y-1">
                        <label for="typeSelect" class="block text-sm font-medium text-slate-700">
                            Type <span class="text-red-500">*</span>
                        </label>

                        <select id="typeSelect" name="type_id" required
                            class="block px-3 py-2.5 w-full rounded-md border-slate-300 text-sm shadow-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       @error('type_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            <option disabled value="" {{ old('type_id') ? '' : 'selected' }}>
                                -- Select document type --
                            </option>

                            @foreach ($types as $type)
                                <option value="{{ $type->id }}"
                                    {{ (string) old('type_id') === (string) $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('type_id')
                            <p class="mt-1 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Other Type (optional, shown conditionally if needed) --}}
                    <div id="otherFormGroup" class="space-y-1 hidden">
                        <label for="otherInput" class="block text-sm font-medium text-slate-700">
                            Other Type Name
                        </label>
                        <input type="text" id="otherInput" name="other" value="{{ old('other') }}"
                            class="block px-3 py-2.5 w-full rounded-md border-slate-300 text-sm shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">

                        @error('other')
                            <p class="mt-1 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Name --}}
                    <div class="space-y-1">
                        <label for="name" class="block text-sm font-medium text-slate-700">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="block px-3 py-2.5 w-full rounded-md border-slate-300 text-sm shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <p class="mt-1 text-xs text-slate-500">
                            Contoh: KITAS Raymond Lay, BPKB Mobil Alphard F 1223 ED
                        </p>

                        @error('name')
                            <p class="mt-1 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Document ID --}}
                    <div class="space-y-1">
                        <label for="document_id" class="block text-sm font-medium text-slate-700">
                            Document ID <span class="text-slate-400 text-xs">(Optional)</span>
                        </label>
                        <input type="text" id="document_id" name="document_id" value="{{ old('document_id') }}"
                            class="block px-3 py-2.5 w-full rounded-md border-slate-300 text-sm shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('document_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <p class="mt-1 text-xs text-slate-500">
                            Contoh: 90S/A8D.89OU
                        </p>

                        @error('document_id')
                            <p class="mt-1 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="space-y-1">
                        <label for="description" class="block text-sm font-medium text-slate-700">
                            Description <span class="text-slate-400 text-xs">(Optional)</span>
                        </label>
                        <textarea id="description" name="description" rows="3"
                            class="block px-3 py-2.5 w-full rounded-md border-slate-300 text-sm shadow-sm
                                         focus:border-indigo-500 focus:ring-indigo-500
                                         @error('description') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('description') }}</textarea>

                        @error('description')
                            <p class="mt-1 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Expired Date --}}
                    <div class="space-y-1">
                        <label for="expired_date" class="block text-sm font-medium text-slate-700">
                            Expired Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="expired_date" name="expired_date" value="{{ old('expired_date') }}"
                            required
                            class="block px-3 py-2.5 w-full rounded-md border-slate-300 text-sm shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('expired_date') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">

                        @error('expired_date')
                            <p class="mt-1 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Upload file --}}
                    <div class="space-y-1">
                        <label for="fileInput" class="block text-sm font-medium text-slate-700">
                            Upload file <span class="text-slate-400 text-xs">(Optional)</span>
                        </label>
                        <input type="file" id="fileInput" name="files[]"
                            class="block px-3 py-2.5 w-full text-sm text-slate-700
                                      file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5
                                      file:text-sm file:font-medium file:text-indigo-700
                                      hover:file:bg-indigo-100
                                      @error('files') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">

                        <p class="mt-1 text-xs text-slate-500">
                            Format yang diizinkan: PDF, images, Excel, dll.
                            <span class="block">
                                Jika type tertentu (misalnya KITAS), beberapa file dapat diupload sekaligus.
                            </span>
                        </p>

                        @error('files')
                            <p class="mt-1 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 py-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="reset"
                            class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50
                                       px-3 py-1.5 text-xs font-medium text-amber-800 hover:bg-amber-100
                                       focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-1">
                            Clear
                        </button>
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
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const typeSelect = document.getElementById('typeSelect');
            const otherForm = document.getElementById('otherFormGroup');
            const otherInput = document.getElementById('otherInput');
            const fileInput = document.getElementById('fileInput');

            function applyTypeLogic() {
                const val = typeSelect.value;

                // === Other type (jika mau dipakai, sesuaikan ID Type "Other" di sini) ===
                // Contoh: jika type_id == 1 dianggap "Other"
                if (val === '1') {
                    otherForm.classList.remove('hidden');
                    otherInput.setAttribute('required', 'required');
                } else {
                    otherForm.classList.add('hidden');
                    otherInput.removeAttribute('required');
                }

                // === Multiple file untuk type tertentu (logic lama: type_id == 3) ===
                if (val === '3') {
                    fileInput.setAttribute('multiple', 'multiple');
                } else {
                    fileInput.removeAttribute('multiple');
                }
            }

            if (typeSelect) {
                typeSelect.addEventListener('change', applyTypeLogic);
                // Jalankan sekali di awal untuk handle old() value
                applyTypeLogic();
            }
        });
    </script>
@endpush
