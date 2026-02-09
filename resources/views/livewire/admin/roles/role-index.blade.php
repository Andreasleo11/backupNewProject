<div class="max-w-7xl mx-auto space-y-6 py-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
                Role Management
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Define roles and assign permissions to control system access.
            </p>
        </div>
        <div>
            <button wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-all hover:bg-blue-500 hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                New Role
            </button>
        </div>
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
                placeholder="Search roles or permissions...">
        </div>
    </div>

    {{-- Roles Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($roles as $role)
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $role->name }}</h3>
                                @if(in_array($role->name, ['super-admin', 'admin']))
                                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                        System Role
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-slate-50 flex items-center justify-center text-xs font-bold text-slate-600 border border-slate-200" title="Permissions Count">
                            {{ $role->permissions->count() }}
                        </div>
                    </div>

                    <div class="mt-4">
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Key Permissions</h4>
                        <div class="flex flex-wrap gap-2 max-h-24 overflow-hidden">
                            @forelse($role->permissions->take(5) as $perm)
                                <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
                                    {{ $perm->name }}
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">No permissions assigned</span>
                            @endforelse
                            @if($role->permissions->count() > 5)
                                <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-500 ring-1 ring-inset ring-slate-200">
                                    +{{ $role->permissions->count() - 5 }} more
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card Actions --}}
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-end gap-3">
                     @if (!in_array($role->name, ['super-admin', 'admin']))
                        <button wire:click="confirmDelete({{ $role->id }})"
                            class="text-sm font-medium text-rose-600 hover:text-rose-700 transition-colors">
                            Delete
                        </button>
                    @endif
                    <button wire:click="openEditModal({{ $role->id }})"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-all">
                        Configure
                    </button>
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
    <x-modal wire:model="showModal" maxWidth="2xl">
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

                {{-- Permission Matrix --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                         <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            Assign Permissions
                        </label>
                        <div class="flex gap-2">
                             <button type="button" wire:click="$set('selectedPermissions', [])"
                                class="text-xs font-medium text-slate-500 hover:text-slate-700">
                                Deselect All
                            </button>
                            <span class="text-slate-300">|</span>
                             <button type="button" wire:click="$set('selectedPermissions', {{ $permissions->pluck('name') }})"
                                class="text-xs font-medium text-blue-600 hover:text-blue-700">
                                Select All
                            </button>
                        </div>
                    </div>
                   
                    <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 max-h-[400px] overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @forelse ($permissions as $perm)
                                <label class="relative flex items-start p-3 rounded-lg border border-slate-200 bg-white hover:bg-blue-50/50 hover:border-blue-200 transition-all cursor-pointer group">
                                     <div class="flex h-5 items-center">
                                        <input type="checkbox" value="{{ $perm->name }}" wire:model.defer="selectedPermissions"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <span class="font-medium text-slate-700 group-hover:text-blue-700">{{ $perm->name }}</span>
                                    </div>
                                </label>
                            @empty
                                <div class="col-span-full text-center py-6 text-slate-500 italic">
                                    No permissions defined in the system.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-500 transition-all hover:scale-105 active:scale-95">
                        {{ $modalMode === 'create' ? 'Create Role' : 'Save Changes' }}
                    </button>
                </div>
            </form>
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
