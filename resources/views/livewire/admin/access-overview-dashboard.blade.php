<div class="max-w-7xl mx-auto space-y-8 py-6 px-4 sm:px-6 lg:px-8">
    {{-- Header Section --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-10 shadow-2xl">
        <div class="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
        
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    Access Overview Dashboard
                </h1>
                <p class="mt-3 max-w-2xl text-lg text-slate-300">
                    System-wide security posture, user distribution, and granular access auditing.
                </p>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-md transition-all hover:bg-white/20">
                    Manage Users
                </a>
                <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:bg-blue-500 hover:scale-105">
                    Manage Roles
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Users --}}
        <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-blue-50 transition-all group-hover:scale-150"></div>
            <div class="relative">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-medium text-slate-500 uppercase tracking-wider">Total Users</h3>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['total_users'] }}</p>
                <div class="mt-2 flex items-center text-xs">
                    <span class="font-semibold text-emerald-600">{{ $stats['active_users'] }}</span>
                    <span class="ml-1 text-slate-400 font-medium">currently active</span>
                </div>
            </div>
        </div>

        {{-- Roles --}}
        <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-indigo-50 transition-all group-hover:scale-150"></div>
            <div class="relative">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-medium text-slate-500 uppercase tracking-wider">System Roles</h3>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['total_roles'] }}</p>
                <div class="mt-2 text-xs text-slate-400 font-medium italic">
                    Defining access boundaries
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-violet-50 transition-all group-hover:scale-150"></div>
            <div class="relative">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040L3 6.247a11.955 11.955 0 011.088 18.107L12 21.503l7.912-2.149c4.243-1.076 5.331-11.861 1.088-15.338l-1.382-.232z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-medium text-slate-500 uppercase tracking-wider">Granular Permissions</h3>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['total_permissions'] }}</p>
                <div class="mt-2 text-xs text-slate-400 font-medium italic">
                    Functional capability nodes
                </div>
            </div>
        </div>

        {{-- System Health --}}
        <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-rose-50 transition-all group-hover:scale-150"></div>
            <div class="relative">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-medium text-slate-500 uppercase tracking-wider">Audit logs</h3>
                <p class="mt-1 text-3xl font-bold text-slate-900">Live</p>
                <div class="mt-2 flex items-center text-xs">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="ml-2 text-emerald-600 font-bold uppercase tracking-widest">Active Monitoring</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        {{-- Role Distribution --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Role Distribution</h2>
                    <p class="text-sm text-slate-500 mt-1">Breakdown of system assignments by role.</p>
                </div>
                <div class="p-2 rounded-xl bg-slate-50 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($roleDistribution as $role)
                    <div class="relative pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-xs font-bold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-100">
                                    {{ $role->name }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold inline-block text-blue-600">
                                    {{ $role->count }} Users
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded-full bg-slate-100">
                            @php
                                $percentage = ($stats['total_users'] > 0) ? ($role->count / $stats['total_users']) * 100 : 0;
                            @endphp
                            <div style="width:{{ $percentage }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-1000"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Check Tool --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Access Quick-Check</h2>
                    <p class="text-sm text-slate-500 mt-1">Instantly audit any user's effective access.</p>
                </div>
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="relative">
                <input type="text" 
                    wire:model.live.debounce.300ms="userSearch" 
                    placeholder="Search user by name or email..."
                    class="w-full rounded-2xl border-slate-200 bg-slate-50 py-4 pl-12 pr-4 text-sm focus:border-blue-500 focus:ring-blue-500 transition-all">
                <div class="absolute left-4 top-4 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                @if($userSearch)
                    <button wire:click="clearSearch" class="absolute right-4 top-4 text-slate-400 hover:text-rose-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>

            @if($selectedUser)
                <div class="mt-8 animate-in fade-in slide-in-from-top-4 duration-500">
                    <div class="rounded-2xl bg-blue-50/50 p-6 border border-blue-100">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-blue-600 flex items-center justify-center text-white text-xl font-bold">
                                {{ substr($selectedUser['name'], 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $selectedUser['name'] }}</h3>
                                <p class="text-xs text-slate-500">{{ $selectedUser['email'] }}</p>
                            </div>
                            <span class="ml-auto px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $selectedUser['active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $selectedUser['active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Assigned Roles</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($userRoles as $role)
                                        <span class="px-2 py-1 rounded-md bg-white border border-blue-100 text-[11px] font-semibold text-blue-700 shadow-sm">
                                            {{ $role }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Total Permissions</h4>
                                <p class="text-2xl font-black text-blue-600">{{ count($userPermissions) }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Permission Preview</h4>
                            <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto custom-scrollbar p-1">
                                @forelse(array_slice($userPermissions, 0, 15) as $perm)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600 font-medium">
                                        {{ $perm }}
                                    </span>
                                @empty
                                    <span class="text-[10px] text-slate-400 italic">No direct permissions</span>
                                @endforelse
                                @if(count($userPermissions) > 15)
                                    <span class="text-[10px] text-slate-400 font-bold italic">+{{ count($userPermissions) - 15 }} more</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($userSearch && !$selectedUser)
                <div class="mt-8 text-center p-8 border-2 border-dashed border-slate-100 rounded-3xl">
                    <p class="text-sm text-slate-400">No user found matching your search.</p>
                </div>
            @else
                <div class="mt-8 text-center p-8 border-2 border-dashed border-slate-100 rounded-3xl">
                    <p class="text-sm text-slate-400">Search for a user to start auditing.</p>
                </div>
            @endif
        </div>
    </div>
    <style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(203, 213, 225, 0.4);
        border-radius: 20px;
    }
    </style>
</div>

