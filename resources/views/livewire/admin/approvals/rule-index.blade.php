<div class="w-full space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Approval Rules</h1>
            <p class="mt-1 text-sm text-slate-500">Configure approval workflows for different document types.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Quick Stats --}}
            <div class="hidden lg:flex items-center gap-4 px-4 py-2 bg-white border border-slate-200 rounded-md shadow-sm">
                <div class="flex items-center gap-3">
                    <div>
                        <div class="text-sm font-bold text-slate-900 leading-none">{{ $stats['total_rules'] }}</div>
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Total</div>
                    </div>
                    <div class="w-px h-6 bg-slate-200"></div>
                    <div>
                        <div class="text-sm font-bold text-slate-900 leading-none">{{ $stats['active_rules'] }}</div>
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Active</div>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.approval-rules.create') }}"
                class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 shadow-sm hover:bg-slate-900/90 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Rule
            </a>
        </div>
    </div>

    {{-- Data Table Container --}}
    <div class="bg-white border border-slate-200 rounded-md shadow-sm overflow-hidden flex flex-col">
        {{-- Table Toolbar --}}
        <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row gap-4 justify-between items-center">
            <div class="relative w-full sm:w-96 flex-shrink-0">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.500ms="search"
                    class="flex h-9 w-full rounded-md border border-slate-200 bg-white py-1 pl-9 pr-3 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors placeholder:text-slate-500"
                    placeholder="Search rules...">
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select wire:model.live="statusFilter" class="h-9 rounded-md border border-slate-200 bg-white px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <select wire:model.live="perPage" class="h-9 rounded-md border border-slate-200 bg-white px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 font-medium">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                        </th>
                        <th class="px-4 py-3">Code & Name</th>
                        <th class="px-4 py-3">Target Model</th>
                        <th class="px-4 py-3 text-center">Priority</th>
                        <th class="px-4 py-3 text-center">Steps</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rules as $rule)
                        <tr class="hover:bg-slate-50/50 transition-colors" wire:key="rule-row-{{ $rule->id }}">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $rule->id }}" class="rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $rule->name }}</div>
                                <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $rule->code }} <span class="bg-slate-100 px-1 py-0.5 rounded ml-1">v{{ $rule->version_number }}</span></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ class_basename($rule->model_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs font-mono font-medium text-slate-500">{{ $rule->priority }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-50 text-indigo-700 font-bold text-xs">
                                    {{ $rule->steps_count }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($rule->active)
                                    <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.approval-rules.edit', $rule->id) }}" class="p-1 text-slate-400 hover:text-indigo-600 transition-colors" title="Edit Rule & Steps">
                                        <x-bx-pencil class="w-5 h-5" />
                                    </a>
                                    <button wire:click="confirmDelete({{ $rule->id }})" class="p-1 text-slate-400 hover:text-red-600 transition-colors" title="Delete Rule">
                                        <x-bx-trash class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <x-bx-git-branch class="w-9 h-9 text-slate-300 mb-2" />
                                    <p class="text-sm font-medium text-slate-900">No rules found</p>
                                    <p class="text-xs text-slate-500 mt-1">Try adjusting your filters or search query.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bulk Actions Footer --}}
        @if(count($selectedRows) > 0)
            <div class="bg-indigo-50 border-t border-indigo-100 px-4 py-3 flex items-center justify-between">
                <span class="text-sm font-medium text-indigo-800">{{ count($selectedRows) }} rules selected</span>
                <div class="flex gap-2">
                    <button wire:click="bulkActivate" class="text-xs font-medium text-emerald-700 bg-emerald-100 border border-emerald-200 rounded px-3 py-1.5 hover:bg-emerald-200 transition-colors shadow-sm">
                        Activate
                    </button>
                    <button wire:click="bulkDeactivate" class="text-xs font-medium text-amber-700 bg-amber-100 border border-amber-200 rounded px-3 py-1.5 hover:bg-amber-200 transition-colors shadow-sm">
                        Deactivate
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if ($rules->hasPages())
        <div class="mt-4">
            {{ $rules->links(data: ['scrollTo' => false]) }}
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <x-modal wire:model="showDeleteModal" maxWidth="sm">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <x-bx-trash class="w-5 h-5 text-red-600" />
                </div>
                <h2 class="text-lg font-bold text-slate-900">Delete Rule</h2>
            </div>
            
            @if($activeRequestsCount > 0)
                <div class="mb-4 rounded-md bg-amber-50 p-3 border border-amber-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-bx-error class="text-amber-400 w-5 h-5 mt-0.5" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">Warning: Active Requests</h3>
                            <div class="mt-1 text-xs text-amber-700">
                                This rule is currently being used by <strong>{{ $activeRequestsCount }}</strong> active approval request(s). Deleting it may impact ongoing workflows.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <p class="text-sm text-slate-500 mb-6">Are you sure you want to delete this rule? This will also remove all its associated workflow steps.</p>
            
            <div class="flex justify-end gap-3">
                <button type="button" wire:click="$set('showDeleteModal', false)"
                    class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button wire:click="executeDelete" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                    <x-bx-loader-alt class="animate-spin" wire:loading wire:target="executeDelete" />
                    <span wire:loading.remove wire:target="executeDelete">Delete Rule</span>
                    <span wire:loading wire:target="executeDelete">Deleting...</span>
                </button>
            </div>
        </div>
    </x-modal>
</div>
