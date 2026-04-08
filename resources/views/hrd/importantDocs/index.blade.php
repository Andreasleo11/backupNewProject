@extends('new.layouts.app')

@section('page-title', 'Important Documents')
@section('page-subtitle', 'Enterprise Document Lifecycle & Archive Command Center')

@section('content')
    <div class="px-4 py-4 space-y-4" x-data>
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
                            // Target by name is safer than hardcoded index
                            table.column('status_type:name').search(status).draw();
                        }
                    }
                })
            })
        </script>
        @endpush

        {{-- Sleek Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    Command Center
                    <span class="h-6 w-[2px] bg-slate-200 mx-1"></span>
                    <span class="text-slate-400 font-medium text-lg">Document Library</span>
                </h1>
            </div>

            <a href="{{ route('hrd.importantDocs.create') }}"
               class="inline-flex h-10 items-center justify-center rounded-xl bg-indigo-600 px-6 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all active:scale-95">
                <i class="bx bx-plus-circle mr-2 text-lg"></i>
                Add Document
            </a>
        </div>

        {{-- Main Content Card --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-6" x-data="{ filtersOpen: false }">
            {{-- Enterprise Tab Bar --}}
            <div class="border-b border-slate-200 bg-slate-50/30 px-6">
                <nav class="flex -mb-px gap-6" aria-label="Tabs">
                    @php
                        $tabLinks = [
                            ['id' => 'all', 'label' => 'Total Library', 'count' => $stats['total'], 'icon' => 'bx-collection'],
                            ['id' => 'required', 'label' => 'Action Required', 'count' => $stats['action_needed'], 'icon' => 'bx-bell-plus', 'color' => 'text-rose-600'],
                            ['id' => 'archived', 'label' => 'Archive', 'count' => $stats['archived'], 'icon' => 'bx-trash-alt'],
                        ];
                    @endphp

                    @foreach($tabLinks as $t)
                        <a href="{{ route('hrd.importantDocs.index', ['tab' => $t['id'], 'threshold' => $threshold]) }}" 
                           class="group inline-flex items-center py-3 px-1 border-b-2 font-black text-[11px] uppercase tracking-widest transition-all gap-2.5 {{ $tab === $t['id'] ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600 hover:border-slate-300' }}">
                            <i class="bx {{ $t['icon'] }} text-xl {{ $tab === $t['id'] ? ($t['color'] ?? 'text-indigo-600') : '' }}"></i>
                            {{ $t['label'] }}
                            @if($t['count'] > 0)
                                <span class="ml-1 rounded-lg px-2 py-0.5 text-[10px] font-black {{ $tab === $t['id'] ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200' }}">
                                    {{ $t['count'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Optimized Filter Toolbar --}}
            <div class="px-6 py-4 border-b border-slate-100 bg-white flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-6">
                    {{-- Status Filter Pills (Context Aware) --}}
                    @if($tab !== 'archived')
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Toggle Status</span>
                        <div class="flex p-1 bg-slate-100 rounded-xl gap-1">
                            <button type="button" @click="$store.docLibrary.filterStatus('active')"
                                 class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-tighter transition-all {{ $tab === 'required' ? 'hidden' : '' }}"
                                 :class="$store.docLibrary.activeStatus === 'active' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'">
                                Active
                            </button>
                            <button type="button" @click="$store.docLibrary.filterStatus('expiring')"
                                 class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-tighter transition-all"
                                 :class="$store.docLibrary.activeStatus === 'expiring' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'">
                                Expiring
                            </button>
                            <button type="button" @click="$store.docLibrary.filterStatus('expired')"
                                 class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-tighter transition-all"
                                 :class="$store.docLibrary.activeStatus === 'expired' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'">
                                Expired
                            </button>
                            <button type="button" @click="$store.docLibrary.filterStatus('')" 
                                    x-show="$store.docLibrary.activeStatus !== ''"
                                    class="px-2 text-[10px] font-black text-indigo-600 hover:scale-110 transition-transform" title="Clear Filters">
                                <i class="bx bx-x-circle text-lg"></i>
                            </button>
                        </div>
                    </div>
                    @else
                    <div class="flex items-center gap-3 px-4 py-2 bg-amber-50 rounded-xl border border-amber-100">
                        <i class="bx bx-trash-alt text-amber-500 text-xl"></i>
                        <span class="text-[11px] font-bold text-amber-700 leading-tight">You are in the Trash. Documents here have been soft-deleted and can be restored or permanently purged.</span>
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-3 relative">
                    {{-- More Filters Trigger --}}
                    <button type="button" @click="filtersOpen = !filtersOpen"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 hover:border-slate-300 transition-all"
                            :class="filtersOpen ? 'ring-2 ring-indigo-500/20 border-indigo-500 text-indigo-700 bg-indigo-50/50' : ''">
                        <i class="bx bx-filter-alt"></i>
                        More Filters
                        <i class="bx bx-chevron-down transition-transform" :class="filtersOpen ? 'rotate-180' : ''"></i>
                    </button>

                    {{-- Elegant Filter Dropdown --}}
                    <div x-show="filtersOpen" @click.away="filtersOpen = false" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute right-0 top-full mt-2 w-72 bg-white rounded-2xl shadow-2xl border border-slate-200 p-3 z-50 space-y-3">
                        
                        {{-- Category Block --}}
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Filter by Category</label>
                            <div class="relative group">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                    <i class="bx bx-tag-alt"></i>
                                </div>
                                <select id="typeFilter" 
                                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all appearance-none">
                                    <option value="">All Categories</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->name }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Warning Limit Block --}}
                        <div class="space-y-2 pb-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]" title="Docs turn Amber when expiry is closer than this limit">Warning Limit</label>
                            <form action="{{ route('hrd.importantDocs.index') }}" method="GET" class="relative group">
                                <input type="hidden" name="tab" value="{{ $tab }}">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-amber-500 transition-colors">
                                    <i class="bx bx-timer"></i>
                                </div>
                                <select name="threshold" onchange="this.form.submit()" 
                                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all appearance-none">
                                    @foreach([1, 2, 3, 6] as $m)
                                        <option value="{{ $m }}" {{ $threshold == $m ? 'selected' : '' }}>{{ $m }} Months Notice</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <div class="p-6">
                <div class="important-doc-table">
                    {!! $dataTable->table(['class' => 'table table-hover table-striped w-full'], true) !!}
                </div>
            </div>
        </div>

        {{-- Slide-over Sidebar (Offcanvas) --}}
        <template x-teleport="body">
            <div x-show="$store.docLibrary.detailOpen" 
                 class="fixed inset-0 z-[150] overflow-hidden" 
                 style="display: none;"
                 x-description="Slide-over panel, show/hide based on slide-over state.">
                <div class="absolute inset-0 overflow-hidden">
                    {{-- Background overlay --}}
                    <div x-show="$store.docLibrary.detailOpen" 
                         x-transition:enter="ease-in-out duration-500" 
                         x-transition:enter-start="opacity-0" 
                         x-transition:enter-end="opacity-100" 
                         x-transition:leave="ease-in-out duration-500" 
                         x-transition:leave-start="opacity-100" 
                         x-transition:leave-end="opacity-0" 
                         class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" 
                         @click="$store.docLibrary.detailOpen = false"></div>

                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div x-show="$store.docLibrary.detailOpen" 
                             x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" 
                             x-transition:enter-start="translate-x-full" 
                             x-transition:enter-end="translate-x-0" 
                             x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" 
                             x-transition:leave-start="translate-x-0" 
                             x-transition:leave-end="translate-x-full" 
                             class="pointer-events-auto w-screen max-w-2xl">
                            <div class="flex h-full flex-col bg-slate-50 shadow-2xl ring-1 ring-slate-200">
                                {{-- Sidebar Header --}}
                                <div class="bg-white px-6 py-4 border-b border-slate-200 flex items-center justify-between">
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

                                {{-- Dynamic Content (AJAX Injection) --}}
                                <div class="relative flex-1 overflow-y-auto">
                                    <template x-if="$store.docLibrary.detailLoading">
                                        <div class="absolute inset-0 flex items-center justify-center bg-slate-50/50 backdrop-blur-[2px] z-10">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="h-10 w-10 border-4 border-indigo-600/20 border-t-indigo-600 rounded-full animate-spin"></div>
                                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Loading document...</span>
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
            </div>
        </template>
    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
    <script>
        $(document).ready(function() {
            // Category Filter integration - Use column name for robustness
            $('#typeFilter').on('change', function() {
                window.LaravelDataTables['importantdocument-table'].column('type:name').search($(this).val()).draw();
            });
        });
    </script>
@endpush
