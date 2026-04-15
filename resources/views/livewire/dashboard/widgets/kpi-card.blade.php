@php
    $colorClasses = match ($color) {
        'blue' => 'from-blue-500/5 to-transparent border-blue-100/50 text-blue-600 bg-blue-50 shadow-blue-100/20',
        'emerald'
            => 'from-emerald-500/5 to-transparent border-emerald-100/50 text-emerald-600 bg-emerald-50 shadow-emerald-100/20',
        'rose' => 'from-rose-500/5 to-transparent border-rose-100/50 text-rose-600 bg-rose-50 shadow-rose-100/20',
        'indigo'
            => 'from-indigo-500/5 to-transparent border-indigo-100/50 text-indigo-600 bg-indigo-50 shadow-indigo-100/20',
        default
            => 'from-slate-500/5 to-transparent border-slate-100/50 text-slate-600 bg-slate-50 shadow-slate-100/20',
    };
    $isPositive = str_contains($trend, '+');
@endphp

<div
    class="relative overflow-hidden rounded-3xl bg-white border border-slate-200 p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:border-blue-100 group">
    {{-- Ambient Glow --}}
    <div
        class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-gradient-to-br {{ $colorClasses }} blur-2xl opacity-0 group-hover:opacity-100 transition-opacity">
    </div>

    <div class="relative z-10 flex items-start justify-between">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1 leading-none">
                {{ $label }}</p>
            <h4 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-none pt-1">{{ $value }}</h4>

            @if ($trend)
                <div class="mt-3 flex items-center gap-1.5">
                    <span
                        class="flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isPositive ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="{{ $isPositive ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}" />
                        </svg>
                        {{ $trend }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">vs last month</span>
                </div>
            @endif
        </div>

        <div class="p-3 rounded-2xl {{ $colorClasses }} border transition-all group-hover:scale-110 duration-500">
            <i class='bx bx-{{ $icon }} text-xl'></i>
        </div>
    </div>
</div>
