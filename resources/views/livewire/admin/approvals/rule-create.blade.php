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
                <input type="text" wire:model.defer="rule_model_type" id="rule_model_type" autofocus
                    class="peer block w-full rounded-md border border-slate-200 bg-transparent px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950 placeholder-transparent"
                    placeholder="App\Models\Document">
                <label for="rule_model_type" class="absolute left-4 -top-2.5 bg-white px-1 text-xs font-medium text-slate-500 transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-sm peer-focus:-top-2.5 peer-focus:text-xs peer-focus:text-slate-900">
                    Target Model Class (e.g. App\Models\Invoice) <span class="text-red-500">*</span>
                </label>
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

        {{-- JSON Editor --}}
        <div class="space-y-1">
            <div class="flex justify-between items-center mb-1">
                <label class="block text-sm font-bold text-slate-700">
                    Match Expression (JSON)
                </label>
                <span class="text-xs text-slate-500 font-mono">{"field": "value"}</span>
            </div>
            <textarea wire:model.defer="rule_match_expr_raw" rows="6"
                class="flex w-full rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-mono focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors shadow-inner" placeholder='{}'></textarea>
            <p class="text-xs text-slate-500 mt-1">Leave as <code>{}</code> to match all instances of the target model.</p>
            @error('rule_match_expr_raw')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
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
