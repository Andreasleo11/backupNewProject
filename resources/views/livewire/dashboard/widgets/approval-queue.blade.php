<div class="h-full flex flex-col rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden group hover:shadow-xl hover:border-blue-100 transition-all duration-500">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-slate-800 tracking-tight">Pending Approvals</h3>
        </div>
        <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-widest">
            {{ $approvals->count() }} Items
        </span>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        @forelse ($approvals as $step)
            @php
                $request = $step->request;
                $approvable = $request->approvable;
                $type = class_basename($request->approvable_type);
                $bgGradient = match($type) {
                    'PurchaseRequest' => 'from-blue-500/10 to-transparent',
                    'OvertimeForm' => 'from-amber-500/10 to-transparent',
                    default => 'from-slate-500/10 to-transparent'
                };
                $accentColor = match($type) {
                    'PurchaseRequest' => 'text-blue-600 bg-blue-50',
                    'OvertimeForm' => 'text-amber-600 bg-amber-50',
                    default => 'text-slate-600 bg-slate-50'
                };
            @endphp
            
            <div class="relative mb-4 last:mb-0 p-4 rounded-2xl border border-slate-100 bg-white transition-all hover:bg-slate-50/50 hover:scale-[1.01] hover:shadow-md group/item overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r {{ $bgGradient }} opacity-0 group-hover/item:opacity-100 transition-opacity"></div>
                
                <div class="relative z-10 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black uppercase tracking-[0.2em] {{ $accentColor }} px-1.5 py-0.5 rounded-md inline-block mb-1 w-fit">
                                {{ preg_replace('/(?<!^)[A-Z]/', ' $0', $type) }}
                            </span>
                            <span class="font-bold text-slate-900 text-sm">
                                @if($approvable)
                                    {{ $approvable->doc_number ?? $approvable->number ?? $approvable->id }}
                                @else
                                    <span class="text-slate-400 italic">Deleted or invalid record</span>
                                @endif
                            </span>
                            <span class="text-[11px] text-slate-500 mt-0.5">
                                Submitted {{ $request->submitted_at?->diffForHumans() ?? 'recently' }} by <span class="font-bold text-slate-700">{{ $request->submittingUser?->name ?? 'User' }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ $request->url ?? '#' }}" 
                           class="flex items-center justify-center h-10 w-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        <button class="h-10 px-4 rounded-xl bg-blue-600 text-white text-[11px] font-black uppercase tracking-widest shadow-lg shadow-blue-200 hover:bg-blue-700 hover:scale-105 transition-all">
                            Sign off
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="h-full flex flex-col items-center justify-center py-10 opacity-40 grayscale">
                <div class="h-20 w-20 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">All caught up!</p>
            </div>
        @endforelse
    </div>
    
    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 mt-auto">
        <a href="#" class="text-[11px] font-black uppercase tracking-widest text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-2 group/all">
            View All Requests
            <svg class="h-3 w-3 group-hover/all:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>
    </div>
</div>
