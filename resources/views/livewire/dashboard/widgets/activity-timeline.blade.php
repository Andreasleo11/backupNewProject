<div
    class="h-full flex flex-col rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden group hover:shadow-xl hover:border-violet-100 transition-all duration-500">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-violet-50 text-violet-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-slate-800 tracking-tight">Recent Activity</h3>
        </div>
        <button class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <div class="relative">
            {{-- Vertical Line --}}
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-100 rounded-full"></div>

            <div class="space-y-8">
                @forelse ($activities as $activity)
                    @php
                        $icon = match ($activity->event) {
                            'created' => 'plus',
                            'updated' => 'edit-alt',
                            'deleted' => 'trash',
                            'approved' => 'check-circle',
                            'rejected' => 'x-circle',
                            default => 'info-circle',
                        };
                        $color = match ($activity->event) {
                            'created' => 'text-emerald-500 bg-emerald-50',
                            'updated' => 'text-blue-500 bg-blue-50',
                            'deleted' => 'text-rose-500 bg-rose-50',
                            'approved' => 'text-indigo-500 bg-indigo-50',
                            'rejected' => 'text-amber-500 bg-amber-50',
                            default => 'text-slate-500 bg-slate-50',
                        };
                    @endphp
                    <div class="relative pl-10 group/activity">
                        {{-- Icon Dot --}}
                        <div
                            class="absolute left-0 top-0.5 h-8 w-8 rounded-full {{ $color }} border-4 border-white shadow-sm flex items-center justify-center z-10 transition-transform group-hover/activity:scale-110">
                            <i class='bx bx-{{ $icon }} text-sm'></i>
                        </div>

                        <div class="flex flex-col">
                            <p class="text-[13px] text-slate-600 leading-relaxed pt-0.5">
                                <span class="font-bold text-slate-900">{{ $activity->causer?->name ?? 'System' }}</span>
                                {{ $activity->description }}
                                <span
                                    class="font-bold text-slate-800">{{ $activity->subject?->doc_number ?? ($activity->subject?->name ?? '') }}</span>
                            </p>
                            <span
                                class="text-[10px] font-medium text-slate-400 mt-1 flex items-center gap-1.5 uppercase tracking-wider">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $activity->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 opacity-30">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">No recent activity</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 mt-auto">
        <a href="#"
            class="text-[11px] font-black uppercase tracking-widest text-violet-600 hover:text-violet-800 transition-colors flex items-center gap-2 group/all">
            Full Audit Log
            <svg class="h-3 w-3 group-hover/all:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>
    </div>
</div>
