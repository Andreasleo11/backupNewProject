<div class="w-full space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Approval Rules</h1>
            <p class="mt-1 text-sm text-slate-500">Configure approval workflows for different document types.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Quick Stats --}}
            <div class="hidden lg:flex items-center gap-4 px-4 py-2.5 bg-white border border-slate-200 rounded-md">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-md bg-slate-100 flex items-center justify-center text-slate-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 leading-none">{{ $stats['total_rules'] }}</div>
                            <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Total</div>
                        </div>
                    </div>
                    <div class="w-px h-6 bg-slate-200"></div>
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 leading-none">{{ $stats['active_rules'] }}</div>
                            <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Active</div>
                        </div>
                    </div>
                </div>
            </div>

            <button wire:click="openCreateRule"
                class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 shadow-sm hover:bg-slate-900/90 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Rule
            </button>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Sidebar: Rule List --}}
        <div class="lg:col-span-2">
            {{-- Quick Filters --}}
            <div class="space-y-3 mb-6">
                {{-- Status Filters --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Status</label>
                    <div class="flex gap-1 rounded-md bg-slate-100 p-1">
                        <button wire:click="setStatusFilter('all')"
                            class="flex-1 px-3 py-1.5 rounded-sm text-sm font-medium transition-all {{ $statusFilter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                            All
                        </button>
                        <button wire:click="setStatusFilter('active')"
                            class="flex-1 px-3 py-1.5 rounded-sm text-sm font-medium transition-all {{ $statusFilter === 'active' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                            Active
                        </button>
                        <button wire:click="setStatusFilter('inactive')"
                            class="flex-1 px-3 py-1.5 rounded-sm text-sm font-medium transition-all {{ $statusFilter === 'inactive' ? 'bg-white text-amber-700 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                            Inactive
                        </button>
                    </div>
                </div>

                {{-- Model Type Filters --}}
                @if(count($availableModelTypes) > 1)
                    <div class="mt-4">
                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Type</label>
                        <div class="space-y-1">
                            <button wire:click="setModelTypeFilter(null)"
                                class="w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-all {{ !$modelTypeFilter ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                                All Types
                            </button>
                            @foreach($availableModelTypes as $modelType)
                                <button wire:click="setModelTypeFilter({{ json_encode($modelType) }})"
                                    class="w-full text-left px-3 py-2 rounded-md text-sm font-medium transition-all {{ $modelTypeFilter === $modelType ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <div class="flex items-center justify-between">
                                        <span class="truncate">{{ class_basename($modelType) }}</span>
                                        <span class="text-[10px] bg-slate-200 px-1.5 py-0.5 rounded-md">{{ $modelTypeStats[$modelType]['total'] }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                {{-- View Options --}}
                 <div class="mt-4">
                     <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">View</label>
                     <button wire:click="toggleGroupByModel"
                             class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium transition-all {{ $groupByModel ? 'bg-slate-100 text-slate-900' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                         <span>{{ $groupByModel ? 'Grouped by Type' : 'List View' }}</span>
                         <svg class="h-4 w-4 transition-transform {{ $groupByModel ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                         </svg>
                     </button>
                 </div>
                 {{-- Current Version Filter --}}
                <div class="mt-4">
                    <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Version</label>
                    <button wire:click="$toggle('currentVersionFilter')"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium transition-all {{ $currentVersionFilter ? 'bg-slate-100 text-slate-900' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                        <span class="flex items-center gap-2">
                            Current Version Only
                        </span>
                        @if($currentVersionFilter)
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </button>
                </div>
            </div>

            {{-- Bulk Actions (when items selected) --}}
            @if(count($selectedRules) > 0)
                <div class="p-4 bg-amber-50 border border-amber-200 rounded-md mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-semibold text-amber-800">{{ count($selectedRules) }} selected</span>
                        <button wire:click="clearSelection" class="text-amber-600 hover:text-amber-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="bulkActivate"
                            class="flex-1 px-3 py-2 text-xs font-medium bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                            Activate
                        </button>
                        <button wire:click="bulkDeactivate"
                            class="flex-1 px-3 py-2 text-xs font-medium bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                            Deactivate
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <div class="lg:col-span-4">
             {{-- Sidebar Header --}}
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-bold text-slate-900">Rules</h2>
            </div>



            {{-- Search --}}
            <div class="relative mb-4">
                <input type="text" wire:model.live.debounce.400ms="search"
                    class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 pl-9 text-sm shadow-sm transition-colors placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950"
                    placeholder="Search rules...">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            
            {{-- Rules List --}}
            <div class="space-y-3">
                @forelse ($rules as $rule)
                    @include('livewire.admin.approvals._rule-card', ['rule' => $rule])
                @empty
                    <div class="text-center py-8">
                        <div class="text-slate-300 mb-3">
                            <svg class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-slate-900 mb-1">No rules found</h3>
                        <p class="text-xs text-slate-500">Try adjusting your search or filters above.</p>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if (!$groupByModel && $rules->hasPages())
                    <div class="pt-4 border-t border-slate-200/60">
                        {{ $rules->links(data: ['scrollTo' => false]) }}
                    </div>
                @endif
            </div>
        </div>
            
        {{-- Main Detail Area --}}
        <div class="lg:col-span-6">
            @if ($selectedRule)
                <div class="bg-white border border-slate-200 rounded-md p-5 mb-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900 mb-1">{{ $selectedRule->name }}</h2>
                            <p class="text-sm text-slate-500">Configure approval steps for this rule.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button wire:click="openEditRule({{ $selectedRule->id }})"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Rule
                            </button>
                            <button wire:click="openCreateStep"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium text-slate-50 bg-slate-900 hover:bg-slate-900/90 transition-colors shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Step
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Content Area --}}
                    {{-- Compact List View --}}
                    <div class="flex-1 overflow-auto custom-scrollbar bg-slate-50">
                        {{-- Match Conditions Summary --}}
                        <div class="p-4 border-b border-slate-200 bg-white">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Trigger Conditions</h3>
                                @if (!empty($selectedRule->match_expr))
                                    <button wire:click="toggleJsonViewer"
                                        class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                                        {{ $showJsonViewer ? 'Hide JSON' : 'View JSON' }}
                                    </button>
                                @endif
                            </div>
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                                @if (empty($selectedRule->match_expr))
                                    <span class="text-sm text-slate-500 italic">No conditions specified (matches all)</span>
                                @else
                                    <div class="space-y-1">
                                        @foreach ($selectedRule->match_expr as $key => $value)
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-mono bg-slate-200 px-2 py-1 rounded">{{ $key }}</span>
                                                <span class="text-xs text-slate-600">→</span>
                                                <span class="text-xs font-mono bg-slate-100 px-2 py-1 rounded text-slate-700">{{ is_string($value) ? $value : json_encode($value) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if (isset($showJsonViewer) && $showJsonViewer)
                                        <div class="mt-4 pt-4 border-t border-slate-300">
                                            <div class="font-mono text-xs bg-slate-900 text-green-400 p-3 rounded overflow-x-auto whitespace-pre-wrap">{{ json_encode($selectedRule->match_expr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Steps Table --}}
                        <div class="p-4">
                            <div class="bg-white rounded-md border border-slate-200 overflow-hidden">
                                <div class="px-3 py-2 border-b border-slate-200 bg-slate-50">
                                    <h4 class="text-sm font-semibold text-slate-800">Approval Steps ({{ $steps->count() }})</h4>
                                </div>

                                @forelse ($steps as $step)
                                    <div class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50 transition-colors">
                                        <div class="px-3 py-2">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    <div class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs">
                                                        {{ $step->sequence }}
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 uppercase">
                                                                {{ $step->approver_type }}
                                                            </span>
                                                            @if ($step->parallel_group)
                                                                <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                                                                    Parallel
                                                                </span>
                                                            @endif
                                                            @if ($step->final)
                                                                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                                                    Final
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="text-sm font-medium text-slate-900">
                                                            @if ($step->approver_type === 'role')
                                                                {{ $step->role?->name ?? 'Unknown Role (ID: ' . $step->approver_id . ')' }}
                                                            @else
                                                                {{ $step->user?->name ?? 'Unknown User (ID: ' . $step->approver_id . ')' }}
                                                                @if ($step->user?->email)
                                                                    <span class="text-xs text-slate-500 ml-2">{{ $step->user->email }}</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button wire:click="openEditStep({{ $step->id }})"
                                                        class="p-1.5 rounded text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <div x-data="{ open: false }">
                                                        <button @click="open = true"
                                                            class="p-1.5 rounded text-slate-400 hover:bg-slate-100 hover:text-red-600 transition-colors">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>

                                                        <template x-teleport="body">
                                                            <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition.opacity>
                                                                <div @click.away="open = false" class="bg-white rounded-md shadow-lg w-full max-w-sm p-6 space-y-4">
                                                                    <div class="space-y-2">
                                                                        <h3 class="text-lg font-semibold text-slate-900">Are you sure?</h3>
                                                                        <p class="text-sm text-slate-500">This action cannot be undone. This will permanently delete the step.</p>
                                                                    </div>
                                                                    <div class="flex items-center justify-end gap-2 pt-4">
                                                                        <button @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition-colors">Cancel</button>
                                                                        <button @click="open = false; $wire.deleteStep({{ $step->id }})" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                                                                            <span wire:loading.remove wire:target="deleteStep({{ $step->id }})">Delete Step</span>
                                                                            <span wire:loading wire:target="deleteStep({{ $step->id }})">Deleting...</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center">
                                        <svg class="h-8 w-8 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        <p class="text-sm text-slate-500 mb-2">No steps defined</p>
                                        <button wire:click="openCreateStep"
                                            class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline">
                                            + Add First Step
                                        </button>
                                    </div>
                                @endforelse
                            </div>
                    </div>
                </div>

            @else
                {{-- Empty State: No Rule Selected --}}
                <div class="p-12 flex flex-col items-center justify-center text-center bg-white border border-slate-200 rounded-md h-full min-h-[400px]">
                    <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-slate-900 mb-1">Select a Rule</h3>
                    <p class="text-sm text-slate-500 mb-8 max-w-xs">Choose a rule from the list to view its approval workflow and steps.</p>
                    <div class="space-y-3 text-sm text-slate-600">
                        <div class="flex items-center gap-3 text-left">
                            <div class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-semibold text-slate-600">1</span>
                            </div>
                            <span>Select a rule from the list</span>
                        </div>
                        <div class="flex items-center gap-3 text-left">
                            <div class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-semibold text-slate-600">2</span>
                            </div>
                            <span>View the approval workflow</span>
                        </div>
                        <div class="flex items-center gap-3 text-left">
                            <div class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-semibold text-slate-600">3</span>
                            </div>
                            <span>Edit rules or add approval steps as needed</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Rule Modal --}}
    <x-modal wire:model="showRuleModal" maxWidth="lg">
        <div class="p-4">
            <h2 class="text-xl font-bold text-slate-900 mb-6">
                {{ $editingRuleId ? 'Edit Rule Settings' : 'Create New Rule' }}
            </h2>
            <form wire:submit.prevent="saveRule" class="space-y-4">
                {{-- Model Type --}}
                <div class="relative">
                    <input type="text" wire:model.defer="rule_model_type" id="rule_model_type"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 placeholder-transparent peer"
                        placeholder="Target Model Class">
                    <label for="rule_model_type"
                        class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        Target Model Class <span class="text-red-500">*</span>
                    </label>
                    @error('rule_model_type')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Code --}}
                    <div class="relative">
                        <input type="text" wire:model.defer="rule_code" id="rule_code"
                            class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 placeholder-transparent peer"
                            placeholder="Unique Code">
                        <label for="rule_code"
                            class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                            Unique Code <span class="text-red-500">*</span>
                        </label>
                        @error('rule_code')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Priority --}}
                    <div class="relative">
                        <input type="number" wire:model.defer="rule_priority" id="rule_priority"
                            class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 placeholder-transparent peer"
                            placeholder="Priority">
                        <label for="rule_priority"
                            class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                            Priority <span class="text-red-500">*</span>
                        </label>
                        @error('rule_priority')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Name --}}
                <div class="relative">
                    <input type="text" wire:model.defer="rule_name" id="rule_name"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 placeholder-transparent peer"
                        placeholder="Rule Name">
                    <label for="rule_name"
                        class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        Rule Name <span class="text-red-500">*</span>
                    </label>
                    @error('rule_name')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- JSON Editor --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        Match Expression (JSON)
                    </label>
                    <textarea wire:model.defer="rule_match_expr_raw" rows="5"
                        class="flex w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm font-mono focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950"></textarea>
                    <p class="text-[10px] text-slate-400">Structure: {"field": "value", "field_op": "value"}</p>
                    @error('rule_match_expr_raw')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Version Notes (only when editing) --}}
                @if($editingRuleId)
                    <div class="relative">
                        <textarea wire:model.defer="version_notes" rows="2"
                            class="flex w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 placeholder-transparent peer"
                            placeholder="Version Notes"></textarea>
                        <label for="version_notes"
                            class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                            Version Notes (describe what changed)
                        </label>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="rule_active" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900"></div>
                        <span class="ml-3 text-sm font-medium text-slate-700">Rule Active</span>
                    </label>

                    <div class="flex gap-3">
                        <button type="button" wire:click="$set('showRuleModal', false)"
                            class="px-4 py-2 rounded-md border border-slate-200 text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-slate-900 text-slate-50 text-sm font-medium hover:bg-slate-900/90 transition-colors disabled:opacity-50"
                            wire:loading.attr="disabled" wire:target="saveRule">
                            <svg wire:loading wire:target="saveRule" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveRule">Save Rule</span>
                            <span wire:loading wire:target="saveRule">Saving...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Step Modal --}}
    <x-modal wire:model="showStepModal" maxWidth="md">
        <div class="p-4">
            <h2 class="text-xl font-bold text-slate-900 mb-6">
                {{ $editingStepId ? 'Edit Flow Step' : 'Add Flow Step' }}
            </h2>
            <form wire:submit.prevent="saveStep" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    {{-- Sequence --}}
                    <div class="relative">
                        <input type="number" wire:model.defer="step_sequence" id="step_sequence"
                            class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 placeholder-transparent peer"
                            placeholder="Sequence">
                        <label for="step_sequence"
                            class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                            Sequence <span class="text-red-500">*</span>
                        </label>
                        @error('step_sequence')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="relative">
                        <select wire:model.defer="step_approver_type" id="step_approver_type"
                            class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 appearance-none">
                            <option value="user">Specific User</option>
                            <option value="role">System Role</option>
                        </select>
                        <label for="step_approver_type"
                            class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                            Approver Type
                        </label>
                    </div>
                </div>

                {{-- Approver ID --}}
                <div class="relative">
                    <input type="number" wire:model.defer="step_approver_id" id="step_approver_id"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 placeholder-transparent peer"
                        placeholder="Target ID">
                    <label for="step_approver_id"
                        class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        Target ID (User/Role ID) <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-[10px] text-slate-400">Enter the ID of the User or Role table.</p>
                    @error('step_approver_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center gap-6 py-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="step_final" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                        <span class="ml-2 text-sm text-slate-600">Final Step?</span>
                    </label>

                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="step_parallel_group" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-500"></div>
                        <span class="ml-2 text-sm text-slate-600">Parallel?</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showStepModal', false)"
                        class="px-4 py-2 rounded-md border border-slate-200 text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-slate-900 text-slate-50 text-sm font-medium hover:bg-slate-900/90 transition-colors disabled:opacity-50"
                        wire:loading.attr="disabled" wire:target="saveStep">
                        <svg wire:loading wire:target="saveStep" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveStep">{{ $editingStepId ? 'Save Changes' : 'Add to Flow' }}</span>
                        <span wire:loading wire:target="saveStep">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('confirm-delete-rule', (data) => {
                const confirmed = confirm(
                    `Warning: This rule "${data.ruleName}" is currently being used by ${data.activeRequestsCount} active approval request(s).\n\n` +
                    `Deleting this rule will affect ongoing approvals. Are you sure you want to proceed?`
                );

                if (confirmed) {
                    Livewire.dispatch('force-delete-rule', { ruleId: data.ruleId });
                }
            });

            Livewire.on('force-delete-rule', (data) => {
                $wire.forceDeleteRule(data.ruleId);
            });

            Livewire.on('confirm-new-version', (data) => {
                const confirmed = confirm(
                    `Warning: This rule "${data.ruleName}" is currently being used by ${data.activeRequestsCount} active approval request(s).\n\n` +
                    `Creating a new version will NOT affect ongoing approvals (they will continue using the old version).\n\n` +
                    `Do you want to create a new version anyway?`
                );

                if (confirmed) {
                    Livewire.dispatch('force-new-version', { ruleId: data.ruleId, data: data.data });
                }
            });

            Livewire.on('force-new-version', (data) => {
                $wire.forceNewVersion = true;
                $wire.forceCreateNewVersion(data.ruleId, data.data);
            });
        });
    </script>
</div>