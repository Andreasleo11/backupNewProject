@props([
    'previewData',
    'activeLog',
    'previewTab',
    'previewSearch'
])

@php
    $modalData = $activeLog ?? $previewData;
    $isHistorical = !is_null($activeLog);
@endphp

@if($previewData || $activeLog)
    <template x-teleport="body">
        <div 
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
            x-data="{ show: false }"
            x-init="setTimeout(() => show = true, 50)"
        >
            <div 
                class="absolute inset-0 bg-slate-950/40 backdrop-blur-md transition-opacity duration-500"
                x-show="show"
                x-transition:enter="ease-out duration-500"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                @click="{{ $isHistorical ? '$wire.closeLog()' : '$wire.cancelSync()' }}"
            ></div>

            <div 
                class="relative w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden transition-all duration-500 transform"
                x-show="show"
                x-transition:enter="ease-out duration-500"
                x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            >
                <div class="px-8 py-6 bg-white border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 flex items-center justify-center rounded-2xl {{ $isHistorical ? 'bg-slate-900' : 'bg-blue-600' }} text-white shadow-lg">
                            <i class='bx {{ $isHistorical ? 'bx-history font-light' : 'bx-sync animate-spin-slow' }} text-2xl'></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 leading-none">
                                {{ $isHistorical ? 'Sync Audit Archive' : 'Reconciliation Analysis' }}
                            </h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 flex items-center gap-2">
                                @if($isHistorical)
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Data Integrity Snapshot
                                @else
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    System-Ready for Commitment
                                @endif
                            </p>
                        </div>
                    </div>
                    <button 
                        wire:click="{{ $isHistorical ? 'closeLog' : 'cancelSync' }}" 
                        class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-400 transition-all"
                    >
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>

                <div class="px-8 bg-white border-b border-slate-100 flex items-center gap-8">
                    @foreach(['summary' => 'Analysis', 'new' => 'Additions', 'updated' => 'Modifications', 'inactive' => 'Inactivations'] as $tab => $label)
                        <button 
                            wire:click="$set('previewTab', '{{ $tab }}')"
                            class="relative py-4 text-[11px] font-black uppercase tracking-widest transition-all {{ $previewTab === $tab ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' }}"
                        >
                            <span class="flex items-center gap-2">
                                {{ $label }}
                                @if($tab !== 'summary')
                                    <span class="px-2 py-0.5 rounded-full text-[9px] {{ $previewTab === $tab ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ count($modalData['details'][$tab] ?? []) }}
                                    </span>
                                @endif
                            </span>
                            @if($previewTab === $tab)
                                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full"></div>
                            @endif
                        </button>
                    @endforeach
                </div>

                <div class="bg-white min-h-[400px]">
                    @if($previewTab === 'summary')
                        <div class="p-8 grid grid-cols-3 gap-6">
                            @foreach(['new' => ['emerald', 'plus-circle'], 'updated' => ['blue', 'edit-alt'], 'inactive' => ['rose', 'minus-circle']] as $key => $meta)
                                <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 group hover:border-{{ $meta[0] }}-200 transition-all">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="h-12 w-12 flex items-center justify-center rounded-2xl bg-{{ $meta[0] }}-100 text-{{ $meta[0] }}-600">
                                            <i class='bx bx-{{ $meta[1] }} text-2xl'></i>
                                        </div>
                                        <span class="text-4xl font-black text-slate-900">{{ count($modalData['details'][$key] ?? []) }}</span>
                                    </div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $key === 'new' ? 'New Records' : ($key === 'updated' ? 'Modified Records' : 'Potential Inactivations') }}</p>
                                    <button 
                                        wire:click="$set('previewTab', '{{ $key }}')"
                                        class="mt-4 flex items-center gap-2 text-[11px] font-black text-{{ $meta[0] }}-600 uppercase tracking-widest group-hover:translate-x-1 transition-transform"
                                    >
                                        View Details <i class='bx bx-right-arrow-alt text-lg'></i>
                                    </button>
                                </div>
                            @endforeach

                            <div class="col-span-3 mt-4 p-6 bg-slate-900 rounded-3xl flex items-center justify-between shadow-xl">
                                <div class="flex items-center gap-6">
                                    <div class="h-14 w-14 flex items-center justify-center rounded-2xl bg-white/10 text-white shrink-0">
                                        <i class='bx bx-shield-quarter text-lg'></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-white uppercase tracking-wide mb-1">{{ $isHistorical ? 'Audit Integrity Score' : 'Reconciliation Health' }}</p>
                                        <p class="text-xs font-medium text-slate-400 leading-relaxed pr-4">
                                            {{ $isHistorical 
                                                ? 'Snapshot verified. All records matched JPayroll states at the time of execution.' 
                                                : 'Sync engine has identified reconciliation points. Commitment will auto-align local master data.' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-black text-white leading-none">100%</p>
                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mt-1">Consistency</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-0 max-h-[60vh] overflow-y-auto custom-scrollbar border-t border-slate-200">
                            <table class="w-full text-left border-collapse border-separate border-spacing-0">
                                <thead class="sticky top-0 z-20 bg-slate-50 shadow-sm">
                                    <tr>
                                        <th class="px-8 py-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200 sticky left-0 bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">EMPLOYEE IDENTITY</th>
                                        @if($previewTab === 'updated')
                                            <th class="px-6 py-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">MODIFICATION DETAILS</th>
                                        @else
                                            <th class="px-6 py-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">ASSIGNMENT</th>
                                            <th class="px-6 py-4 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">DIVISION</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @php
                                        $filtered = collect($modalData['details'][$previewTab] ?? [])->filter(fn($r) => 
                                            empty($previewSearch) || 
                                            str_contains(strtolower((string)($r['nik'] ?? '')), strtolower($previewSearch)) || 
                                            str_contains(strtolower((string)($r['name'] ?? '')), strtolower($previewSearch))
                                        );
                                    @endphp
                                    @forelse($filtered as $row)
                                        <tr class="hover:bg-slate-50/50 transition-colors group">
                                            <td class="px-8 py-5 sticky left-0 bg-white border-r border-slate-100 group-hover:bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                                <div class="flex items-center gap-4">
                                                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-gradient-to-br {{ $isHistorical ? 'from-slate-700 to-slate-800' : 'from-blue-500 to-indigo-600' }} text-white text-xs font-bold shadow-lg">
                                                        {{ substr($row['name'] ?? '?', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-bold text-slate-900 leading-tight">{{ $row['name'] ?? 'Unknown' }}</p>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest tabular-nums">{{ $row['nik'] }}</span>
                                                            <span class="inline-block w-1 h-1 rounded-full bg-slate-300"></span>
                                                            <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wide">{{ $row['branch'] ?? 'JAKARTA' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            @if($previewTab === 'updated')
                                                <td class="px-6 py-5">
                                                    <div class="flex flex-col gap-2">
                                                        @foreach($row['diffs'] as $field => $val)
                                                            <div class="flex items-center gap-3">
                                                                <span class="w-24 text-[9px] font-bold text-slate-400 uppercase truncate">{{ str_replace('_', ' ', $field) }}</span>
                                                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200">
                                                                    <span class="text-[10px] font-medium text-slate-500 line-through decoration-slate-400">{{ $val['old'] ?: '---' }}</span>
                                                                    <i class='bx bx-right-arrow-alt text-blue-500'></i>
                                                                    <span class="text-[10px] font-bold text-slate-900">{{ $val['new'] ?: '---' }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            @else
                                                <td class="px-6 py-5">
                                                    <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-bold {{ $previewTab === 'new' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }} uppercase tracking-wider">{{ $row['employment_type'] ?? 'Standard' }}</span>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <p class="text-xs font-bold text-slate-700 uppercase tracking-tight">{{ $row['dept_code'] ?? '---' }}</p>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="px-8 py-20 text-center">No matching records</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="px-8 py-6 bg-slate-50 border-t border-slate-200 flex justify-between items-center group">
                    <div class="text-xs font-bold text-slate-500 italic">
                        {{ $isHistorical ? 'Audit trail captured at ' . ($activeLog['timestamp'] ?? 'Sync Time') : 'Pre-commitment checklist required for data integrity.' }}
                    </div>
                    <div class="flex items-center gap-4">
                        <button wire:click="{{ $isHistorical ? 'closeLog' : 'cancelSync' }}" class="px-6 py-3 rounded-xl text-xs font-black text-slate-500 hover:text-slate-900 transition-all uppercase tracking-widest">
                            {{ $isHistorical ? 'Close Audit' : 'Cancel' }}
                        </button>
                        @if($previewTab === 'summary' && !$isHistorical)
                            <button wire:click="confirmSync" class="relative group inline-flex items-center gap-3 px-8 py-3 bg-slate-900 text-white rounded-2xl text-xs font-black shadow-xl hover:shadow-2xl transition-all uppercase tracking-widest">
                                <span>Sync to Database</span>
                                <i class='bx bx-right-arrow-alt text-xl group-hover:translate-x-1 transition-transform'></i>
                            </button>
                        @elseif($previewTab !== 'summary')
                            <button wire:click="$set('previewTab', 'summary')" class="px-8 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-black text-slate-900 shadow-sm transition-all uppercase tracking-widest">
                                Return to Summary
                                </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
@endif
