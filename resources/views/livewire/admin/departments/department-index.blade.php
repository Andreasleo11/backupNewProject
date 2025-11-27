<div class="space-y-4">
    {{-- Alerts --}}
    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header + filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Department master</h2>
            <p class="mt-0.5 text-xs text-slate-500">
                Manage department codes, names, branches and active status.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Search code or name..."
                       class="w-56 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm
                              focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-4.35-4.35M11 19a8 8 0 1 0-8-8 8 8 0 0 0 8 8z" />
                    </svg>
                </span>
            </div>

            <select wire:model.live.debounce.350ms="branchFilter"
                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs shadow-sm
                           focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">All branches</option>
                <option value="JAKARTA">Jakarta</option>
                <option value="KARAWANG">Karawang</option>
            </select>

            <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox"
                       wire:model.live.debounce.350ms="onlyActive"
                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Active only
            </label>

            <select wire:model.live.debounce.350ms="perPage"
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
                New Department
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">#</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Code</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Dept No</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Branch</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Office</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($departments as $dept)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 text-slate-500">
                            {{ $loop->iteration + ($departments->currentPage() - 1) * $departments->perPage() }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            {{ $dept->code }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $dept->name }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $dept->dept_no }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $dept->branch }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($dept->is_office)
                                <span
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-medium text-emerald-700">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Yes
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-500">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    No
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($dept->is_active)
                                <span
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-medium text-emerald-700">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-500">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <button type="button"
                                        wire:click="toggleStatus({{ $dept->id }})"
                                        class="rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                    {{ $dept->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button type="button"
                                        wire:click="openEditModal({{ $dept->id }})"
                                        class="rounded-md bg-slate-800 px-2.5 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-slate-900">
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-xs text-slate-500">
                            No departments found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($departments->hasPages())
            <div class="px-4 py-3">
                {{ $departments->links() }}
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-data="{ open: @entangle('showModal').live }">
        <div class="fixed inset-0 z-40 bg-black/30"
             x-show="open"
             @click="open = false"></div>

        <div x-show="open"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
             x-transition.opacity
             @keydown.escape.window="open = false">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-slate-200"
                 @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h3 class="text-sm font-semibold text-slate-900">
                        {{ $editingId ? 'Edit department' : 'Create department' }}
                    </h3>
                    <button type="button"
                            class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            @click="open = false">
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="save" class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               wire:model.defer="code"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs shadow-sm
                                      focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('code')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               wire:model.defer="name"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs shadow-sm
                                      focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Dept No <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               wire:model.defer="dept_no"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs shadow-sm
                                      focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('dept_no')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Branch <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.defer="branch"
                                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs shadow-sm
                                       focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="JAKARTA">Jakarta</option>
                            <option value="KARAWANG">Karawang</option>
                        </select>
                        @error('branch')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                        <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-700">
                            <input type="checkbox"
                                   wire:model.defer="is_active"
                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Active
                        </label>

                        <div class="space-x-2">
                            <button type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                    @click="open = false">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm
                                           hover:bg-indigo-700">
                                {{ $editingId ? 'Save changes' : 'Create' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
