<div>
    @if ($message)
        @php
            $classes = match ($type) {
                'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'error'   => 'border-red-200 bg-red-50 text-red-800',
                default   => 'border-slate-200 bg-slate-50 text-slate-800',
            };
        @endphp
    
        <div class="mb-3 rounded-lg border {{ $classes }} px-4 py-3 text-xs">
            <div class="flex items-start justify-between gap-2">
                <span>{{ $message }}</span>
                <button type="button"
                        class="text-[11px] text-slate-400 hover:text-slate-700"
                        wire:click="clear">
                    ✕
                </button>
            </div>
        </div>
    @endif
</div>
