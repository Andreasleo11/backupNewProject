<div>
    @if ($message)
        @php
            $config = match ($type) {
                'success' => [
                    'bg' => 'bg-emerald-50/80',
                    'border' => 'border-emerald-200/50',
                    'text' => 'text-emerald-800',
                    'icon_bg' => 'bg-emerald-100',
                    'icon_color' => 'text-emerald-600',
                    'icon' => 'check-circle'
                ],
                'error' => [
                    'bg' => 'bg-rose-50/80',
                    'border' => 'border-rose-200/50',
                    'text' => 'text-rose-800',
                    'icon_bg' => 'bg-rose-100',
                    'icon_color' => 'text-rose-600',
                    'icon' => 'x-circle'
                ],
                default => [
                    'bg' => 'bg-blue-50/80',
                    'border' => 'border-blue-200/50',
                    'text' => 'text-blue-800',
                    'icon_bg' => 'bg-blue-100',
                    'icon_color' => 'text-blue-600',
                    'icon' => 'clock'
                ],
            };
        @endphp
    
        <div x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="mb-6 rounded-2xl border backdrop-blur-md shadow-lg shadow-black/5 {{ $config['border'] }} {{ $config['bg'] }} px-5 py-4">
            
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $config['icon_bg'] }} {{ $config['icon_color'] }} shadow-sm">
                        @include('new.layouts.partials.nav-icon', ['name' => $config['icon']])
                    </div>
                    <div>
                        <p class="text-[13px] font-bold {{ $config['text'] }} leading-tight">
                            {{ $message }}
                        </p>
                    </div>
                </div>
                
                <button type="button"
                        class="h-8 w-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-white/50 hover:text-slate-600 transition-all duration-200"
                        @click="show = false"
                        wire:click="clear">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>
