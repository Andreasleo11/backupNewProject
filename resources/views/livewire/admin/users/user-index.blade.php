<div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
    {{-- Filters / actions row --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">User Management</h1>
            <!-- <p class="mt-0.5 text-xs text-slate-500">
                Search, filter, link employees, assign roles, change passwords, and toggle status.
            </p> -->
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Search name or email..."
                       class="w-64 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm
                              focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-4.35-4.35M11 19a8 8 0 1 0-8-8 8 8 0 0 0 8 8z" />
                    </svg>
                </span>
            </div>

            <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox"
                       wire:model.live.debounce.400ms="onlyActive"
                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Active only
            </label>

            <select wire:model.live.debounce.400ms="perPage"
                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs shadow-sm
                           focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="10">10 / page</option>
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
            </select>

            <button type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white shadow-sm
                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                </svg>
                New User
            </button>
        </div>
    </div>

    {{-- Users table --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Name
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Email
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Roles
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Employee Info
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Status
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($users as $index => $user)
                    <tr class="hover:bg-slate-50/60" wire:key="user-row-{{ $user->id }}">
                        <td class="px-4 py-3 text-slate-500">
                            {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                        </td>

                        {{-- User info --}}
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $user->name }}</div>
                            @if ($user->employeeNik)
                                <div class="text-xs text-slate-500">
                                    {{ $user->employeeNik }} — {{ $user->employeeName }}
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-slate-600">
                            {{ $user->email }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $role)
                                    <span
                                        class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                        {{ $role }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400">No roles</span>
                                @endforelse
                            </div>
                        </td>

                        <td class="px-4 py-3 text-xs text-slate-600">
                            @if ($user->employeeBranch || $user->employeeDeptCode)
                                <div>{{ $user->employeeBranch ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">
                                    Dept: {{ $user->employeeDeptCode ?? '-' }}
                                </div>
                            @else
                                <span class="text-slate-400">No employee link</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if ($user->active)
                                <span
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <button type="button"
                                        wire:click="toggleStatus({{ $user->id }})"
                                        class="rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                    {{ $user->active ? 'Suspend' : 'Activate' }}
                                </button>

                                <button type="button"
                                        wire:click="openEditModal({{ $user->id }})"
                                        class="rounded-md bg-slate-800 px-2.5 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-slate-900">
                                    Edit
                                </button>

                                <button type="button"
                                        wire:click="openPasswordModal({{ $user->id }})"
                                        class="rounded-md bg-amber-500 px-2.5 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-amber-600 text-nowrap">
                                    Change Password
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        {{-- 7 columns total --}}
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                            No users found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
    @if ($users->hasPages())
        <div class="px-4 py-3">
            {{ $users->links() }}
        </div>
    @endif

    {{-- User form modal --}}
    <div x-data="{ openModal: @entangle('showModal').live }">
        {{-- Backdrop --}}
        <div class="fixed inset-0 z-40 bg-black/30"
             x-show="openModal"
             @click="openModal = false"></div>

        {{-- Modal --}}
        <div x-show="openModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
             x-transition.opacity
             @keydown.escape.window="openModal = false">
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-slate-200" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">
                        {{ $editingId ? 'Edit User' : 'Create User' }}
                    </h2>
                    <button type="button"
                            class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            wire:click="$set('showModal', false)">
                        <span class="sr-only">Close</span>
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="save" class="px-5 py-4 space-y-4">
                    {{-- Employee (searchable) --}}
                    <div x-data="{ open: false }" class="relative">
                        <label class="block text-xs font-medium text-slate-700">
                            Employee <span class="text-red-500">*</span>
                        </label>

                        <div class="mt-1">
                            <div class="flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm"
                                 @click="open = true">
                                <input type="text"
                                       wire:model.live.debounce.300ms="employeeSearch"
                                       placeholder="Search NIK or name..."
                                       class="flex-1 bg-transparent text-sm outline-none"
                                       @focus="open = true"
                                       autocomplete="off">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-4 w-4 text-slate-400"
                                     viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                          clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        @if ($selectedEmployeeLabel)
                            <p class="mt-1 text-xs text-slate-500">
                                Selected: {{ $selectedEmployeeLabel }}
                            </p>
                        @endif

                        @error('employeeId')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        {{-- Dropdown list --}}
                        <div x-show="open"
                             x-cloak
                             @click.away="open = false"
                             class="absolute z-50 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-slate-200 bg-white text-sm shadow-lg">
                            @forelse ($employeeOptions as $emp)
                                <button type="button"
                                        class="flex w-full items-center justify-between px-3 py-1.5 text-left hover:bg-slate-50"
                                        wire:click="selectEmployee({{ $emp['id'] }})"
                                        @click="open = false">
                                    <div>
                                        <div class="font-medium text-slate-800">
                                            {{ $emp['nik'] }} — {{ $emp['name'] }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $emp['branch'] }} · Dept {{ $emp['dept_code'] }}
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <div class="px-3 py-2 text-xs text-slate-400">
                                    No employees found.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Name --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               wire:model.defer="name"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm
                                      focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               wire:model.defer="email"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm
                                      focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password (create only) --}}
                    @if (is_null($editingId))
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-700">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password"
                                       wire:model.defer="password"
                                       class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm
                                              focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password"
                                       wire:model.defer="password_confirmation"
                                       class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm
                                              focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>
                    @endif

                    {{-- Roles --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Roles
                        </label>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @foreach ($availableRoles as $role)
                                <label
                                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">
                                    <input type="checkbox"
                                           value="{{ $role }}"
                                           wire:model.defer="selectedRoles"
                                           class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $role }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedRoles')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Active --}}
                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                        <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-700">
                            <input type="checkbox"
                                   wire:model.defer="active"
                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Active
                        </label>

                        <div class="space-x-2">
                            <button type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                    wire:click="$set('showModal', false)">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm
                                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                {{ $editingId ? 'Save changes' : 'Create user' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Password modal --}}
    <div x-data="{ openPasswordModal: @entangle('showPasswordModal').live }">
        {{-- Backdrop --}}
        <div class="fixed inset-0 z-40 bg-black/30"
             x-show="openPasswordModal"
             @click="openPasswordModal = false"></div>

        {{-- Modal --}}
        <div x-show="openPasswordModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
             x-transition.opacity>
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-slate-200" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">
                        Change Password
                    </h2>
                    <button type="button"
                            class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            @click="openPasswordModal = false">
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="savePassword" class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               wire:model.defer="newPassword"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm
                                      focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('newPassword')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               wire:model.defer="newPassword_confirmation"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm
                                      focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div class="flex items-center justify-end border-t border-slate-100 pt-3 space-x-2">
                        <button type="button"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                @click="openPasswordModal = false">
                            Cancel
                        </button>
                        <button type="submit"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-indigo-700">
                            Save Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>