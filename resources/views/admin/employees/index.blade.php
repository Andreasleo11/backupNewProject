@section('page-title', 'Employee Master')
@section('page-subtitle', 'Management and Audit Desk')

<div class="space-y-6">
    {{-- 1. Dashboard Header (Title + Sync + Stats) --}}
    <div class="mb-8 space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full lg:w-auto">
                <div class="flex items-center gap-3">
                    <div
                        class="h-12 w-12 flex items-center justify-center rounded-2xl bg-slate-900 text-white shadow-xl shrink-0">
                        <i class='bx bx-user-pin text-2xl'></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                            Employee Master
                        </h2>
                        <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest">
                            {{ config('app.name') }} HR Command Center</p>
                    </div>
                </div>

                {{-- Ultra-Minimal Sync Action --}}
                <div
                    class="flex items-center gap-1.5 p-1.5 bg-white border border-slate-200 rounded-2xl shadow-sm self-start sm:self-auto">
                    <button wire:click="sync" wire:loading.attr="disabled"
                        class="h-8 w-8 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-white rounded-lg transition-all disabled:opacity-50 group"
                        title="Sync from JPayroll">
                        <i class='bx bx-refresh text-xl group-hover:rotate-180 transition-all duration-700'
                            wire:loading.class="animate-spin"></i>
                    </button>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="h-9 w-9 flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition-all"
                            title="View Audit History">
                            <i class='bx bx-history text-lg'></i>
                        </button>

                        {{-- Dropdown for recent syncs --}}
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute left-0 sm:left-auto sm:right-0 mt-3 w-72 bg-white rounded-2xl shadow-2xl border border-slate-100 py-4 z-[100] overflow-hidden"
                            style="display: none;">
                            <div class="px-6 pb-2 border-b border-slate-50">
                                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Recent Syncs
                                </h4>
                            </div>
                            <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                @forelse($this->recentSyncs as $log)
                                    <button wire:click="viewLog({{ $log->id }})" @click="open = false"
                                        class="w-full px-6 py-2 flex items-center justify-between hover:bg-slate-50 transition-colors group/row">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-1.5 w-1.5 rounded-full {{ $log->status === 'completed' ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }}">
                                            </div>
                                            <div class="text-left">
                                                <span
                                                    class="text-[10px] font-bold text-slate-900">{{ $log->started_at?->format('d M, H:i') }}</span>
                                                <span class="mx-1 text-slate-300">·</span>
                                                <span
                                                    class="text-[9px] font-medium text-slate-400 uppercase tracking-tighter">{{ $log->total_rows }}
                                                    Rows</span>
                                            </div>
                                        </div>
                                        <i
                                            class='bx bx-right-arrow-alt text-slate-300 opacity-0 group-hover/row:opacity-100 group-hover/row:translate-x-1 transition-all'></i>
                                    </button>
                                @empty
                                    <div class="px-6 py-4">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase">Empty</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- High-Density Metric Grid --}}
            <div class="w-full lg:max-w-2xl grid grid-cols-2 sm:grid-cols-4 gap-3 lg:gap-4">
                @php
                    $stats = [
                        [
                            'label' => 'Total',
                            'value' => $this->globalStats['total'],
                            'icon' => 'group',
                            'color' => 'blue',
                            'info' => 'Total active personnel',
                        ],
                        [
                            'label' => 'Perm',
                            'value' => $this->globalStats['permanent'],
                            'icon' => 'shield',
                            'color' => 'emerald',
                            'info' => 'Inc. Tetap/Asing/Manajemen',
                        ],
                        [
                            'label' => 'Cntrt',
                            'value' => $this->globalStats['contract'],
                            'icon' => 'file',
                            'color' => 'amber',
                            'info' => 'Inc. Kontrak/Magang',
                        ],
                        [
                            'label' => 'KRWG',
                            'value' => $this->globalStats['karawang'],
                            'icon' => 'map-pin',
                            'color' => 'purple',
                            'info' => 'Personnel assigned to Karawang Hub',
                        ],
                    ];
                @endphp
                @foreach ($stats as $stat)
                    <div
                        class="flex items-center gap-3 px-4 py-2.5 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition-all group relative">
                        <div
                            class="h-9 w-9 flex items-center justify-center rounded-xl bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 group-hover:bg-{{ $stat['color'] }}-600 group-hover:text-white transition-all duration-300">
                            <i class='bx bx-{{ $stat['icon'] }} text-xl'></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-1 cursor-help" x-data="{ open: false }"
                                @mouseenter="open = true" @mouseleave="open = false">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter leading-none">
                                    {{ $stat['label'] }}</p>
                                <i
                                    class='bx bx-info-circle text-[8px] text-slate-300 group-hover:text-{{ $stat['color'] }}-400 transition-colors'></i>

                                {{-- Alpine-Powered Tooltip --}}
                                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 p-2 bg-slate-900 text-white text-[8px] font-bold rounded-lg z-[110] whitespace-nowrap shadow-2xl pointer-events-none"
                                    style="display: none;">
                                    {{ $stat['info'] }}
                                    <div
                                        class="absolute top-full left-1/2 -translate-x-1/2 border-8 border-transparent border-t-slate-900">
                                    </div>
                                </div>
                            </div>
                            <p
                                class="text-base font-black text-slate-900 tabular-nums leading-none mt-0.5 tracking-tight">
                                {{ number_format($stat['value']) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 2. Search & Filtering System (Alpine + TALL) --}}
    <div class="space-y-3" x-data="{
        localOpen: @js($showAdvancedFilters),
        init() {
            this.$watch('localOpen', value => $wire.set('showAdvancedFilters', value, false))
        }
    }">
        <div class="flex flex-col md:flex-row items-center gap-3">
            {{-- Primary Search --}}
            <div class="relative flex-1 group w-full">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i
                        class='bx bx-search text-lg text-slate-400 group-focus-within:text-blue-600 transition-colors'></i>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name or NIK..."
                    class="block w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
            </div>

            {{-- Actions Pill --}}
            <div
                class="flex items-center gap-1.5 p-1 bg-white border border-slate-200 rounded-xl shadow-sm self-stretch md:self-auto">
                {{-- Toggle Advanced --}}
                <button @click="localOpen = !localOpen"
                    class="h-9 px-3 flex items-center gap-2 rounded-lg transition-all"
                    :class="localOpen ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'"
                    title="Advanced Filters">
                    <i class='bx bx-slider-alt text-lg'></i>
                    <span class="text-[10px] font-black uppercase tracking-widest hidden sm:block">Filters</span>
                </button>

                <div class="w-px h-5 bg-slate-100 mx-1"></div>

                {{-- Row Density Selector (KISS) --}}
                <select wire:model.live="perPage"
                    class="h-9 px-3 bg-transparent text-[10px] font-black text-slate-700 uppercase tracking-widest outline-none cursor-pointer border-none">
                    @foreach ([10, 25, 50, 100] as $option)
                        <option value="{{ $option }}">{{ $option }} Rows</option>
                    @endforeach
                </select>

                <div class="w-px h-5 bg-slate-100 mx-1"></div>

                {{-- Minimalist Reset --}}
                <button wire:click="resetFilters"
                    class="h-9 w-9 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-all"
                    title="Clear All Filters">
                    <i class='bx bx-x-circle text-xl'></i>
                </button>
            </div>
        </div>

        {{-- Advanced Filters Panel (Collapsible - TALL High Speed) --}}
        <div x-show="localOpen" x-collapse x-cloak
            class="p-4 bg-slate-50 border border-slate-200 rounded-2xl grid grid-cols-1 md:grid-cols-3 gap-4 shadow-inner">
            {{-- Branch Filter --}}
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Branch</label>
                <select wire:model.live="branch"
                    class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                    <option value="">All Branches</option>
                    @foreach ($this->availableBranches as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Department Filter --}}
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Department</label>
                <select wire:model.live="deptCode"
                    class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                    <option value="">All Departments</option>
                    @foreach ($this->availableDepartments as $d)
                        <option value="{{ $d->dept_no }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="space-y-1.5">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Employment
                    Status</label>
                <select wire:model.live="employmentType"
                    class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                    <option value="">All Statuses</option>
                    @foreach ($this->availableEmploymentTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- 3. Main Data Grid --}}
    <x-employee.table :employees="$this->employees" />

    {{-- 4. Overlays & Modals (Teleport to Body) --}}
    <x-employee.modals :preview-data="$previewData" :active-log="$activeLog" :preview-tab="$previewTab" :preview-search="$previewSearch" />

    {{-- 5. Persistent Drawer (Teleport to Body) --}}
    <x-employee.audit-drawer :selected-employee="$selectedEmployee" />

    {{-- Global Module Styles --}}
    <style>
        @keyframes loading {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(400%);
            }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>
</div>
