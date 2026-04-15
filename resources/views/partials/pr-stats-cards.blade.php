@php
    $cardConfigs = [
        'pending_my_approval' => [
            'label' => 'Pending Action',
            'icon' => 'bx-bell',
            'color' => 'amber',
            'filter' => 'my_approval',
            'description' => 'Awaiting your approval',
        ],
        'my_active' => [
            'label' => 'My Requests',
            'icon' => 'bx-paper-plane',
            'color' => 'blue',
            'filter' => 'my_active',
            'description' => 'PRs you created in review',
        ],
        'dept_active' => [
            'label' => 'Dept Pipeline',
            'icon' => 'bx-buildings',
            'color' => 'indigo',
            'filter' => 'dept_active',
            'description' => 'Department PRs in progress',
        ],
        'drafts' => [
            'label' => 'Drafts',
            'icon' => 'bx-edit-alt',
            'color' => 'rose',
            'filter' => 'drafts',
            'description' => 'Unsubmitted requests',
        ],
        'in_review' => [
            'label' => 'Global Pipeline',
            'icon' => 'bx-loader-alt',
            'color' => 'sky',
            'filter' => 'in_review',
            'description' => 'Total active across company',
        ],
        'approved_this_month' => [
            'label' => 'Approved (' . now()->format('M') . ')',
            'icon' => 'bx-check-double',
            'color' => 'emerald',
            'filter' => 'approved_month',
            'description' => 'Monthly throughput',
        ],
    ];
@endphp

<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($stats as $key => $value)
        @if (isset($cardConfigs[$key]))
            @php
                $config = $cardConfigs[$key];
                $color = $config['color'];
                $isActive = $preset === $config['filter'];
            @endphp

            <button @click="$wire.setPreset('{{ $config['filter'] }}')"
                class="w-full text-left group relative overflow-hidden rounded-2xl bg-white p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl border {{ $isActive ? "border-{$color}-400 shadow-md shadow-{$color}-100/50 ring-1 ring-{$color}-400/20" : "border-slate-100 shadow-sm hover:border-{$color}-300" }}">

                {{-- Dynamic Gradient Background --}}
                <div
                    class="absolute inset-0 bg-gradient-to-br from-{{ $color }}-50/40 via-white to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 {{ $isActive ? 'opacity-100' : '' }}">
                </div>

                <div class="relative z-10 flex items-center justify-between">
                    <div class="min-w-0">
                        <p
                            class="text-[10px] font-bold tracking-widest uppercase {{ $isActive ? "text-{$color}-600" : "text-slate-400 group-hover:text-{$color}-600" }} transition-colors">
                            {{ $config['label'] }}
                        </p>
                        <div class="mt-1 flex items-baseline gap-2">
                            <p
                                class="text-2xl font-black text-slate-800 tracking-tight group-hover:text-{{ $color }}-700 transition-colors leading-none">
                                {{ is_array($value) ? count($value) : $value }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl shadow-sm transition-all duration-300 group-hover:scale-110 {{ $isActive ? "bg-{$color}-500 text-white shadow-{$color}-200" : "bg-{$color}-50 text-{$color}-500 group-hover:bg-{$color}-500 group-hover:text-white" }}">
                        <i
                            class='bx {{ $config['icon'] }} text-2xl {{ $key === 'in_review' ? 'animate-spin-slow' : '' }}'></i>
                    </div>
                </div>

                {{-- Progress Indicator Decoration --}}
                <div class="absolute bottom-0 left-0 h-1 w-full bg-slate-50/50">
                    <div class="h-full bg-gradient-to-r from-{{ $color }}-400 to-{{ $color }}-600 transition-all duration-1000 ease-out"
                        style="width: {{ min((is_array($value) ? count($value) : $value) * 10, 100) }}%"></div>
                </div>
            </button>
        @elseif($key === 'total_value_pending')
            {{-- Strategic Est Value Card --}}
            <div
                class="group relative overflow-hidden rounded-2xl bg-white p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl border border-slate-100 shadow-sm hover:border-indigo-300">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-indigo-50/40 via-white to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                </div>

                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p
                            class="text-[10px] font-bold tracking-widest uppercase text-slate-400 group-hover:text-indigo-600 transition-colors">
                            Pending Est. Value</p>
                        <div class="mt-1 space-y-0.5">
                            @foreach ($value as $currency => $amount)
                                <div class="truncate flex items-baseline gap-1.5">
                                    <span
                                        class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{{ $currency }}</span>
                                    <span
                                        class="text-lg font-black text-slate-800 tracking-tight group-hover:text-indigo-700 transition-colors">
                                        {{ number_format($amount, 0) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div
                        class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500 shadow-sm transition-all duration-300 group-hover:scale-110 group-hover:bg-indigo-500 group-hover:text-white">
                        <i class='bx bx-pie-chart-alt text-2xl'></i>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
