@extends('new.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-4">
        {{-- Slim Header --}}
        @if (!request('iframe'))
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Document Details</h1>
                    <nav class="text-[10px] font-bold uppercase tracking-widest text-slate-400" aria-label="Breadcrumb">
                        <ol class="flex items-center gap-1.5 p-0 m-0">
                            <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                            </li>
                            <li>/</li>
                            <li><a href="{{ route('hrd.importantDocs.index') }}"
                                    class="hover:text-indigo-600 transition-colors">Important Documents</a></li>
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
        @endif

        @include('hrd.importantDocs.partials.detail_content')
    </div>
    </div>
@endsection
