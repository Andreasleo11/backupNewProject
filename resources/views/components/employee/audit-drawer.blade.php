@props(['selectedEmployee'])

<template x-teleport="body">
    <div 
        x-data="{ show: false }"
        x-show="show"
        x-init="$watch('$wire.selectedNik', value => show = !!value)"
        class="fixed inset-0 z-[150] overflow-hidden"
        style="display: none;"
    >
        <div class="absolute inset-0 overflow-hidden">
            {{-- Backdrop --}}
            <div 
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" 
                x-show="show"
                x-transition:enter="ease-in-out duration-500"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in-out duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="$wire.closeAudit()"
            ></div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div 
                    class="pointer-events-auto w-screen max-w-lg transform transition ease-in-out duration-500 sm:duration-700"
                    x-show="show"
                    x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                >
                    <div class="flex h-full flex-col overflow-y-auto bg-white shadow-2xl border-l border-slate-200">
                        @if($selectedEmployee)
                            <div class="px-8 py-10 border-b border-slate-100 bg-slate-50/50">
                                <div class="flex items-center justify-between mb-8">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-200">
                                            <i class='bx bx-fingerprint text-lg'></i>
                                        </div>
                                        <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Employee Audit Desk</h2>
                                    </div>
                                    <button @click="$wire.closeAudit()" class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-white hover:shadow-sm text-slate-400 hover:text-slate-900 transition-all">
                                        <i class='bx bx-x text-2xl'></i>
                                    </button>
                                </div>
                                
                                <div class="flex items-center gap-6">
                                    <div class="h-20 w-20 rounded-[2.5rem] bg-slate-900 flex items-center justify-center text-white text-2xl font-black shadow-2xl relative overflow-hidden group">
                                        <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/20 to-transparent"></div>
                                        {{ substr($selectedEmployee->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ $selectedEmployee->name }}</h3>
                                        <div class="flex items-center gap-3 mt-1.5">
                                            <p class="text-[11px] font-black text-blue-600 tabular-nums uppercase tracking-widest bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">{{ $selectedEmployee->nik }}</p>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 text-[10px] font-bold text-slate-600 uppercase tracking-wider">
                                                {{ $selectedEmployee->employment_type }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1 px-8 py-8 space-y-8 custom-scrollbar overflow-x-hidden focus:outline-none">
                                {{-- Performance History --}}
                                <section class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                            <i class='bx bx-trending-up text-blue-500'></i> Performance & Metrics
                                        </h4>
                                        <span class="text-[10px] font-bold text-slate-300 uppercase">Recent Cycles</span>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        @forelse($selectedEmployee->evaluationData->sortByDesc('Month')->take(3) as $eval)
                                            <div 
                                                class="bg-white rounded-[1.5rem] border border-slate-100 shadow-sm hover:shadow-md transition-all overflow-hidden group p-3"
                                                x-data="{ expanded: false }"
                                            >
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-3">
                                                            <div class="pl-1">
                                                                <p class="text-[11px] font-black text-slate-900 uppercase tracking-tight">{{ $eval->Month->format('F Y') }}</p>
                                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">{{ $eval->evaluation_type ?? 'Staff' }} Evaluation</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-3">
                                                            <div class="text-right">
                                                                <p class="text-base font-black text-slate-900 leading-none">{{ $eval->total }}</p>
                                                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Points</p>
                                                            </div>
                                                            <button 
                                                                @click="expanded = !expanded"
                                                                class="h-7 w-7 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all"
                                                            >
                                                                <i class='bx text-lg transition-transform duration-300' :class="expanded ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- Attendance Mini-Bar (High-Readability Labels) --}}
                                                    <div class="flex items-center gap-1.5 p-1 bg-slate-50/80 rounded-xl border border-slate-100">
                                                        @foreach([
                                                            'Alpha' => ['rose', 'Absent'], 
                                                            'Telat' => ['amber', 'Late'], 
                                                            'Izin' => ['blue', 'Permit'], 
                                                            'Sakit' => ['slate', 'Sick']
                                                        ] as $key => $meta)
                                                            <div class="flex-1 flex flex-col items-center py-1.5 border-r border-slate-200/50 last:border-none">
                                                                <span class="text-[10px] font-black {{ ($eval->$key ?? 0) > 0 ? 'text-'.$meta[0].'-600' : 'text-slate-300' }}">
                                                                    {{ $eval->$key ?? 0 }}
                                                                </span>
                                                                <span class="text-[7px] font-black text-slate-400 uppercase tracking-tighter">{{ $meta[1] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    {{-- Expanded Full Scorecard --}}
                                                    <div 
                                                        x-show="expanded" 
                                                        x-collapse
                                                        class="pt-2 border-t border-slate-50 space-y-4"
                                                    >
                                                        <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                                                            @php
                                                                $allMarkers = [
                                                                    'kemampuan_kerja' => 'Kemampuan',
                                                                    'disiplin_kerja' => 'Disiplin',
                                                                    'integritas' => 'Integritas',
                                                                    'tanggung_jawab' => 'Tj. Jawab',
                                                                    'kerajinan_kerja' => 'Kerajinan',
                                                                    'prestasi' => 'Prestasi',
                                                                    'loyalitas' => 'Loyalitas'
                                                                ];
                                                            @endphp
                                                            @foreach($allMarkers as $field => $label)
                                                                @if(isset($eval->$field))
                                                                    <div class="flex items-center justify-between group/marker">
                                                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider group-hover/marker:text-slate-600 transition-colors">{{ $label }}</span>
                                                                        <span class="px-1.5 py-0.5 bg-slate-100 rounded text-[9px] font-black text-slate-900 group-hover/marker:bg-blue-50 group-hover/marker:text-blue-700 transition-colors">{{ $eval->$field }}</span>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                        
                                                        @if($eval->Keterangan)
                                                            <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100/50">
                                                                <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1 flex items-center gap-1">
                                                                    <i class='bx bx-comment-detail'></i> Evaluation Note
                                                                </p>
                                                                <p class="text-[10px] font-medium text-slate-600 italic leading-relaxed">
                                                                    "{{ $eval->Keterangan }}"
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                            </div>
                                        @empty
                                            <div class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-slate-100 rounded-[2rem] bg-slate-50/30">
                                                <i class='bx bx-file-blank text-3xl text-slate-200 mb-2'></i>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No evaluation records found</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </section>

                                {{-- Disciplinary Desk --}}
                                <section class="space-y-4">
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <i class='bx bx-shield-x text-rose-500'></i> Disciplinary Desk
                                    </h4>
                                    <div class="space-y-4">
                                        @forelse($selectedEmployee->warningLogs->sortByDesc('Date')->take(3) as $log)
                                            <div class="relative pl-8 pb-6 border-l-2 border-rose-100 last:pb-0">
                                                <div class="absolute -left-[9px] top-0 h-4 w-4 rounded-full bg-white border-4 border-rose-500"></div>
                                                <div class="p-4 bg-rose-50/30 rounded-2xl border border-rose-100/50">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <p class="text-[11px] font-black text-rose-900 tabular-nums">{{ $log->Date }}</p>
                                                        <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[8px] font-black uppercase rounded">Warning Issued</span>
                                                    </div>
                                                    <p class="text-xs font-medium text-rose-800 leading-relaxed italic pr-4">
                                                        "{{ $log->Violation }}"
                                                    </p>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="p-6 bg-emerald-50/30 border border-emerald-100 rounded-[2rem] flex items-center gap-4">
                                                <div class="h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                                    <i class='bx bx-check-shield text-xl'></i>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-black text-emerald-900 uppercase">Impeccable Performance</p>
                                                    <p class="text-[10px] font-medium text-emerald-600 italic">No violation logs recorded in master data.</p>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </section>

                                {{-- Attendance Context --}}
                                <section class="space-y-4">
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <i class='bx bx-map-pin text-amber-500'></i> Attendance Context
                                    </h4>
                                    @if($selectedEmployee->latestDailyReport)
                                        <div class="bg-slate-900 rounded-[2rem] p-6 shadow-xl relative overflow-hidden group">
                                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:rotate-12 transition-transform">
                                                <i class='bx bx-current-location text-6xl text-white'></i>
                                            </div>
                                            <div class="relative z-10">
                                                <div class="flex items-center justify-between mb-4">
                                                    <span class="text-[10px] font-black text-white/40 uppercase tracking-widest">Master Daily Report</span>
                                                    <span class="text-[10px] font-black text-blue-400 tabular-nums">{{ $selectedEmployee->latestDailyReport->sort_datetime->format('d M, H:i') }}</span>
                                                </div>
                                                <p class="text-xs font-medium text-slate-300 leading-relaxed italic border-l-2 border-blue-500/30 pl-4 py-1">
                                                    "{{ Str::limit($selectedEmployee->latestDailyReport->report_content, 180) }}"
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-slate-100 rounded-[2rem] bg-slate-50/30">
                                            <i class='bx bx-map-alt text-3xl text-slate-200 mb-2'></i>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No recent geospatial reports</p>
                                        </div>
                                    @endif
                                </section>
                            </div>

                            <div class="px-8 py-8 border-t border-slate-100 bg-slate-50/80 backdrop-blur-md flex gap-4">
                                <button 
                                    class="flex-1 flex items-center justify-center gap-2 px-6 py-4 bg-white border border-slate-200 text-slate-900 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm"
                                >
                                    Full Profile <i class='bx bx-link-external'></i>
                                </button>
                                <button 
                                    @click="$wire.closeAudit()"
                                    class="px-8 py-4 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-2xl shadow-slate-200 hover:shadow-slate-300 hover:-translate-y-0.5 active:translate-y-0 transition-all"
                                >
                                    Done
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
