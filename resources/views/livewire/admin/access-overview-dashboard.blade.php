<div class="w-full space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Access Overview Dashboard
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                System-wide security posture, user distribution, and granular access auditing.
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-medium text-slate-900 border border-slate-200 hover:bg-slate-100 transition-colors">
                Manage Users
            </a>
            <a href="{{ route('admin.roles.index') }}"
                class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors">
                Manage Roles
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        {{-- Total Users --}}
        <div class="rounded-md border border-slate-200 bg-white p-6">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-md bg-slate-100 text-slate-900">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Users</h3>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['total_users'] }}</p>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs">
                <span class="font-semibold text-emerald-600">{{ $stats['active_users'] }}</span>
                <span class="ml-1 text-slate-500 font-medium">currently active</span>
            </div>
        </div>

        {{-- Roles --}}
        <div class="rounded-md border border-slate-200 bg-white p-6">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-md bg-slate-100 text-slate-900">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">System Roles</h3>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['total_roles'] }}</p>
                </div>
            </div>
            <div class="mt-4 text-xs text-slate-500 font-medium">
                Defining access boundaries
            </div>
        </div>

        {{-- Permissions --}}
        <div class="rounded-md border border-slate-200 bg-white p-6">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-md bg-slate-100 text-slate-900">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040L3 6.247a11.955 11.955 0 011.088 18.107L12 21.503l7.912-2.149c4.243-1.076 5.331-11.861 1.088-15.338l-1.382-.232z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Permissions</h3>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['total_permissions'] }}</p>
                </div>
            </div>
            <div class="mt-4 text-xs text-slate-500 font-medium">
                Functional capability nodes
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Role Distribution --}}
        <div class="rounded-md border border-slate-200 bg-white p-6">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Role Distribution</h2>
                <p class="text-sm text-slate-500">Breakdown of system assignments by role.</p>
            </div>

            <div class="space-y-4">
                @foreach ($roleDistribution as $role)
                    <div class="relative pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold px-2 py-1 uppercase rounded-md text-slate-600 bg-slate-100 border border-slate-200">
                                {{ $role->name }}
                            </span>
                            <span class="text-xs font-bold text-slate-600">
                                {{ $role->count }} Users
                            </span>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded-full bg-slate-100">
                            @php
                                $percentage =
                                    $stats['total_users'] > 0 ? ($role->count / $stats['total_users']) * 100 : 0;
                            @endphp
                            <div style="width:{{ $percentage }}%"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-slate-900">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Check Tool --}}
        <div class="rounded-md border border-slate-200 bg-white p-6">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Access Quick-Check</h2>
                <p class="text-sm text-slate-500">Instantly audit any user's effective access.</p>
            </div>

            <div class="relative mb-6">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="userSearch"
                    placeholder="Search user by name or email..."
                    class="flex h-10 w-full rounded-md border border-slate-200 bg-transparent px-3 py-2 pl-11 text-sm shadow-sm transition-colors placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950">
                @if ($userSearch)
                    <button wire:click="clearSearch"
                        class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>

            @if ($selectedUser)
                <div class="rounded-md bg-slate-50 p-5 border border-slate-200">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-md bg-slate-100 flex items-center justify-center text-slate-900 font-bold">
                            {{ substr($selectedUser['name'], 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900">{{ $selectedUser['name'] }}</h3>
                            <p class="text-xs text-slate-500">{{ $selectedUser['email'] }}</p>
                        </div>
                        <span class="ml-auto px-2 py-1 rounded-sm text-[10px] font-bold uppercase {{ $selectedUser['active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $selectedUser['active'] ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2">Roles</h4>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($userRoles as $role)
                                    <span class="px-2 py-1 rounded bg-white border border-slate-200 text-xs text-slate-700">
                                        {{ $role }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400">None</span>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2">Permissions</h4>
                            <p class="text-xl font-bold text-slate-900">{{ count($userPermissions) }}</p>
                        </div>
                    </div>
                </div>
            @elseif($userSearch && !$selectedUser)
                <div class="text-center py-6">
                    <p class="text-sm text-slate-500">No user found matching your search.</p>
                </div>
            @else
                <div class="text-center py-6">
                    <p class="text-sm text-slate-500">Search for a user to start auditing.</p>
                </div>
            @endif
        </div>
    </div>
</div>