@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')
@section('page-subtitle', 'Define system roles and their associated permission matrices.')

<div class="max-w-7xl mx-auto space-y-6 py-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
                Role Management
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Define roles and assign permissions to control system access.
            </p>
        </div>
        @can('role.create')
            <button wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-all hover:bg-blue-500 hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                New Role
            </button>
        @endcan
    </div>

    {{-- Search --}}
    <div class="rounded-2xl border border-slate-200 bg-white/50 p-4 shadow-sm backdrop-blur-xl">
        <div class="relative max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text"
                wire:model.live.debounce.400ms="search"
                class="block w-full rounded-xl border-0 bg-white py-3 pl-11 pr-4 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 transition-all"
                placeholder="Search roles...">
        </div>
    </div>

    {{-- Roles Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($roles as $role)
            @php
                // Build module summary dynamically from config
                $moduleCounts = [];
                foreach (config('permission_groups.groups', []) as $label => $prefixes) {
                    $prefixes = (array) $prefixes;
                    $count = $role->permissions->filter(fn($p) => 
                        collect($prefixes)->contains(fn($px) => str_starts_with($p->name, (string)$px))
                    )->count();
                    
                    if ($count > 0) {
                        // Use a shorter label for the summary chip if possible
                        $shortLabel = match($label) {
                            'Evaluation & Discipline' => 'Evaluation',
                            'Purchase Request'        => 'PR',
                            'Roles & Permissions'     => 'Roles',
                            default => $label
                        };
                        $moduleCounts[$shortLabel] = $count;
                    }
                }
            @endphp
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $role->name }}</h3>
                                @if(in_array($role->name, ['super-admin', 'admin']))
                                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                        System Role
                                    </span>
                                @endif
                            </div>
                        </div>
                        {{-- Permission count badge --}}
                        <div class="h-8 w-8 rounded-full bg-slate-50 flex items-center justify-center text-xs font-bold text-slate-600 border border-slate-200" title="{{ $role->permissions->count() }} permissions">
                            {{ $role->permissions->count() }}
                        </div>
                    </div>

                    {{-- Module summary chips --}}
                    <div class="mt-3">
                        @if ($role->name === 'super-admin')
                            <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                All Permissions
                            </span>
                        @elseif(empty($moduleCounts))
                            <span class="text-xs text-slate-400 italic">No permissions assigned</span>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($moduleCounts as $mod => $count)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-50 px-2 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                                        {{ $mod }}
                                        <span class="ml-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[9px] font-bold text-blue-700">{{ $count }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card Actions --}}
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-end gap-3">
                    @if (!in_array($role->name, ['super-admin', 'admin']))
                        @can('role.delete')
                            <button wire:click="confirmDelete({{ $role->id }})"
                                wire:confirm="Delete role '{{ $role->name }}'? Users with this role will lose its permissions."
                                class="text-sm font-medium text-rose-500 hover:text-rose-700 transition-colors">
                                Delete
                            </button>
                        @endcan
                    @endif
                    @can('role.update')
                        <button wire:click="openEditModal({{ $role->id }})"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-all">
                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Configure
                        </button>
                    @endcan
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center">
                <div class="mx-auto h-24 w-24 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-slate-900">No roles found</h3>
                <p class="mt-1 text-slate-500">Try adjusting your search.</p>
            </div>
        @endforelse
    </div>

    {{-- Role Modal --}}
    <x-modal wire:model="showModal" maxWidth="3xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900">
                    {{ $modalMode === 'create' ? 'Create Role' : 'Configure Role' }}
                </h2>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="save" class="space-y-6">
                {{-- Role Name --}}
                <div class="relative">
                    <input type="text" wire:model.defer="name" id="roleName"
                        class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                        placeholder=" ">
                    <label for="roleName"
                        class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:left-4 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                        Role Name <span class="text-red-500">*</span>
                    </label>
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Grouped Permission Matrix --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Assign Permissions
                        </label>
                        <div class="flex gap-3">
                            <button type="button" wire:click="$set('selectedPermissions', [])"
                                class="text-xs font-medium text-slate-500 hover:text-rose-600 transition-colors">
                                Clear All
                            </button>
                            <span class="text-slate-200">|</span>
                            <button type="button" wire:click="$set('selectedPermissions', {{ $permissions->pluck('name') }})"
                                class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                Select All
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/50 p-4 max-h-[480px] overflow-y-auto custom-scrollbar">
                        @foreach ($groupedPermissions as $groupLabel => $groupPerms)
                            @php
                                $groupNames   = collect($groupPerms)->pluck('name')->toArray();
                                $allInGroup   = collect($groupNames)->every(fn($n) => in_array($n, $selectedPermissions));
                                $someInGroup  = collect($groupNames)->contains(fn($n) => in_array($n, $selectedPermissions));
                            @endphp
                            <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                                {{-- Group Header --}}
                                <button type="button"
                                    wire:click="toggleGroup('{{ $groupLabel }}')"
                                    class="w-full flex items-center justify-between px-4 py-2.5 text-left hover:bg-slate-50 transition-colors group/hdr">
                                    <div class="flex items-center gap-2">
                                        {{-- Tri-state indicator --}}
                                        <span class="h-4 w-4 rounded flex items-center justify-center flex-shrink-0 text-white text-[10px]
                                            {{ $allInGroup ? 'bg-blue-600' : ($someInGroup ? 'bg-blue-200' : 'border border-slate-300 bg-white') }}">
                                            @if ($allInGroup)
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            @elseif($someInGroup)
                                                <span class="h-1.5 w-1.5 rounded-full bg-blue-600 block"></span>
                                            @endif
                                        </span>
                                        <span class="text-sm font-semibold text-slate-700 group-hover/hdr:text-blue-700 transition-colors">{{ $groupLabel }}</span>
                                        <span class="text-[11px] text-slate-400 font-medium">
                                            ({{ collect($groupNames)->intersect($selectedPermissions)->count() }}/{{ count($groupNames) }})
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-medium text-blue-600 opacity-0 group-hover/hdr:opacity-100 transition-opacity">
                                        {{ $allInGroup ? 'Deselect all' : 'Select all' }}
                                    </span>
                                </button>

                                {{-- Permission checkboxes --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-px border-t border-slate-100 bg-slate-100">
                                    @foreach ($groupPerms as $perm)
                                        <label class="relative flex items-start p-3 bg-white hover:bg-blue-50/60 transition-colors cursor-pointer group/perm">
                                            <div class="flex h-5 items-center">
                                                <input type="checkbox" value="{{ $perm->name }}"
                                                    wire:model.defer="selectedPermissions"
                                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                @php
                                                    $parts = explode('.', $perm->name, 2);
                                                    $action = $parts[1] ?? $perm->name;
                                                @endphp
                                                <span class="font-medium text-slate-700 group-hover/perm:text-blue-700 transition-colors">{{ $action }}</span>
                                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $perm->name }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('selectedPermissions') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        wire:loading.attr="disabled" wire:target="save"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-500 transition-all hover:scale-105 active:scale-95 disabled:opacity-60 disabled:scale-100">
                        <span wire:loading.remove wire:target="save">{{ $modalMode === 'create' ? 'Create Role' : 'Save Changes' }}</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</div>
