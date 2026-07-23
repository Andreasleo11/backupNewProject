<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-900 text-white p-6 rounded-2xl shadow-xl border border-slate-800 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <x-bx-shield-quarter class="text-9xl" />
        </div>
        <div class="relative z-10 space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    <x-bx-lock-alt class="mr-1" /> Super User Console
                </span>
                <span class="text-slate-400 text-xs">• Real-Time Forensics</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                System Audit & Security Logs
            </h1>
            <p class="text-slate-400 text-sm max-w-2xl">
                Comprehensive activity trail, data modification tracking, and user event forensics across the platform.
            </p>
        </div>

        <div class="relative z-10 flex items-center gap-3">
            <button wire:click="resetFilters" 
                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 hover:text-white transition-all shadow-sm">
                <x-bx-reset class="w-4 h-4" /> Reset Filters
            </button>
            <button wire:click="$refresh" 
                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-600/30">
                <x-bx-refresh class="w-4 h-4" /> Refresh Trail
            </button>
        </div>
    </div>

    <!-- Security Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Logs -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Recorded Logs</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalLogs) }}</h3>
                <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                    <x-bx-data class="text-indigo-500" /> Full audit persistence
                </p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold shrink-0">
                <x-bx-list-ul class="" />
            </div>
        </div>

        <!-- Today's Logs -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Today's Activity</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($todayLogs) }}</h3>
                <p class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                    <x-bx-time class="text-emerald-500" /> Since 00:00 today
                </p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold shrink-0">
                <x-bx-pulse class="" />
            </div>
        </div>

        <!-- Critical Deletions -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Deletions & Purges</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($deletedLogs) }}</h3>
                <p class="text-[11px] text-rose-500 font-semibold mt-1 flex items-center gap-1">
                    <x-bx-error-circle class="" /> High importance events
                </p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl font-bold shrink-0">
                <x-bx-trash class="w-6 h-6" />
            </div>
        </div>

        <!-- Active Causers 24h -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Actors (24h)</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($activeCausers) }}</h3>
                <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                    <x-bx-user-check class="text-blue-500" /> Distinct user actions
                </p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold shrink-0">
                <x-bx-group class="" />
            </div>
        </div>
    </div>

    <!-- Advanced Search & Multi-Facet Filters Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search Keyword Input -->
            <div class="lg:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <x-bx-search class="w-5 h-5" />
                </div>
                <input wire:model.live.debounce.300ms="search" type="search"
                    class="w-full pl-10 pr-9 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all placeholder:text-slate-400"
                    placeholder="Search by action, user name, email, or subject ID..." />
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <x-bx-x class="w-5 h-5" />
                    </button>
                @endif
            </div>

            <!-- Event Filter -->
            <div>
                <select wire:model.live="eventFilter" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-700 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all font-medium">
                    <option value="">All Event Types</option>
                    <option value="created">Created (Additions)</option>
                    <option value="updated">Updated (Modifications)</option>
                    <option value="deleted">Deleted (Removals)</option>
                </select>
            </div>

            <!-- Target Model/Subject Filter -->
            <div>
                <select wire:model.live="subjectFilter" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-700 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all font-medium">
                    <option value="">All Model Subjects</option>
                    @foreach($availableSubjects as $fullClass => $shortName)
                        <option value="{{ $fullClass }}">{{ $shortName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range Filter -->
            <div>
                <select wire:model.live="dateFilter" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-50/50 text-slate-700 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all font-medium">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="7days">Last 7 Days</option>
                    <option value="30days">Last 30 Days</option>
                    <option value="custom">Custom Date Range</option>
                </select>
            </div>
        </div>

        <!-- Custom Date Range Row -->
        @if($dateFilter === 'custom')
        <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-slate-100 bg-slate-50/50 p-3 rounded-xl">
            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Date Range:</span>
            <div class="flex items-center gap-2">
                <input wire:model.live="startDate" type="date" class="py-1.5 px-3 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-indigo-500" />
                <span class="text-slate-400 text-xs">to</span>
                <input wire:model.live="endDate" type="date" class="py-1.5 px-3 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-indigo-500" />
            </div>
        </div>
        @endif

        <!-- Active Filter Indicator Tags -->
        @if($search || $eventFilter || $subjectFilter || $dateFilter)
        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 text-xs">
            <span class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Active Filters:</span>
            @if($search)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
                    Search: "{{ $search }}"
                    <button wire:click="$set('search', '')" class="hover:text-indigo-900"><x-bx-x class="" /></button>
                </span>
            @endif
            @if($eventFilter)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
                    Event: {{ ucfirst($eventFilter) }}
                    <button wire:click="$set('eventFilter', '')" class="hover:text-indigo-900"><x-bx-x class="" /></button>
                </span>
            @endif
            @if($subjectFilter)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
                    Subject: {{ class_basename($subjectFilter) }}
                    <button wire:click="$set('subjectFilter', '')" class="hover:text-indigo-900"><x-bx-x class="" /></button>
                </span>
            @endif
            @if($dateFilter)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-medium">
                    Date: {{ ucfirst($dateFilter) }}
                    <button wire:click="$set('dateFilter', '')" class="hover:text-indigo-900"><x-bx-x class="" /></button>
                </span>
            @endif
            <button wire:click="resetFilters" class="text-rose-600 hover:underline font-semibold ml-2 text-xs">Clear All</button>
        </div>
        @endif
    </div>

    <!-- Forensics Data Table -->
    <div class="bg-white shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Audit Log Entries</h3>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                    {{ $activities->total() }} total
                </span>
            </div>

            <!-- Per Page Dropdown -->
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span>Display per page:</span>
                <select wire:model.live="perPage" class="py-1 px-2 rounded-lg border border-slate-200 text-xs bg-white font-medium focus:border-indigo-500">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-[11px] text-slate-500 uppercase tracking-wider bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 font-bold">Date & Time</th>
                        <th class="px-6 py-3.5 font-bold">Actor (Causer)</th>
                        <th class="px-6 py-3.5 font-bold">Event & Description</th>
                        <th class="px-6 py-3.5 font-bold">Target Subject</th>
                        <th class="px-6 py-3.5 font-bold text-right">Forensics Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activities as $activity)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <!-- Date & Time -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-slate-900">{{ $activity->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-slate-500 font-mono flex items-center gap-1 mt-0.5">
                                <x-bx-time class="text-slate-400" /> {{ $activity->created_at->format('H:i:s') }}
                                <span class="text-[10px] text-slate-400 font-sans">({{ $activity->created_at->diffForHumans() }})</span>
                            </div>
                        </td>

                        <!-- Actor (Causer) -->
                        <td class="px-6 py-4">
                            @if($activity->causer)
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                        {{ strtoupper(substr($activity->causer->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">{{ $activity->causer->name ?? 'Unknown User' }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $activity->causer->email ?? class_basename($activity->causer_type) }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-base shrink-0 border border-slate-200">
                                        <x-bx-bot class="" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">System Engine</div>
                                        <div class="text-[11px] text-slate-400">Automated Task / Seed</div>
                                    </div>
                                </div>
                            @endif
                        </td>

                        <!-- Event & Action -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border
                                    @if($activity->event === 'created') bg-emerald-50 text-emerald-700 border-emerald-200
                                    @elseif($activity->event === 'updated') bg-indigo-50 text-indigo-700 border-indigo-200
                                    @elseif($activity->event === 'deleted') bg-rose-50 text-rose-700 border-rose-200
                                    @else bg-slate-100 text-slate-700 border-slate-200 @endif">
                                    <span class="h-1.5 w-1.5 rounded-full mr-1.5
                                        @if($activity->event === 'created') bg-emerald-500
                                        @elseif($activity->event === 'updated') bg-indigo-500
                                        @elseif($activity->event === 'deleted') bg-rose-500
                                        @else bg-slate-400 @endif"></span>
                                    {{ $activity->event ?? 'action' }}
                                </span>

                                @if($activity->log_name && $activity->log_name !== 'default')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $activity->log_name }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-700 font-medium mt-1.5">
                                {{ $activity->description }}
                            </div>
                        </td>

                        <!-- Target Subject -->
                        <td class="px-6 py-4">
                            @if($activity->subject_type)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-semibold text-xs border border-slate-200">
                                    <x-bx-cube class="text-slate-500" />
                                    {{ class_basename($activity->subject_type) }}
                                </div>
                                <div class="text-[11px] font-mono text-slate-500 mt-1">ID: #{{ $activity->subject_id }}</div>
                            @else
                                <span class="text-slate-400 italic text-xs">No subject target</span>
                            @endif
                        </td>

                        <!-- Inspect Button -->
                        <td class="px-6 py-4 text-right">
                            <button wire:click="inspectActivity({{ $activity->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                <x-bx-search-alt class="w-4 h-4" />
                                Inspect Changes
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="h-16 w-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 border border-slate-200">
                                <x-bx-shield-x class="" />
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">No matching audit logs found</h3>
                            <p class="text-slate-500 text-sm mt-1 max-w-sm mx-auto">There are no system audit records matching your specified search and filter criteria.</p>
                            <button wire:click="resetFilters" class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition-all">
                                <x-bx-reset class="" /> Clear All Filters
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($activities->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            {{ $activities->links() }}
        </div>
        @endif
    </div>

    <!-- Forensic Details & Changes Inspection Modal -->
    @if($selectedActivity)
    <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-data="{ copied: false }">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="closeInspection"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-slate-200">
                
                <!-- Modal Header -->
                <div class="bg-slate-900 text-white px-6 py-5 flex items-center justify-between relative overflow-hidden">
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="h-10 w-10 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-xl font-bold">
                            <x-bx-analyse class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-white">Log Inspection #{{ $selectedActivity->id }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider
                                    @if($selectedActivity->event === 'created') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                                    @elseif($selectedActivity->event === 'updated') bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                                    @elseif($selectedActivity->event === 'deleted') bg-rose-500/20 text-rose-300 border border-rose-500/30
                                    @else bg-slate-700 text-slate-300 @endif">
                                    {{ $selectedActivity->event ?? 'event' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $selectedActivity->created_at->format('F d, Y \a\t H:i:s A') }}</p>
                        </div>
                    </div>
                    
                    <button wire:click="closeInspection" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition-all">
                        <x-bx-x class="w-6 h-6" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
                    
                    <!-- Actor & Target Overview Card -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Actor / Triggered By</span>
                            <div class="mt-1 flex items-center gap-2">
                                <x-bx-user-circle class="w-5 h-5 text-indigo-600" />
                                <div>
                                    <div class="text-sm font-bold text-slate-900">
                                        {{ $selectedActivity->causer->name ?? 'System Engine' }}
                                    </div>
                                    <div class="text-xs text-slate-500 font-mono">
                                        {{ $selectedActivity->causer->email ?? ($selectedActivity->causer_type ? class_basename($selectedActivity->causer_type) . ' #' . $selectedActivity->causer_id : 'Automated Background Process') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Subject</span>
                            <div class="mt-1 flex items-center gap-2">
                                <x-bx-cube class="w-5 h-5 text-purple-600" />
                                <div>
                                    <div class="text-sm font-bold text-slate-900">
                                        {{ $selectedActivity->subject_type ? class_basename($selectedActivity->subject_type) : 'None' }}
                                    </div>
                                    <div class="text-xs text-slate-500 font-mono">
                                        Subject ID: #{{ $selectedActivity->subject_id ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Activity Action Summary:</span>
                        <div class="mt-1 text-sm font-medium text-slate-800 p-3 rounded-xl bg-indigo-50/50 border border-indigo-100">
                            {{ $selectedActivity->description }}
                        </div>
                    </div>

                    <!-- Visual Changes Diff Table (Old vs New) -->
                    @if(!empty($parsedDiff))
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                <x-bx-git-compare class="text-indigo-600 w-4 h-4" /> Attribute Modification Comparison
                            </h4>
                            <span class="text-[10px] text-slate-400 font-mono">{{ count($parsedDiff) }} fields affected</span>
                        </div>

                        <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-100 text-slate-600 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-2.5">Field / Attribute</th>
                                        <th class="px-4 py-2.5">Previous Value (Old)</th>
                                        <th class="px-4 py-2.5">Updated Value (New)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-mono">
                                    @foreach($parsedDiff as $diff)
                                    <tr class="@if($diff['changed']) bg-amber-50/30 @endif">
                                        <td class="px-4 py-3 font-bold text-slate-800">{{ $diff['field'] }}</td>
                                        <td class="px-4 py-3">
                                            @if($diff['old'] !== '')
                                                <span class="px-2 py-1 rounded bg-rose-50 text-rose-700 border border-rose-200 line-through text-[11px] block break-all">
                                                    {{ $diff['old'] }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic text-[11px]">&lt;empty&gt;</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($diff['new'] !== '')
                                                <span class="px-2 py-1 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] block break-all font-semibold">
                                                    {{ $diff['new'] }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic text-[11px]">&lt;empty&gt;</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Raw JSON Payload Viewer -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                                <x-bx-code-alt class="text-slate-400 w-4 h-4" /> Full Raw Properties Payload (JSON)
                            </span>
                            <button @click="navigator.clipboard.writeText(JSON.stringify({{ json_encode($selectedActivity->properties) }}, null, 2)); copied = true; setTimeout(() => copied = false, 2000)"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">
                                <x-bx-check class="w-4 h-4 text-emerald-600" x-show="copied" x-cloak />
                                <x-bx-copy class="w-4 h-4 text-slate-500" x-show="!copied" x-cloak />
                                <span x-text="copied ? 'Copied!' : 'Copy JSON'"></span>
                            </button>
                        </div>
                        <div class="bg-slate-900 text-emerald-400 p-4 rounded-2xl text-[11px] font-mono whitespace-pre-wrap max-h-48 overflow-y-auto shadow-inner border border-slate-800 custom-scrollbar-thin">
                            @if(!empty($selectedActivity->properties))
                                {{ json_encode($selectedActivity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                            @else
                                <span class="text-slate-500 italic">No additional payload properties stored.</span>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-between items-center">
                    <span class="text-xs text-slate-400 font-mono">Log ID: {{ $selectedActivity->id }}</span>
                    <button wire:click="closeInspection" class="px-5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 transition-all shadow-sm">
                        Close Inspection
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>
