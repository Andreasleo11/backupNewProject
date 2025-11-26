<div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
    {{-- Filter / actions row --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Role Management</h1>
            <!-- <p class="mt-0.5 text-xs text-slate-500">
                Manage roles and their permissions for your application.
            </p> -->
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
            <div class="relative w-full sm:w-60">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                </span>
                <input type="text" wire:model.live="search"
                    class="block w-full rounded-md border border-slate-200 bg-white py-1.5 pl-8 pr-3 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    placeholder="Search role...">
            </div>

            <button type="button" wire:click="openCreateModal"
                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                </svg>
                New Role
            </button>
        </div>
    </div>

    {{-- Roles table --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th
                            class="w-12 px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            #
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Role
                        </th>
                        <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Permissions
                        </th>
                        <th
                            class="w-44 px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($roles as $index => $role)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-2 text-xs text-slate-500">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-slate-900">
                                        {{ $role->name }}
                                    </span>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-1">
                                        @if ($role->name === 'admin')
                                            <span
                                                class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-medium text-indigo-700">
                                                System role
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-700">
                                    {{ $role->permissions->count() }} permission(s)
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" wire:click="openEditModal({{ $role->id }})"
                                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                        Edit
                                    </button>

                                    <button type="button" wire:click="confirmDelete({{ $role->id }})"
                                        @if ($role->name === 'admin') disabled @endif
                                        class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 shadow-sm transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-50 disabled:text-slate-400">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">
                                No roles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50">
            <div class="mx-4 w-full max-w-3xl rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">
                        {{ $modalMode === 'create' ? 'Create Role' : 'Edit Role' }}
                    </h2>
                    <button type="button" wire:click="$set('showModal', false)"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        <span class="sr-only">Close</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="max-h-[420px] space-y-4 overflow-y-auto px-5 py-4">
                        {{-- Role name --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700">
                                Role Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model.defer="name"
                                class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Permissions --}}
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <label class="text-sm font-medium text-slate-700 mb-0">
                                    Permissions
                                </label>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="$set('selectedPermissions', [])"
                                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        Clear all
                                    </button>
                                    <button type="button"
                                        wire:click="$set('selectedPermissions', {{ $permissions->pluck('name') }})"
                                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        Select all
                                    </button>
                                </div>
                            </div>

                            <div class="rounded-lg border border-slate-200 bg-slate-50/60 px-3 py-3">
                                <div class="max-h-72 space-y-2 overflow-y-auto">
                                    @forelse ($permissions as $perm)
                                        <label class="flex items-start gap-2 text-sm text-slate-700">
                                            <input id="perm_{{ $perm->id }}" type="checkbox"
                                                value="{{ $perm->name }}" wire:model.defer="selectedPermissions"
                                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span>{{ $perm->name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-slate-500">
                                            No permissions defined yet.
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-3">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            {{ $modalMode === 'create' ? 'Create' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
