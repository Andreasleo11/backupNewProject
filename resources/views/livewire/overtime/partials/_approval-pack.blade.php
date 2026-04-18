{{--
    Reusable approval pack (date group) card.
    Variables expected:
        $groupKey       – string
        $isExpanded     – bool
        $items          – Collection of OvertimeForm
        $first          – first OvertimeForm in group
        $totalEmployees – int
        $totalHours     – float
        $totalForms     – int
        $insights       – array keyed by NIK
        $indent         – Tailwind margin class
--}}

<div wire:key="pack-{{ $groupKey }}"
     class="group bg-white rounded-xl border border-amber-200/60 shadow-sm overflow-hidden {{ $indent }} transition-all
            {{ $isExpanded ? 'ring-2 ring-amber-400/20 border-amber-300 shadow' : 'hover:border-amber-300 hover:shadow-sm' }}">

    {{-- PACK HEADER - Inline & Compact --}}
    <div class="px-4 py-3 flex items-center justify-between gap-2 hover:bg-amber-50/40 transition-colors cursor-pointer"
        wire:click="toggleGroup('{{ $groupKey }}')">

        <div class="flex items-center gap-3 flex-1 min-w-0">

            <!-- Checkbox -->
            <input type="checkbox" 
                value="{{ $groupKey }}" 
                wire:model.live="selectedPackKeys" 
                @click.stop
                class="h-3 w-3 rounded-lg border-2 border-slate-200 text-indigo-600 flex-shrink-0">

            <!-- Icon + Date Info -->
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <i class='bx bx-calendar text-xl text-amber-600 flex-shrink-0'></i>
                
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-slate-900 truncate">
                        {{ date('l, d M Y', strtotime($first->first_overtime_date)) }}
                    </h3>
                    <p class="text-xs text-slate-500 truncate">
                        {{ $totalForms }} forms • {{ $totalEmployees }} people • {{ round($totalHours, 1) }}h
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Approve Button -->
        <button type="button"
                wire:click="approvePack('{{ $groupKey }}')"
                wire:confirm="Approve this entire pack?"
                wire:loading.attr="disabled"
                @click.stop
                class="w-5 h-5 flex items-center justify-center text-slate-400 hover:text-emerald-600 rounded-2xl hover:bg-white transition-all flex-shrink-0">
            <i class='bx bx-check text-xl'></i>
        </button>

        <!-- Expand Button -->
        <button type="button"
                class="w-5 h-5 flex items-center justify-center text-slate-400 hover:text-amber-600 rounded-2xl hover:bg-white transition-all flex-shrink-0">
            <i class='bx {{ $isExpanded ? "bx-chevron-up" : "bx-chevron-down" }} text-xl'></i>
        </button>
    </div>

    {{-- EXPANDED CONTENT - Compact Version --}}
    @if ($isExpanded)
        <div class="px-5 pb-4 pt-3 border-t border-slate-100 bg-slate-50/70"
            x-data="{ viewMode: 'list' }"
            x-init="$nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }))">

            <!-- Compact View Toggle -->
            <div class="flex justify-end mb-3">
                <div class="inline-flex bg-white border border-slate-200 rounded-2xl p-0.5 text-xs">
                    <button @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-indigo-100 text-indigo-700 font-medium shadow-sm' : 'text-slate-600 hover:text-slate-700'"
                            class="px-3 py-1.5 rounded-[14px] flex items-center gap-1.5 transition-all">
                        <i class='bx bx-grid-alt text-sm'></i> 
                        <span>Grid</span>
                    </button>
                    <button @click="viewMode = 'list'"
                            :class="viewMode === 'list' ? 'bg-indigo-100 text-indigo-700 font-medium shadow-sm' : 'text-slate-600 hover:text-slate-700'"
                            class="px-3 py-1.5 rounded-[14px] flex items-center gap-1.5 transition-all">
                        <i class='bx bx-list-ul text-sm'></i> 
                        <span>List</span>
                    </button>
                </div>
            </div>

            <!-- GRID VIEW - More compact -->
            <div x-show="viewMode === 'grid'" 
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach ($items->flatMap->details as $detail)
                    <a href="{{ route('overtime.detail', $detail->header_id) }}"
                    target="_blank"
                    class="block bg-white border border-slate-200 rounded-2xl p-3.5 hover:border-indigo-300 hover:shadow-sm transition-all text-sm">
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-slate-900 truncate">{{ $detail->name }}</div>
                                <div class="text-xs font-mono text-slate-500">{{ $detail->NIK }}</div>
                            </div>
                            @if ($detail->header_id)
                                <div class="text-[10px] font-mono text-amber-600 bg-slate-100 px-1.5 py-px rounded">
                                    #{{ $detail->header_id }}
                                </div>
                            @endif
                        </div>

                        <div class="mt-2 text-xs text-slate-600 line-clamp-2 leading-tight">
                            "{{ $detail->job_desc }}"
                        </div>

                        <div class="mt-2.5 text-xs font-mono text-indigo-700">
                            @if ($detail->start_date === $detail->end_date)
                                {{ date('M j H:i', strtotime($detail->start_time)) }} – {{ date('H:i', strtotime($detail->end_time)) }}
                            @else
                                {{ date('M j', strtotime($detail->start_date)) }} → {{ date('M j', strtotime($detail->end_date)) }}
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- LIST VIEW - Much tighter & cleaner -->
            <div x-show="viewMode === 'list'" class="space-y-2">
                @foreach ($items->flatMap->details as $detail)
                    <a href="{{ route('overtime.detail', $detail->header_id) }}"
                    target="_blank"
                    class="flex items-center gap-4 bg-white border border-slate-200 rounded-2xl px-4 py-3 hover:border-indigo-300 hover:shadow-sm transition-all">
                        
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-slate-900 truncate text-sm">{{ $detail->name }}</div>
                            <div class="text-xs font-mono text-slate-500 flex items-center gap-2">
                                {{ $detail->NIK }}
                                @if ($detail->header_id)
                                    <span class="text-amber-600">• #{{ $detail->header_id }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex-1 min-w-0 text-xs text-slate-600 italic line-clamp-1">
                            "{{ $detail->job_desc }}"
                        </div>

                        <div class="font-mono text-xs text-indigo-700 whitespace-nowrap">
                            @if ($detail->start_date === $detail->end_date)
                                {{ date('H:i', strtotime($detail->start_time)) }}–{{ date('H:i', strtotime($detail->end_time)) }}
                            @else
                                {{ date('M j', strtotime($detail->start_date)) }} → {{ date('M j', strtotime($detail->end_date)) }}
                            @endif
                        </div>

                        @if ($insights[$detail->NIK] ?? null)
                            <div class="text-xs font-medium px-2 py-1 bg-rose-50 text-rose-700 rounded-lg whitespace-nowrap">
                                {{ $insights[$detail->NIK]['monthly_hours'] }}h
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>