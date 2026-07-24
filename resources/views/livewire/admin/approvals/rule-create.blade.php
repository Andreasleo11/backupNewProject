<div class="w-full space-y-6">
    <div class="flex items-center justify-between mb-6 border-b border-slate-200 pb-4">
        <h2 class="text-xl font-bold text-slate-900">
            Create New Rule
        </h2>
        <a href="{{ route('admin.approval-rules.index') }}"
            class="text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">
            &larr; Back to Rules
        </a>
    </div>

    <form wire:submit.prevent="saveRule" class="space-y-6 max-w-4xl bg-white p-6 rounded-md border border-slate-200 shadow-sm">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Target Model --}}
            <div class="relative md:col-span-2">
                <label for="rule_model_type" class="block text-xs font-semibold text-slate-500 mb-1">
                    Target Form / Module <span class="text-red-500">*</span>
                </label>
                <select wire:model.defer="rule_model_type" id="rule_model_type" autofocus
                    class="block w-full rounded-md border border-slate-200 bg-white px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                    <option value="">— Select a registered form —</option>
                    @foreach($availableModules as $className => $label)
                        <option value="{{ $className }}">{{ $label }} ({{ class_basename($className) }})</option>
                    @endforeach
                </select>
                @error('rule_model_type')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Rule Name --}}
            <div class="relative">
                <input type="text" wire:model.defer="rule_name" id="rule_name"
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent"
                    placeholder="Rule Name">
                <label for="rule_name" class="absolute left-4 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                    Rule Name <span class="text-red-500">*</span>
                </label>
                @error('rule_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Unique Code --}}
            <div class="relative">
                <input type="text" wire:model.defer="rule_code" id="rule_code"
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent"
                    placeholder="Unique Code">
                <label for="rule_code" class="absolute left-4 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                    Unique Code <span class="text-red-500">*</span>
                </label>
                @error('rule_code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Priority --}}
            <div class="relative">
                <input type="number" wire:model.defer="rule_priority" id="rule_priority"
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent"
                    placeholder="Priority">
                <label for="rule_priority" class="absolute left-4 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                    Priority (Lower runs first) <span class="text-red-500">*</span>
                </label>
                @error('rule_priority')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.defer="rule_active" class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900"></div>
                    <span class="ml-3 text-sm font-medium text-slate-700">Rule Active</span>
                </label>
            </div>
        </div>

        {{-- Condition Builder --}}
        <div class="space-y-3 pt-3 border-t border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-bold text-slate-700">Trigger Conditions</label>
                <button type="button" wire:click="addCondition" class="text-xs font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded transition-colors">+ Add Condition</button>
            </div>
            
            @if(count($ruleConditions) === 0)
                <div class="text-sm text-slate-500 italic p-4 bg-slate-50 rounded-md border border-slate-200 border-dashed text-center">
                    No conditions set. This rule will match ALL requests for the selected form.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($ruleConditions as $index => $condition)
                        <div class="flex items-start gap-3 relative group">
                            <div class="flex-1 space-y-1">
                                <div class="flex gap-3">
                                    <input type="text" wire:model.defer="ruleConditions.{{ $index }}.field" placeholder="Field (e.g. status)" class="w-1/3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950">
                                    <select wire:model.defer="ruleConditions.{{ $index }}.operator" class="w-1/3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950">
                                        <option value="==">Equals</option>
                                        <option value="in">In List</option>
                                        <option value="not_in">Not In List</option>
                                        <option value=">">Greater Than</option>
                                        <option value=">=">Greater Than or Equal</option>
                                        <option value="<=">Less Than or Equal</option>
                                        <option value="any">Contains Any Tag</option>
                                    </select>
                                    <input type="text" wire:model.defer="ruleConditions.{{ $index }}.value" placeholder="Value (comma-separated for lists)" class="w-1/3 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950">
                                </div>
                                @error('ruleConditions.'.$index.'.field')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <button type="button" wire:click="removeCondition({{ $index }})" class="p-2 text-slate-400 hover:text-red-600 transition-colors bg-white rounded-md border border-slate-200 shadow-sm hover:border-red-200 hover:bg-red-50" title="Remove Condition">
                                <x-bx-x class="w-5 h-5" />
                            </button>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-slate-500 leading-tight">Note: 'Greater/Less Than' operators only work on the <strong>amount</strong> field. 'Contains Any Tag' only works on the <strong>tags</strong> field.</p>
            @endif
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 mt-6">
            <a href="{{ route('admin.approval-rules.index') }}" class="px-4 py-2 rounded-md border border-slate-200 bg-white text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="saveRule" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors disabled:opacity-50 shadow-sm">
                <x-bx-loader-alt class="animate-spin" wire:loading wire:target="saveRule" />
                <span wire:loading.remove wire:target="saveRule">Create & Continue to Steps</span>
                <span wire:loading wire:target="saveRule">Saving...</span>
            </button>
        </div>
    </form>
</div>
