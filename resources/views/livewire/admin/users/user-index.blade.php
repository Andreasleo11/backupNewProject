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
            <button wire:click="openCreateModal" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 shadow-sm hover:bg-slate-900/90 transition-colors disabled:opacity-50">
                <i class='bx bx-loader-alt animate-spin' wire:loading wire:target="openCreateModal"></i>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" wire:loading.remove wire:target="openCreateModal">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New User
            </button>
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
                <input type="text" wire:model.live.debounce.400ms="search"
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
                </select>
            </div>
        </div>
    </div>

    {{-- Users Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($users as $user)
            <div wire:key="user-card-{{ $user->id }}"
                class="group flex flex-col justify-between rounded-md border border-slate-200 bg-white hover:bg-slate-50 transition-colors">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-10 w-10 flex-shrink-0 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-lg">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm truncate" title="{{ $user->name }}">
                                    {{ $user->name }}</h3>
                                <p class="text-xs text-slate-500 truncate" title="{{ $user->email }}">
                                    {{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            @if ($user->active)
                                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 border border-emerald-200">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 border border-slate-200">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3">
                        {{-- Employee Info --}}
                        <div class="flex items-center gap-2 text-sm text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100">
                            <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            @if ($user->employeeNik)
                                <div class="truncate text-xs">
                                    <span class="font-medium text-slate-900">{{ $user->employeeNik }}</span>
                                    <span class="text-slate-400 mx-1">•</span>
                                    <span>{{ $user->employeeDeptCode ?? 'No Dept' }}</span>
                                    @if ($user->employeeBranch)
                                        <span class="ml-1 text-[10px] uppercase text-slate-500 border border-slate-200 px-1 rounded bg-white">
                                            {{ $user->employeeBranch }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400 italic text-xs">No employee record linked</span>
                            @endif
                        </div>

                        {{-- Roles --}}
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($user->roles as $role)
                                <span class="inline-flex items-center rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 border border-slate-200">
                                    {{ $role }}
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">No roles assigned</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Card Actions --}}
                @can('user.update')
                    <div class="border-t border-slate-100 bg-slate-50 px-5 py-3 flex items-center justify-between">
                        @if($user->active)
                            <button wire:click="confirmSuspend({{ $user->id }})"
                                class="text-xs font-medium text-slate-500 hover:text-red-600 transition-colors">
                                Suspend Access
                            </button>
                        @else
                            <button wire:click="toggleStatus({{ $user->id }})"
                                class="text-xs font-medium text-slate-500 hover:text-emerald-600 transition-colors">
                                Restore Access
                            </button>
                        @endif
                        <div class="flex items-center gap-1">
                            <button wire:click="openPasswordModal({{ $user->id }})"
                                class="p-1.5 rounded text-slate-400 hover:bg-white hover:text-slate-600 transition-colors"
                                title="Change Password">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </button>
                            <button wire:click="openEditModal({{ $user->id }})"
                                class="p-1.5 rounded text-slate-400 hover:bg-white hover:text-blue-600 transition-colors"
                                title="Edit User">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endcan
            </div>
        @empty
            <div class="col-span-full py-12 text-center rounded-md border border-slate-200 bg-white">
                <div class="mx-auto h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 border border-slate-100">
                    <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-900">No users found</h3>
                <p class="mt-1 text-sm text-slate-500 mb-4">There are no users matching your criteria.</p>
                @can('user.create')
                    <button wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors">
                        Add First User
                    </button>
                @endcan
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($users->hasPages())
        <div class="mt-6">{{ $users->links() }}</div>
    @endif

    {{-- ────────────────────────────────────────────────────────── --}}
    {{-- User Create / Edit Modal                                   --}}
    {{-- ────────────────────────────────────────────────────────── --}}
    <x-modal wire:model="showModal" maxWidth="2xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900">
                    {{ $editingId ? 'Edit User' : 'Create User' }}
                </h2>
                <button wire:click="$set('showModal', false)"
                    class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="save" class="space-y-5">
                {{-- Employee Search --}}
                <div class="relative group">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="employeeSearch" id="employeeSearch"
                            autofocus
                            class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent peer"
                            placeholder="Search Employee (NIK or Name)" autocomplete="off">
                        <label for="employeeSearch"
                            class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                            Search Employee (NIK or Name) <span class="text-red-500">*</span>
                        </label>
                    </div>
                    @if ($selectedEmployeeLabel)
                        <div
                            class="mt-2 flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 border border-emerald-100">
                            <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Linked: {{ $selectedEmployeeLabel }}
                        </div>
                    @endif
                    @if (!empty($employeeOptions))
                        <div
                            class="absolute z-50 mt-1 max-h-56 w-full overflow-auto rounded-xl border border-slate-100 bg-white shadow-xl">
                            @foreach ($employeeOptions as $emp)
                                <button type="button"
                                    class="w-full px-4 py-3 text-left transition-colors hover:bg-slate-50 border-b border-slate-50 last:border-0"
                                    wire:click="selectEmployee({{ $emp['id'] }})">
                                    <div class="font-medium text-slate-900">{{ $emp['name'] }}</div>
                                    <div class="text-xs text-slate-500 flex items-center gap-2">
                                        <span class="font-mono bg-slate-100 px-1 rounded">{{ $emp['nik'] }}</span>
                                        <span>•</span><span>{{ $emp['branch'] }}</span>
                                        <span>•</span><span>{{ $emp['dept_code'] }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @error('employeeId')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Name + Email --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="relative">
                        <input type="text" wire:model.defer="name" id="name"
                            class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                            placeholder=" ">
                        <label for="name"
                            class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="relative">
                        <input type="email" wire:model.defer="email" id="email"
                            class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                            placeholder=" ">
                        <label for="email"
                            class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Password (Create Only) --}}
                @if (is_null($editingId))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="relative">
                            <input type="password" wire:model.defer="password" id="password"
                                class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                                placeholder=" ">
                            <label for="password"
                                class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                                Password <span class="text-red-500">*</span>
                            </label>
                            @error('password')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="relative">
                            <input type="password" wire:model.defer="password_confirmation"
                                id="password_confirmation"
                                class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                                placeholder=" ">
                            <label for="password_confirmation"
                                class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                        </div>
                    </div>
                @endif

                {{-- ── Roles & Direct Permissions (tabbed when editing, roles-only when creating) ── --}}
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    {{-- Tab bar --}}
                    <div class="flex border-b border-slate-200 bg-slate-50">
                        <button type="button" wire:click="$set('modalTab', 'roles')"
                            class="flex-1 py-2.5 text-sm font-semibold transition-colors
                                {{ $modalTab === 'roles'
                                    ? 'bg-white text-blue-700 border-b-2 border-blue-600 -mb-px'
                                    : 'text-slate-500 hover:text-slate-800' }}">
                            Roles
                        </button>
                        @if ($editingId)
                            <button type="button" wire:click="$set('modalTab', 'permissions')"
                                class="flex-1 py-2.5 text-sm font-semibold transition-colors
                                    {{ $modalTab === 'permissions'
                                        ? 'bg-white text-blue-700 border-b-2 border-blue-600 -mb-px'
                                        : 'text-slate-500 hover:text-slate-800' }}">
                                Direct Permissions
                                @if (count($selectedDirectPermissions) > 0)
                                    <span
                                        class="ml-1.5 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-[10px] font-bold text-white">
                                        {{ count($selectedDirectPermissions) }}
                                    </span>
                                @endif
                            </button>
                        @endif
                    </div>

                    {{-- Roles Tab --}}
                    <div class="{{ $modalTab === 'roles' ? 'block' : 'hidden' }} p-4">
                        <div class="space-y-4 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                            @foreach ($this->groupedRoles as $groupName => $roles)
                                <div class="space-y-2">
                                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">
                                        {{ $groupName }}</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($roles as $role)
                                            <label class="cursor-pointer group/role relative"
                                                title="{{ $this->getRoleDescription($role) }}">
                                                <input type="checkbox" value="{{ $role }}"
                                                    wire:model.defer="selectedRoles" class="peer sr-only">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-600 transition-all
                                                    hover:border-blue-300 hover:bg-blue-50
                                                    peer-checked:!border-blue-600 peer-checked:!bg-blue-600 peer-checked:text-white peer-checked:shadow-md select-none">
                                                    <svg class="h-3 w-3 opacity-0 peer-checked/role:opacity-100 transition-opacity hidden peer-checked:block"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    {{ $role }}
                                                </span>
                                                {{-- Tooltip --}}
                                                <span
                                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover/role:block
                                                    whitespace-nowrap rounded-lg bg-slate-800 px-3 py-1.5 text-xs text-white shadow-lg z-50 pointer-events-none max-w-xs text-center">
                                                    {{ $this->getRoleDescription($role) }}
                                                    <span
                                                        class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-800"></span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Direct Permissions Tab (edit mode only) --}}
                    @if ($editingId)
                        <div class="{{ $modalTab === 'permissions' ? 'block' : 'hidden' }} p-4">
                            <div class="mb-4 text-xs text-slate-500 bg-slate-50 border border-slate-200 p-3 rounded-lg">
                                Direct permissions are <strong>user-specific overrides</strong> that stack on top of role permissions. Use these to fine-tune access.
                            </div>

                            <div class="space-y-4 max-h-[320px] overflow-y-auto custom-scrollbar">
                                @foreach ($groupedPermissions as $groupLabel => $groupPerms)
                                    <div>
                                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">{{ $groupLabel }}</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach ($groupPerms as $perm)
                                                <label class="flex items-start gap-2 cursor-pointer p-2 rounded hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-colors">
                                                    <input type="checkbox" value="{{ $perm->name }}" wire:model.defer="selectedDirectPermissions"
                                                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                                                    <div>
                                                        @php $action = explode('.', $perm->name, 2)[1] ?? $perm->name; @endphp
                                                        <div class="text-xs font-medium text-slate-700">{{ $action }}</div>
                                                        <div class="text-[10px] text-slate-400 font-mono">{{ $perm->name }}</div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Status + Actions --}}
                <div class="flex items-center justify-between border-t border-slate-100 pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.defer="active" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                        <span class="text-sm font-medium text-slate-700">Active Status</span>
                    </label>
                    <div class="flex gap-3">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors disabled:opacity-50">
                            <i class='bx bx-loader-alt animate-spin' wire:loading wire:target="save"></i>
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Create User' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-modal>

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
                    <label for="newPassword"
                        class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    @error('newPassword')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="relative">
                    <input type="password" wire:model.defer="newPassword_confirmation" id="newPassword_confirmation"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent peer"
                        placeholder="Confirm New Password">
                    <label for="newPassword_confirmation"
                        class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showPasswordModal', false)"
                        class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="savePassword"
                        class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors disabled:opacity-50">
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
                <button type="button" wire:click="$set('showSuspendModal', false)"
                    class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button wire:click="executeSuspend" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                    <i class='bx bx-loader-alt animate-spin' wire:loading wire:target="executeSuspend"></i>
                    <span wire:loading.remove wire:target="executeSuspend">Yes, Suspend User</span>
                    <span wire:loading wire:target="executeSuspend">Suspending...</span>
                </button>
            </div>
        </div>
    </x-modal>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</div>
