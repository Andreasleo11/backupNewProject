<div class="w-full space-y-6">
    {{-- 1. Dashboard Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Employee Master
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Manage and audit employee records and sync from JPayroll.
            </p>
        </div>

        <div class="flex items-center gap-3">

                {{-- Sync Panel (Phase Picker) --}}
                <div x-data="{ open: false, phases: @entangle('syncPhases') }" class="relative">
                    <button @click="open = !open" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 shadow-sm hover:bg-slate-900/90 transition-colors disabled:opacity-50"
                        title="Sync from JPayroll">
                        <i class='bx bx-refresh text-lg' wire:loading.class="animate-spin"
                            wire:target="sync"></i>
                        <span>Sync Data</span>
                    </button>

                    {{-- Phase picker dropdown --}}
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute left-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 z-[100] overflow-hidden"
                        style="display:none;">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Select Sync Phases</p>
                        </div>

                        <div class="p-4 space-y-2">
                            {{-- Employees --}}
                            <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors group">
                                <input type="checkbox" x-model="phases" value="employees"
                                    class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <i class='bx bx-user text-slate-600 text-base'></i>
                                        <span class="text-xs font-black text-slate-900">Employee Master</span>
                                    </div>
                                    <p class="text-[9px] text-slate-400 mt-0.5 ml-6">Upsert employee records from JPayroll</p>
                                </div>
                            </label>

                            {{-- Annual Leave --}}
                            <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors group">
                                <input type="checkbox" x-model="phases" value="annual_leave"
                                    class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <i class='bx bx-calendar-check text-slate-600 text-base'></i>
                                        <span class="text-xs font-black text-slate-900">Annual Leave</span>
                                    </div>
                                    <p class="text-[9px] text-slate-400 mt-0.5 ml-6">Refresh leave balance quotas</p>
                                </div>
                            </label>

                            {{-- Attendance --}}
                            <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors group">
                                <input type="checkbox" x-model="phases" value="attendance"
                                    class="w-4 h-4 rounded text-purple-600 border-slate-300 focus:ring-purple-500">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <i class='bx bx-time text-slate-600 text-base'></i>
                                        <span class="text-xs font-black text-slate-900">Attendance Records</span>
                                    </div>
                                    <p class="text-[9px] text-slate-400 mt-0.5 ml-6">Import raw daily attendance data</p>
                                </div>
                            </label>

                            {{-- Date range (shown only when attendance is ticked) --}}
                            <div x-show="phases.includes('attendance')" x-transition style="display: none;" class="ml-7 mt-1 p-3 bg-purple-50 rounded-xl border border-purple-100 space-y-2">
                                <p class="text-[9px] font-black text-purple-400 uppercase tracking-widest">Attendance Date Range</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-[8px] font-bold text-slate-500 uppercase">From</label>
                                        <input type="date" wire:model="syncFromDate"
                                            class="w-full mt-1 px-2 py-1.5 text-[10px] font-medium bg-white border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-400 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-[8px] font-bold text-slate-500 uppercase">To</label>
                                        <input type="date" wire:model="syncToDate"
                                            class="w-full mt-1 px-2 py-1.5 text-[10px] font-medium bg-white border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-400 outline-none">
                                    </div>
                                </div>
                                <p class="text-[8px] text-slate-400">Defaults to start of month &rarr; yesterday.</p>
                            </div>
                        </div>

                        <div class="px-4 pb-4 flex items-center justify-between gap-3">
                            <span class="text-[9px] font-bold text-slate-400">
                                <span x-text="phases.length"></span> phase(s) selected
                            </span>
                            <button wire:click="sync" @click="open = false"
                                wire:loading.attr="disabled"
                                :disabled="phases.length === 0"
                                class="flex items-center gap-2 px-5 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow hover:bg-slate-700 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                                <i class='bx bx-search-alt text-sm' wire:loading.class="hidden" wire:target="sync"></i>
                                <i class='bx bx-loader-alt animate-spin text-sm hidden' wire:loading.class.remove="hidden" wire:target="sync"></i>
                                Preview
                            </button>
                        </div>
                    </div>

                    {{-- History button --}}
                    <div x-data="{ histOpen: false }" class="absolute left-full ml-2 top-0">
                        <button @click="histOpen = !histOpen"
                            class="inline-flex items-center justify-center p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors bg-white shadow-sm"
                            title="View Sync History">
                            <i class='bx bx-history text-lg'></i>
                        </button>

                        <div x-show="histOpen" @click.away="histOpen = false" x-transition
                            class="absolute left-0 sm:left-auto sm:right-0 mt-3 w-72 bg-white rounded-2xl shadow-2xl border border-slate-100 py-4 z-[100] overflow-hidden"
                            style="display: none;">
                            <div class="px-6 pb-2 border-b border-slate-50">
                                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Recent Syncs</h4>
                            </div>
                            <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                @forelse($this->recentSyncs as $log)
                                    <button wire:click="viewLog({{ $log->id }})" @click="histOpen = false"
                                        class="w-full px-6 py-2 flex items-center justify-between hover:bg-slate-50 transition-colors group/row">
                                        <div class="flex items-center gap-3">
                                            <div class="h-1.5 w-1.5 rounded-full {{ $log->status === 'completed' ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }}"></div>
                                            <div class="text-left">
                                                <span class="text-[10px] font-bold text-slate-900">{{ $log->started_at?->format('d M, H:i') }}</span>
                                                @php
                                                    $snapshotPhases = $log->results_snapshot['phases'] ?? [];
                                                @endphp
                                                @if(count($snapshotPhases))
                                                    <span class="mx-1 text-slate-300">·</span>
                                                    <span class="text-[9px] font-medium text-purple-500 uppercase tracking-tighter">{{ implode(', ', $snapshotPhases) }}</span>
                                                @endif
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
    </div>

    {{-- 2. Search & Filtering System (Alpine + TALL) --}}
    <div class="rounded-md border border-slate-200 bg-white p-4" x-data="{
        localOpen: @js($showAdvancedFilters),
        init() {
            this.$watch('localOpen', value => $wire.set('showAdvancedFilters', value, false))
        }
    }">
        <div class="flex flex-col lg:flex-row gap-4 justify-between items-center">
            {{-- Search --}}
            <div class="relative w-full lg:w-96">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" autofocus
                    class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent py-1 pl-9 pr-3 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors placeholder:text-slate-500"
                    placeholder="Search name or NIK...">
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto justify-end">
                {{-- Active Toggle --}}
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.live="activeOnly" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                    <span class="text-sm font-medium text-slate-700">Active Only</span>
                </label>

                {{-- Toggle Advanced --}}
                <button @click="localOpen = !localOpen"
                    class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium border transition-colors"
                    :class="localOpen ? 'bg-slate-100 text-slate-900 border-slate-200' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
                    title="Advanced Filters">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filters
                </button>

                {{-- Row Density Selector --}}
                <select wire:model.live="perPage"
                    class="flex h-9 w-32 rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                    @foreach ([10, 25, 50, 100] as $option)
                        <option value="{{ $option }}">{{ $option }} / page</option>
                    @endforeach
                </select>

                {{-- Reset Filters --}}
                <button wire:click="resetFilters"
                    class="p-2 text-slate-400 hover:text-rose-600 transition-colors rounded-lg hover:bg-rose-50"
                    title="Clear All Filters">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Advanced Filters Panel --}}
        <div x-show="localOpen" x-collapse x-cloak class="mt-4 pt-4 border-t border-slate-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Branch Filter --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Branch</label>
                    <select wire:model.live="branch"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                        <option value="">All Branches</option>
                        @foreach ($this->availableBranches as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Department Filter --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                    <select wire:model.live="deptCode"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                        <option value="">All Departments</option>
                        @foreach ($this->availableDepartments as $d)
                            <option value="{{ $d->dept_no }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Employment Status</label>
                    <select wire:model.live="employmentType"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                        <option value="">All Statuses</option>
                        @foreach ($this->availableEmploymentTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Main Data Grid --}}
    <x-employee.table :employees="$this->employees" />

    {{-- 4. Overlays & Modals (Teleport to Body) --}}
    <x-employee.modals :preview-data="$previewData" :active-log="$activeLog" :preview-phase="$previewPhase" :preview-tab="$previewTab" :preview-search="$previewSearch" />

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
