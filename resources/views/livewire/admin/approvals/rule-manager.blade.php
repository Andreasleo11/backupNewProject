<div class="px-4 py-6 max-w-6xl mx-auto">
    {{-- Alerts --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Approval Rules</h1>
            <p class="mt-0.5 text-xs text-slate-500">
                Manage rule templates and their steps. These rules are used by the generic approval engine.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <input type="text" wire:model.live.debounce.400ms="search"
                       placeholder="Search code, name, model..."
                       class="w-64 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 21L16.65 16.65M18 11C18 14.866 14.866 18 11 18C7.13401 18 4 14.866 4 11C4 7.13401 7.13401 4 11 4C14.866 4 18 7.13401 18 11Z"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>

            <button type="button" wire:click="openCreateRule"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                <svg class="mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 5C10.5523 5 11 5.44772 11 6V9H14C14.5523 9 15 9.44772 15 10C15 10.5523 14.5523 11 14 11H11V14C11 14.5523 10.5523 15 10 15C9.44772 15 9 14.5523 9 14V11H6C5.44772 11 5 10.5523 5 10C5 9.44772 5.44772 9 6 9H9V6C9 5.44772 9.44772 5 10 5Z"/>
                </svg>
                New Rule
            </button>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Rules list --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Rule Templates
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-500">Code</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-500">Name</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-500">Model</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-500">Priority</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-500">Active</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rules as $rule)
                            <tr class="{{ $selectedRule && $selectedRule->id === $rule->id ? 'bg-indigo-50/40' : 'hover:bg-slate-50/70' }}"
                                wire:key="rule-{{ $rule->id }}">
                                <td class="px-3 py-2 text-xs font-semibold text-slate-900">
                                    <button type="button"
                                            wire:click="selectRule({{ $rule->id }})"
                                            class="hover:underline">
                                        {{ $rule->code }}
                                    </button>
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-700">
                                    {{ $rule->name }}
                                </td>
                                <td class="px-3 py-2 text-[11px] text-slate-500">
                                    {{ class_basename($rule->model_type) }}
                                </td>
                                <td class="px-3 py-2 text-center text-[11px] text-slate-600">
                                    {{ $rule->priority }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if ($rule->active)
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button"
                                                wire:click="openEditRule({{ $rule->id }})"
                                                class="rounded border border-slate-200 px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50">
                                            Edit
                                        </button>
                                        <button type="button"
                                                wire:click="deleteRule({{ $rule->id }})"
                                                onclick="return confirm('Delete this rule and its steps?')"
                                                class="rounded border border-red-200 px-2 py-1 text-[11px] text-red-600 hover:bg-red-50">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-xs text-slate-500">
                                    No rules defined yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rules->hasPages())
                <div class="border-t border-slate-100 px-4 py-2 text-[11px] text-slate-500">
                    {{ $rules->links() }}
                </div>
            @endif
        </div>

        {{-- Selected rule details + steps --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 flex items-center justify-between">
                <span>Rule Details & Steps</span>
                @if ($selectedRule)
                    <button type="button"
                            wire:click="openCreateStep"
                            class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-slate-800">
                        + Add Step
                    </button>
                @endif
            </div>

            @if (! $selectedRule)
                <div class="p-4 text-xs text-slate-500">
                    Select a rule on the left to view and manage its steps.
                </div>
            @else
                <div class="px-4 py-3 space-y-3 text-xs">
                    <div>
                        <div class="text-[11px] font-semibold uppercase text-slate-500">Summary</div>
                        <div class="mt-1 text-slate-800">
                            <div class="font-semibold">{{ $selectedRule->code }} — {{ $selectedRule->name }}</div>
                            <div class="mt-0.5 text-[11px] text-slate-500">
                                Model: {{ $selectedRule->model_type }} · Priority: {{ $selectedRule->priority }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-[11px] font-semibold uppercase text-slate-500">Match Expression</div>
                        <pre class="mt-1 max-h-40 overflow-auto rounded border border-slate-100 bg-slate-50 px-2 py-1.5 text-[11px] text-slate-700">
{{ json_encode($selectedRule->match_expr ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                        </pre>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase text-slate-500">Steps</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-[11px]">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-2 py-1 text-left font-semibold text-slate-500">Seq</th>
                                        <th class="px-2 py-1 text-left font-semibold text-slate-500">Type</th>
                                        <th class="px-2 py-1 text-left font-semibold text-slate-500">Approver ID</th>
                                        <th class="px-2 py-1 text-center font-semibold text-slate-500">Final</th>
                                        <th class="px-2 py-1 text-center font-semibold text-slate-500">Parallel</th>
                                        <th class="px-2 py-1 text-right font-semibold text-slate-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($steps as $step)
                                        <tr wire:key="step-{{ $step->id }}">
                                            <td class="px-2 py-1 text-slate-700">{{ $step->sequence }}</td>
                                            <td class="px-2 py-1 text-slate-700">
                                                {{ strtoupper($step->approver_type) }}
                                            </td>
                                            <td class="px-2 py-1 text-slate-600">
                                                {{ $step->approver_id ?? '—' }}
                                            </td>
                                            <td class="px-2 py-1 text-center">
                                                @if ($step->final)
                                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] text-emerald-700">Yes</span>
                                                @else
                                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-500">No</span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-1 text-center">
                                                @if ($step->parallel_group)
                                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-600">Parallel</span>
                                                @else
                                                    <span class="text-[10px] text-slate-400">Single</span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-1 text-right">
                                                <div class="inline-flex items-center gap-1">
                                                    <button type="button"
                                                            wire:click="openEditStep({{ $step->id }})"
                                                            class="rounded border border-slate-200 px-2 py-0.5 text-[10px] text-slate-700 hover:bg-slate-50">
                                                        Edit
                                                    </button>
                                                    <button type="button"
                                                            wire:click="deleteStep({{ $step->id }})"
                                                            onclick="return confirm('Delete this step?')"
                                                            class="rounded border border-red-200 px-2 py-0.5 text-[10px] text-red-600 hover:bg-red-50">
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-2 py-3 text-center text-[11px] text-slate-500">
                                                No steps defined. Add at least one approver step.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Rule Modal --}}
    <div x-data="{ open: @entangle('showRuleModal').live }">
        <div x-show="open" x-cloak class="fixed inset-0 z-40 bg-black/30" @click="open = false"></div>

        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
             x-transition.opacity>
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-slate-200" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">
                        {{ $editingRuleId ? 'Edit Rule' : 'New Rule' }}
                    </h2>
                    <button type="button"
                            class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            @click="open = false">
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="saveRule" class="px-5 py-4 space-y-3 text-xs">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-700">
                            Model Type <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.defer="rule_model_type"
                               placeholder="App\Infrastructure\Persistence\Eloquent\Models\PurchaseRequest"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('rule_model_type')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-700">
                                Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model.defer="rule_code"
                                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            @error('rule_code')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-700">
                                Priority
                            </label>
                            <input type="number" wire:model.defer="rule_priority"
                                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            @error('rule_priority')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-700">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.defer="rule_name"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @error('rule_name')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-[11px] font-medium text-slate-700">
                            <input type="checkbox" wire:model.defer="rule_active"
                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Active
                        </label>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-700">
                            Match Expression (JSON)
                        </label>
                        <textarea wire:model.defer="rule_match_expr_raw" rows="6"
                                  class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px] font-mono shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                        <p class="mt-1 text-[10px] text-slate-400">
                            Example: {"from_department_id": 210, "to_department_id": 320, "amount_gte": 50000000}
                        </p>
                        @error('rule_match_expr_raw')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end border-t border-slate-100 pt-3 gap-2">
                        <button type="button"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                                @click="open = false">
                            Cancel
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-indigo-700">
                            Save Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Step Modal --}}
    <div x-data="{ open: @entangle('showStepModal').live }">
        <div x-show="open" x-cloak class="fixed inset-0 z-40 bg-black/30" @click="open = false"></div>

        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
             x-transition.opacity>
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-slate-200" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">
                        {{ $editingStepId ? 'Edit Step' : 'New Step' }}
                    </h2>
                    <button type="button"
                            class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            @click="open = false">
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="saveStep" class="px-5 py-4 space-y-3 text-xs">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-700">
                                Sequence <span class="text-red-500">*</span>
                            </label>
                            <input type="number" min="1" wire:model.defer="step_sequence"
                                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            @error('step_sequence')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-700">
                                Approver Type <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.defer="step_approver_type"
                                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="user">User (by ID)</option>
                                <option value="role">Role (by ID)</option>
                            </select>
                            @error('step_approver_type')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-700">
                            Approver ID
                        </label>
                        <input type="number" wire:model.defer="step_approver_id"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <p class="mt-1 text-[10px] text-slate-400">
                            User ID or Role ID depending on the approver type.
                        </p>
                        @error('step_approver_id')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label class="inline-flex items-center gap-2 text-[11px] font-medium text-slate-700">
                            <input type="checkbox" wire:model.defer="step_final"
                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Final step
                        </label>
                        <label class="inline-flex items-center gap-2 text-[11px] font-medium text-slate-700">
                            <input type="checkbox" wire:model.defer="step_parallel_group"
                                   class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Parallel group
                        </label>
                    </div>

                    <div class="flex items-center justify-end border-t border-slate-100 pt-3 gap-2">
                        <button type="button"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                                @click="open = false">
                            Cancel
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-indigo-700">
                            Save Step
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
