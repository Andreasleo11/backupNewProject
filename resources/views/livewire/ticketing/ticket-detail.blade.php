<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
    
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('ticketing.list') ?? '#' }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    {{ $ticketData->ticket_number }}
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold bg-slate-100 text-slate-600 border-slate-200">
                        {{ $ticketData->status->value }}
                    </span>
                </h1>
                <p class="text-sm font-medium text-slate-500 mt-1">Reported by {{ optional($ticketData->reporter)->name ?? 'Unknown' }} • {{ $ticketData->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        <div>
            @if($ticketData->status->value !== 'Closed')
                <button 
                    wire:click="$dispatch('openStatusModal', { ticket: {{ $ticketData->id }} })"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-slate-800 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span>Update Status</span>
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left: Main Content & Timeline --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Ticket Description --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-lg font-black text-slate-900">{{ $ticketData->title }}</h2>
                    <div class="text-xs font-bold text-slate-400 mt-1 tracking-wide uppercase">{{ $ticketData->category->name }}</div>
                </div>
                <div class="p-6">
                    <div class="prose prose-sm prose-slate max-w-none">
                        {!! nl2br(e($ticketData->description)) !!}
                    </div>
                </div>
            </div>

            {{-- Activity Timeline --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-black text-slate-800 tracking-wide flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Activity Log
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($ticketData->activities as $index => $activity)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute left-5 top-5 -ml-px h-full w-[2px] bg-slate-100" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex items-start space-x-4">
                                            
                                            {{-- Icon Column --}}
                                            <div class="relative">
                                                @if($activity->type->value === 'comment')
                                                    {{-- Human Comment Avatar/Icon --}}
                                                    <span class="h-10 w-10 rounded-full flex items-center justify-center ring-4 ring-white bg-indigo-100 text-indigo-600 font-bold text-xs uppercase z-10 shadow-sm">
                                                        {{ substr(optional($activity->user)->name ?? 'U', 0, 2) }}
                                                    </span>
                                                @else
                                                    {{-- System Action Icon (Smaller, Gray) --}}
                                                    <span class="h-8 w-8 ml-1 rounded-full flex items-center justify-center ring-4 ring-white bg-slate-50 text-slate-400 z-10 border border-slate-100">
                                                        @if($activity->type->value === 'status_change')
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                                        @elseif($activity->type->value === 'assignment')
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        @else
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Content Column --}}
                                            <div class="min-w-0 flex-1">
                                                @if($activity->type->value === 'comment')
                                                    {{-- Chat Bubble Style for Comments --}}
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-sm font-bold text-slate-900">{{ optional($activity->user)->name ?? 'User' }}</span>
                                                        <span class="text-[11px] font-medium text-slate-400">{{ $activity->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <div class="rounded-2xl rounded-tl-none bg-indigo-50 border border-indigo-100 p-4 text-sm text-indigo-900 shadow-sm inline-block max-w-full">
                                                        {!! nl2br(e($activity->reason)) !!}
                                                    </div>
                                                @else
                                                    {{-- Muted System Log Style --}}
                                                    <div class="py-1">
                                                        <p class="text-sm text-slate-500 flex items-center justify-between">
                                                            <span>
                                                                <span class="font-medium text-slate-700">{{ optional($activity->user)->name ?? 'System' }}</span>
                                                                @if($activity->type->value === 'status_change')
                                                                    changed status to <span class="font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded-md">{{ $activity->new_state }}</span>
                                                                @elseif($activity->type->value === 'assignment')
                                                                    {{ $activity->new_state }}
                                                                @endif
                                                            </span>
                                                            <span class="text-[11px] text-slate-400 font-medium">{{ $activity->created_at->diffForHumans() }}</span>
                                                        </p>
                                                        @if($activity->reason)
                                                            <p class="mt-2 text-sm text-slate-600 bg-slate-50 rounded-xl p-3 border border-slate-100">
                                                                "{{ $activity->reason }}"
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </li>
                            @endforeach
                            
                            {{-- Thread Start --}}
                            <li>
                                <div class="relative pb-4">
                                    <div class="relative flex items-start space-x-4 pt-2">
                                        <div class="relative">
                                            <span class="h-8 w-8 ml-1 rounded-full flex items-center justify-center ring-4 ring-white bg-slate-100 text-slate-400 z-10 border border-slate-200">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 py-1">
                                            <div class="flex items-center justify-between text-sm text-slate-500">
                                                <span>Ticket reported by <span class="font-medium text-slate-800">{{ optional($ticketData->reporter)->name ?? 'Unknown' }}</span></span>
                                                <span class="text-[11px] font-medium text-slate-400">{{ $ticketData->created_at->format('M d, H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Post Comment Input --}}
                <div class="px-6 py-5 border-t border-slate-100 bg-slate-50/50">
                    <form wire:submit.prevent="postComment" class="flex items-start gap-4">
                        <div class="h-10 w-10 shrink-0 rounded-full flex items-center justify-center bg-indigo-600 text-white font-bold text-xs uppercase shadow-sm">
                            {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                        </div>
                        <div class="flex-1">
                            <textarea wire:model="newComment" rows="2" placeholder="Reply to this ticket or add internal notes..." class="block w-full rounded-2xl border-slate-200 py-3 px-4 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white shadow-sm resize-none"></textarea>
                            @error('newComment') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-md hover:bg-slate-800 transition-colors">
                                    <span>Post Reply</span>
                                    <div wire:loading wire:target="postComment" class="h-3 w-3 rounded-full border border-white border-t-transparent animate-spin"></div>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- Right: Sidebar Details --}}
        <div class="space-y-6">
            
            {{-- Properties --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-black text-slate-800 tracking-wide">Ticket Properties</h3>
                </div>
                <div class="p-6 space-y-4">
                    
                    {{-- Priority --}}
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Priority</div>
                        <div class="font-bold text-slate-900">{{ $ticketData->priority->value }}</div>
                    </div>

                    {{-- Assignee --}}
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Assignee</div>
                        @if($ticketData->assignee)
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold">
                                    {{ substr($ticketData->assignee->name, 0, 2) }}
                                </div>
                                <span class="font-bold text-slate-900">{{ $ticketData->assignee->name }}</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-400">Unassigned</span>
                                <button wire:click="assignToMe" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Assign to me</button>
                            </div>
                        @endif
                    </div>

                    {{-- SLA Timer --}}
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">SLA Target</div>
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="font-bold text-slate-700">{{ $ticketData->category->sla_hours }} Hours</span>
                        </div>
                    </div>

                    {{-- Time on Hold --}}
                    @if($ticketData->total_hold_time_minutes > 0 || $ticketData->status->value === 'On Hold')
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Time On Hold</div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-amber-600">
                                {{ $ticketData->getFormattedHoldTime() }}
                            </span>
                            @if($ticketData->status->value === 'On Hold')
                                <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                            @endif
                        </div>
                    </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
    
    {{-- Teleport Status Modal --}}
    @push('modals')
        @livewire('ticketing.ticket-status-modal')
    @endpush
</div>
