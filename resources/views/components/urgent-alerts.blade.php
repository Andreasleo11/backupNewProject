@props(['alerts' => []])

@if(count($alerts) > 0)
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
    <div class="flex items-center gap-2 mb-3">
        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <h3 class="text-sm font-semibold text-red-800">Urgent Alerts</h3>
    </div>

    <div class="space-y-2">
        @foreach($alerts as $alert)
            <div class="flex items-start gap-3 p-3 rounded-lg bg-white border border-red-200">
                <div class="flex-shrink-0">
                    @if($alert['priority'] === 'high')
                        <div class="w-2 h-2 rounded-full bg-red-500 mt-1"></div>
                    @elseif($alert['priority'] === 'medium')
                        <div class="w-2 h-2 rounded-full bg-yellow-500 mt-1"></div>
                    @else
                        <div class="w-2 h-2 rounded-full bg-blue-500 mt-1"></div>
                    @endif
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-800">{{ $alert['message'] }}</p>
                    <p class="text-xs text-red-600 mt-1">{{ $alert['action_required'] }}</p>
                </div>
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $alert['priority'] === 'high' ? 'bg-red-100 text-red-800' :
                           ($alert['priority'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                        {{ ucfirst($alert['priority']) }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif