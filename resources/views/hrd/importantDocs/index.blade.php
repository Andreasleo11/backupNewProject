@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-6 space-y-5">
        {{-- Header --}}
        <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">
                    Important Documents
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Daftar dokumen penting yang dikelola oleh HRD untuk kebutuhan operasional & administrasi.
                </p>
            </div>

            <div>
                <a href="{{ route('hrd.importantDocs.create') }}"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm
                          hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <span class="mr-1 text-base leading-none">+</span>
                    <span>Add Document</span>
                </a>
            </div>
        </section>

        {{-- Breadcrumb --}}
        <section>
            <nav class="text-sm" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-1 text-slate-500">
                    <li>
                        <a href="{{ route('hrd') }}" class="hover:text-slate-700 hover:underline">
                            Dashboard
                        </a>
                    </li>
                    <li class="text-slate-400">/</li>
                    <li class="font-medium text-slate-700">
                        Important Documents
                    </li>
                </ol>
            </nav>
        </section>

        {{-- Delete modals per document --}}
        @foreach ($importantDocs as $importantDoc)
            @include('partials.delete-confirmation-modal', [
                'id' => $importantDoc->id,
                'route' => 'hrd.importantDocs.delete',
                'title' => 'Delete Confirmation',
                'body' =>
                    'Are you sure want to delete <strong>' .
                    $importantDoc->name .
                    ' ' .
                    $importantDoc->document_id .
                    '</strong>?',
            ])
        @endforeach

        {{-- Table section --}}
        <section class="mt-2">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-800">
                        Daftar Dokumen
                    </h2>
                    <p class="text-xs text-slate-500">
                        Total:
                        <span class="font-semibold text-slate-800">
                            {{ $importantDocs->count() }}
                        </span>
                        dokumen
                    </p>
                </div>

                <div class="px-3 pb-4 pt-2">
                    <div class="overflow-x-auto">
                        {{-- Biarkan DataTables generate struktur tabel --}}
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
