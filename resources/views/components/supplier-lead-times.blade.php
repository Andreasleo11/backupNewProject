@props(['supplierData' => []])

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h3 class="text-lg font-semibold text-slate-900 mb-4">Supplier Lead Times</h3>
    <p class="text-sm text-slate-500 mb-6">Average fulfillment time and reliability by supplier</p>

    <div class="space-y-3">
        @forelse($supplierData as $supplier)
            <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full {{ $supplier['reliability_score'] >= 80 ? 'bg-green-100' : ($supplier['reliability_score'] >= 60 ? 'bg-yellow-100' : 'bg-red-100') }} flex items-center justify-center">
                            <span class="text-xs font-bold {{ $supplier['reliability_score'] >= 80 ? 'text-green-800' : ($supplier['reliability_score'] >= 60 ? 'text-yellow-800' : 'text-red-800') }}">
                                {{ round($supplier['reliability_score']) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $supplier['vendor'] }}</p>
                            <p class="text-xs text-slate-500">{{ $supplier['total_orders'] }} orders</p>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-slate-900">{{ $supplier['avg_lead_time'] }} days</p>
                    <p class="text-xs text-slate-500">
                        {{ $supplier['lead_time_range']['min'] }}-{{ $supplier['lead_time_range']['max'] }} days
                    </p>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-slate-500">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2 text-sm">No supplier lead time data available</p>
            </div>
        @endforelse
    </div>

    @if(count($supplierData) > 0)
        <div class="mt-6 pt-4 border-t border-slate-200">
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <span class="text-slate-600">High Reliability (80-100)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <span class="text-slate-600">Medium (60-79)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span class="text-slate-600">Low (<60)</span>
                </div>
            </div>
        </div>
    @endif
</div>