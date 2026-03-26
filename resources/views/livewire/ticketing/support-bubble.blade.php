<div class="fixed bottom-6 right-6 z-[100]" wire:poll.30s="checkUpdates">
    {{-- Bubble Button --}}
    <button 
        wire:click="toggle"
        class="relative flex h-14 w-14 items-center justify-center rounded-full bg-slate-900 text-white shadow-2xl shadow-indigo-500/30 hover:bg-slate-800 transition-transform hover:scale-105"
    >
        @if($isOpen)
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        @else
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
        @endif

        {{-- Notification Dot --}}
        @if($hasUnreadUpdates)
            <span class="absolute right-0 top-0 flex h-3.5 w-3.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-rose-500 border-2 border-slate-900"></span>
            </span>
        @endif
    </button>

    {{-- Concierge Panel --}}
    <div 
        x-data="{ show: @entangle('isOpen') }"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        @click.away="$wire.set('isOpen', false)"
        class="absolute bottom-20 right-0 w-[400px] rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[600px]"
        x-cloak
    >
        {{-- Header --}}
        <div class="bg-slate-900 px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white">IT Concierge</h3>
                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">We typically reply in < 5 mins</p>
                </div>
            </div>
            <button wire:click="toggle" class="text-slate-400 hover:text-white transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-slate-100 bg-slate-50/50">
            <button 
                wire:click="$set('activeTab', 'new')" 
                class="flex-1 py-3 text-xs font-bold uppercase tracking-widest {{ $activeTab === 'new' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-white' : 'text-slate-400 hover:text-slate-600' }}"
            >
                New Ticket
            </button>
            <button 
                wire:click="$set('activeTab', 'my_tickets')" 
                class="flex-1 py-3 text-xs font-bold uppercase tracking-widest {{ $activeTab === 'my_tickets' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-white' : 'text-slate-400 hover:text-slate-600' }}"
            >
                My Tickets
            </button>
        </div>

        {{-- Content Area --}}
        <div class="flex-1 overflow-y-auto p-6">
            @if(session()->has('success'))
                <div class="p-3 mb-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session()->has('error'))
                <div class="p-3 mb-4 rounded-xl bg-rose-50 border border-rose-100 text-rose-700 text-xs font-bold">
                    {{ session('error') }}
                </div>
            @endif

            @if($activeTab === 'new')
                {{-- Tab A: New Ticket Form --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Issue Overview</label>
                        <input type="text" wire:model="title" placeholder="e.g. Printer is jammed" class="block w-full rounded-xl border-slate-200 py-2 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 shadow-sm">
                        @error('title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Category</label>
                        <select wire:model="category_id" class="block w-full rounded-xl border-slate-200 py-2 pl-3 pr-8 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 shadow-sm">
                            <option value="">Select a category</option>
                            @foreach($this->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Priority</label>
                        <select wire:model="priority" class="block w-full rounded-xl border-slate-200 py-2 pl-3 pr-8 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 shadow-sm">
                            <option value="Low">Low (No rush)</option>
                            <option value="Medium">Medium (Affects some work)</option>
                            <option value="High">High (Cannot perform duties)</option>
                            <option value="Critical">Critical (System down)</option>
                        </select>
                        @error('priority') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Details</label>
                        <textarea wire:model="description" rows="3" placeholder="Please provide steps to reproduce or any error messages..." class="block w-full rounded-xl border-slate-200 py-2 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 shadow-sm"></textarea>
                        @error('description') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2">
                        <button wire:click="submitTicket" class="w-full flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-black text-white shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-colors">
                            <span>Submit Request</span>
                            <div wire:loading wire:target="submitTicket" class="h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin"></div>
                        </button>
                    </div>
                </div>

            @else
                {{-- Tab B: My Tickets --}}
                <div class="space-y-3">
                    @forelse($this->myTickets as $ticket)
                        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer" onclick="window.location.href='{{ route('ticketing.show', $ticket->id) ?? '#' }}'">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="text-sm font-bold text-slate-900 truncate pr-2">{{ $ticket->title }}</h4>
                                @php
                                    $statusColors = [
                                        'Open' => 'bg-indigo-50 text-indigo-700',
                                        'In Progress' => 'bg-emerald-50 text-emerald-700',
                                        'On Hold' => 'bg-amber-50 text-amber-700',
                                        'Resolved' => 'bg-slate-100 text-slate-600',
                                        'Closed' => 'bg-slate-100 text-slate-400',
                                    ];
                                    $color = $statusColors[$ticket->status->value] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span class="inline-flex shrink-0 items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $color }}">
                                    {{ $ticket->status->value }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                                <span>{{ $ticket->ticket_number }}</span>
                                <span>Updated {{ $ticket->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-400">
                            <svg class="mx-auto h-10 w-10 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                            <p class="text-sm font-semibold">No active tickets.</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>
