{{-- 
    Purchase Request Approval Timeline (Vertical)
    Shows visual progress through the approval workflow
    
    Props:
    - $record: Model with approvalRequest relationship loaded
--}}

@php
    $approval = $record->approvalRequest;
    $steps = $approval?->steps->sortBy('sequence') ?? collect();
    $currentStep = $approval?->current_step ?? 0;
    $totalSteps = $steps->count();
@endphp

@if($approval && $totalSteps > 0)
    <div class="relative px-2 py-2">
        
        <div class="space-y-0">
            @foreach($steps as $step)
                @php
                    $isCompleted = $step->sequence < $currentStep;
                    $isCurrent = $step->sequence == $currentStep;
                    $isPending = $step->sequence > $currentStep;
                    $isLast = $loop->last;
                    
                    $userLabel = $step->approver_name;
                    $roleLabel = $step->approver_label;
                    
                    // Colors
                    $dotColor = match(true) {
                        $isCompleted => 'bg-emerald-500 ring-emerald-100',
                        $isCurrent && $approval->status == 'RETURNED' => 'bg-orange-500 ring-orange-100', // Returned
                        $step->status == 'CANCELED' => 'bg-rose-100 ring-rose-50 border-2 border-rose-500', // Canceled step
                        $isCurrent && $approval->status == 'CANCELED' => 'bg-rose-500 ring-rose-100', // Current but canceled
                        $isCurrent => 'bg-white border-2 border-indigo-600 ring-indigo-50',
                        $approval->status == 'REJECTED' && $isCurrent => 'bg-rose-500 ring-rose-100', // If rejected at this step
                        default => 'bg-slate-200 ring-slate-50',
                    };
                    
                    $icon = match(true) {
                        $isCompleted => '<i class="bi bi-check text-white text-xs"></i>',
                        $isCurrent && $approval->status == 'RETURNED' => '<i class="bi bi-arrow-return-left text-white text-xs"></i>',
                        $step->status == 'CANCELED' || ($isCurrent && $approval->status == 'CANCELED') => '<i class="bi bi-x text-rose-600 text-xs"></i>',
                        $isCurrent => '<div class="h-2 w-2 rounded-full bg-indigo-600"></div>',
                        default => '',
                    };
                @endphp
                
                <div class="relative flex gap-4 pb-8 {{ $isLast ? 'pb-0' : '' }}">
                    {{-- Connecting Line --}}
                    @if(!$isLast)
                        <div class="absolute left-3.5 top-8 h-full w-0.5 bg-slate-100 -ml-px"></div>
                    @endif
                    
                    {{-- Dot/Icon --}}
                    <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full ring-4 {{ $dotColor }} transition-all">
                        {!! $icon !!}
                    </div>
                    
                    {{-- Content --}}
                    <div class="pt-0.5 w-full">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold {{ $isCurrent ? 'text-indigo-700' : 'text-slate-700' }}">
                                {{ $userLabel }}
                            </span>
                            <span class="text-[10px] uppercase tracking-wide text-slate-500">
                                {{ $roleLabel }}
                            </span>
                            
                            @if($step->acted_at)
                                <span class="mt-1 text-[10px] text-slate-400">
                                    {{ \Carbon\Carbon::parse($step->acted_at)->format('d M Y, H:i') }}
                                </span>
                            @elseif($isCurrent)
                                @if($approval->status == 'RETURNED')
                                    <span class="mt-1 text-[10px] font-bold text-orange-600">
                                        Returned for Revision
                                    </span>
                                @else
                                    <div class="mt-2 flex items-center gap-1.5">
                                        <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                        </span>
                                        <span class="text-[10px] font-medium text-indigo-500">Awaiting Action</span>
                                    </div>
                                @endif
                            @endif
                            
                            {{-- Specific comments if any --}}
                            @if($step->remarks)
                                <div class="mt-2 rounded-lg bg-slate-50 p-2 text-[10px] italic text-slate-600 border border-slate-100">
                                    "{{ $step->remarks }}"
                                </div>
                            @endif

                            {{-- Return Reason --}}
                            @if($step->return_reason)
                                <div class="mt-2 rounded-lg bg-orange-50 p-2 text-[10px] text-orange-800 border border-orange-100">
                                    <span class="font-bold">Reason:</span> "{{ $step->return_reason }}"
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- Final Status --}}
        @if($approval->status == 'APPROVED')
             <div class="relative flex gap-4 mt-8">
                 <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-600 ring-4 ring-emerald-100">
                    <i class="bi bi-check-all text-white text-xs"></i>
                </div>
                <div class="pt-1">
                    <p class="text-xs font-bold text-emerald-700">Fully Approved</p>
                </div>
             </div>
        @elseif($approval->status == 'REJECTED')
             <div class="relative flex gap-4 mt-8">
                 <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-600 ring-4 ring-rose-100">
                    <i class="bi bi-x-lg text-white text-xs"></i>
                </div>
                <div class="pt-1">
                    <p class="text-xs font-bold text-rose-700">Request Rejected</p>
                </div>
             </div>
        @elseif($approval->status == 'RETURNED')
             <div class="relative flex gap-4 mt-8">
                 <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-orange-500 ring-4 ring-orange-100">
                    <i class="bi bi-arrow-return-left text-white text-xs"></i>
                </div>
                <div class="pt-1">
                    <p class="text-xs font-bold text-orange-700">Returned for Revision</p>
                    <p class="text-[10px] text-slate-500">Please check comments and resubmit.</p>
                </div>
             </div>
        @elseif($approval->status == 'CANCELED')
             <div class="relative flex gap-4 mt-8">
                 <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-600 ring-4 ring-rose-100">
                    <i class="bi bi-x-circle text-white text-xs"></i>
                </div>
                <div class="pt-1">
                    <p class="text-xs font-bold text-rose-700">Workflow Canceled</p>
                    <p class="text-[10px] text-slate-500">This report is permanently terminated.</p>
                </div>
             </div>
        @endif

    </div>
@else
    <div class="py-4 text-center">
        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
            <i class="bi bi-hourglass text-lg"></i>
        </div>
        <p class="text-xs text-slate-500">Workflow not started</p>
    </div>
@endif
