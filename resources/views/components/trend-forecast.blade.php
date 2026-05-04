@props(['forecast' => []])

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h3 class="text-lg font-semibold text-slate-900 mb-4">Spending Forecast</h3>
    <p class="text-sm text-slate-500 mb-6">Predictive analysis based on historical trends</p>

    @if($forecast)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="text-center p-4 rounded-lg bg-slate-50">
                <p class="text-2xl font-bold text-slate-900">{{ number_format($forecast['next_month_prediction'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">Next Month Prediction (IDR)</p>
            </div>
            <div class="text-center p-4 rounded-lg bg-slate-50">
                <div class="flex items-center justify-center gap-2">
                    @if(($forecast['trend_direction'] ?? '') === 'increasing')
                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    @endif
                    <span class="text-sm font-medium capitalize">{{ $forecast['trend_direction'] ?? 'stable' }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Trend Direction</p>
            </div>
            <div class="text-center p-4 rounded-lg bg-slate-50">
                <p class="text-2xl font-bold {{ ($forecast['confidence_level'] ?? 0) >= 80 ? 'text-green-600' : (($forecast['confidence_level'] ?? 0) >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $forecast['confidence_level'] ?? 0 }}%
                </p>
                <p class="text-xs text-slate-500 mt-1">Forecast Confidence</p>
            </div>
        </div>

        @if(isset($forecast['seasonal_factors']) && count($forecast['seasonal_factors']) > 0)
            <div class="border-t border-slate-200 pt-6">
                <h4 class="text-sm font-medium text-slate-900 mb-3">Seasonal Patterns</h4>
                <div class="grid grid-cols-6 gap-2">
                    @foreach($forecast['seasonal_factors'] as $factor)
                        <div class="text-center p-2 rounded {{ $factor['is_peak'] ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                            <p class="text-xs font-medium">{{ \Carbon\Carbon::create()->month($factor['month'])->format('M') }}</p>
                            <p class="text-xs">{{ number_format($factor['average'], 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-slate-500 mt-2">Peak months highlighted in blue</p>
            </div>
        @endif
    @else
        <div class="text-center py-12 text-slate-500">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <p class="mt-2 text-sm">Insufficient historical data for forecasting</p>
            <p class="text-xs text-slate-400 mt-1">Need at least 6 months of data</p>
        </div>
    @endif
</div>