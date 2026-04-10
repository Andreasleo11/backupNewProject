@section('title', 'Overtime Approval Hub')
@section('page-title', 'Approval Command Center')
@section('page-subtitle', 'Batch review and high-speed multi-approvals')

<div class="space-y-6 pb-20 font-sans">

    {{-- TOP COMMAND BAR --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-xl shadow-indigo-100 ring-4 ring-indigo-50">
                <i class='bx bxs-zap text-2xl'></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight uppercase">Approval Hub</h1>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-0.5">Focus: Contextual Roster Review</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
             <a href="{{ route('overtime.index') }}" 
                class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class='bx bx-arrow-back'></i> Back to Index
             </a>
             <button type="button" wire:click="approveSelected" wire:loading.attr="disabled"
                class="px-6 py-2.5 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:-translate-y-0.5 transition-all flex items-center gap-2 disabled:opacity-50">
                <i class='bx bx-check-double text-lg'></i> Approve Selected
             </button>
        </div>
    </div>

    @if($groups->isEmpty())
        <div class="bg-white rounded-[3rem] border-4 border-dashed border-slate-100 p-20 text-center animate-in fade-in duration-700">
            <div class="h-24 w-24 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-200">
                <i class='bx bx-check-double text-6xl'></i>
            </div>
            <h3 class="text-xl font-black text-slate-400 uppercase tracking-tight">Zero Pending Tasks</h3>
            <p class="text-[10px] text-slate-300 font-bold uppercase tracking-widest mt-2">All your assigned flows are cleared</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4">
            @foreach($groups as $key => $items)
                @php
                    $first = $items[0];
                    $totalEmployees = $items->flatMap->details->pluck('NIK')->unique()->count();
                    $totalHours = $items->flatMap->details->sum(function($d) {
                        try {
                            $s = \Carbon\Carbon::parse($d->start_time);
                            $e = \Carbon\Carbon::parse($d->end_time);
                            if($e->lt($s)) $e->addDay();
                            return ($s->diffInMinutes($e) - (int)$d->break) / 60;
                        } catch(\Exception $e) { return 0; }
                    });
                    $isExpanded = $expandedGroups[$key] ?? false;
                @endphp

                <div wire:key="pack-{{ $key }}" class="group bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden transition-all {{ $isExpanded ? 'ring-4 ring-indigo-500/5 border-indigo-200 shadow-xl' : 'hover:border-slate-300' }}">
                    {{-- GROUP HEADER --}}
                    <div class="px-8 py-6 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-6 cursor-pointer" wire:click="toggleGroup('{{ $key }}')">
                            <input type="checkbox" value="{{ $key }}" wire:model.live="selectedPackKeys" @click.stop 
                                class="h-5 w-5 rounded-lg border-2 border-slate-200 text-indigo-600 focus:ring-indigo-500 transition-all">
                            
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1">
                                    {{ $first->department?->name }} · {{ $first->branch }}
                                </span>
                                <h3 class="text-base font-black text-slate-900 leading-none">
                                    {{ date('l, d M Y', strtotime($first->first_overtime_date)) }}
                                </h3>
                            </div>

                            <div class="h-8 w-px bg-slate-100 hidden sm:block"></div>

                            <div class="hidden sm:flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black">
                                        {{ $totalEmployees }}
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">People</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                                        {{ round($totalHours, 1) }}
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hours</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right hidden sm:block mr-2">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-0.5">Requested by</p>
                                <p class="text-[11px] font-black text-slate-700">{{ $first->user->name }}</p>
                            </div>
                            <button type="button" wire:click="approvePack('{{ $key }}')" wire:loading.attr="disabled"
                                class="h-10 px-5 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm flex items-center gap-2">
                                <i class='bx bx-check-shield'></i> Approve Group
                            </button>
                            <i class='bx bx-chevron-down text-xl text-slate-300 transition-transform duration-300 {{ $isExpanded ? 'rotate-180' : '' }}'></i>
                        </div>
                    </div>

                    {{-- EXPANDED ROSTER --}}
                    @if ($isExpanded)
                        <div x-data x-init="$nextTick(() => { $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }) })" 
                             x-show="true" x-collapse
                             class="px-8 pb-8 pt-4 border-t border-slate-50 bg-slate-50/30">
                            <div class="bg-white rounded-2xl border border-slate-200/60 overflow-hidden shadow-inner">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50/80 border-b border-slate-100">
                                            <th class="px-6 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Employee</th>
                                            <th class="px-6 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">Task / Work Detail</th>
                                            <th class="px-6 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Timing</th>
                                            <th class="px-6 py-3 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Context Insights</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach($items->flatMap->details as $detail)
                                            @php $insight = $insights[$detail->NIK] ?? null; @endphp
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col">
                                                        <span class="text-[11px] font-black text-slate-900 uppercase tracking-tight">{{ $detail->name }}</span>
                                                        <span class="text-[9px] font-mono font-bold text-slate-400">{{ $detail->NIK }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <p class="text-[10px] text-slate-600 font-medium leading-relaxed italic">"{{ $detail->job_desc }}"</p>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50/50 border border-indigo-100/50 text-indigo-600 font-mono text-[10px] font-bold">
                                                        {{ substr($detail->start_time, 0, 5) }} – {{ substr($detail->end_time, 0, 5) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center justify-center gap-3">
                                                        @if($insight)
                                                            {{-- Monthly Hourly Insight --}}
                                                            <div class="group relative">
                                                                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border transition-all
                                                                    {{ $insight['monthly_hours'] > 40 ? 'bg-rose-50 border-rose-200 text-rose-600' : 'bg-emerald-50 border-emerald-200 text-emerald-600' }}">
                                                                    <i class='bx bx-calendar-check text-xs'></i>
                                                                    <span class="text-[9px] font-black uppercase">{{ $insight['monthly_hours'] }}h MTD</span>
                                                                </div>
                                                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-32 hidden group-hover:block bg-slate-900 text-white text-[8px] p-2 rounded-lg text-center z-10 shadow-xl">
                                                                     Total Approved OT this month
                                                                </div>
                                                            </div>

                                                            {{-- Consecutive Streak Insight --}}
                                                            @if($insight['streak_days'] > 2)
                                                                <div class="group relative">
                                                                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border transition-all animate-pulse
                                                                        {{ $insight['streak_days'] >= 5 ? 'bg-amber-100 border-amber-300 text-amber-700' : 'bg-slate-100 border-slate-200 text-slate-600' }}">
                                                                        <i class='bx bxs-hot text-xs'></i>
                                                                        <span class="text-[9px] font-black uppercase">{{ $insight['streak_days'] }} Days Streak</span>
                                                                    </div>
                                                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-32 hidden group-hover:block bg-slate-900 text-white text-[8px] p-2 rounded-lg text-center z-10 shadow-xl">
                                                                         Consecutive work days with overtime
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <div class="h-6 w-20 bg-slate-100 rounded-full animate-pulse"></div>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
