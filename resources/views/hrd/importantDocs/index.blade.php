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
                            // Target by name is safer than hardcoded index
                            table.column('status_type:name').search(status).draw();
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
            {{-- Enterprise Tab Bar --}}
            <div class="border-b border-slate-200 bg-slate-50/30">
                <nav class="flex -mb-px px-6 gap-8" aria-label="Tabs">
                    @php
                        $tabLinks = [
                            ['id' => 'all', 'label' => 'All Active', 'count' => $stats['total'], 'icon' => 'bx-list-ul'],
                            ['id' => 'required', 'label' => 'Action Required', 'count' => $stats['action_needed'], 'icon' => 'bx-error', 'color' => 'text-rose-600'],
                            ['id' => 'archived', 'label' => 'Archive', 'count' => $stats['archived'], 'icon' => 'bx-archive'],
                        ];
                    @endphp

                    @foreach($tabLinks as $t)
                        <a href="{{ route('hrd.importantDocs.index', ['tab' => $t['id'], 'threshold' => $threshold]) }}" 
                           class="group inline-flex items-center py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all gap-2 {{ $tab === $t['id'] ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600 hover:border-slate-300' }}">
                            <i class="bx {{ $t['icon'] }} text-lg {{ $tab === $t['id'] ? ($t['color'] ?? 'text-indigo-600') : '' }}"></i>
                            {{ $t['label'] }}
                            @if($t['count'] > 0)
                                <span class="ml-1 rounded-full px-2 py-0.5 text-[10px] font-black {{ $tab === $t['id'] ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200' }}">
                                    {{ $t['count'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Filter Toolbar --}}
            <div class="px-5 py-3 border-b border-slate-100 bg-white flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    {{-- Status Filter Pills (Context Aware) --}}
                    @if($tab !== 'archived')
                    <div class="flex items-center gap-2 pr-4 border-r border-slate-200">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Quick Filter:</span>
                        <button type="button" @click="$store.docLibrary.filterStatus('active')"
                             class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold transition-all border cursor-pointer {{ $tab === 'required' ? 'opacity-50 grayscale' : '' }}"
                             :class="$store.docLibrary.activeStatus === 'active' ? 'bg-emerald-100 text-emerald-800 border-emerald-300 shadow-xs' : 'bg-white text-emerald-600 border-emerald-100 hover:bg-emerald-50'"
                             @if($tab === 'required') disabled title="Not available in this tab" @endif>
                            Active
                        </button>
                        <button type="button" @click="$store.docLibrary.filterStatus('expiring')"
                             class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold transition-all border cursor-pointer"
                             :class="$store.docLibrary.activeStatus === 'expiring' ? 'bg-amber-100 text-amber-800 border-amber-300 shadow-xs' : 'bg-white text-amber-600 border-amber-100 hover:bg-amber-50'">
                            Expiring
                        </button>
                        <button type="button" @click="$store.docLibrary.filterStatus('expired')"
                             class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold transition-all border cursor-pointer"
                             :class="$store.docLibrary.activeStatus === 'expired' ? 'bg-rose-100 text-rose-800 border-rose-300 shadow-xs' : 'bg-white text-rose-600 border-rose-100 hover:bg-rose-50'">
                            Expired
                        </button>
                        <button type="button" @click="$store.docLibrary.filterStatus('')" 
                                x-show="$store.docLibrary.activeStatus !== ''"
                                class="text-[10px] font-bold text-slate-400 hover:text-indigo-600 underline underline-offset-4">
                            Clear
                        </button>
                    </div>
                    @else
                    <div class="flex items-center gap-2 pr-4 border-r border-slate-200">
                        <i class="bx bx-info-circle text-amber-500 text-lg"></i>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest italic">Viewing soft-deleted documents only. Recovery options available in actions.</span>
                    </div>
                    @endif

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
                </div>

                {{-- Warning Limit Filter --}}
                <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg pl-3 pr-1 py-1 shadow-xs hover:border-slate-300 transition-colors group"
                     title="Adjust how early you receive warnings (Amber highlight) for expiring documents.">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-r border-slate-100 pr-2 group-hover:text-amber-500 transition-colors">Warning Limit</span>
                    <form action="{{ route('hrd.importantDocs.index') }}" method="GET" class="flex items-center">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <select name="threshold" onchange="this.form.submit()" 
                            class="text-[11px] font-bold border-none bg-transparent focus:ring-0 text-slate-700 cursor-pointer py-0">
                            @foreach([1, 2, 3, 6] as $m)
                                <option value="{{ $m }}" {{ $threshold == $m ? 'selected' : '' }}>{{ $m }} Months</option>
                            @endforeach
                        </select>
                    </form>
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
