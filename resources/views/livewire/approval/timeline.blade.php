<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 h-full">
    <div class="flex justify-between items-center pb-4 mb-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-800 flex items-center gap-1.5">
            <i class="bi bi-clock-history text-slate-500"></i> Approval Workflow
        </h3>
        @if ($request)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200 uppercase">
                {{ $request->status }}
            </span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-150/60 text-slate-600 border border-slate-200 uppercase">
                DRAFT
            </span>
        @endif
    </div>

    @if (!$request)
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <div class="text-slate-300 mb-2">
                <i class="bi bi-file-earmark-lock text-3xl"></i>
            </div>
            <p class="text-xs text-slate-500 font-medium">No active approval request has been initiated yet.</p>
        </div>
    @else
        {{-- Steps Stepper --}}
        <div class="mb-6">
            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Approval Steps</h4>
            <div class="relative pl-8 space-y-6">
                <!-- Vertical Line -->
                <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-100"></div>

                @foreach ($request->steps as $s)
                    @php
                        $isApproved = $s->status === 'APPROVED';
                        $isRejected = $s->status === 'REJECTED';
                        $isSkipped = $s->status === 'SKIPPED';
                        $isPending = $s->status === 'PENDING';
                    @endphp
                    <div class="relative flex items-start gap-4">
                        <!-- Step Circle Icon -->
                        <div class="absolute -left-[30px] flex items-center justify-center">
                            @if ($isApproved)
                                <span class="flex h-[24px] w-[24px] items-center justify-center rounded-full bg-emerald-500 text-white ring-4 ring-emerald-50">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            @elseif ($isRejected)
                                <span class="flex h-[24px] w-[24px] items-center justify-center rounded-full bg-rose-500 text-white ring-4 ring-rose-50">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </span>
                            @elseif ($isSkipped)
                                <span class="flex h-[24px] w-[24px] items-center justify-center rounded-full bg-amber-500 text-white ring-4 ring-amber-50">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                    </svg>
                                </span>
                            @else
                                <span class="flex h-[24px] w-[24px] items-center justify-center rounded-full border-2 border-slate-300 bg-white text-slate-400 font-bold text-[10px]">
                                    {{ $s->sequence }}
                                </span>
                            @endif
                        </div>

                        <!-- Step Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-slate-800">
                                    @if ($s->approver_type === 'user')
                                        User #{{ $s->approver_id }}
                                    @else
                                        Role #{{ $s->approver_id }}
                                    @endif
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{
                                    $isApproved ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : (
                                    $isRejected ? 'bg-rose-50 text-rose-700 border border-rose-100' : (
                                    $isSkipped ? 'bg-amber-50 text-amber-700 border border-amber-100' :
                                    'bg-slate-50 text-slate-600 border border-slate-200'
                                    ))
                                }}">
                                    {{ $s->status }}
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">Sequence {{ $s->sequence }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- History Log --}}
        @if ($request->actions->count())
            <div class="mt-6 pt-6 border-t border-slate-100">
                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Activity Log</h4>
                <div class="space-y-4">
                    @foreach ($request->actions as $a)
                        <div class="text-xs">
                            <div class="flex items-center justify-between gap-4 text-slate-500 mb-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-slate-700">{{ $a->from_status ?? 'DRAFT' }}</span>
                                    <i class="bi bi-arrow-right text-slate-300"></i>
                                    <span class="font-bold text-slate-700">{{ $a->to_status }}</span>
                                </div>
                                <span class="text-[9px] font-medium text-slate-400 whitespace-nowrap">{{ $a->created_at->format('d M Y H:i') }}</span>
                            </div>
                            @if ($a->remarks)
                                <div class="bg-slate-50 rounded-lg p-2.5 text-slate-600 italic border border-slate-100/60 font-mono text-[10px] leading-relaxed">
                                    "{{ $a->remarks }}"
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
