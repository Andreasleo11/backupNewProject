<div class="w-full space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Department Master
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Configure organizational structure and valid charge codes.
            </p>
        </div>
        <div>
            @can('department.create')
                <button wire:click="openCreateModal" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 shadow-sm hover:bg-slate-900/90 transition-colors disabled:opacity-50">
                    <i class='bx bx-loader-alt animate-spin' wire:loading wire:target="openCreateModal"></i>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" wire:loading.remove wire:target="openCreateModal">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Department
                </button>
            @endcan
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="rounded-md border border-slate-200 bg-white p-4">
        <div class="flex flex-col lg:flex-row gap-4 justify-between items-center">

            {{-- Search --}}
            <div class="relative w-full lg:w-96">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.400ms="search"
                    class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent py-1 pl-9 pr-3 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors placeholder:text-slate-500"
                    placeholder="Search by code or name...">
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto justify-end">
                {{-- Branch Filter --}}
                <select wire:model.live="branchFilter"
                    class="flex h-9 w-40 rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                    <option value="">All Branches</option>
                    <option value="JAKARTA">Jakarta</option>
                    <option value="KARAWANG">Karawang</option>
                </select>

                {{-- Status Toggle --}}
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.live="onlyActive" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                    <span class="text-sm font-medium text-slate-700">Active Only</span>
                </label>

                <select wire:model.live="perPage"
                    class="flex h-9 w-32 rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                    <option value="10">10 / page</option>
                    <option value="25">25 / page</option>
                    <option value="50">50 / page</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Data Grid --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr>
                        <th scope="col"
                            class="px-6 py-4 text-left text-sm font-medium text-slate-500 border-b border-slate-200">Code
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-sm font-medium text-slate-500 border-b border-slate-200">
                            Department Name</th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-sm font-medium text-slate-500 border-b border-slate-200">Branch
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-sm font-medium text-slate-500 border-b border-slate-200">Dept
                            No</th>
                        <th scope="col"
                            class="px-6 py-4 text-center text-sm font-medium text-slate-500 border-b border-slate-200">
                            Status</th>
                        <th scope="col" class="relative px-6 py-4 border-b border-slate-200"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($departments as $dept)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-xs font-bold ring-1 ring-inset ring-blue-600/10">
                                        {{ substr($dept->code, 0, 2) }}
                                    </div>
                                    <span class="font-bold text-slate-900">{{ $dept->code }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $dept->name }}</div>
                                @if ($dept->is_office)
                                    <div class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Head Office
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $dept->branch === 'JAKARTA' ? 'bg-purple-50 text-purple-700 ring-purple-600/20' : 'bg-orange-50 text-orange-700 ring-orange-600/20' }}">
                                    {{ $dept->branch }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 font-mono">
                                {{ $dept->dept_no }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                @if ($dept->is_active)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                @can('department.update')
                                <div class="flex items-center justify-end gap-2">
                                        <button wire:click="toggleStatus({{ $dept->id }})"
                                            class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded transition-colors"
                                            title="{{ $dept->is_active ? 'Deactivate' : 'Activate' }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button wire:click="openEditModal({{ $dept->id }})"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                            title="Edit Department">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div
                                    class="mx-auto h-16 w-16 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mb-4">
                                    <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900">No departments found</h3>
                                <p class="mt-1 text-sm text-slate-500 mb-4">There are no departments matching your criteria.</p>
                                @can('department.create')
                                    <button wire:click="openCreateModal"
                                        class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors">
                                        Add First Department
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($departments->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                {{ $departments->links() }}
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <x-modal wire:model="showModal" maxWidth="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900">
                    {{ $editingId ? 'Edit Department' : 'New Department' }}
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
                <div class="grid grid-cols-2 gap-5">
                    {{-- Code --}}
                    <div class="relative">
                        <input type="text" wire:model.defer="code" id="code" autofocus
                            class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent peer"
                            placeholder="Dept Code">
                        <label for="code"
                            class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                            Dept Code <span class="text-red-500">*</span>
                        </label>
                        @error('code')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dept No --}}
                    <div class="relative">
                        <input type="text" wire:model.defer="dept_no" id="dept_no"
                            class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent peer"
                            placeholder="Dept No">
                        <label for="dept_no"
                            class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                            Dept No <span class="text-red-500">*</span>
                        </label>
                        @error('dept_no')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Name --}}
                <div class="relative">
                    <input type="text" wire:model.defer="name" id="name"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent peer"
                        placeholder="Department Name">
                    <label for="name"
                        class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        Department Name <span class="text-red-500">*</span>
                    </label>
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Branch --}}
                <div class="relative">
                    <select wire:model.defer="branch" id="branch"
                        class="flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent peer appearance-none">
                        <option value="JAKARTA">Jakarta</option>
                        <option value="KARAWANG">Karawang</option>
                    </select>
                    <label for="branch"
                        class="absolute left-3 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                        Branch Location <span class="text-red-500">*</span>
                    </label>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    @error('branch')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.defer="is_active" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950">
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
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Create Department' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-modal>
</div>
