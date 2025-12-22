@extends('new.layouts.app')

@section('content')
    @php
        use Carbon\Carbon;

        $expiredDate = Carbon::parse($importantDoc->expired_date);
        $isExpired = $expiredDate->isPast();
    @endphp

    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        {{-- Header + Breadcrumb --}}
        <section class="flex flex-col gap-3">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Detail Important Document
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Lihat informasi lengkap dan lampiran untuk dokumen ini.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('hrd.importantDocs.index') }}"
                        class="inline-flex items-center rounded-md border border-slate-200 bg-white
                              px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50
                              focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                        Back
                    </a>

                    <a href="{{ route('hrd.importantDocs.edit', $importantDoc->id) }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5
                              text-xs font-semibold text-white shadow-sm hover:bg-indigo-700
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Edit
                    </a>
                </div>
            </div>

            <nav class="text-xs text-slate-500" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-1">
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
                        Detail
                    </li>
                </ol>
            </nav>
        </section>

        {{-- Detail Card --}}
        <section>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm px-6 py-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-2">
                        <h2 class="text-lg font-semibold text-slate-900 break-words">
                            {{ $importantDoc->name }}
                        </h2>

                        @if ($importantDoc->document_id)
                            <p class="text-xs font-mono text-slate-500">
                                ID: {{ $importantDoc->document_id }}
                            </p>
                        @endif

                        @if ($importantDoc->description)
                            <p class="mt-2 text-sm text-slate-600 whitespace-pre-line">
                                {{ $importantDoc->description }}
                            </p>
                        @endif
                    </div>

                    <div class="space-y-2 text-sm text-right md:text-left">
                        {{-- Type --}}
                        <div class="flex items-center justify-end gap-2 md:justify-start">
                            <span class="text-slate-500">Type</span>
                            <span
                                class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                {{ $importantDoc->type->name }}
                            </span>
                        </div>

                        {{-- Expired Date --}}
                        <div class="flex items-center justify-end gap-2 md:justify-start">
                            <span class="text-slate-500">Expired</span>
                            <span class="text-sm font-medium text-slate-800">
                                {{ $expiredDate->format('d-m-Y') }}
                            </span>
                        </div>

                        {{-- Status --}}
                        <div class="flex items-center justify-end gap-2 md:justify-start">
                            <span class="text-slate-500">Status</span>
                            @if ($isExpired)
                                <span
                                    class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">
                                    Expired
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    Active
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Attachments --}}
        <section aria-label="attachment" class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h3 class="text-sm font-semibold text-slate-800">
                    Attachments
                </h3>
                @if ($importantDoc->files->isNotEmpty())
                    <p class="text-xs text-slate-500">
                        {{ $importantDoc->files->count() }} file attached
                    </p>
                @endif
            </div>

            @if ($importantDoc->files->isNotEmpty())
                <div class="space-y-2">
                    @foreach ($importantDoc->files as $file)
                        @php
                            $filename = $file->name;
                            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $url = asset('storage/importantDocuments/' . $filename);

                            if (in_array($extension, ['pdf'])) {
                                $label = 'PDF';
                            } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
                                $label = 'Spreadsheet';
                            } elseif (in_array($extension, ['png', 'jpg', 'jpeg'])) {
                                $label = 'Image';
                            } elseif (in_array($extension, ['doc', 'docx'])) {
                                $label = 'Document';
                            } else {
                                $label = strtoupper($extension ?: 'FILE');
                            }
                        @endphp

                        <div
                            class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-xs font-semibold text-slate-700">
                                    {{ $label }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-800" title="{{ $filename }}">
                                        {{ $filename }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Click download to save this file.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ $url }}" target="_blank"
                                    class="hidden sm:inline-flex items-center rounded-md border border-slate-200
                                          bg-white px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50
                                          focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                                    Open
                                </a>
                                <a href="{{ $url }}" download="{{ $filename }}"
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1.5
                                          text-xs font-semibold text-white shadow-sm hover:bg-emerald-700
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                                    Download
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                    No attachment were uploaded for this document.
                </div>
            @endif
        </section>
    </div>
@endsection
