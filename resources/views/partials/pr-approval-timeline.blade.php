{{-- 
    Purchase Request Approval Timeline
    Shows visual progress through the approval workflow
    
    Props:
    - $pr: PurchaseRequest model with approvalRequest relationship loaded
--}}

@php
    $approval = $pr->approvalRequest;
    $steps = $approval?->steps->sortBy('sequence') ?? collect();
    $currentStep = $approval?->current_step ?? 0;
    $totalSteps = $steps->count();
@endphp

@if($approval && $totalSteps > 0)
    <div class="relative px-4 py-6">
        {{-- Progress Bar Background --}}
        <div class="absolute left-8 right-8 top-10 h-1 bg-slate-200 rounded"></div>
        
        {{-- Progress Bar Fill --}}
        @php
            $progressPercent = $totalSteps > 0 ? (($currentStep / $totalSteps) * 100) : 0;
        @endphp
        <div class="absolute left-8 right-8 top-10 h-1 bg-indigo-500 rounded transition-all duration-500"
             style="width: {{ $progressPercent }}%"></div>
        
        {{-- Steps --}}
        <div class="relative flex justify-between">
            @foreach($steps as $step)
                @php
                    $isCompleted = $step->sequence < $currentStep;
                    $isCurrent = $step->sequence == $currentStep;
                    $isPending = $step->sequence > $currentStep;
                    
                    $circleClasses = match(true) {
                        $isCompleted => 'bg-emerald-500 border-emerald-500 text-white',
                        $isCurrent => 'bg-white border-indigo-500 text-indigo-600',
                        default => 'bg-white border-slate-300 text-slate-400',
                    };
                    
                    $labelClasses = match(true) {
                        $isCurrent => 'text-indigo-600 font-semibold',
                        $isCompleted => 'text-emerald-600',
                        default => 'text-slate-500',
                    };
                @endphp
                
                <div class="flex flex-col items-center" style="flex: 1;">
                    {{-- Circle --}}
                    <div class="relative z-10 flex h-12 w-12 items-center justify-center rounded-full border-4 {{ $circleClasses }} shadow-sm">
                        @if($isCompleted)
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <span class="text-sm font-bold">{{ $step->sequence }}</span>
                        @endif
                    </div>
                    
                    {{-- Label --}}
                    <div class="mt-3 text-center max-w-[120px]">
                        <p class="text-xs {{ $labelClasses }} truncate" title="{{ $step->approver_snapshot_label ?? $step->approver?->name ?? 'Unknown' }}">
                            {{ $step->approver_snapshot_label ?? $step->approver?->name ?? 'Unknown' }}
                        </p>
                        
                        @if($step->acted_at)
                            <p class="text-[10px] text-slate-400 mt-1">
                                {{ \Carbon\Carbon::parse($step->acted_at)->format('d M, H:i') }}
                            </p>
                        @elseif($isCurrent)
                            <p class="text-[10px] text-indigo-500 mt-1 font-medium">
                                Pending
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- Status Summary --}}
        <div class="mt-6 text-center">
            <p class="text-xs text-slate-500">
                @if($approval->status == 'APPROVED')
                    <span class="text-emerald-600 font-semibold">✓ Fully Approved</span>
                @elseif($approval->status == 'REJECTED')
                    <span class="text-rose-600 font-semibold">✗ Rejected</span>
                @else
                    Step <span class="font-semibold">{{ $currentStep }}</span> of <span class="font-semibold">{{ $totalSteps }}</span>
                @endif
            </p>
        </div>
    </div>
@else
    {{-- No workflow available --}}
    <div class="px-4 py-6 text-center">
        <p class="text-sm text-slate-400">
            No approval workflow initiated yet.
        </p>
    </div>
@endif
