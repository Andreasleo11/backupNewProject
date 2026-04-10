@php
    function sort_icon($field, $sortBy, $sortDirection) {
        if ($sortBy !== $field) return '';
        return $sortDirection === 'asc' ? '↑' : '↓';
    }
@endphp

<div class="max-w-7xl mx-auto space-y-6 py-6" x-data="{ showColumnsModal: false }">
    {{-- Audit-First Dashboard Header --}}
    <div class="mb-8 space-y-4">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Employee Master</h2>
                <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">Enterprise Human Resource Information System</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Global Actions --}}
                <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-2xl border border-slate-200 shadow-sm">
                    <button 
                        wire:click="sync" 
                        wire:loading.attr="disabled"
                        class="relative group inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-black shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all active:translate-y-0 disabled:opacity-50 uppercase tracking-widest"
                    >
                        <i class='bx bx-refresh text-lg group-hover:rotate-180 transition-transform duration-700' wire:loading.class="animate-spin"></i>
                        <span wire:loading.remove>Sync from JPayroll</span>
                        <span wire:loading>Processing Sync...</span>
                    </button>
                    
                    <div x-data="{ open: false }" class="relative">
                        <button 
                            @click="open = !open"
                            class="p-2.5 text-slate-500 hover:text-slate-900 hover:bg-white rounded-xl transition-all relative"
                            title="Sync History"
                        >
                            <i class='bx bx-history text-xl'></i>
                            @if($recentSyncs->count() > 0)
                                <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-blue-500 border-2 border-slate-100"></span>
                            @endif
                        </button>
                        
                        {{-- Dropdown for recent syncs --}}
                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            x-transition 
                            class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 py-4 z-50 overflow-hidden"
                            style="display: none;"
                        >
                            <div class="px-6 pb-3 border-b border-slate-50">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Recent Synchronizations</h4>
                            </div>
                            <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                @forelse($recentSyncs as $log)
                                    <button 
                                        wire:click="viewLog({{ $log->id }})"
                                        @click="open = false"
                                        class="w-full px-6 py-3 flex items-center justify-between hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 flex items-center justify-center rounded-lg {{ $log->status === 'completed' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
                                                <i class='bx {{ $log->status === 'completed' ? 'bx-check-double' : 'bx-time' }} text-lg'></i>
                                            </div>
                                            <div class="text-left">
                                                <p class="text-[11px] font-bold text-slate-900">{{ $log->started_at?->diffForHumans() }}</p>
                                                <p class="text-[9px] font-medium text-slate-400 uppercase tracking-wide">{{ $log->total_rows }} Records Processed</p>
                                            </div>
                                        </div>
                                        <i class='bx bx-chevron-right text-slate-300'></i>
                                    </button>
                                @empty
                                    <div class="px-6 py-8 text-center">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">No sync logs found</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $stats = [
                    ['label' => 'Total Active', 'value' => $employees->total(), 'icon' => 'group', 'color' => 'blue'],
                    ['label' => 'Permanent', 'value' => $employees->where('employment_type', 'PERMANENT')->count(), 'icon' => 'shield-check', 'color' => 'emerald'],
                    ['label' => 'Contract', 'value' => $employees->where('employment_type', 'CONTRACT')->count(), 'icon' => 'file-text', 'color' => 'amber'],
                    ['label' => 'Remote', 'value' => $employees->where('branch', 'REMOTE')->count(), 'icon' => 'globe', 'color' => 'purple'],
                ];
            @endphp
            @foreach($stats as $stat)
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 group-hover:scale-110 transition-transform">
                            <i class='bx bx-{{ $stat['icon'] }} text-xl'></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $stat['label'] }}</p>
                            <p class="text-xl font-bold text-slate-900 leading-none mt-1">{{ number_format($stat['value']) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Search & Filtering Bar --}}
        <div class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class='bx bx-search text-lg text-slate-400 group-focus-within:text-blue-600 transition-colors'></i>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by name, NIK, or position..."
                    class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none"
                >
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                    <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest group-focus-within:hidden">Search Command</span>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <select 
                    wire:model.live="branch"
                    class="px-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none cursor-pointer"
                >
                    <option value="">All Branches</option>
                    <option value="JAKARTA">Jakarta HQ</option>
                    <option value="REMOTE">Remote Office</option>
                </select>
                
                <button 
                    wire:click="resetFilters"
                    class="p-3 bg-white border border-slate-200 rounded-2xl text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm"
                    title="Reset All Filters"
                >
                    <i class='bx bx-reset text-xl'></i>
                </button>
            </div>
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
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-slate-200 border-separate border-spacing-0">
                <thead class="bg-slate-50">
                    <tr>
                         <th class="sticky left-0 z-30 bg-slate-50 px-8 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-600 border-b border-slate-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                             <button wire:click="sort_by('name')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Employee Info
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ sort_icon('name', $sortBy, $sortDirection) }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">NIK</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">Department</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">Position</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">Branch</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">Status</th>
                         <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">
                             <button wire:click="sort_by('start_date')" class="flex items-center gap-1 group hover:text-blue-600 transition-colors">
                                Join Date
                                <span class="text-xs transition-colors group-hover:text-blue-600">{{ sort_icon('start_date', $sortBy, $sortDirection) }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 whitespace-nowrap">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="sticky left-0 z-10 bg-white group-hover:bg-slate-50/50 px-8 py-5 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] border-r border-slate-100">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 text-white text-xs font-bold shadow-lg shadow-slate-200/50">
                                        {{ substr($employee->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900 leading-tight">{{ $employee->name }}</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <div class="text-[10px] font-bold text-blue-600 uppercase tracking-widest tabular-nums">{{ $employee->nik }}</div>
                                            <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                                            <div class="text-[10px] font-medium text-slate-500 uppercase tracking-wide">{{ $employee->employment_type }}</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-700 uppercase tracking-tight">{{ $employee->nik }}</div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-xs font-semibold text-slate-600 uppercase">
                                {{ $employee->dept_code }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="text-xs font-bold text-slate-800">{{ $employee->position }}</div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $employee->branch === 'JAKARTA' ? 'bg-purple-100/50 text-purple-700 border border-purple-200/50' : 'bg-orange-100/50 text-orange-700 border border-orange-200/50' }}">
                                    {{ $employee->branch }}
                                </span>
                            </td>
                             <td class="px-6 py-5 whitespace-nowrap">
                                 @php
                                     $isActive = is_null($employee->end_date) || \Carbon\Carbon::parse($employee->end_date)->isFuture();
                                 @endphp
                                @if ($isActive)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/10 uppercase tracking-widest">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1 text-[10px] font-bold text-slate-500 ring-1 ring-inset ring-slate-500/10 uppercase tracking-widest">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Terminated
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-xs font-medium text-slate-500">
                                {{ $employee->start_date }}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-xs font-medium text-slate-500">
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
            <div class="px-8 py-5 border-t border-slate-100 bg-slate-50">
                {{ $employees->links() }}
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
                                    {{ $isHistorical ? 'Historical Sync Audit' : 'Sync Analysis Report' }}
                                </h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">
                                    {{ $isHistorical ? 'Archived Log • Read Only' : 'Payroll Engine • Automated Reconciliation' }}
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

                                <div class="col-span-3 mt-4 p-6 bg-slate-900 rounded-3xl flex items-center gap-6 shadow-xl">
                                    <div class="h-14 w-14 flex items-center justify-center rounded-2xl bg-white/10 text-white shrink-0">
                                        <i class='bx bx-shield-quarter text-lg'></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-white uppercase tracking-wide mb-1">{{ $isHistorical ? 'Audit Log Metadata' : 'Action Required' }}</p>
                                        <p class="text-xs font-medium text-slate-400 leading-relaxed italic pr-4">
                                            {{ $isHistorical 
                                                ? 'This is a snapshot of the analysis performed during the synchronization. It is preserved for auditing purposes.' 
                                                : 'This analysis identifies records to be added, modified or removed. Commitment will overwrite local master data with JPayroll production values.' }}
                                        </p>
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
</div>
