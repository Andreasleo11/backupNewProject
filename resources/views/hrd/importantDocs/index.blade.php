@extends('new.layouts.app')

@section('content')
    <div class="px-4 py-6 space-y-4">
        {{-- Slim Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    Important Documents
                    <button type="button" title="System Logic: 
- Green: Active/Healthy 
- Amber: Expiring within {{ $thresholdDays }} days 
- Red: Expired/Action Required" 
                        class="text-slate-300 hover:text-indigo-500 transition-colors cursor-help">
                        <i class="bx bx-help-circle text-sm"></i>
                    </button>
                </h1>
                <nav class="text-[10px] font-bold uppercase tracking-widest text-slate-400" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1.5">
                        <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a></li>
                        <li>/</li>
                        <li class="text-slate-500">Document Library</li>
                    </ol>
                </nav>
            </div>

            <a href="{{ route('hrd.importantDocs.create') }}"
               class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition-all active:scale-95">
                <i class="bx bx-plus-circle mr-1.5 text-base"></i>
                Add Document
            </a>
        </div>

        {{-- Main Content Card --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mt-4">
            {{-- Unified Toolbar: Stats & Filters --}}
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                {{-- Compact Stats Pills --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Summary:</span>
                    
                    {{-- Total --}}
                    <div class="inline-flex items-center rounded-full bg-slate-200/50 px-2 py-0.5 text-[10px] font-bold text-slate-700 border border-slate-200 cursor-help"
                         title="Total number of documents managed in the system.">
                        <span class="mr-1 text-slate-400 italic">Total:</span> {{ $stats['total'] }}
                    </div>

                    {{-- Active --}}
                    <div class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200 cursor-help"
                         title="Documents that are healthy and well beyond the warning limit.">
                        <span class="mr-1 text-emerald-500/70 italic">Active:</span> {{ $stats['active'] }}
                    </div>

                    {{-- Expiring Soon --}}
                    <div class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200 cursor-help"
                         title="Documents expiring within your current warning limit ({{ $thresholdDays }} days).">
                        <span class="mr-1 text-amber-500/70 italic">Expiring:</span> {{ $stats['expiring_soon'] }}
                    </div>

                    {{-- Expired --}}
                    <div class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 border border-rose-200 cursor-help {{ $stats['expired'] > 0 ? 'animate-pulse' : '' }}"
                         title="Documents that have already passed their expiry date and need immediate action.">
                        <span class="mr-1 text-rose-500/70 italic">Expired:</span> {{ $stats['expired'] }}
                    </div>
                </div>

                {{-- Interactive Controls --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Category Filter --}}
                    <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg pl-3 pr-1 py-1 shadow-xs hover:border-slate-300 transition-colors group"
                         title="Filter results by document category (e.g. KITAS, BPKB)">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-r border-slate-100 pr-2 group-hover:text-indigo-500 transition-colors">Category</span>
                        <select id="typeFilter" 
                            class="text-[11px] font-bold border-none bg-transparent focus:ring-0 text-slate-700 cursor-pointer py-0 min-w-[100px]">
                            <option value="">All Categories</option>
                            @foreach($types as $type)
                                <option value="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Warning Limit Filter --}}
                    <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg pl-3 pr-1 py-1 shadow-xs hover:border-slate-300 transition-colors group"
                         title="Adjust how early you receive warnings (Amber highlight) for expiring documents.">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-r border-slate-100 pr-2 group-hover:text-amber-500 transition-colors">Warning Limit</span>
                        <form action="{{ route('hrd.importantDocs.index') }}" method="GET" class="flex items-center">
                            <select name="threshold" onchange="this.form.submit()" 
                                class="text-[11px] font-bold border-none bg-transparent focus:ring-0 text-slate-700 cursor-pointer py-0">
                                @foreach([1, 2, 3, 6] as $m)
                                    <option value="{{ $m }}" {{ $threshold == $m ? 'selected' : '' }}>{{ $m }} Months</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Table Wrapper --}}
            <div class="p-3">
                <div class="overflow-x-auto">
                    {{ $dataTable->table(['class' => 'table w-full align-middle text-sm']) }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeFilter = document.getElementById('typeFilter');
            
            if (typeFilter) {
                typeFilter.addEventListener('change', function() {
                    const type = this.value;
                    const table = window.LaravelDataTables["importantdocument-table"];
                    
                    if (table) {
                        // Type is column 2 (0-indexed: ID 0, DocInfo 1, Category 2)
                        table.column(2).search(type).draw();
                    }
                });
            }
        });
    </script>
@endpush
