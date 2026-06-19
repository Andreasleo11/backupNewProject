<div class="w-full space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                User Management
            </h1>
            <p class="mt-1 text-sm text-slate-500">Manage system access, roles, and employee linkages.</p>
        </div>
        @can('user.create')
            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 shadow-sm hover:bg-slate-900/90 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New User
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
                {{-- Debounce increased or use blur for better scalability --}}
                <input type="text" wire:model.live.debounce.500ms="search"
                    class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent py-1 pl-9 pr-3 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors placeholder:text-slate-500"
                    placeholder="Search users by name, email, or role...">
            </div>
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.live="onlyActive" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                    <span class="text-sm font-medium text-slate-700">Active Only</span>
                </label>
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
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Linked Employee</th>
                        <th class="px-4 py-3">Roles</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors" wire:key="user-row-{{ $user->id }}">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $user->id }}" class="rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 flex-shrink-0 rounded-md bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-xs border border-slate-200">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->employeeNik)
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-900">{{ $user->employeeNik }}</span>
                                        <span class="text-xs text-slate-500">{{ $user->employeeDeptCode ?? 'No Dept' }} • {{ $user->employeeBranch ?? '-' }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">Unlinked</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1 max-w-[200px] overflow-hidden">
                                    @forelse ($user->roles as $role)
                                        <span class="inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600 border border-slate-200">
                                            {{ $role }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">None</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($user->active)
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 border border-emerald-200">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 border border-slate-200">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                @can('user.update')
                                    <button wire:click="openPasswordModal({{ $user->id }})" class="p-1.5 rounded text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors" title="Change Password">
                                        <i class="bx bx-key"></i>
                                    </button>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="p-1.5 rounded text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition-colors inline-block" title="Edit User">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    @if($user->active)
                                        <button wire:click="confirmSuspend({{ $user->id }})" class="p-1.5 rounded text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Suspend User">
                                            <i class="bx bx-block"></i>
                                        </button>
                                    @else
                                        <button wire:click="toggleStatus({{ $user->id }})" class="p-1.5 rounded text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 transition-colors" title="Restore User">
                                            <i class="bx bx-check-shield"></i>
                                        </button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="bx bx-user-x text-4xl mb-2 text-slate-300"></i>
                                    <p>No users found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Bulk Actions Footer --}}
        @if(count($selectedRows) > 0)
            <div class="bg-blue-50 border-t border-blue-100 px-4 py-3 flex items-center justify-between">
                <span class="text-sm font-medium text-blue-800">{{ count($selectedRows) }} users selected</span>
                <div class="flex gap-2">
                    <button wire:click="bulkSuspend" wire:loading.attr="disabled" class="text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded px-3 py-1 hover:bg-slate-50 transition-colors">
                        <span wire:loading.remove wire:target="bulkSuspend">Toggle Status</span>
                        <span wire:loading wire:target="bulkSuspend">Processing...</span>
                    </button>
                    <button wire:click="openBulkRoleModal" wire:loading.attr="disabled" class="text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded px-3 py-1 hover:bg-slate-50 transition-colors">
                        Bulk Assign Role
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if ($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
    @endif

    {{-- Password Modal --}}
    <x-modal wire:model="showPasswordModal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Change Password</h2>
            <form wire:submit.prevent="savePassword" class="space-y-5">
                <div class="relative">
                    <input type="password" wire:model.defer="newPassword" id="newPassword"
                        autofocus
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent peer"
                        placeholder="New Password">
                    <label for="newPassword" class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    @error('newPassword')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="relative">
                    <input type="password" wire:model.defer="newPassword_confirmation" id="newPassword_confirmation"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent peer"
                        placeholder="Confirm New Password">
                    <label for="newPassword_confirmation" class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showPasswordModal', false)" class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="savePassword" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors disabled:opacity-50">
                        <i class='bx bx-loader-alt animate-spin' wire:loading wire:target="savePassword"></i>
                        <span wire:loading.remove wire:target="savePassword">Update Password</span>
                        <span wire:loading wire:target="savePassword">Updating...</span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Suspend Confirmation Modal --}}
    <x-modal wire:model="showSuspendModal" maxWidth="sm">
        <div class="p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-2">Suspend Access</h2>
            <p class="text-sm text-slate-500 mb-6">Are you sure you want to suspend this user? They will immediately lose access to the system.</p>
            <div class="flex justify-end gap-3">
                <button type="button" wire:click="$set('showSuspendModal', false)" class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button wire:click="executeSuspend" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                    <i class='bx bx-loader-alt animate-spin' wire:loading wire:target="executeSuspend"></i>
                    <span wire:loading.remove wire:target="executeSuspend">Yes, Suspend User</span>
                    <span wire:loading wire:target="executeSuspend">Suspending...</span>
                </button>
            </div>
        </div>
    </x-modal>

    {{-- Bulk Assign Role Modal --}}
    <x-modal wire:model="showBulkRoleModal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-slate-900 mb-2">Bulk Assign Role</h2>
            <p class="text-sm text-slate-500 mb-6">Select a role to assign to the {{ count($selectedRows) }} selected user(s). This will <strong>add</strong> the role to their existing roles.</p>
            
            <form wire:submit.prevent="executeBulkRole" class="space-y-5">
                <div class="relative">
                    <select wire:model.defer="bulkRoleToAssign" id="bulkRoleToAssign"
                        class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 peer">
                        <option value="">Select a role...</option>
                        @foreach(\Spatie\Permission\Models\Role::orderBy('name')->pluck('name') as $roleName)
                            <option value="{{ $roleName }}">{{ $roleName }}</option>
                        @endforeach
                    </select>
                    <label for="bulkRoleToAssign" class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        Role to Assign <span class="text-red-500">*</span>
                    </label>
                    @error('bulkRoleToAssign')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showBulkRoleModal', false)" class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="executeBulkRole" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors disabled:opacity-50">
                        <i class='bx bx-loader-alt animate-spin' wire:loading wire:target="executeBulkRole"></i>
                        <span wire:loading.remove wire:target="executeBulkRole">Assign Role</span>
                        <span wire:loading wire:target="executeBulkRole">Assigning...</span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</div>
