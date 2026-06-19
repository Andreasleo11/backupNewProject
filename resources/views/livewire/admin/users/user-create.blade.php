<div class="w-full space-y-6">
    <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
        <h2 class="text-xl font-bold text-slate-900">
            Create New User
        </h2>
        <a href="{{ route('admin.users.index') }}"
            class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">
            &larr; Back to Users
        </a>
    </div>

    <form wire:submit.prevent="save" class="space-y-6 max-w-4xl bg-white p-6 rounded-md border border-slate-200">
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
                <div class="mt-2 flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 border border-emerald-100">
                    <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Linked: {{ $selectedEmployeeLabel }}
                </div>
            @endif
            @if (!empty($employeeOptions))
                <div class="absolute z-50 mt-1 max-h-56 w-full overflow-auto rounded-md border border-slate-200 bg-white shadow-lg">
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
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950"
                    placeholder=" ">
                <label for="name" class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-slate-900">
                    Full Name <span class="text-red-500">*</span>
                </label>
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="relative">
                <input type="email" wire:model.defer="email" id="email"
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950"
                    placeholder=" ">
                <label for="email" class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-slate-900">
                    Email Address <span class="text-red-500">*</span>
                </label>
                @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Password --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="relative">
                <input type="password" wire:model.defer="password" id="password"
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950"
                    placeholder=" ">
                <label for="password" class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-slate-900">
                    Password <span class="text-red-500">*</span>
                </label>
                @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="relative">
                <input type="password" wire:model.defer="password_confirmation" id="password_confirmation"
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950"
                    placeholder=" ">
                <label for="password_confirmation" class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-slate-900">
                    Confirm Password <span class="text-red-500">*</span>
                </label>
            </div>
        </div>

        {{-- Roles --}}
        <div class="rounded-md border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-900">Role Assignments</h3>
                <p class="text-xs text-slate-500 mt-1">Select the roles this user should have in the system.</p>
            </div>
            <div class="p-4 space-y-4">
                @foreach ($this->groupedRoles as $groupName => $roles)
                    <div class="space-y-2">
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">{{ $groupName }}</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($roles as $role)
                                <label class="cursor-pointer group/role relative" title="{{ $this->getRoleDescription($role) }}">
                                    <input type="checkbox" value="{{ $role }}" wire:model.defer="selectedRoles" class="peer sr-only">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-600 transition-all hover:border-slate-400 hover:bg-slate-50 peer-checked:!border-slate-900 peer-checked:!bg-slate-900 peer-checked:text-white peer-checked:shadow-md select-none">
                                        <svg class="h-3 w-3 opacity-0 peer-checked/role:opacity-100 transition-opacity hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $role }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Status + Actions --}}
        <div class="flex items-center justify-between border-t border-slate-100 pt-5">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model.defer="active" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                <span class="text-sm font-medium text-slate-700">Active Status</span>
            </label>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                    Cancel
                </a>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors disabled:opacity-50">
                    <i class='bx bx-loader-alt animate-spin' wire:loading wire:target="save"></i>
                    <span wire:loading.remove wire:target="save">Create User</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </div>
    </form>
</div>
