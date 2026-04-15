<div class="h-[calc(100vh-64px)] overflow-hidden flex flex-col md:flex-row bg-slate-50">
    {{-- Sidebar: Rule List --}}
    <div class="w-full md:w-80 lg:w-96 flex flex-col border-r border-slate-200 bg-white h-full">
        <div class="p-4 border-b border-slate-100 flex-shrink-0">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-lg font-bold text-slate-900 tracking-tight">Approval Rules</h1>
                <button wire:click="openCreateRule"
                    class="rounded-lg bg-indigo-600 p-2 text-white shadow-lg shadow-indigo-500/30 transition-all hover:bg-indigo-500 hover:scale-105 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="relative">
                <input type="text" wire:model.live.debounce.400ms="search"
                    class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-indigo-500 focus:bg-white focus:ring-0 transition-all"
                    placeholder="Search rules...">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
            @forelse ($rules as $rule)
                <button wire:click="selectRule({{ $rule->id }})"
                    class="group w-full text-left rounded-xl p-3 transition-all border {{ $selectedRuleId === $rule->id ? 'bg-indigo-50 border-indigo-200 shadow-md ring-1 ring-indigo-500/20' : 'bg-white border-slate-100 hover:border-indigo-200 hover:shadow-sm' }}">
                    <div class="flex justify-between items-start mb-1">
                        <span
                            class="font-bold text-sm {{ $selectedRuleId === $rule->id ? 'text-indigo-700' : 'text-slate-700 group-hover:text-indigo-600' }}">
                            {{ $rule->code }}
                        </span>
                        @if ($rule->active)
                            <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                        @else
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 line-clamp-1 mb-2">{{ $rule->name }}</div>
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600">
                            {{ class_basename($rule->model_type) }}
                        </span>
                        <div
                            class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity {{ $selectedRuleId === $rule->id ? 'opacity-100' : '' }}">
                            <div wire:click.stop="openEditRule({{ $rule->id }})"
                                class="p-1 text-slate-400 hover:text-indigo-600 cursor-pointer">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div wire:click.stop="deleteRule({{ $rule->id }})"
                                class="p-1 text-slate-400 hover:text-rose-600 cursor-pointer">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </button>
            @empty
                <div class="text-center py-8 text-xs text-slate-400">
                    No rules found.
                </div>
            @endforelse

            @if ($rules->hasPages())
                <div class="pt-2">
                    {{ $rules->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
    </div>

    {{-- Main Content: Visual Builder --}}
    <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 relative">
        @if ($selectedRule)
            {{-- Toolbar --}}
            <div class="h-16 border-b border-slate-200 bg-white flex items-center justify-between px-6 shadow-sm z-10">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-bold text-slate-900">
                        {{ $selectedRule->name }}
                        <span
                            class="ml-2 text-xs font-normal text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                            Priority: {{ $selectedRule->priority }}
                        </span>
                    </h2>
                </div>
                <div class="flex items-center gap-3">
                    <button wire:click="openEditRule({{ $selectedRule->id }})"
                        class="text-xs font-semibold text-slate-500 hover:text-indigo-600 transition-colors">
                        Edit Settings
                    </button>
                    <div class="h-4 w-px bg-slate-300"></div>
                    <button wire:click="openCreateStep"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-indigo-500/20 transition-all hover:bg-indigo-500 hover:scale-105 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Add Step
                    </button>
                </div>
            </div>

            {{-- Flow Canvas --}}
            <div class="flex-1 overflow-auto p-8 custom-scrollbar bg-grid-slate-100">

                {{-- Match Logic Card --}}
                <div class="max-w-3xl mx-auto mb-12 relative group">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl opacity-10 blur-xl group-hover:opacity-20 transition-opacity">
                    </div>
                    <div class="relative bg-white border border-blue-100 rounded-2xl p-5 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div
                                class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Trigger Conditions
                                </h3>
                                <div
                                    class="mt-2 font-mono text-xs bg-slate-50 p-3 rounded-lg border border-slate-200 text-slate-600 overflow-x-auto whitespace-pre-wrap">
                                    {{ json_encode($selectedRule->match_expr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Downward Connector --}}
                    <div class="absolute left-1/2 -bottom-12 h-12 w-0.5 bg-slate-300 transform -translate-x-1/2"></div>
                    <div
                        class="absolute left-1/2 -bottom-2 h-2 w-2 rounded-full bg-slate-300 transform -translate-x-1/2">
                    </div>
                </div>

                {{-- Steps Flow --}}
                <div class="max-w-3xl mx-auto space-y-12">
                    @forelse ($steps as $index => $step)
                        <div class="relative group">
                            {{-- Connector line from previous step --}}
                            @if ($index > 0)
                                <div
                                    class="absolute left-1/2 -top-12 h-12 w-0.5 bg-slate-300 transform -translate-x-1/2 z-0">
                                </div>
                            @endif

                            <div
                                class="relative z-10 bg-white border border-slate-200 rounded-2xl p-1 shadow-sm transition-all hover:shadow-lg hover:border-indigo-200 hover:-translate-y-1">
                                <div class="flex items-stretch rounded-xl overflow-hidden">
                                    {{-- Step Number --}}
                                    <div
                                        class="bg-indigo-50 w-16 flex flex-col items-center justify-center border-r border-indigo-100">
                                        <span class="text-xs font-semibold text-indigo-400 uppercase">Step</span>
                                        <span class="text-2xl font-black text-indigo-600">{{ $step->sequence }}</span>
                                    </div>

                                    {{-- Content --}}
                                    <div class="flex-1 p-4">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10 uppercase tracking-widest">
                                                        {{ $step->approver_type }}
                                                    </span>
                                                    @if ($step->parallel_group)
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                                                            Parallel
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-lg font-bold text-slate-900">
                                                    {{ $step->approver_type === 'role' ? 'Role ID: ' : 'User ID: ' }}
                                                    {{ $step->approver_id }}
                                                </div>
                                            </div>

                                            {{-- Context Menu (Actions) --}}
                                            <div
                                                class="flex items-center gap-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button wire:click="openEditStep({{ $step->id }})"
                                                    class="p-2 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button wire:click="deleteStep({{ $step->id }})"
                                                    class="p-2 rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all"
                                                    onclick="return confirm('Delete this step?')">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Connector to next step (or end) --}}
                            @if (!$loop->last)
                                <div
                                    class="absolute left-1/2 -bottom-12 h-12 w-0.5 bg-slate-300 transform -translate-x-1/2">
                                </div>
                            @elseif($step->final)
                                <div
                                    class="absolute left-1/2 -bottom-12 h-12 w-0.5 bg-emerald-400 transform -translate-x-1/2">
                                </div>
                                <div class="absolute left-1/2 -bottom-20 transform -translate-x-1/2">
                                    <span
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 ring-4 ring-white shadow-lg">
                                        APPROVED
                                    </span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div
                            class="text-center py-12 rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50/50">
                            <svg class="h-10 w-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <p class="text-sm text-slate-500 font-medium">No steps defined for this rule yet.</p>
                            <button wire:click="openCreateStep"
                                class="mt-4 text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline">
                                + Add First Approver Step
                            </button>
                        </div>
                    @endforelse
                </div>

                {{-- Padding for scrolling --}}
                <div class="h-32"></div>
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-500">
                <div class="h-24 w-24 bg-slate-100 rounded-full flex items-center justify-center mb-6 shadow-inner">
                    <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">No Rule Selected</h3>
                <p class="mt-2 text-sm max-w-sm">Select a rule from the sidebar to visualize and edit its approval
                    workflow.</p>
            </div>
        @endif
    </div>

    {{-- Rules Modal --}}
    <x-modal wire:model="showRuleModal" maxWidth="lg">
        <div class="p-6">
            <h2 class="text-xl font-bold text-slate-900 mb-6">
                {{ $editingRuleId ? 'Edit Rule Settings' : 'Create New Rule' }}
            </h2>
            <form wire:submit.prevent="saveRule" class="space-y-4">
                {{-- Model Type --}}
                <div class="relative">
                    <input type="text" wire:model.defer="rule_model_type" id="rule_model_type"
                        class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                        placeholder=" ">
                    <label for="rule_model_type"
                        class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:left-4 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                        Target Model Class <span class="text-red-500">*</span>
                    </label>
                    @error('rule_model_type')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Code --}}
                    <div class="relative">
                        <input type="text" wire:model.defer="rule_code" id="rule_code"
                            class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                            placeholder=" ">
                        <label for="rule_code"
                            class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:left-4 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                            Unique Code <span class="text-red-500">*</span>
                        </label>
                        @error('rule_code')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Priority --}}
                    <div class="relative">
                        <input type="number" wire:model.defer="rule_priority" id="rule_priority"
                            class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                            placeholder=" ">
                        <label for="rule_priority"
                            class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:left-4 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                            Priority <span class="text-red-500">*</span>
                        </label>
                        @error('rule_priority')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Name --}}
                <div class="relative">
                    <input type="text" wire:model.defer="rule_name" id="rule_name"
                        class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                        placeholder=" ">
                    <label for="rule_name"
                        class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:left-4 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                        Rule Name <span class="text-red-500">*</span>
                    </label>
                    @error('rule_name')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                {{-- JSON Editor --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        Match Expression (JSON)
                    </label>
                    <textarea wire:model.defer="rule_match_expr_raw" rows="5"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-mono focus:border-blue-500 focus:bg-white focus:ring-0"></textarea>
                    <p class="text-[10px] text-slate-400">Structure: {"field": "value", "field_op": "value"}</p>
                    @error('rule_match_expr_raw')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="rule_active" class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600">
                        </div>
                        <span class="ml-3 text-sm font-medium text-slate-700">Rule Active</span>
                    </label>

                    <div class="flex gap-3">
                        <button type="button" wire:click="$set('showRuleModal', false)"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-500 transition-all hover:scale-105 active:scale-95">
                            Save Rule
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Step Modal --}}
    <x-modal wire:model="showStepModal" maxWidth="md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-slate-900 mb-6">
                {{ $editingStepId ? 'Edit Flow Step' : 'Add Flow Step' }}
            </h2>
            <form wire:submit.prevent="saveStep" class="space-y-4">

                <div class="grid grid-cols-2 gap-4">
                    {{-- Sequence --}}
                    <div class="relative">
                        <input type="number" wire:model.defer="step_sequence" id="step_sequence"
                            class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                            placeholder=" ">
                        <label for="step_sequence"
                            class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:left-4 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                            Sequence <span class="text-red-500">*</span>
                        </label>
                        @error('step_sequence')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="relative">
                        <select wire:model.defer="step_approver_type" id="step_approver_type"
                            class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0 appearance-none">
                            <option value="user">Specific User</option>
                            <option value="role">System Role</option>
                        </select>
                        <label for="step_approver_type"
                            class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:left-4 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                            Approver Type
                        </label>
                    </div>
                </div>

                {{-- Approver ID --}}
                <div class="relative">
                    <input type="number" wire:model.defer="step_approver_id" id="step_approver_id"
                        class="peer block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-0"
                        placeholder=" ">
                    <label for="step_approver_id"
                        class="absolute left-4 top-2 z-10 origin-[0] -translate-y-6 scale-75 transform text-xs text-slate-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:left-4 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:text-blue-600">
                        Target ID (User/Role ID) <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-[10px] text-slate-400">Enter the ID of the User or Role table.</p>
                    @error('step_approver_id')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center gap-6 py-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="step_final" class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500">
                        </div>
                        <span class="ml-2 text-sm text-slate-600">Final Step?</span>
                    </label>

                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="step_parallel_group" class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-500">
                        </div>
                        <span class="ml-2 text-sm text-slate-600">Parallel?</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showStepModal', false)"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-500 transition-all hover:scale-105 active:scale-95">
                        {{ $editingStepId ? 'Save Changes' : 'Add to Flow' }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <style>
        .bg-grid-slate-100 {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='%23f1f5f9'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</div>
