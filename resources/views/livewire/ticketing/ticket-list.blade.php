<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    
    {{-- Header & Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Support Tickets</h1>
            <p class="text-sm font-medium text-slate-500 mt-1">Manage and track all IT requests and issues.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('ticketing.dashboard') ?? '#' }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                <span>KPI Dashboard</span>
            </a>
            <button class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-indigo-700 transition-colors" @click="$dispatch('open-support-bubble', { tab: 'new' })">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>New Ticket</span>
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="w-full md:w-96 relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search ticket # or title..." class="block w-full rounded-xl border-slate-200 pl-10 pr-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 transition-colors">
        </div>

        <div class="flex w-full md:w-auto gap-3">
            <select wire:model.live="statusFilter" class="block w-full rounded-xl border-slate-200 py-2 pl-3 pr-10 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->value }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="priorityFilter" class="block w-full rounded-xl border-slate-200 py-2 pl-3 pr-10 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50">
                <option value="">All Priorities</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority->value }}">{{ $priority->value }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Datatable --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 table-fixed">
                <thead class="bg-slate-50/80 text-xs font-bold uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="w-32 px-6 py-4 text-left">Ticket</th>
                        <th class="px-6 py-4 text-left">Details</th>
                        <th class="w-40 px-6 py-4 text-left">Status</th>
                        <th class="w-32 px-6 py-4 text-left">Priority</th>
                        <th class="w-48 px-6 py-4 text-left">Assignee</th>
                        <th class="w-40 px-6 py-4 text-right">Age</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50/50 transition-colors group cursor-pointer" wire:click="gotoDetail({{ $ticket->id }})">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                    {{ $ticket->ticket_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 mb-0.5 truncate">{{ $ticket->title }}</div>
                                <div class="text-xs text-slate-500 font-medium">
                                    {{ $ticket->category->name }} • Created by {{ optional($ticket->reporter)->name ?? 'System' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'Open' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'In Progress' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'On Hold' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Resolved' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        'Closed' => 'bg-slate-100 text-slate-400 border-slate-200',
                                    ];
                                    $color = $statusColors[$ticket->status->value] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $color }}">
                                    @if($ticket->status->value === 'On Hold')
                                        <svg class="-ml-0.5 mr-1.5 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @endif
                                    {{ $ticket->status->value }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $priColors = [
                                        'Low' => 'text-slate-500',
                                        'Medium' => 'text-sky-600',
                                        'High' => 'text-orange-500',
                                        'Critical' => 'text-rose-600 font-black flex items-center gap-1',
                                    ];
                                @endphp
                                <span class="text-xs font-bold {{ $priColors[$ticket->priority->value] ?? '' }}">
                                    @if($ticket->priority->value === 'Critical')
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    @endif
                                    {{ $ticket->priority->value }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($ticket->assignee)
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-600 uppercase">
                                            {{ substr($ticket->assignee->name, 0, 2) }}
                                        </div>
                                        <span class="text-sm font-semibold text-slate-700">{{ $ticket->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs font-bold text-slate-400 border border-dashed border-slate-300 rounded-full px-2 py-0.5">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-xs font-medium text-slate-500">{{ $ticket->created_at->diffForHumans() }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                <h3 class="mt-2 text-sm font-bold text-slate-900">No tickets found</h3>
                                <p class="mt-1 text-sm text-slate-500">Adjust your filters or create a new ticket.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tickets->hasPages())
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
