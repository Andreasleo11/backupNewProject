{{-- ===== MODALS =====
     1. Decision Intelligence Snapshot Drawer (slide-over from right)
     2. Delete Confirmation Modal
     Both teleported to body. Unchanged from original.
--}}

{{-- ── 1. Snapshot Drawer ── --}}
<template x-teleport="body">
    <div x-cloak x-show="$wire.showSnapshot" class="relative z-[150]">

        <div x-show="$wire.showSnapshot" x-transition.opacity
            class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm"
            @click="$wire.showSnapshot = false">
        </div>

        <div class="fixed inset-y-0 right-0 z-[160] w-full max-w-lg md:max-w-xl flex">
            <div x-show="$wire.showSnapshot"
                x-transition:enter="transform transition ease-in-out duration-500"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-500"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-full bg-white shadow-2xl flex flex-col pointer-events-auto">

                {{-- Header --}}
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg">
                            <i class='bx bx-file-find text-2xl'></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Batch Approval Snapshot</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Decision Intelligence Summary</p>
                        </div>
                    </div>
                    <button @click="$wire.showSnapshot = false"
                        class="h-10 w-10 rounded-xl hover:bg-slate-100 transition-all flex items-center justify-center text-slate-400">
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto px-8 py-8 space-y-8">

                    {{-- Aggregates --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-5 rounded-2xl bg-indigo-50 border border-indigo-100">
                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest block mb-2">Total Volume</span>
                            <div class="flex items-end gap-2 text-indigo-900">
                                <span class="text-4xl font-black leading-none">{{ $snapshot['total_hours'] ?? 0 }}</span>
                                <span class="text-xs font-black uppercase mb-1">Total Hours</span>
                            </div>
                        </div>
                        <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-100">
                            <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest block mb-2">Unique Entities</span>
                            <div class="flex items-end gap-2 text-emerald-900">
                                <span class="text-4xl font-black leading-none">{{ $snapshot['total_employees'] ?? 0 }}</span>
                                <span class="text-xs font-black uppercase mb-1">Employees</span>
                            </div>
                        </div>
                    </div>

                    {{-- Date & Dept Context --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Temporal Span</h4>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 p-3 rounded-xl bg-slate-50 border border-slate-100">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Start</p>
                                    <p class="text-xs font-black text-slate-700">{{ $snapshot['date_range']['start'] ?? '—' }}</p>
                                </div>
                                <i class='bx bx-right-arrow-alt text-slate-300'></i>
                                <div class="flex-1 p-3 rounded-xl bg-slate-50 border border-slate-100">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">End</p>
                                    <p class="text-xs font-black text-slate-700">{{ $snapshot['date_range']['end'] ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Departments</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($snapshot['departments'] ?? [] as $name => $count)
                                    <span class="px-2 py-1 rounded-lg bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-600">
                                        {{ $name }} <span class="opacity-50">×{{ $count }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Heuristic Warnings --}}
                    @if (!empty($warnings))
                        <div class="p-6 rounded-3xl bg-rose-50 border border-rose-100 space-y-4">
                            <div class="flex items-center gap-3 text-rose-600">
                                <i class='bx bx-error-alt text-2xl'></i>
                                <h4 class="text-sm font-black uppercase tracking-tight">Risk Anomalies Detected</h4>
                            </div>
                            <div class="space-y-3">
                                @if (isset($warnings['overlaps']))
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Session Conflicts</p>
                                        @foreach ($warnings['overlaps'] as $overlap)
                                            <p class="text-xs font-medium text-rose-700 leading-relaxed">• {{ $overlap }}</p>
                                        @endforeach
                                    </div>
                                @endif
                                @if (isset($warnings['intensity']))
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Workload Intensity</p>
                                        <p class="text-xs font-medium text-rose-700">• {{ $warnings['intensity'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-8 rounded-3xl bg-emerald-50 border border-emerald-100 text-center">
                            <div class="h-12 w-12 rounded-full bg-emerald-500 text-white flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-100">
                                <i class='bx bx-check text-2xl'></i>
                            </div>
                            <h4 class="text-sm font-black text-emerald-900 uppercase">Heuristics Clear</h4>
                            <p class="text-xs text-emerald-600 mt-1">No session overlaps or threshold violations detected in this batch.</p>
                        </div>
                    @endif

                    <div class="h-px bg-slate-100"></div>
                    <p class="text-[10px] font-medium text-slate-400 text-center leading-relaxed italic">
                        By proceeding, your digital signature will be applied to all
                        <span x-text="selectedIds.length"></span> selected forms.
                    </p>
                </div>

                {{-- Footer --}}
                <div class="p-8 border-t border-slate-100 flex items-center gap-3 bg-slate-50/30">
                    <button type="button" @click="$wire.showSnapshot = false"
                        class="flex-1 h-12 rounded-2xl border border-slate-200 bg-white text-[11px] font-black text-slate-600 uppercase tracking-widest hover:bg-slate-50 transition-all">
                        Back to Index
                    </button>
                    <button type="button" wire:click="bulkApprove" wire:loading.attr="disabled"
                        class="flex-[1.5] h-12 rounded-2xl bg-emerald-600 text-[11px] font-black text-white uppercase tracking-widest shadow-xl shadow-emerald-100 hover:bg-emerald-700 hover:-translate-y-0.5 transition-all">
                        <span wire:loading.remove wire:target="bulkApprove">Confirm Batch Approval</span>
                        <span wire:loading wire:target="bulkApprove">
                            <i class='bx bx-loader-alt animate-spin'></i> Signing…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- ── 2. Delete Confirmation Modal ── --}}
<template x-teleport="body">
    <div x-cloak x-show="deleteOpen" class="relative z-[60]">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"
            x-show="deleteOpen" x-transition.opacity
            @click="deleteOpen = false">
        </div>
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-show="deleteOpen" x-transition role="dialog" aria-modal="true">
            <div class="w-full max-w-sm rounded-3xl bg-white/95 backdrop-blur-2xl shadow-2xl border border-white/80 p-6 text-center relative overflow-hidden"
                @click.stop>
                <div class="absolute -top-10 -right-10 h-28 w-28 rounded-full bg-rose-50 blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-10 -left-10 h-28 w-28 rounded-full bg-rose-50 blur-2xl pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                        <i class='bx bx-trash text-3xl'></i>
                    </div>
                    <h3 class="text-base font-black text-slate-800">Delete OT-{{ $pendingDeleteId }}?</h3>
                    <p class="mt-2 text-xs text-slate-500 leading-relaxed mb-5">
                        This will permanently remove all detail rows and approval data for this form. This action cannot be undone.
                    </p>
                    <div class="flex gap-2">
                        <button @click="deleteOpen = false"
                            class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-black text-slate-600 hover:bg-slate-50 transition-all">
                            Cancel
                        </button>
                        <button wire:click="deleteConfirmed" wire:loading.attr="disabled"
                            class="flex-1 rounded-xl bg-rose-600 py-2.5 text-xs font-black text-white shadow-md shadow-rose-500/20 hover:bg-rose-700 disabled:opacity-50 transition-all">
                            <i class='bx bx-trash'></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
