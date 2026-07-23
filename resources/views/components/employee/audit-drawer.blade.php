@props(['selectedEmployee'])

<template x-teleport="body">
    <div x-data="{ show: false }" x-show="show" x-init="$watch('$wire.selectedNik', value => show = !!value)" class="fixed inset-0 z-[150] overflow-hidden"
        style="display: none;">
        <div class="absolute inset-0 overflow-hidden">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" x-show="show"
                x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="$wire.closeAudit()">
            </div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div class="pointer-events-auto w-screen max-w-lg transform transition ease-in-out duration-500 sm:duration-700"
                    x-show="show" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                    x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                    <div class="flex h-full flex-col overflow-y-auto bg-white shadow-2xl border-l border-slate-200">
                        @if ($selectedEmployee)
                            <div class="px-8 py-10 border-b border-slate-100 bg-slate-50/50">
                                <div class="flex items-center justify-between mb-8">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-8 w-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-200">
                                            <x-bx-fingerprint class="w-5 h-5" />
                                        </div>
                                        <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Employee 
                                            Audit Desk</h2>
                                    </div>
                                    <button @click="$wire.closeAudit()"
                                        class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-white hover:shadow-sm text-slate-400 hover:text-slate-900 transition-all">
                                        <x-bx-x class="w-6 h-6" />
                                    </button>
                                </div>

                                <div class="flex items-center gap-6">
                                    <div
                                        class="h-20 w-20 rounded-[2.5rem] bg-slate-900 flex items-center justify-center text-white text-2xl font-black shadow-2xl relative overflow-hidden group">
                                        <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/20 to-transparent">
                                        </div>
                                        {{ substr($selectedEmployee->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                                            {{ $selectedEmployee->name }}</h3>
                                        <div class="flex items-center gap-3 mt-1.5">
                                            <p
                                                class="text-[11px] font-black text-blue-600 tabular-nums uppercase tracking-widest bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                                                {{ $selectedEmployee->nik }}</p>
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 text-[10px] font-bold text-slate-600 uppercase tracking-wider">
                                                {{ $selectedEmployee->employment_type }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex-1 px-8 py-8 space-y-8 custom-scrollbar overflow-x-hidden focus:outline-none">
                                {{-- Performance History --}}
                                <section class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                            <x-bx-trending-up class="text-blue-500" /> Performance & Metrics
                                        </h4>
                                        <span class="text-[10px] font-bold text-slate-300 uppercase">Recent
                                            Cycles</span>
                                    </div>

                                    <div class="space-y-3">
                                        @forelse($selectedEmployee->evaluationData->sortByDesc('Month')->take(3) as $eval)
                                            <div class="bg-white rounded-[1.5rem] border border-slate-100 shadow-sm hover:shadow-md transition-all overflow-hidden group p-3"
                                                x-data="{ expanded: false }">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div class="pl-1">
                                                            <p
                                                                class="text-[11px] font-black text-slate-900 uppercase tracking-tight">
                                                                {{ $eval->Month->format('F Y') }}</p>
                                                            <p
                                                                class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">
                                                                {{ $eval->evaluation_type ?? 'Staff' }} Evaluation</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-right">
                                                            <p class="text-base font-black text-slate-900 leading-none">
                                                                {{ $eval->total }}</p>
                                                            <p
                                                                class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-0.5">
                                                                Points</p>
                                                        </div>
                                                        <button @click="expanded = !expanded"
                                                            class="h-7 w-7 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all">
                                                            <x-bx-chevron-up class="w-5 h-5 transition-transform duration-300" x-show="expanded" x-cloak />
                                                            <x-bx-chevron-down class="w-5 h-5 transition-transform duration-300" x-show="!expanded" x-cloak />
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Attendance Mini-Bar (High-Readability Labels) --}}
                                                <div
                                                    class="flex items-center gap-1.5 p-1 bg-slate-50/80 rounded-xl border border-slate-100">
                                                    @foreach ([
                                                        'Alpha' => ['rose', 'Absent'],
                                                        'Telat' => ['amber', 'Late'],
                                                        'Izin' => ['blue', 'Permit'],
                                                        'Sakit' => ['slate', 'Sick'],
                                                    ] as $key => $meta)
                                                        <div
                                                            class="flex-1 flex flex-col items-center py-1.5 border-r border-slate-200/50 last:border-none">
                                                            <span
                                                                class="text-[10px] font-black {{ ($eval->$key ?? 0) > 0 ? 'text-' . $meta[0] . '-600' : 'text-slate-300' }}">
                                                                {{ $eval->$key ?? 0 }}
                                                            </span>
                                                            <span
                                                                class="text-[7px] font-black text-slate-400 uppercase tracking-tighter">{{ $meta[1] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                {{-- Expanded Full Scorecard --}}
                                                <div x-show="expanded" x-collapse
                                                    class="pt-2 border-t border-slate-50 space-y-4">
                                                    <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                                                        @php
                                                            $allMarkers = [
                                                                'kemampuan_kerja' => 'Kemampuan',
                                                                'disiplin_kerja' => 'Disiplin',
                                                                'integritas' => 'Integritas',
                                                                'tanggung_jawab' => 'Tj. Jawab',
                                                                'kerajinan_kerja' => 'Kerajinan',
                                                                'prestasi' => 'Prestasi',
                                                                'loyalitas' => 'Loyalitas',
                                                            ];
                                                        @endphp
                                                        @foreach ($allMarkers as $field => $label)
                                                            @if (isset($eval->$field))
                                                                <div
                                                                    class="flex items-center justify-between group/marker">
                                                                    <span
                                                                        class="text-[9px] font-bold text-slate-400 uppercase tracking-wider group-hover/marker:text-slate-600 transition-colors">{{ $label }}</span>
                                                                    <span
                                                                        class="px-1.5 py-0.5 bg-slate-100 rounded text-[9px] font-black text-slate-900 group-hover/marker:bg-blue-50 group-hover/marker:text-blue-700 transition-colors">{{ $eval->$field }}</span>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>

                                                    @if ($eval->Keterangan)
                                                        <div
                                                            class="p-3 bg-blue-50/50 rounded-xl border border-blue-100/50">
                                                            <p
                                                                class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1 flex items-center gap-1">
                                                                <x-bx-comment-detail class="" /> Evaluation Note
                                                            </p>
                                                            <p
                                                                class="text-[10px] font-medium text-slate-600 italic leading-relaxed">
                                                                "{{ $eval->Keterangan }}"
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div
                                                class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-slate-100 rounded-[2rem] bg-slate-50/30">
                                                <x-bx-file-blank class="w-8 h-8 text-slate-200 mb-2" />
                                                <p
                                                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                    No evaluation records found</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </section>

                                {{-- Disciplinary Desk --}}
                                <section class="space-y-4">
                                    <h4
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                        <x-bx-shield-x class="text-rose-500" /> Disciplinary Desk
                                    </h4>
                                    <div class="space-y-4">
                                        @forelse($selectedEmployee->warningLogs->sortByDesc('Date')->take(3) as $log)
                                            <div class="relative pl-8 pb-6 border-l-2 border-rose-100 last:pb-0">
                                                <div
                                                    class="absolute -left-[9px] top-0 h-4 w-4 rounded-full bg-white border-4 border-rose-500">
                                                </div>
                                                <div class="p-4 bg-rose-50/30 rounded-2xl border border-rose-100/50">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <p class="text-[11px] font-black text-rose-900 tabular-nums">
                                                            {{ $log->Date }}</p>
                                                        <span
                                                            class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[8px] font-black uppercase rounded">Warning
                                                            Issued</span>
                                                    </div>
                                                    <p
                                                        class="text-xs font-medium text-rose-800 leading-relaxed italic pr-4">
                                                        "{{ $log->Violation }}"
                                                    </p>
                                                </div>
                                            </div>
                                        @empty
                                            <div
                                                class="p-6 bg-emerald-50/30 border border-emerald-100 rounded-[2rem] flex items-center gap-4">
                                                <div
                                                    class="h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                                    <x-bx-check-shield class="w-5 h-5" />
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-black text-emerald-900 uppercase">
                                                        Impeccable Performance</p>
                                                    <p class="text-[10px] font-medium text-emerald-600 italic">No
                                                        violation logs recorded in master data.</p>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </section>

                                {{-- Attendance Context --}}
                                <section class="space-y-4" 
                                    x-data="{
                                        filter: '30d',
                                        records: @js($selectedEmployee->attendanceRecords ?? []),
                                        get filteredRecords() {
                                            const now = new Date();
                                            return this.records.filter(r => {
                                                const shiftDate = new Date(r.shift_date);
                                                if (this.filter === 'mtd') {
                                                    return shiftDate.getMonth() === now.getMonth() && shiftDate.getFullYear() === now.getFullYear();
                                                } else if (this.filter === '30d') {
                                                    const diffDays = (now - shiftDate) / (1000 * 60 * 60 * 24);
                                                    return diffDays <= 30 && diffDays >= 0;
                                                }
                                                return true;
                                            });
                                        },
                                        get summary() {
                                            let s = { alpha: 0, telat: 0, izin: 0, sakit: 0 };
                                            this.filteredRecords.forEach(r => {
                                                s.alpha += r.alpha || 0;
                                                s.telat += r.telat || 0;
                                                s.izin += r.izin || 0;
                                                s.sakit += r.sakit || 0;
                                            });
                                            return s;
                                        },
                                        get infractions() {
                                            return this.filteredRecords.filter(r => r.alpha > 0 || r.telat > 0 || r.izin > 0 || r.sakit > 0).slice(0, 5);
                                        }
                                    }">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                            <x-bx-calendar-check class="text-emerald-500" /> Attendance Context
                                        </h4>
                                        <div class="flex items-center bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                                            <button @click="filter = 'mtd'" :class="filter === 'mtd' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'" class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest transition-all">MTD</button>
                                            <button @click="filter = '30d'" :class="filter === '30d' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'" class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest transition-all">30 Days</button>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-slate-900 rounded-[2rem] p-6 shadow-xl">
                                        {{-- Metric KPI Row --}}
                                        <div class="grid grid-cols-4 gap-2 mb-6">
                                            <template x-for="[key, label, color] in [['alpha', 'Absent', 'rose'], ['telat', 'Late', 'amber'], ['izin', 'Permit', 'blue'], ['sakit', 'Sick', 'slate']]">
                                                <div class="flex flex-col items-center p-3 rounded-2xl bg-white/5 border border-white/10">
                                                    <span class="text-xl font-black tabular-nums leading-none"
                                                        :class="summary[key] > 0 ? 'text-' + color + '-400' : 'text-white/20'"
                                                        x-text="summary[key]"></span>
                                                    <span class="text-[8px] font-bold text-white/50 uppercase tracking-widest mt-1" x-text="label"></span>
                                                </div>
                                            </template>
                                        </div>

                                        {{-- Timeline / Status --}}
                                        <div class="relative">
                                            <template x-if="infractions.length === 0">
                                                <div class="flex items-center justify-center py-4 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                                                    <div class="flex items-center gap-2 text-emerald-400">
                                                        <x-bx-check-shield class="w-5 h-5" />
                                                        <span class="text-xs font-bold tracking-widest uppercase">Perfect Attendance</span>
                                                    </div>
                                                </div>
                                            </template>

                                            <template x-if="infractions.length > 0">
                                                <div class="space-y-3">
                                                    <p class="text-[9px] font-black text-white/40 uppercase tracking-widest mb-3">Recent Exceptions</p>
                                                    <template x-for="r in infractions" :key="r.id">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></div>
                                                            <div class="flex-1 flex justify-between items-center pb-2 border-b border-white/5">
                                                                <span class="text-xs font-bold text-slate-300" x-text="new Date(r.shift_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })"></span>
                                                                <div class="flex items-center gap-2">
                                                                    <template x-if="r.alpha > 0"><span class="px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-400 text-[9px] font-bold uppercase tracking-widest">Abs: <span x-text="r.alpha"></span></span></template>
                                                                    <template x-if="r.telat > 0"><span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-400 text-[9px] font-bold uppercase tracking-widest">Late: <span x-text="r.telat"></span></span></template>
                                                                    <template x-if="r.izin > 0"><span class="px-2 py-0.5 rounded-md bg-blue-500/20 text-blue-400 text-[9px] font-bold uppercase tracking-widest">Pmt: <span x-text="r.izin"></span></span></template>
                                                                    <template x-if="r.sakit > 0"><span class="px-2 py-0.5 rounded-md bg-slate-500/20 text-slate-400 text-[9px] font-bold uppercase tracking-widest">Sck: <span x-text="r.sakit"></span></span></template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div
                                class="px-8 py-8 border-t border-slate-100 bg-slate-50/80 backdrop-blur-md flex gap-4">
                                <button
                                    class="flex-1 flex items-center justify-center gap-2 px-6 py-4 bg-white border border-slate-200 text-slate-900 rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                                    Full Profile <x-bx-link-external class="" />
                                </button>
                                <button @click="$wire.closeAudit()"
                                    class="px-8 py-4 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-2xl shadow-slate-200 hover:shadow-slate-300 hover:-translate-y-0.5 active:translate-y-0 transition-all">
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
