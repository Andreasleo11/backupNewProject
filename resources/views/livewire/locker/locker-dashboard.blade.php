<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
    x-data="{ 
        isAssignModalOpen: @entangle('isAssignModalOpen'),
        isIncidentModalOpen: @entangle('isIncidentModalOpen')
    }">
    
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Locker Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">Real-time overview of locker availability and employee assignments.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('lockers.manage') }}" 
                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white text-slate-700 text-sm font-bold border border-slate-200 shadow-sm hover:bg-slate-50 transition-all">
                <i class='bx bx-cog text-lg'></i>
                Manage Units
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class='bx bx-check-circle text-2xl'></i>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">Available</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ \App\Models\Locker::where('status', 'available')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                    <i class='bx bx-user text-2xl'></i>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">Occupied</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ \App\Models\Locker::where('status', 'occupied')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class='bx bx-wrench text-2xl'></i>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-400">Maintenance</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ \App\Models\Locker::where('status', 'maintenance')->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white/60 backdrop-blur-md rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="p-4 flex flex-col md:flex-row items-center gap-4">
            <div class="relative flex-1 w-full">
                <i class='bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg'></i>
                <input type="text" wire:model.live.debounce.300ms="search" 
                    placeholder="Search locker # or location..." 
                    class="w-full pl-11 pr-4 py-2.5 bg-white/80 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <select wire:model.live="statusFilter" 
                    class="flex-1 md:w-48 px-4 py-2.5 bg-white/80 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all">
                    <option value="">All Statuses</option>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Locker Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($this->lockers as $locker)
            <div class="group relative bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                {{-- Status Bar --}}
                <div class="h-1.5 w-full {{ match($locker->status) {
                    'available' => 'bg-emerald-500',
                    'occupied' => 'bg-blue-500',
                    'maintenance' => 'bg-amber-500',
                    default => 'bg-slate-300'
                } }}"></div>

                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ $locker->locker_number }}</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $locker->location ?: 'No Location' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($locker->status === 'occupied' && $locker->currentAssignment && $locker->currentAssignment->incidents()->where('is_paid', false)->exists())
                                <div class="group/fine relative">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] text-white animate-pulse">
                                        <i class='bx bxs-error-circle'></i>
                                    </span>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover/fine:block w-32 px-2 py-1 bg-slate-900 text-white text-[9px] rounded shadow-xl z-20 text-center">
                                        Unpaid fines active
                                    </div>
                                </div>
                            @endif
                            <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter {{ match($locker->status) {
                                'available' => 'bg-emerald-50 text-emerald-600',
                                'occupied' => 'bg-blue-50 text-blue-600',
                                'maintenance' => 'bg-amber-50 text-amber-600',
                                default => 'bg-slate-50 text-slate-600'
                            } }}">
                                {{ $locker->status }}
                            </span>
                        </div>
                    </div>

                    @if ($locker->status === 'occupied' && $locker->currentAssignment)
                        <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-700 shadow-sm">
                                    <i class='bx bx-user text-xl'></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-black text-slate-800 truncate">{{ $locker->currentAssignment->employee->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mt-0.5">{{ $locker->currentAssignment->employee->nik }}</p>
                                </div>
                                <button wire:click="openIncidentModal({{ $locker->currentAssignment->id }})" 
                                    class="h-8 w-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-all shadow-sm group/btn"
                                    title="Report Issue">
                                    <i class='bx bx-error text-lg'></i>
                                </button>
                            </div>

                            {{-- Active Fines List --}}
                            @php $unpaidFines = $locker->currentAssignment->incidents()->where('is_paid', false)->get(); @endphp
                            @if($unpaidFines->count() > 0)
                                <div class="mt-3 space-y-2">
                                    @foreach($unpaidFines as $incident)
                                        <div class="flex items-center justify-between p-2 rounded-xl bg-rose-50/50 border border-rose-100">
                                            <div>
                                                <p class="text-[9px] font-black text-rose-600 uppercase tracking-tighter">{{ str_replace('_', ' ', $incident->type) }}</p>
                                                <p class="text-[10px] font-bold text-slate-700">Rp {{ number_format($incident->fine_amount, 0, ',', '.') }}</p>
                                            </div>
                                            <button wire:click="markFineAsPaid({{ $incident->id }})" 
                                                class="px-2 py-1 rounded-lg bg-emerald-500 text-white text-[8px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-colors shadow-sm shadow-emerald-100">
                                                Paid
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-3 pt-3 border-t border-slate-200/60">
                                <p class="text-[9px] font-bold text-slate-400 uppercase">Assigned Since</p>
                                <p class="text-[10px] font-bold text-slate-600">{{ $locker->currentAssignment->assigned_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <button onclick="confirm('Are you sure you want to release this locker?') || event.stopImmediatePropagation()"
                            wire:click="release({{ $locker->id }})"
                            class="w-full mt-4 py-2.5 rounded-xl bg-rose-50 text-rose-600 text-xs font-bold hover:bg-rose-600 hover:text-white transition-all">
                            Release Locker
                        </button>
                    @elseif ($locker->status === 'available')
                        <div class="mt-4 py-8 flex flex-col items-center justify-center border-2 border-dashed border-slate-100 rounded-3xl">
                            <i class='bx bx-plus-circle text-2xl text-slate-200 mb-2'></i>
                            <p class="text-xs font-bold text-slate-300 uppercase">Empty Unit</p>
                        </div>
                        
                        <button wire:click="openAssignModal({{ $locker->id }})"
                            class="w-full mt-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                            Assign Employee
                        </button>
                    @else
                        <div class="mt-4 py-8 flex flex-col items-center justify-center bg-amber-50/50 rounded-3xl border border-amber-100">
                            <i class='bx bx-error-circle text-2xl text-amber-200 mb-2'></i>
                            <p class="text-xs font-bold text-amber-400 uppercase tracking-widest">Out of Service</p>
                        </div>
                        <button disabled class="w-full mt-4 py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed">
                            Maintenance Mode
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4">
                    <i class='bx bx-search text-4xl'></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">No lockers found matching your criteria</h3>
                <p class="text-slate-500 mt-1">Try adjusting your search or filters.</p>
            </div>
        @endforelse
    </div>

    @if ($this->lockers->hasPages())
        <div class="mt-8">
            {{ $this->lockers->links() }}
        </div>
    @endif

    {{-- Assignment Modal --}}
    <template x-teleport="body">
        <div x-show="isAssignModalOpen" x-cloak 
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div class="absolute inset-0" @click="isAssignModalOpen = false"></div>
            
            <div class="relative w-full max-w-md transform transition-all" 
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Assign Locker</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Select an employee to allocate this unit</p>
                        </div>
                        <button @click="isAssignModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>

                    <div class="p-8 space-y-6">
                        {{-- Employee Search --}}
                        <div class="relative">
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Search Employee</label>
                            <div class="relative">
                                <i class='bx bx-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400'></i>
                                <input type="text" wire:model.live.debounce.300ms="employeeSearch" 
                                    placeholder="Type name or NIK..." 
                                    class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all">
                            </div>

                            @if ($employeeSearch && !$selectedEmployeeNik)
                                <div class="absolute z-[110] left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-y-auto max-h-64 custom-scrollbar">
                                    @forelse ($this->employees as $employee)
                                        <button wire:click="selectEmployee('{{ $employee->nik }}')" 
                                            class="w-full px-4 py-3 text-left hover:bg-slate-50 flex items-center gap-3 transition-colors border-b border-slate-50 last:border-0">
                                            <div class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-[10px]">
                                                {{ substr($employee->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-700">{{ $employee->name }}</p>
                                                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-tighter">{{ $employee->nik }}</p>
                                            </div>
                                        </button>
                                    @empty
                                        <div class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                                            No employees found.
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                            @error('selectedEmployeeNik') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Notes (Optional)</label>
                            <textarea wire:model="notes" rows="3"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                                placeholder="Add any additional info..."></textarea>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="button" @click="isAssignModalOpen = false" 
                                class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all">
                                Cancel
                            </button>
                            <button wire:click="assign" 
                                class="flex-1 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                                Confirm Assignment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Incident Modal --}}
    <template x-teleport="body">
        <div x-show="isIncidentModalOpen" x-cloak 
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div class="absolute inset-0" @click="isIncidentModalOpen = false"></div>
            
            <div class="relative w-full max-w-md transform transition-all" 
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">Report Issue</h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Log an incident or issue a fine</p>
                        </div>
                        <button @click="isIncidentModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="reportIncident" class="p-8 space-y-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Issue Type</label>
                            <select wire:model="incidentType" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="lost_key">Lost Physical Key</option>
                                <option value="damage">Locker Damage</option>
                                <option value="misuse">Unauthorized Misuse</option>
                                <option value="other">Other Issue</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Fine Amount (IDR)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                                <input type="number" wire:model="fineAmount" 
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                                    placeholder="0">
                            </div>
                            @error('fineAmount') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Internal Notes</label>
                            <textarea wire:model="incidentNotes" rows="3"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 transition-all"
                                placeholder="Describe the incident..."></textarea>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="button" @click="isIncidentModalOpen = false" 
                                class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="flex-1 px-6 py-3 rounded-xl bg-rose-600 text-white font-bold shadow-lg shadow-rose-100 hover:bg-rose-700 hover:-translate-y-0.5 transition-all">
                                Issue Fine
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>
