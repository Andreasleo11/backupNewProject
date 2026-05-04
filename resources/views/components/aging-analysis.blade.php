@props(['agingData' => []])

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h3 class="text-lg font-semibold text-slate-900 mb-4">PO Aging Analysis</h3>
    <p class="text-sm text-slate-500 mb-6">Distribution of approved POs by days since approval</p>

    <div class="space-y-4">
        @forelse($agingData as $bucket)
            <div class="flex items-center justify-between p-3 rounded-lg {{ $bucket['bucket'] === '90+ days' ? 'bg-red-50 border border-red-200' : 'bg-slate-50' }}">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full {{ $bucket['bucket'] === '90+ days' ? 'bg-red-500' : 'bg-slate-400' }}"></div>
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $bucket['bucket'] }}</p>
                        <p class="text-xs text-slate-500">{{ $bucket['count'] }} POs • Avg {{ round($bucket['avg_age']) }} days</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-900">{{ number_format($bucket['value'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">IDR</p>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-slate-500">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-sm">No aging data available</p>
            </div>
        @endforelse
    </div>

    @if(count($agingData) > 0)
        <div class="mt-6 pt-4 border-t border-slate-200">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ collect($agingData)->sum('count') }}</p>
                    <p class="text-xs text-slate-500">Total POs</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ number_format(collect($agingData)->sum('value'), 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500">Total Value (IDR)</p>
                </div>
            </div>
        </div>
    @endif
</div>