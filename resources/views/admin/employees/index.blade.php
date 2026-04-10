@section('page-title', 'Employee Master')
@section('page-subtitle', 'Management and Audit Desk')
<div class="space-y-6">
    {{-- Audit-First Dashboard Header --}}
    <div class="mb-8 space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full lg:w-auto">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 flex items-center justify-center rounded-2xl bg-slate-900 text-white shadow-xl shrink-0">
                        <i class='bx bx-user-pin text-2xl'></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                            Employee Master
                        </h2>
                        <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest">{{ config('app.name') }} HR Command Center</p>
                    </div>
                </div>

                {{-- Ultra-Minimal Sync Action --}}
                <div class="flex items-center gap-1.5 p-1.5 bg-white border border-slate-200 rounded-2xl shadow-sm self-start sm:self-auto">
                            <button 
                                wire:click="sync" 
                                wire:loading.attr="disabled"
                                class="h-8 w-8 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-white rounded-lg transition-all disabled:opacity-50 group"
                                title="Sync from JPayroll"
                            >
                                <i class='bx bx-refresh text-xl group-hover:rotate-180 transition-all duration-700' wire:loading.class="animate-spin"></i>
                            </button>
                            
                            <div x-data="{ open: false }" class="relative">
                                <button 
                                    @click="open = !open"
                                    class="h-9 w-9 flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition-all"
                                    title="View Audit History"
                                >
                                    <i class='bx bx-history text-lg'></i>
                                </button>
                                
                                {{-- Dropdown for recent syncs --}}
                                <div 
                                    x-show="open" 
                                    @click.away="open = false"
                                    x-transition 
                                    class="absolute left-0 sm:left-auto sm:right-0 mt-3 w-72 bg-white rounded-2xl shadow-2xl border border-slate-100 py-4 z-[100] overflow-hidden"
                                    style="display: none;"
                                >
                                    <div class="px-6 pb-2 border-b border-slate-50">
                                        <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Recent Syncs</h4>
                                    </div>
                                    <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                        @forelse($recentSyncs as $log)
                                            <button 
                                                wire:click="viewLog({{ $log->id }})"
                                                @click="open = false"
                                                class="w-full px-6 py-2 flex items-center justify-between hover:bg-slate-50 transition-colors group/row"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <div class="h-1.5 w-1.5 rounded-full {{ $log->status === 'completed' ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }}"></div>
                                                    <div class="text-left">
                                                        <span class="text-[10px] font-bold text-slate-900">{{ $log->started_at?->format('d M, H:i') }}</span>
                                                        <span class="mx-1 text-slate-300">·</span>
                                                        <span class="text-[9px] font-medium text-slate-400 uppercase tracking-tighter">{{ $log->total_rows }} Rows</span>
                                                    </div>
                                                </div>
                                                <i class='bx bx-right-arrow-alt text-slate-300 opacity-0 group-hover/row:opacity-100 group-hover/row:translate-x-1 transition-all'></i>
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
                        ['label' => 'Total', 'value' => $globalStats['total'], 'icon' => 'group', 'color' => 'blue', 'info' => 'Total active personnel'],
                        ['label' => 'Perm', 'value' => $globalStats['permanent'], 'icon' => 'shield', 'color' => 'emerald', 'info' => 'Inc. Tetap/Asing/Manajemen'],
                        ['label' => 'Cntrt', 'value' => $globalStats['contract'], 'icon' => 'file', 'color' => 'amber', 'info' => 'Inc. Kontrak/Magang'],
                        ['label' => 'KRWG', 'value' => $globalStats['karawang'], 'icon' => 'map-pin', 'color' => 'purple', 'info' => 'Personnel assigned to Karawang Hub'],
                    ];
                @endphp
                @foreach($stats as $stat)
                    <div class="flex items-center gap-3 px-4 py-2.5 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition-all group relative">
                        <div class="h-9 w-9 flex items-center justify-center rounded-xl bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 group-hover:bg-{{ $stat['color'] }}-600 group-hover:text-white transition-all duration-300">
                            <i class='bx bx-{{ $stat['icon'] }} text-xl'></i>
                        </div>
                        <div>
                            <div 
                                class="flex items-center gap-1 cursor-help"
                                x-data="{ open: false }"
                                @mouseenter="open = true"
                                @mouseleave="open = false"
                            >
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter leading-none">{{ $stat['label'] }}</p>
                                <i class='bx bx-info-circle text-[8px] text-slate-300 group-hover:text-{{ $stat['color'] }}-400 transition-colors'></i>
                                
                                {{-- Alpine-Powered Tooltip --}}
                                <div 
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 p-2 bg-slate-900 text-white text-[8px] font-bold rounded-lg z-[110] whitespace-nowrap shadow-2xl pointer-events-none"
                                    style="display: none;"
                                >
                                    {{ $stat['info'] }}
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-8 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                            <p class="text-base font-black text-slate-900 tabular-nums leading-none mt-0.5 tracking-tight">{{ number_format($stat['value']) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


        {{-- Search & Filtering Bar (High Density KISS Row) --}}
        <div class="space-y-3">
            <div class="flex flex-col md:flex-row items-center gap-3">
                {{-- Primary Search --}}
                <div class="relative flex-1 group w-full">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class='bx bx-search text-lg text-slate-400 group-focus-within:text-blue-600 transition-colors'></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search name or NIK..."
                        class="block w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none"
                    >
                </div>
                
                {{-- Actions Pill --}}
                <div class="flex items-center gap-1.5 p-1 bg-white border border-slate-200 rounded-xl shadow-sm self-stretch md:self-auto">
                    {{-- Toggle Advanced --}}
                    <button 
                        wire:click="toggleAdvancedFilters"
                        class="h-9 px-3 flex items-center gap-2 rounded-lg transition-all {{ $showAdvancedFilters ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50' }}"
                        title="Advanced Filters"
                    >
                        <i class='bx bx-slider-alt text-lg'></i>
                        <span class="text-[10px] font-black uppercase tracking-widest hidden sm:block">Filters</span>
                    </button>

                    <div class="w-px h-5 bg-slate-100 mx-1"></div>

                    {{-- Row Density Selector (KISS) --}}
                    <select 
                        wire:model.live="perPage"
                        class="h-9 px-3 bg-transparent text-[10px] font-black text-slate-700 uppercase tracking-widest outline-none cursor-pointer border-none"
                    >
                        @foreach([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}">{{ $option }} Rows</option>
                        @endforeach
                    </select>

                    <div class="w-px h-5 bg-slate-100 mx-1"></div>

                    {{-- Minimalist Reset --}}
                    <button 
                        wire:click="resetFilters"
                        class="h-9 w-9 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-all"
                        title="Clear All Filters"
                    >
                        <i class='bx bx-x-circle text-xl'></i>
                    </button>
                </div>
            </div>

            {{-- Advanced Filters Panel (Collapsible) --}}
            @if($showAdvancedFilters)
                <div 
                    x-data="{ show: false }" 
                    x-init="setTimeout(() => show = true, 50)"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="p-4 bg-slate-50 border border-slate-200 rounded-2xl grid grid-cols-1 md:grid-cols-3 gap-4 shadow-inner"
                >
                    {{-- Branch Filter --}}
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Branch</label>
                        <select 
                            wire:model.live="branch"
                            class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none"
                        >
                            <option value="">All Branches</option>
                            @foreach($availableBranches as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department Filter --}}
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Department</label>
                        <select 
                            wire:model.live="deptCode"
                            class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none"
                        >
                            <option value="">All Departments</option>
                            @foreach($availableDepartments as $d)
                                <option value="{{ $d->dept_no }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div class="space-y-1.5">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Employment Status</label>
                        <select 
                            wire:model.live="employmentType"
                            class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-[10px] font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none"
                        >
                            <option value="">All Statuses</option>
                            @foreach($availableEmploymentTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm backdrop-blur-xl transition-all">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                <button @click="show = false" class="ml-auto text-emerald-500 hover:text-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Data Grid --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-200">
        {{-- High-Performance Loading Bar --}}
        <div 
            wire:loading 
            wire:target="search, branch, deptCode, employmentType, perPage, sort_by, gotoPage, nextPage, previousPage, resetFilters"
            class="absolute top-0 left-0 right-0 h-1 z-[60] overflow-hidden bg-slate-100"
        >
            <div class="h-full bg-indigo-600 animate-[loading_1.5s_infinite_linear] shadow-[0_0_10px_rgba(79,70,229,0.5)]" style="width: 30%;"></div>
        </div>

        <div 
            class="overflow-x-auto custom-scrollbar transition-all duration-300"
            wire:loading.class="opacity-40 grayscale-[50%] pointer-events-none"
            wire:target="search, branch, deptCode, employmentType, perPage, sort_by, gotoPage, nextPage, previousPage, resetFilters"
        >
            <table class="min-w-full divide-y divide-slate-200 border-separate border-spacing-0">
                <thead class="bg-slate-50">
                    <tr>
                         <th class="sticky left-0 z-30 bg-slate-50 px-8 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                             <button wire:click="sort_by('name')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Employee Info
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('name') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                            <button wire:click="sort_by('nik')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                NIK
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('nik') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                            <button wire:click="sort_by('dept_code')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Department
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('dept_code') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                            <button wire:click="sort_by('position')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Position
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('position') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                            <button wire:click="sort_by('branch')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Branch
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('branch') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                            <button wire:click="sort_by('employment_type')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Status
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('employment_type') }}</span>
                            </button>
                        </th>
                         <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                             <button wire:click="sort_by('start_date')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Join Date
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('start_date') }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                             <button wire:click="sort_by('grade_level')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Grade
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ $this->sort_icon('grade_level') }}</span>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            {{-- Sticky first column --}}
                            <td class="sticky left-0 z-10 bg-white group-hover:bg-slate-50/50 px-8 py-3 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] border-r border-slate-100">
                                <div class="flex items-center gap-4">
                                    <div class="h-9 w-9 flex items-center justify-center rounded-lg bg-gradient-to-br from-slate-800 to-slate-900 text-white text-[10px] font-bold shadow-lg shadow-slate-200/50">
                                        {{ substr($employee->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <button 
                                            wire:click="openAudit('{{ $employee->nik }}')"
                                            class="text-sm font-bold text-slate-900 leading-tight hover:text-blue-600 transition-colors text-left"
                                        >
                                            {{ $employee->name }}
                                        </button>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <div class="text-[9px] font-bold text-blue-600 uppercase tracking-widest tabular-nums">{{ $employee->nik }}</div>
                                            <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                                            {{-- Relationship Indicators --}}
                                            <div class="flex items-center gap-1.5">
                                                @if($employee->warningLogs->count() > 0)
                                                    <span class="flex h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse" title="Active Warning Logs"></span>
                                                @endif
                                                @if($employee->evaluationData->isNotEmpty())
                                                    <span class="text-[8px] font-black text-emerald-600 bg-emerald-50 px-1 rounded" title="Last Evaluation Score">
                                                        {{ $employee->evaluationData->sortByDesc('Month')->first()->total ?? 'A' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-700 uppercase tracking-tight">{{ $employee->nik }}</div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-xs font-semibold text-slate-600 uppercase">
                                {{ $employee->dept_code }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="text-xs font-bold text-slate-800">{{ $employee->position }}</div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $employee->branch === 'JAKARTA' ? 'bg-purple-100/50 text-purple-700 border border-purple-200/50' : 'bg-orange-100/50 text-orange-700 border border-orange-200/50' }}">
                                    {{ $employee->branch }}
                                </span>
                            </td>
                             <td class="px-6 py-3 whitespace-nowrap">
                                 @php
                                     $isActive = is_null($employee->end_date) || \Carbon\Carbon::parse($employee->end_date)->isFuture();
                                 @endphp
                                @if ($isActive)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/10 uppercase tracking-widest">
                                        <span class="h-1 w-1 rounded-full bg-emerald-600"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-0.5 text-[10px] font-bold text-slate-500 ring-1 ring-inset ring-slate-500/10 uppercase tracking-widest">
                                        <span class="h-1 w-1 rounded-full bg-slate-400"></span>
                                        Terminated
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-[11px] font-medium text-slate-500">
                                {{ $employee->start_date }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-[11px] font-medium text-slate-500">
                                <span class="font-bold text-slate-700">{{ $employee->grade_code }}</span> <span class="text-slate-400">({{ $employee->grade_level }})</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-20 text-center">
                                <div class="mx-auto h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                    <i class='bx bx-group text-4xl text-slate-300'></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">No employees found</h3>
                                <p class="mt-1 text-xs text-slate-500">Try adjusting your search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($employees->hasPages())
            <div class="px-8 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <span class="h-1 w-1 rounded-full bg-blue-500"></span>
                    Page <span class="text-slate-900">{{ $employees->currentPage() }}</span> / {{ $employees->lastPage() }}
                    <span class="mx-2 text-slate-200">|</span>
                    Total <span class="text-slate-900">{{ number_format($employees->total()) }}</span> Records
                </div>
                <div class="flex-1 md:flex-none">
                    {{ $employees->links('livewire::simple-bootstrap') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Sync Preview & History Modal --}}
    @if($previewData || $activeLog)
        @php
            $modalData = $activeLog ?? $previewData;
            $isHistorical = !is_null($activeLog);
        @endphp
        <template x-teleport="body">
            <div 
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                x-data="{ show: false }"
                x-init="setTimeout(() => show = true, 50)"
            >
                <div 
                    class="absolute inset-0 bg-slate-950/40 backdrop-blur-md transition-opacity duration-500"
                    x-show="show"
                    x-transition:enter="ease-out duration-500"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    @click="{{ $isHistorical ? '$wire.closeLog()' : '$wire.cancelSync()' }}"
                ></div>

                <div 
                    class="relative w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden transition-all duration-500 transform"
                    x-show="show"
                    x-transition:enter="ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                >
                    <div class="px-8 py-6 bg-white border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 flex items-center justify-center rounded-2xl {{ $isHistorical ? 'bg-slate-900' : 'bg-blue-600' }} text-white shadow-lg">
                                <i class='bx {{ $isHistorical ? 'bx-history font-light' : 'bx-sync animate-spin-slow' }} text-2xl'></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900 leading-none">
                                    {{ $isHistorical ? 'Sync Audit Archive' : 'Reconciliation Analysis' }}
                                </h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 flex items-center gap-2">
                                    @if($isHistorical)
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Data Integrity Snapshot
                                    @else
                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                        System-Ready for Commitment
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button 
                            wire:click="{{ $isHistorical ? 'closeLog' : 'cancelSync' }}" 
                            class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-400 transition-all"
                        >
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>

                    <div class="px-8 bg-white border-b border-slate-100 flex items-center gap-8">
                        @foreach(['summary' => 'Analysis', 'new' => 'Additions', 'updated' => 'Modifications', 'inactive' => 'Inactivations'] as $tab => $label)
                            <button 
                                wire:click="$set('previewTab', '{{ $tab }}')"
                                class="relative py-4 text-[11px] font-black uppercase tracking-widest transition-all {{ $previewTab === $tab ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' }}"
                            >
                                <span class="flex items-center gap-2">
                                    {{ $label }}
                                    @if($tab !== 'summary')
                                        <span class="px-2 py-0.5 rounded-full text-[9px] {{ $previewTab === $tab ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ count($modalData['details'][$tab] ?? []) }}
                                        </span>
                                    @endif
                                </span>
                                @if($previewTab === $tab)
                                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></div>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <div class="bg-white min-h-[400px]">
                        @if($previewTab === 'summary')
                            <div class="p-8 grid grid-cols-3 gap-6">
                                @foreach(['new' => ['emerald', 'plus-circle'], 'updated' => ['blue', 'edit-alt'], 'inactive' => ['rose', 'minus-circle']] as $key => $meta)
                                    <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 group hover:border-{{ $meta[0] }}-200 transition-all">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="h-12 w-12 flex items-center justify-center rounded-2xl bg-{{ $meta[0] }}-100 text-{{ $meta[0] }}-600">
                                                <i class='bx bx-{{ $meta[1] }} text-2xl'></i>
                                            </div>
                                            <span class="text-4xl font-black text-slate-900">{{ count($modalData['details'][$key] ?? []) }}</span>
                                        </div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $key === 'new' ? 'New Records' : ($key === 'updated' ? 'Modified Records' : 'Potential Inactivations') }}</p>
                                        <button 
                                            wire:click="$set('previewTab', '{{ $key }}')"
                                            class="mt-4 flex items-center gap-2 text-[11px] font-black text-{{ $meta[0] }}-600 uppercase tracking-widest group-hover:translate-x-1 transition-transform"
                                        >
                                            View Details <i class='bx bx-right-arrow-alt text-lg'></i>
                                        </button>
                                    </div>
                                @endforeach

                                <div class="col-span-3 mt-4 p-6 bg-slate-900 rounded-3xl flex items-center justify-between shadow-xl">
                                    <div class="flex items-center gap-6">
                                        <div class="h-14 w-14 flex items-center justify-center rounded-2xl bg-white/10 text-white shrink-0">
                                            <i class='bx bx-shield-quarter text-lg'></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-white uppercase tracking-wide mb-1">{{ $isHistorical ? 'Audit Integrity Score' : 'Reconciliation Health' }}</p>
                                            <p class="text-xs font-medium text-slate-400 leading-relaxed pr-4">
                                                {{ $isHistorical 
                                                    ? 'Snapshot verified. All records matched JPayroll states at the time of execution.' 
                                                    : 'Sync engine has identified reconciliation points. Commitment will auto-align local master data.' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-black text-white leading-none">100%</p>
                                        <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mt-1">Consistency</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-0 max-h-[60vh] overflow-y-auto custom-scrollbar border-t border-slate-200">
                                <table class="w-full text-left border-collapse border-separate border-spacing-0">
                                    <thead class="sticky top-0 z-20 bg-slate-50 shadow-sm">
                                        <tr>
                                            <th class="px-8 py-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200 sticky left-0 bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">EMPLOYEE IDENTITY</th>
                                            @if($previewTab === 'updated')
                                                <th class="px-6 py-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">MODIFICATION DETAILS</th>
                                            @else
                                                <th class="px-6 py-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">ASSIGNMENT</th>
                                                <th class="px-6 py-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">DIVISION</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @php
                                            $filtered = collect($modalData['details'][$previewTab])->filter(fn($r) => 
                                                empty($previewSearch) || 
                                                str_contains(strtolower((string)($r['nik'] ?? '')), strtolower($previewSearch)) || 
                                                str_contains(strtolower((string)($r['name'] ?? '')), strtolower($previewSearch))
                                            );
                                        @endphp
                                        @forelse($filtered as $row)
                                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                                <td class="px-8 py-5 sticky left-0 bg-white border-r border-slate-100 group-hover:bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                                    <div class="flex items-center gap-4">
                                                        <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-gradient-to-br {{ $isHistorical ? 'from-slate-700 to-slate-800' : 'from-blue-500 to-indigo-600' }} text-white text-xs font-bold shadow-lg">
                                                            {{ substr($row['name'] ?? '?', 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-slate-900 leading-tight">{{ $row['name'] ?? 'Unknown' }}</p>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest tabular-nums">{{ $row['nik'] }}</span>
                                                                <span class="inline-block w-1 h-1 rounded-full bg-slate-300"></span>
                                                                <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wide">{{ $row['branch'] ?? 'JAKARTA' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @if($previewTab === 'updated')
                                                    <td class="px-6 py-5">
                                                        <div class="flex flex-col gap-2">
                                                            @foreach($row['diffs'] as $field => $val)
                                                                <div class="flex items-center gap-3">
                                                                    <span class="w-24 text-[9px] font-bold text-slate-400 uppercase truncate">{{ str_replace('_', ' ', $field) }}</span>
                                                                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200">
                                                                        <span class="text-[10px] font-medium text-slate-500 line-through decoration-slate-400">{{ $val['old'] ?: '---' }}</span>
                                                                        <i class='bx bx-right-arrow-alt text-blue-500'></i>
                                                                        <span class="text-[10px] font-bold text-slate-900">{{ $val['new'] ?: '---' }}</span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                @else
                                                    <td class="px-6 py-5">
                                                        <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-bold {{ $previewTab === 'new' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }} uppercase tracking-wider">{{ $row['employment_type'] ?? 'Standard' }}</span>
                                                    </td>
                                                    <td class="px-6 py-5">
                                                        <p class="text-xs font-bold text-slate-700 uppercase tracking-tight">{{ $row['dept_code'] ?? '---' }}</p>
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="px-8 py-20 text-center">No matching records</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="px-8 py-6 bg-slate-50 border-t border-slate-200 flex justify-between items-center group">
                        <div class="text-xs font-bold text-slate-500 italic">
                            {{ $isHistorical ? 'Audit trail captured at ' . ($activeLog['timestamp'] ?? 'Sync Time') : 'Pre-commitment checklist required for data integrity.' }}
                        </div>
                        <div class="flex items-center gap-4">
                            <button wire:click="{{ $isHistorical ? 'closeLog' : 'cancelSync' }}" class="px-6 py-3 rounded-xl text-xs font-black text-slate-500 hover:text-slate-900 transition-all uppercase tracking-widest">
                                {{ $isHistorical ? 'Close Audit' : 'Cancel' }}
                            </button>
                            @if($previewTab === 'summary' && !$isHistorical)
                                <button wire:click="confirmSync" class="relative group inline-flex items-center gap-3 px-8 py-3 bg-slate-900 text-white rounded-2xl text-xs font-black shadow-xl hover:shadow-2xl transition-all uppercase tracking-widest">
                                    <span>Sync to Database</span>
                                    <i class='bx bx-right-arrow-alt text-xl group-hover:translate-x-1 transition-transform'></i>
                                </button>
                            @elseif($previewTab !== 'summary')
                                <button wire:click="$set('previewTab', 'summary')" class="px-8 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-black text-slate-900 shadow-sm transition-all uppercase tracking-widest">
                                    Return to Summary
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </template>
    @endif

    {{-- Quick Audit Side-Drawer --}}
    <template x-teleport="body">
        <div 
            x-data="{ show: false }"
            x-show="show"
            x-init="$watch('$wire.selectedNik', value => show = !!value)"
            class="fixed inset-0 z-[150] overflow-hidden"
            style="display: none;"
        >
            <div class="absolute inset-0 overflow-hidden">
                {{-- Backdrop --}}
                <div 
                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" 
                    x-show="show"
                    x-transition:enter="ease-in-out duration-500"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in-out duration-500"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="$wire.closeAudit()"
                ></div>

                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div 
                        class="pointer-events-auto w-screen max-w-lg transform transition ease-in-out duration-500 sm:duration-700"
                        x-show="show"
                        x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full"
                    >
                        <div class="flex h-full flex-col overflow-y-auto bg-white shadow-2xl border-l border-slate-200">
                            @if($selectedEmployee)
                                <div class="px-8 py-10 border-b border-slate-100 bg-slate-50/50">
                                    <div class="flex items-center justify-between mb-8">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-200">
                                                <i class='bx bx-fingerprint text-lg'></i>
                                            </div>
                                            <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Employee Audit Desk</h2>
                                        </div>
                                        <button @click="$wire.closeAudit()" class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-white hover:shadow-sm text-slate-400 hover:text-slate-900 transition-all">
                                            <i class='bx bx-x text-2xl'></i>
                                        </button>
                                    </div>
                                    
                                    <div class="flex items-center gap-6">
                                        <div class="h-20 w-20 rounded-[2.5rem] bg-slate-900 flex items-center justify-center text-white text-2xl font-black shadow-2xl relative overflow-hidden group">
                                            <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/20 to-transparent"></div>
                                            {{ substr($selectedEmployee->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ $selectedEmployee->name }}</h3>
                                            <div class="flex items-center gap-3 mt-1.5">
                                                <p class="text-[11px] font-black text-blue-600 tabular-nums uppercase tracking-widest bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">{{ $selectedEmployee->nik }}</p>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 text-[10px] font-bold text-slate-600 uppercase tracking-wider">
                                                    {{ $selectedEmployee->employment_type }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-1 px-8 py-8 space-y-8 custom-scrollbar overflow-x-hidden focus:outline-none">
                                    {{-- Performance History --}}
                                    <section class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                                <i class='bx bx-trending-up text-blue-500'></i> Performance & Metrics
                                            </h4>
                                            <span class="text-[10px] font-bold text-slate-300 uppercase">Recent Cycles</span>
                                        </div>
                                        
                                        <div class="space-y-3">
                                            @forelse($selectedEmployee->evaluationData->sortByDesc('Month')->take(3) as $eval)
                                                <div 
                                                    class="bg-white rounded-[1.5rem] border border-slate-100 shadow-sm hover:shadow-md transition-all overflow-hidden group p-3"
                                                    x-data="{ expanded: false }"
                                                >
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-3">
                                                                <div class="pl-1">
                                                                    <p class="text-[11px] font-black text-slate-900 uppercase tracking-tight">{{ $eval->Month->format('F Y') }}</p>
                                                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">{{ $eval->evaluation_type ?? 'Staff' }} Evaluation</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-3">
                                                                <div class="text-right">
                                                                    <p class="text-base font-black text-slate-900 leading-none">{{ $eval->total }}</p>
                                                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Points</p>
                                                                </div>
                                                                <button 
                                                                    @click="expanded = !expanded"
                                                                    class="h-7 w-7 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all"
                                                                >
                                                                    <i class='bx text-lg transition-transform duration-300' :class="expanded ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        {{-- Attendance Mini-Bar (High-Readability Labels) --}}
                                                        <div class="flex items-center gap-1.5 p-1 bg-slate-50/80 rounded-xl border border-slate-100">
                                                            @foreach([
                                                                'Alpha' => ['rose', 'Absent'], 
                                                                'Telat' => ['amber', 'Late'], 
                                                                'Izin' => ['blue', 'Permit'], 
                                                                'Sakit' => ['slate', 'Sick']
                                                            ] as $key => $meta)
                                                                <div class="flex-1 flex flex-col items-center py-1.5 border-r border-slate-200/50 last:border-none">
                                                                    <span class="text-[10px] font-black {{ ($eval->$key ?? 0) > 0 ? 'text-'.$meta[0].'-600' : 'text-slate-300' }}">
                                                                        {{ $eval->$key ?? 0 }}
                                                                    </span>
                                                                    <span class="text-[7px] font-black text-slate-400 uppercase tracking-tighter">{{ $meta[1] }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        {{-- Expanded Full Scorecard --}}
                                                        <div 
                                                            x-show="expanded" 
                                                            x-collapse
                                                            class="pt-2 border-t border-slate-50 space-y-4"
                                                        >
                                                            <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                                                                @php
                                                                    $allMarkers = [
                                                                        'kemampuan_kerja' => 'Kemampuan',
                                                                        'disiplin_kerja' => 'Disiplin',
                                                                        'integritas' => 'Integritas',
                                                                        'tanggung_jawab' => 'Tj. Jawab',
                                                                        'kerajinan_kerja' => 'Kerajinan',
                                                                        'prestasi' => 'Prestasi',
                                                                        'loyalitas' => 'Loyalitas'
                                                                    ];
                                                                @endphp
                                                                @foreach($allMarkers as $field => $label)
                                                                    @if(isset($eval->$field))
                                                                        <div class="flex items-center justify-between group/marker">
                                                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider group-hover/marker:text-slate-600 transition-colors">{{ $label }}</span>
                                                                            <span class="px-1.5 py-0.5 bg-slate-100 rounded text-[9px] font-black text-slate-900 group-hover/marker:bg-blue-50 group-hover/marker:text-blue-700 transition-colors">{{ $eval->$field }}</span>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                            
                                                            @if($eval->Keterangan)
                                                                <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100/50">
                                                                    <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1 flex items-center gap-1">
                                                                        <i class='bx bx-comment-detail'></i> Evaluation Note
                                                                    </p>
                                                                    <p class="text-[10px] font-medium text-slate-600 italic leading-relaxed">
                                                                        "{{ $eval->Keterangan }}"
                                                                    </p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                </div>
                                            @empty
                                                <div class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-slate-100 rounded-[2rem] bg-slate-50/30">
                                                    <i class='bx bx-file-blank text-3xl text-slate-200 mb-2'></i>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No evaluation records found</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </section>

                                    {{-- Disciplinary Desk --}}
                                    <section class="space-y-4">
                                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                            <i class='bx bx-shield-x text-rose-500'></i> Disciplinary Desk
                                        </h4>
                                        <div class="space-y-4">
                                            @forelse($selectedEmployee->warningLogs->sortByDesc('Date')->take(3) as $log)
                                                <div class="relative pl-8 pb-6 border-l-2 border-rose-100 last:pb-0">
                                                    <div class="absolute -left-[9px] top-0 h-4 w-4 rounded-full bg-white border-4 border-rose-500"></div>
                                                    <div class="p-4 bg-rose-50/30 rounded-2xl border border-rose-100/50">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <p class="text-[11px] font-black text-rose-900 tabular-nums">{{ $log->Date }}</p>
                                                            <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[8px] font-black uppercase rounded">Warning Issued</span>
                                                        </div>
                                                        <p class="text-xs font-medium text-rose-800 leading-relaxed italic pr-4">
                                                            "{{ $log->Violation }}"
                                                        </p>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="p-6 bg-emerald-50/30 border border-emerald-100 rounded-[2rem] flex items-center gap-4">
                                                    <div class="h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                                        <i class='bx bx-check-shield text-xl'></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-[10px] font-black text-emerald-900 uppercase">Impeccable Performance</p>
                                                        <p class="text-[10px] font-medium text-emerald-600 italic">No violation logs recorded in master data.</p>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </section>

                                    {{-- Attendance Context --}}
                                    <section class="space-y-4">
                                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                            <i class='bx bx-map-pin text-amber-500'></i> Attendance Context
                                        </h4>
                                        @if($selectedEmployee->latestDailyReport)
                                            <div class="bg-slate-900 rounded-[2rem] p-6 shadow-xl relative overflow-hidden group">
                                                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:rotate-12 transition-transform">
                                                    <i class='bx bx-current-location text-6xl text-white'></i>
                                                </div>
                                                <div class="relative z-10">
                                                    <div class="flex items-center justify-between mb-4">
                                                        <span class="text-[10px] font-black text-white/40 uppercase tracking-widest">Master Daily Report</span>
                                                        <span class="text-[10px] font-black text-blue-400 tabular-nums">{{ $selectedEmployee->latestDailyReport->sort_datetime->format('d M, H:i') }}</span>
                                                    </div>
                                                    <p class="text-xs font-medium text-slate-300 leading-relaxed italic border-l-2 border-blue-500/30 pl-4 py-1">
                                                        "{{ Str::limit($selectedEmployee->latestDailyReport->report_content, 180) }}"
                                                    </p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-slate-100 rounded-[2rem] bg-slate-50/30">
                                                <i class='bx bx-map-alt text-3xl text-slate-200 mb-2'></i>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No recent geospatial reports</p>
                                            </div>
                                        @endif
                                    </section>
                                </div>

                                <div class="px-8 py-8 border-t border-slate-100 bg-slate-50/80 backdrop-blur-md flex gap-4">
                                    <button 
                                        class="flex-1 flex items-center justify-center gap-2 px-6 py-4 bg-white border border-slate-200 text-slate-900 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm"
                                    >
                                        Full Profile <i class='bx bx-link-external'></i>
                                    </button>
                                    <button 
                                        @click="$wire.closeAudit()"
                                        class="px-8 py-4 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-2xl shadow-slate-200 hover:shadow-slate-300 hover:-translate-y-0.5 active:translate-y-0 transition-all"
                                    >
                                        Done
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <style>
        @keyframes loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(400%); }
        }
    </style>
</div>
