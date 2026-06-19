<div class="w-full space-y-6">
    <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">
                Edit Role: {{ $name }}
            </h2>
            @if(in_array($name, ['super-admin', 'admin']))
                <span class="inline-flex items-center gap-1 rounded bg-indigo-50 px-2 py-1 mt-2 text-xs font-semibold text-indigo-700 border border-indigo-100">
                    <i class="bx bx-shield-quarter"></i> System Role
                </span>
            @endif
        </div>
        <a href="{{ route('admin.roles.index') }}"
            class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">
            &larr; Back to Roles
        </a>
    </div>

    <form wire:submit.prevent="save" class="space-y-6 max-w-5xl bg-white p-6 rounded-md border border-slate-200 shadow-sm">
        {{-- Role Details --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="relative">
                <input type="text" wire:model.defer="name" id="name"
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950"
                    placeholder=" " @if(in_array($name, ['super-admin', 'admin'])) disabled @endif>
                <label for="name" class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-slate-900">
                    Role Name <span class="text-red-500">*</span>
                </label>
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="relative">
                <input type="text" wire:model.defer="description" id="description"
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950"
                    placeholder=" ">
                <label for="description" class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-slate-900">
                    Description
                </label>
                @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Permission Matrix --}}
        <div>
            <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                <h3 class="text-sm font-semibold text-slate-900">Permission Matrix</h3>
                <div class="flex gap-3">
                    <button type="button" wire:click="$set('selectedPermissions', [])"
                        class="text-xs font-medium text-slate-500 hover:text-rose-600 transition-colors"
                        @if($name === 'super-admin') disabled @endif>
                        Clear All
                    </button>
                    <span class="text-slate-200">|</span>
                    <button type="button"
                        wire:click="$set('selectedPermissions', {{ \Spatie\Permission\Models\Permission::pluck('name') }})"
                        class="text-xs font-medium text-slate-600 hover:text-slate-900 transition-colors"
                        @if($name === 'super-admin') disabled @endif>
                        Select All
                    </button>
                </div>
            </div>

            @if($name === 'super-admin')
                <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 mb-4 flex items-start gap-3">
                    <i class="bx bx-info-circle text-indigo-500 text-lg mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-indigo-900">Super Admin Override</h4>
                        <p class="text-xs text-indigo-700 mt-1">The <code>super-admin</code> role automatically bypasses all permission checks via a system Gate. The checkboxes below are display-only and cannot be modified.</p>
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                @foreach ($this->groupedPermissions as $groupLabel => $groupPerms)
                    @php
                        $groupNames = collect($groupPerms)->pluck('name')->toArray();
                        $allInGroup = collect($groupNames)->every(fn($n) => in_array($n, $selectedPermissions));
                        $someInGroup = collect($groupNames)->contains(fn($n) => in_array($n, $selectedPermissions));
                    @endphp
                    <div class="rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm">
                        {{-- Group Header --}}
                        <button type="button" wire:click="toggleGroup('{{ $groupLabel }}')"
                            @if($name === 'super-admin') disabled @endif
                            class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-slate-50 transition-colors group/hdr border-b border-slate-100 bg-slate-50/50 disabled:opacity-75 disabled:cursor-not-allowed">
                            <div class="flex items-center gap-3">
                                <span class="h-4 w-4 rounded border flex items-center justify-center flex-shrink-0 transition-colors
                                    {{ $allInGroup ? 'bg-slate-900 border-slate-900 text-white' : ($someInGroup ? 'bg-slate-200 border-slate-300' : 'bg-white border-slate-300') }}">
                                    @if ($allInGroup)
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif($someInGroup)
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-600 block"></span>
                                    @endif
                                </span>
                                <span class="text-sm font-bold text-slate-800">{{ $groupLabel }}</span>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                    {{ collect($groupNames)->intersect($selectedPermissions)->count() }} / {{ count($groupNames) }}
                                </span>
                            </div>
                        </button>

                        {{-- Permission checkboxes --}}
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 bg-white">
                            @foreach ($groupPerms as $perm)
                                <label class="flex items-start gap-2 cursor-pointer group/perm p-2 rounded-md hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-all @if($name === 'super-admin') opacity-75 @endif">
                                    <input type="checkbox" value="{{ $perm->name }}" wire:model.defer="selectedPermissions"
                                        @if($name === 'super-admin') disabled @endif
                                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950 transition-colors disabled:cursor-not-allowed">
                                    <div>
                                        @php
                                            $parts = explode('.', $perm->name, 2);
                                            $action = $parts[1] ?? $perm->name;
                                        @endphp
                                        <div class="text-sm font-medium text-slate-700 group-hover/perm:text-slate-900 transition-colors">{{ ucwords(str_replace('-', ' ', $action)) }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $perm->name }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('selectedPermissions')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 mt-6">
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors disabled:opacity-50 shadow-sm">
                <i class='bx bx-loader-alt animate-spin' wire:loading wire:target="save"></i>
                <span wire:loading.remove wire:target="save">Save Changes</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
