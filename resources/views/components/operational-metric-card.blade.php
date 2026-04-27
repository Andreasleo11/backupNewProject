@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'changeType' => 'positive',
    'icon' => '',
    'loading' => false,
    'trend' => null,
    'alert' => false,
    'alertMessage' => '',
    'secondaryValue' => null,
    'secondaryLabel' => '',
])

<div class="bg-white rounded-xl border {{ $alert ? 'border-red-300 bg-red-50' : 'border-slate-200' }} shadow-sm hover:shadow-md transition-all duration-200 p-6 relative overflow-hidden">
    @if($alert)
        <div class="absolute top-0 left-0 right-0 h-1 bg-red-500"></div>
    @endif

    <div class="flex items-center justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
                <p class="text-sm font-medium text-slate-600">{{ $title }}</p>
                @if($alert)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Alert
                    </span>
                @endif
            </div>

            @if($loading)
                <div class="space-y-2">
                    <div class="h-8 bg-slate-200 rounded animate-pulse"></div>
                    <div class="h-4 bg-slate-200 rounded animate-pulse w-3/4"></div>
                </div>
            @else
                <div class="space-y-1">
                    <p class="text-3xl font-bold text-slate-900">{{ $value }}</p>
                    @if($secondaryValue)
                        <p class="text-sm text-slate-500">{{ $secondaryLabel }}: <span class="font-medium">{{ $secondaryValue }}</span></p>
                    @endif
                </div>

                @if($alertMessage)
                    <p class="text-xs text-red-600 mt-2">{{ $alertMessage }}</p>
                @endif
            @endif
        </div>

        @if($icon)
            <div class="ml-4 h-12 w-12 rounded-lg {{ $alert ? 'bg-red-100' : 'bg-slate-100' }} flex items-center justify-center">
                <svg class="h-6 w-6 {{ $alert ? 'text-red-600' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            </div>
        @endif
    </div>

    @if($change !== null && !$alert)
        <div class="mt-4 flex items-center gap-2">
            @if($changeType === 'positive')
                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span class="text-sm font-medium text-green-600">+{{ $change }}%</span>
            @elseif($changeType === 'negative')
                <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
                <span class="text-sm font-medium text-red-600">{{ $change }}%</span>
            @else
                <span class="text-sm font-medium text-slate-600">{{ $change }}</span>
            @endif
            <span class="text-sm text-slate-500">{{ $trend ?? 'from last month' }}</span>
        </div>
    @endif
</div>