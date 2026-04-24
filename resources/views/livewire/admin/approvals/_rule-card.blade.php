{{-- Rule Card Component --}}
<div class="relative">
    {{-- Selection checkbox --}}
    @if ($groupByModel || count($selectedRules) > 0)
        <input type="checkbox" wire:model.live="selectedRules" value="{{ $rule->id }}"
            class="absolute top-1/2 left-3 -translate-y-1/2 z-10 w-4 h-4 text-indigo-600 bg-white border-slate-300 rounded focus:ring-indigo-500 cursor-pointer">
    @endif

    <div wire:click="selectRule({{ $rule->id }})"
        class="group w-full text-left rounded-lg transition-all border
            {{ ($groupByModel || count($selectedRules) > 0) ? 'pl-9' : 'pl-3' }}
            pr-3 pt-3 pb-3
            {{ $selectedRuleId === $rule->id
                ? 'bg-indigo-50 border-indigo-300 shadow-sm ring-1 ring-indigo-200'
                : (in_array($rule->id, $selectedRules)
                    ? 'bg-blue-50 border-blue-200'
                    : 'bg-white border-slate-200 hover:border-slate-300 hover:shadow-sm') }}">

        {{-- Header row --}}
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="font-semibold text-slate-900 truncate">{{ $rule->code }}</span>
                <div class="flex items-center gap-1 flex-shrink-0">
                    @if ($rule->active)
                        <div class="w-2 h-2 rounded-full bg-emerald-500" title="Active"></div>
                    @else
                        <div class="w-2 h-2 rounded-full bg-slate-300" title="Inactive"></div>
                    @endif
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                        P{{ $rule->priority }}
                    </span>
                </div>
            </div>

            {{-- Action buttons: always visible when selected, hover-visible otherwise --}}
            <div class="flex gap-1 transition-opacity {{ $selectedRuleId === $rule->id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }}">
                <button wire:click.stop="openEditRule({{ $rule->id }})"
                    class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors"
                    title="Edit rule">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button wire:click.stop="deleteRule({{ $rule->id }})"
                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                    title="Delete rule"
                    wire:confirm="Delete this rule?">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Description --}}
        <div class="text-sm text-slate-600 mb-2 line-clamp-1">{{ $rule->name }}</div>

        {{-- Footer info --}}
        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center gap-3">
                @if (!$groupByModel)
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 text-slate-700 font-medium">
                        {{ class_basename($rule->model_type) }}
                    </span>
                @endif
                <span class="text-slate-500">{{ $rule->steps_count ?? 0 }} steps</span>
            </div>
            @if (!empty($rule->match_expr))
                <span class="text-slate-500">{{ count($rule->match_expr) }} conditions</span>
            @endif
        </div>
    </div>
</div>