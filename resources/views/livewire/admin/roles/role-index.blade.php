<div class="w-full space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Role Management
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Define roles and assign permissions to control system access.
            </p>
        </div>
        @can('role.create')
            <a href="{{ route('admin.roles.create') }}"
                class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 shadow-sm hover:bg-slate-900/90 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Role
            </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-4">
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
            <div class="relative w-full sm:w-96">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.500ms="search"
                    class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent py-1 pl-9 pr-3 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors placeholder:text-slate-500"
                    placeholder="Search roles by name or description...">
            </div>
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <select wire:model.live="perPage"
                    class="flex h-9 w-32 rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="rounded-md border border-slate-200 bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 font-medium">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                        </th>
                        <th class="px-4 py-3">Role Name</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-center">Users Assigned</th>
                        <th class="px-4 py-3 text-center">Permissions</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($roles as $role)
                        <tr class="hover:bg-slate-50/50 transition-colors" wire:key="role-row-{{ $role->id }}">
                            <td class="px-4 py-3 text-center">
                                @if($role->name !== 'super-admin')
                                    <input type="checkbox" wire:model.live="selectedRows" value="{{ $role->id }}" class="rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                                @else
                                    <input type="checkbox" disabled class="rounded border-slate-300 text-slate-300" title="System roles cannot be bulk modified">
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="font-semibold text-slate-900">{{ $role->name }}</div>
                                    @if (in_array($role->name, ['super-admin', 'admin']))
                                        <span class="inline-flex items-center rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700 border border-indigo-100">
                                            System
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="max-w-[300px] truncate text-xs text-slate-500" title="{{ $role->description }}">
                                    {{ $role->description ?? 'No description provided.' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                    {{ $role->users_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($role->name === 'super-admin')
                                    <span class="inline-flex items-center gap-1 rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 border border-indigo-100">
                                        <x-bx-infinite class="" /> All
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                        {{ $role->permissions_count }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                @can('role.update')
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="p-1.5 rounded text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition-colors inline-block" title="Edit Role">
                                        <x-bx-edit-alt class="" />
                                    </a>
                                @endcan
                                @if (!in_array($role->name, ['super-admin', 'admin']))
                                    @can('role.delete')
                                        <button wire:click="confirmDelete({{ $role->id }})" class="p-1.5 rounded text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Delete Role">
                                            <x-bx-trash class="" />
                                        </button>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <x-bx-shield-x class="w-9 h-9 mb-2 text-slate-300" />
                                    <p>No roles found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bulk Actions Footer --}}
        @if(count($selectedRows) > 0)
            <div class="bg-rose-50 border-t border-rose-100 px-4 py-3 flex items-center justify-between">
                <span class="text-sm font-medium text-rose-800">{{ count($selectedRows) }} roles selected</span>
                <div class="flex gap-2">
                    <button wire:click="confirmBulkDelete" class="text-xs font-medium text-rose-600 bg-white border border-rose-200 rounded px-3 py-1 hover:bg-rose-50 transition-colors shadow-sm">
                        Bulk Delete
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if ($roles->hasPages())
        <div class="mt-4">{{ $roles->links() }}</div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <x-modal wire:model="showDeleteModal" maxWidth="sm">
        <div class="p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-2">Delete Role</h2>
            @php
                $roleToDelete = \Spatie\Permission\Models\Role::withCount('users')->find($this->roleToDeleteId);
            @endphp
            @if($roleToDelete && $roleToDelete->users_count > 0)
                <div class="mb-4 rounded-md bg-amber-50 p-3 border border-amber-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-bx-error class="text-amber-400 w-5 h-5 mt-0.5" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">Warning: Active Assignments</h3>
                            <div class="mt-1 text-xs text-amber-700">
                                This role is currently assigned to <strong>{{ $roleToDelete->users_count }} active user(s)</strong>. If you delete it, they will lose these permissions immediately.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <p class="text-sm text-slate-500 mb-6">Are you sure you want to delete this role? This action cannot be undone.</p>
            <div class="flex justify-end gap-3">
                <button type="button" wire:click="$set('showDeleteModal', false)"
                    class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button wire:click="executeDelete" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                    <x-bx-loader-alt class="animate-spin" wire:loading wire:target="executeDelete" />
                    <span wire:loading.remove wire:target="executeDelete">Yes, Delete Role</span>
                    <span wire:loading wire:target="executeDelete">Deleting...</span>
                </button>
            </div>
        </div>
    </x-modal>

    {{-- Bulk Delete Confirmation Modal --}}
    <x-modal wire:model="showBulkDeleteModal" maxWidth="sm">
        <div class="p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-2">Bulk Delete Roles</h2>
            @php
                $rolesToDelete = \Spatie\Permission\Models\Role::withCount('users')->whereIn('id', $this->selectedRows)->get();
                $totalUsersAffected = $rolesToDelete->sum('users_count');
            @endphp
            @if($totalUsersAffected > 0)
                <div class="mb-4 rounded-md bg-amber-50 p-3 border border-amber-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-bx-error class="text-amber-400 w-5 h-5 mt-0.5" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">Warning: Active Assignments</h3>
                            <div class="mt-1 text-xs text-amber-700">
                                The selected roles are currently assigned to a total of <strong>{{ $totalUsersAffected }} active user(s)</strong>. They will lose these permissions immediately.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <p class="text-sm text-slate-500 mb-6">Are you sure you want to delete the {{ count($selectedRows) }} selected role(s)? This action cannot be undone.</p>
            <div class="flex justify-end gap-3">
                <button type="button" wire:click="$set('showBulkDeleteModal', false)"
                    class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button wire:click="executeBulkDelete" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                    <x-bx-loader-alt class="animate-spin" wire:loading wire:target="executeBulkDelete" />
                    <span wire:loading.remove wire:target="executeBulkDelete">Yes, Delete All</span>
                    <span wire:loading wire:target="executeBulkDelete">Deleting...</span>
                </button>
            </div>
        </div>
    </x-modal>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</div>
