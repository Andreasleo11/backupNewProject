@extends('new.layouts.app')

@section('content')
    <div class="px-4 py-6 space-y-4" x-data>
        @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('docLibrary', {
                    detailOpen: false,
                    detailId: null,
                    detailLoading: false,
                    activeStatus: '',
                    sidebarHtml: '',
                    async openDetail(id) {
                        this.detailOpen = true;
                        this.detailId = id;
                        this.detailLoading = true;
                        this.sidebarHtml = ''; // Clear previous

                        try {
                            const response = await fetch('{{ route("hrd.importantDocs.detail", "") }}/' + id, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            this.sidebarHtml = await response.text();
                        } catch (error) {
                            console.error(error);
                            this.sidebarHtml = '<div class="p-8 text-center"><i class="bx bx-error text-3xl text-rose-500 mb-2"></i><p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Failed to load content</p></div>';
                        } finally {
                            this.detailLoading = false;
                        }
                    },
                    filterStatus(status) {
                        this.activeStatus = status;
                        const table = window.LaravelDataTables['importantdocument-table'];
                        if (table) {
                            // status_type is column index 5
                            table.column(5).search(status).draw();
                        }
                    }
                })
            })
        </script>
        @endpush
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
                    <ol class="flex items-center gap-1.5 p-0 m-0">
                        <li><a href="{{ route('home') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a></li>
                        <li>/</li>
                        <li class="text-slate-500">Important Documents</li>
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
                    <button type="button" @click="$store.docLibrary.filterStatus('')"
                         class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold transition-all border cursor-pointer hover:shadow-sm"
                         :class="$store.docLibrary.activeStatus === '' ? 'bg-slate-200 text-slate-800 border-slate-300' : 'bg-slate-100 text-slate-600 border-slate-200 opacity-70 hover:opacity-100'"
                         title="Show all documents">
                        <span class="mr-1 text-slate-400 italic">Total:</span> {{ $stats['total'] }}
                    </button>

                    {{-- Active --}}
                    <button type="button" @click="$store.docLibrary.filterStatus('active')"
                         class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold transition-all border cursor-pointer hover:shadow-sm"
                         :class="$store.docLibrary.activeStatus === 'active' ? 'bg-emerald-100 text-emerald-800 border-emerald-300 scale-105 shadow-xs' : 'bg-emerald-50/50 text-emerald-600 border-emerald-100 opacity-70 hover:opacity-100'"
                         title="Filter healthy documents">
                        <span class="mr-1 text-emerald-500/70 italic">Active:</span> {{ $stats['active'] }}
                    </button>

                    {{-- Expiring Soon --}}
                    <button type="button" @click="$store.docLibrary.filterStatus('expiring')"
                         class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold transition-all border cursor-pointer hover:shadow-sm"
                         :class="$store.docLibrary.activeStatus === 'expiring' ? 'bg-amber-100 text-amber-800 border-amber-300 scale-105 shadow-xs' : 'bg-amber-50/50 text-amber-600 border-amber-100 opacity-70 hover:opacity-100'"
                         title="Filter expiring documents">
                        <span class="mr-1 text-amber-500/70 italic">Expiring:</span> {{ $stats['expiring_soon'] }}
                    </button>

                    {{-- Expired --}}
                    <button type="button" @click="$store.docLibrary.filterStatus('expired')"
                         class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold transition-all border cursor-pointer hover:shadow-sm"
                         :class="$store.docLibrary.activeStatus === 'expired' ? 'bg-rose-100 text-rose-800 border-rose-300 scale-105 shadow-xs' : 'bg-rose-50/50 text-rose-600 border-rose-100 opacity-70 hover:opacity-100'"
                         title="Filter expired documents">
                         <span class="mr-1 text-rose-500/70 italic">Expired:</span> {{ $stats['expired'] }}
                    </button>
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

        {{-- Slide-over Sidebar (Context Preservation Detail) --}}
        <template x-teleport="body">
            <div>
                {{-- Backdrop --}}
                <div x-show="$store.docLibrary.detailOpen" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="$store.docLibrary.detailOpen = false"
                     class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm" x-cloak></div>

                {{-- Sidebar --}}
                <div x-show="$store.docLibrary.detailOpen" 
                     x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="fixed inset-y-0 right-0 z-[60] flex max-w-full pl-10" x-cloak>
                    
                    <div class="w-screen max-w-lg">
                        <div class="flex h-full flex-col bg-white shadow-2xl overflow-hidden rounded-l-2xl">
                            {{-- Sidebar Header --}}
                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <i class="bx bx-file text-indigo-500"></i> Quick Document View
                                </h2>
                                <div class="flex items-center gap-1">
                                    <a :href="'{{ route('hrd.importantDocs.detail', '') }}/' + $store.docLibrary.detailId" 
                                       title="Open Full Page View"
                                       class="rounded-full p-2 text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                                        <i class="bx bx-expand-alt text-xl"></i>
                                    </a>
                                    <button type="button" @click="$store.docLibrary.detailOpen = false" 
                                            title="Close Sidebar"
                                            class="rounded-full p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all">
                                        <i class="bx bx-x text-2xl"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Dynamic Content (Iframe for robust context preservation) --}}
                            <div class="relative flex-1">
                                <template x-if="$store.docLibrary.detailLoading">
                                    <div class="absolute inset-0 z-10 flex items-center justify-center bg-white">
                                        <div class="flex flex-col items-center gap-3">
                                            <i class="bx bx-loader-alt text-4xl text-indigo-600 animate-spin"></i>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Loading Details...</span>
                                        </div>
                                    </div>
                                </template>
                                
                                <div class="p-6 overflow-y-auto h-full" x-html="$store.docLibrary.sidebarHtml" x-show="!$store.docLibrary.detailLoading" x-transition.opacity>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
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
