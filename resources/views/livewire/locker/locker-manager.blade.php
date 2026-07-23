<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
    x-data="{ 
        isModalOpen: @entangle('isModalOpen')
    }">
    
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Locker Management</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">Configure and manage locker units available in the facility.</p>
        </div>
        
        <button wire:click="openCreateModal" 
            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
            <x-bx-plus class="w-5 h-5" />
            Add New Locker
        </button>
    </div>

    {{-- Filters Card --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
        <div class="p-6 bg-slate-50/30 flex flex-col md:flex-row items-center gap-4">
            <div class="relative flex-1 w-full">
                <x-bx-search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5" />
                <input type="text" wire:model.live.debounce.300ms="search" 
                    placeholder="Search locker number or location..." 
                    class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm">
            </div>
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <select wire:model.live="statusFilter" 
                    class="flex-1 md:w-48 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm">
                    <option value="">All Statuses</option>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-4 cursor-pointer hover:text-indigo-600 transition-colors" wire:click="sort_by('locker_number')">
                            Locker # <span class="text-[8px]">{{ $sortBy === 'locker_number' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' }}</span>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:text-indigo-600 transition-colors" wire:click="sort_by('location')">
                            Location <span class="text-[8px]">{{ $sortBy === 'location' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' }}</span>
                        </th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($this->lockers as $locker)
                        <tr class="group hover:bg-slate-50/50 transition-all">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-700">{{ $locker->locker_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-500 font-medium">{{ $locker->location ?: '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusClasses = match($locker->status) {
                                        'available' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'occupied' => 'bg-blue-50 text-blue-600 border-blue-100',
                                        'maintenance' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        default => 'bg-slate-50 text-slate-600 border-slate-100'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClasses }}">
                                    {{ ucfirst($locker->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="openEditModal({{ $locker->id }})" 
                                        class="h-9 w-9 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                        <x-bx-edit-alt class="w-5 h-5" />
                                    </button>
                                    <button onclick="confirm('Are you sure you want to delete this locker?') || event.stopImmediatePropagation()" 
                                        wire:click="delete({{ $locker->id }})" 
                                        class="h-9 w-9 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                        <x-bx-trash class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-4">
                                        <x-bx-cabinet class="w-8 h-8" />
                                    </div>
                                    <h3 class="text-slate-800 font-bold">No lockers found</h3>
                                    <p class="text-slate-500 text-sm mt-1">Try adjusting your search or add a new locker unit.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->lockers->hasPages())
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                {{ $this->lockers->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    <template x-teleport="body">
        <div x-show="isModalOpen" x-cloak 
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div class="absolute inset-0" @click="isModalOpen = false"></div>
            
            <div class="relative w-full max-w-md transform transition-all" 
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">{{ $editingId ? 'Edit Locker' : 'Add New Locker' }}</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $editingId ? 'Modify existing locker details' : 'Configure a new locker unit' }}</p>
                        </div>
                        <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <x-bx-x class="w-6 h-6" />
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="p-8 space-y-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Locker Number</label>
                            <input type="text" wire:model="locker_number" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('locker_number') border-rose-500 @enderror"
                                placeholder="e.g. A-001">
                            @error('locker_number') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Location</label>
                            <input type="text" wire:model="location" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="e.g. Lobby, Hall A">
                            @error('location') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Status</label>
                            <select wire:model="status" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            @error('status') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="button" @click="isModalOpen = false" 
                                class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="flex-1 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                                {{ $editingId ? 'Update Locker' : 'Save Locker' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>
