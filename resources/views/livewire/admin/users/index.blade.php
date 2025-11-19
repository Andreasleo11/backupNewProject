<div class="max-w-6xl mx-auto px-4 py-6">
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Alerts --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <div class="flex items-start gap-2">
                <svg class="mt-0.5 h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">User Management</h1>
            <p class="mt-0.5 text-xs text-slate-500">
                Search users and manage their roles.
            </p>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
            <div class="relative w-full sm:w-72">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                </span>
                <input type="text" placeholder="Search name or email..." wire:model.live="search"
                    class="block w-full rounded-md border border-slate-200 bg-white py-1.5 pl-8 pr-3 text-xs text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    {{-- Users table card --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-4 py-2.5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                Users
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th
                            class="w-12 px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            #
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Name
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Email
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Roles
                        </th>
                        <th
                            class="w-44 px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($users as $index => $user)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-2 text-xs text-slate-500">
                                {{ $users->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-2 text-sm text-slate-900">
                                {{ $user->name }}
                            </td>
                            <td class="px-4 py-2 text-sm text-slate-700">
                                {{ $user->email }}
                            </td>
                            <td class="px-4 py-2">
                                @if ($user->roles->isEmpty())
                                    <span class="text-xs text-slate-400">No roles</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($user->roles as $role)
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                <button type="button" wire:click="openRoleModal({{ $user->id }})"
                                    class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                    Manage Roles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-100 bg-slate-50 px-4 py-2.5">
                {{-- Use Laravel's Tailwind pagination views --}}
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Role assignment modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50">
            <div class="mx-4 w-full max-w-3xl rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">
                        Assign Roles
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

                <form wire:submit.prevent="saveRoles">
                    <div class="max-h-[360px] space-y-4 overflow-y-auto px-5 py-4">
                        @php
                            $user = $editingUserId ? $users->firstWhere('id', $editingUserId) : null;
                        @endphp

                        {{-- User info --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700">
                                User
                            </label>
                            <input type="text"
                                class="mt-1 block w-full cursor-not-allowed rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 shadow-sm"
                                value="{{ $user?->name . ' (' . $user?->email . ')' }}" disabled>
                        </div>

                        {{-- Roles --}}
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <label class="text-sm font-medium text-slate-700 mb-0">
                                    Roles
                                </label>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="$set('selectedRoles', [])"
                                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        Clear all
                                    </button>
                                    <button type="button"
                                        wire:click="$set('selectedRoles', {{ $roles->pluck('name') }})"
                                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        Select all
                                    </button>
                                </div>
                            </div>

                            <div class="rounded-lg border border-slate-200 bg-slate-50/60 px-3 py-3">
                                <div class="max-h-64 space-y-2 overflow-y-auto">
                                    @forelse ($roles as $role)
                                        <label class="flex items-start gap-2 text-sm text-slate-700">
                                            <input id="role_{{ $role->id }}" type="checkbox"
                                                value="{{ $role->name }}" wire:model.defer="selectedRoles"
                                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span>{{ $role->name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-slate-500">
                                            No roles defined yet.
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
                            Save Roles
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
