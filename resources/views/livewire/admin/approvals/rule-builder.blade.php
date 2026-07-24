<div class="w-full">
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-200">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                {{ $this->rule->name }} 
                <span class="px-2 py-0.5 rounded text-xs font-bold bg-indigo-100 text-indigo-700 uppercase">v{{ $this->rule->version_number }}</span>
                @if(!$this->rule->is_current)
                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 uppercase">Legacy Version</span>
                @endif
            </h2>
            <p class="text-sm text-slate-500 mt-1">Configure workflow rules and step sequences.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.approval-rules.index') }}" class="px-3 py-1.5 rounded-md text-sm font-medium text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 transition-colors">
                Back to Index
            </a>
            @if(!$this->rule->is_current)
                <div class="px-3 py-1.5 rounded-md text-sm font-medium bg-amber-50 text-amber-800 border border-amber-200">
                    <x-bx-error-circle class="mr-1" /> Cannot edit legacy version
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        {{-- Left Column: Rule Settings --}}
        <div class="xl:col-span-4 space-y-6">
            <form wire:submit.prevent="updateRule" class="bg-white rounded-md border border-slate-200 shadow-sm p-5 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Rule Settings</h3>
                </div>

                <fieldset @if(!$this->rule->is_current) disabled @endif class="space-y-4">
                    {{-- Target Model --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-500">Target Form / Module</label>
                        <select wire:model.defer="rule_model_type" class="block w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors">
                            <option value="">— Select a registered form —</option>
                            @foreach($availableModules as $className => $label)
                                <option value="{{ $className }}">{{ $label }} ({{ class_basename($className) }})</option>
                            @endforeach
                        </select>
                        @error('rule_model_type')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Name --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-500">Rule Name</label>
                        <input type="text" wire:model.defer="rule_name" class="block w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors">
                        @error('rule_name')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        {{-- Code --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-500">Unique Code</label>
                            <input type="text" wire:model.defer="rule_code" class="block w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors">
                            @error('rule_code')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Priority --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-500">Priority</label>
                            <input type="number" wire:model.defer="rule_priority" class="block w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors">
                            @error('rule_priority')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Active Toggle --}}
                    <div class="flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.defer="rule_active" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900"></div>
                            <span class="ml-3 text-sm font-medium text-slate-700">Rule Active</span>
                        </label>
                    </div>

                    {{-- Condition Builder --}}
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-500">Trigger Conditions</label>
                            <button type="button" wire:click="addCondition" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition-colors">+ Add Condition</button>
                        </div>
                        
                        @if(count($ruleConditions) === 0)
                            <div class="text-xs text-slate-500 italic p-3 bg-slate-50 rounded-md border border-slate-200 border-dashed text-center">
                                No conditions set. This rule will match ALL requests for the selected form.
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach($ruleConditions as $index => $condition)
                                    <div class="flex items-start gap-2 relative group">
                                        <div class="flex-1 space-y-1">
                                            <div class="flex gap-2">
                                                <input type="text" wire:model.defer="ruleConditions.{{ $index }}.field" placeholder="Field (e.g. status)" class="w-1/3 rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950">
                                                <select wire:model.defer="ruleConditions.{{ $index }}.operator" class="w-1/3 rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950">
                                                    <option value="==">Equals</option>
                                                    <option value="in">In List</option>
                                                    <option value="not_in">Not In List</option>
                                                    <option value=">">Greater Than</option>
                                                    <option value=">=">Greater Than or Equal</option>
                                                    <option value="<=">Less Than or Equal</option>
                                                    <option value="any">Contains Any Tag</option>
                                                </select>
                                                <input type="text" wire:model.defer="ruleConditions.{{ $index }}.value" placeholder="Value (comma-separated for lists)" class="w-1/3 rounded-md border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950">
                                            </div>
                                            @error('ruleConditions.'.$index.'.field')<p class="text-[10px] text-red-500">{{ $message }}</p>@enderror
                                        </div>
                                        <button type="button" wire:click="removeCondition({{ $index }})" class="p-1.5 text-slate-400 hover:text-red-600 transition-colors bg-white rounded-md border border-slate-200 shadow-sm hover:border-red-200 hover:bg-red-50" title="Remove Condition">
                                            <x-bx-x class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-[10px] text-slate-400 leading-tight">Note: 'Greater/Less Than' operators only work on the <strong>amount</strong> field. 'Contains Any Tag' only works on the <strong>tags</strong> field.</p>
                        @endif
                    </div>

                    <div class="space-y-1 pt-2">
                        <label class="block text-xs font-semibold text-slate-500">Version Notes (Optional)</label>
                        <input type="text" wire:model.defer="version_notes" placeholder="e.g. Added regional director step" class="block w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-950 transition-colors">
                    </div>
                </fieldset>

                @if($this->rule->is_current)
                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" wire:target="updateRule" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-slate-50 hover:bg-slate-900/90 transition-colors disabled:opacity-50 shadow-sm w-full justify-center">
                            <span wire:loading.remove wire:target="updateRule">Update Rule Settings</span>
                            <span wire:loading wire:target="updateRule"><x-bx-loader-alt class="animate-spin mr-1" /> Updating...</span>
                        </button>
                    </div>
                @endif
            </form>

            {{-- Version History Collapsible --}}
            <div class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden mt-6">
                <button wire:click="$toggle('showVersionHistory')" class="w-full p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <span class="text-sm font-bold text-slate-800">Version History</span>
                    <span class="text-xs text-slate-500">{{ $this->versionHistory->count() }} version(s)</span>
                </button>

                @if($showVersionHistory)
                <div class="border-t border-slate-100 divide-y divide-slate-50">
                    @foreach($this->versionHistory as $ver)
                    <div class="p-3 flex items-center justify-between {{ $ver->id === $ruleId ? 'bg-indigo-50' : '' }}">
                        <div>
                            <span class="font-mono text-xs font-bold">v{{ $ver->version_number }}</span>
                            @if($ver->is_current)
                                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-1 rounded">Current</span>
                            @endif
                            @if($ver->trashed())
                                <span class="text-[10px] bg-red-100 text-red-600 px-1 rounded">Deleted</span>
                            @endif
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $ver->created_at->diffForHumans() }}
                                {{ $ver->version_notes ? '— '.$ver->version_notes : '' }}
                            </div>
                        </div>
                        @if($ver->id !== $ruleId)
                            <button type="button" wire:click="switchToVersion({{ $ver->id }})" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                View
                            </button>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Approval Steps --}}
        <div class="xl:col-span-8">
            <div class="bg-white rounded-md border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full min-h-[500px]">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Approval Steps Pipeline</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Define the sequence of approvals required.</p>
                    </div>
                    @if($this->rule->is_current)
                        <button wire:click="openCreateStep" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium text-slate-50 bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                            <x-bx-plus class="" /> Add Step
                        </button>
                    @endif
                </div>

                <div class="p-5 flex-1 bg-slate-50/30">
                    @if($this->steps->isEmpty())
                        <div class="h-full flex flex-col items-center justify-center text-center p-8 border-2 border-dashed border-slate-200 rounded-lg bg-white">
                            <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3 text-slate-400">
                                <x-bx-git-branch class="w-6 h-6" />
                            </div>
                            <h4 class="text-sm font-medium text-slate-900 mb-1">No workflow steps defined</h4>
                            <p class="text-xs text-slate-500 mb-4 max-w-sm">This rule will automatically approve targets without any human intervention. Add steps to require approvals.</p>
                            @if($this->rule->is_current)
                                <button wire:click="openCreateStep" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                    + Add First Step
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="space-y-3 relative">
                            {{-- Visual line connecting steps --}}
                            <div class="absolute left-[1.375rem] top-6 bottom-6 w-0.5 bg-slate-200 z-0"></div>

                            @foreach($this->steps as $index => $step)
                                <div class="relative z-10 flex items-start gap-4">
                                    {{-- Node Bubble --}}
                                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-white border-2 {{ $step->final ? 'border-emerald-500 text-emerald-600' : 'border-indigo-500 text-indigo-600' }} flex items-center justify-center font-bold text-sm shadow-sm mt-1">
                                        {{ $step->sequence }}
                                    </div>
                                    
                                    {{-- Step Card --}}
                                    <div class="flex-1 bg-white border border-slate-200 rounded-lg p-4 shadow-sm hover:border-slate-300 transition-colors group">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="inline-flex items-center rounded text-[10px] font-bold px-1.5 py-0.5 uppercase tracking-wider bg-slate-100 text-slate-600">
                                                        {{ $step->approver_type }}
                                                    </span>
                                                    @if ($step->parallel_group)
                                                        <span class="inline-flex items-center rounded text-[10px] font-bold px-1.5 py-0.5 uppercase tracking-wider bg-purple-100 text-purple-700" title="All steps with the same sequence run in parallel">
                                                            Parallel
                                                        </span>
                                                    @endif
                                                    @if ($step->final)
                                                        <span class="inline-flex items-center rounded text-[10px] font-bold px-1.5 py-0.5 uppercase tracking-wider bg-emerald-100 text-emerald-700" title="Final approval required">
                                                            Final
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-sm font-semibold text-slate-900 mt-1.5">
                                                    @if ($step->approver_type === 'role')
                                                        <x-bx-shield-quarter class="text-slate-400 mr-1" />
                                                        {{ $step->role?->name ?? 'Unknown Role (ID: ' . $step->approver_id . ')' }}
                                                    @else
                                                        <x-bx-user class="text-slate-400 mr-1" />
                                                        {{ $step->user?->name ?? 'Unknown User (ID: ' . $step->approver_id . ')' }}
                                                        @if ($step->user?->email)
                                                            <span class="text-xs font-normal text-slate-500 ml-1">({{ $step->user->email }})</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            @if($this->rule->is_current)
                                                <div class="flex items-center gap-1 opacity-100 transition-opacity">
                                                    <button wire:click="openEditStep({{ $step->id }})" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors" title="Edit Step">
                                                        <x-bx-pencil class="" />
                                                    </button>
                                                    <div x-data="{ confirmingDelete: false }" class="relative">
                                                        <button x-show="!confirmingDelete" @click="confirmingDelete = true" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete Step">
                                                            <x-bx-trash class="" />
                                                        </button>
                                                        <div x-show="confirmingDelete" @click.away="confirmingDelete = false" class="absolute right-0 top-0 bg-white border border-red-200 shadow-lg rounded-md p-2 flex items-center gap-2 z-20 min-w-max">
                                                            <span class="text-xs font-medium text-red-600 whitespace-nowrap">Delete step?</span>
                                                            <button wire:click="deleteStep({{ $step->id }})" class="px-2 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">Yes</button>
                                                            <button @click="confirmingDelete = false" class="px-2 py-1 bg-slate-100 text-slate-600 text-xs font-medium rounded hover:bg-slate-200">No</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- End Node Bubble --}}
                            <div class="relative z-10 flex items-start gap-4">
                                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-slate-100 border-2 border-slate-200 text-slate-400 flex items-center justify-center mt-1 shadow-sm">
                                    <x-bx-flag class="w-5 h-5" />
                                </div>
                                <div class="flex-1 flex items-center h-11 mt-1">
                                    <span class="text-xs font-medium text-slate-500 uppercase tracking-widest">Pipeline End</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}

    {{-- Step Modal --}}
    <x-modal wire:model="showStepModal" maxWidth="sm">
        <div class="p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-5">
                {{ $editingStepId ? 'Edit Flow Step' : 'Add Flow Step' }}
            </h2>
            <form wire:submit.prevent="saveStep" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    {{-- Sequence --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-500">Sequence</label>
                        <input type="number" wire:model.defer="step_sequence" class="block w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                        @error('step_sequence')<span class="text-xs text-red-500">{{ $message }}</span>@enderror
                    </div>

                    {{-- Type --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-500">Approver Type</label>
                        <select wire:model.defer="step_approver_type" class="block w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                            <option value="user">Specific User</option>
                            <option value="role">System Role</option>
                        </select>
                    </div>
                </div>

                {{-- Approver Search --}}
                <div class="space-y-1 relative" x-data="{ open: false }" @click.away="open = false">
                    <label class="block text-xs font-semibold text-slate-500">
                        {{ $step_approver_type === 'user' ? 'Select User' : 'Select Role' }}
                    </label>
                    <input type="text"
                        wire:model.live.debounce.300ms="approverSearch"
                        @focus="open = true"
                        @input="open = true"
                        placeholder="Type to search (min 2 chars)..."
                        class="block w-full rounded-md border border-slate-200 bg-transparent px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-slate-950">
                    
                    {{-- Dropdown results --}}
                    @if(count($approverResults) > 0)
                    <div x-show="open" class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                        @foreach($approverResults as $result)
                            <button type="button" wire:click="selectApprover({{ $result['id'] }}, '{{ addslashes($result['name']) }}')"
                                @click="open = false" class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 border-b border-slate-100 last:border-0">
                                <div class="font-medium text-slate-900">{{ $result['name'] }}</div>
                                @if(isset($result['email']))
                                    <div class="text-xs text-slate-500">{{ $result['email'] }}</div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    @endif

                    <input type="hidden" wire:model="step_approver_id" />
                    @error('step_approver_id')<span class="text-xs text-red-500">Please select a valid user/role from the dropdown.</span>@enderror
                </div>

                <div class="flex flex-col gap-3 py-3 border-y border-slate-100 my-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="step_final" class="sr-only peer">
                        <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-slate-700">Final Step?</span>
                            <span class="block text-[10px] text-slate-500">Must be approved regardless of previous sequence approvals.</span>
                        </div>
                    </label>

                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="step_parallel_group" class="sr-only peer">
                        <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-500"></div>
                        <div class="ml-3">
                            <span class="block text-sm font-medium text-slate-700">Parallel Step?</span>
                            <span class="block text-[10px] text-slate-500">Runs simultaneously with other parallel steps of the same sequence.</span>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showStepModal', false)" class="px-4 py-2 rounded-md border border-slate-200 text-slate-900 text-sm font-medium hover:bg-slate-100 transition-colors">Cancel</button>
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-slate-900 text-slate-50 text-sm font-medium hover:bg-slate-900/90 transition-colors disabled:opacity-50" wire:loading.attr="disabled" wire:target="saveStep">
                        <x-bx-loader-alt class="animate-spin mr-1" wire:loading wire:target="saveStep" />
                        <span wire:loading.remove wire:target="saveStep">{{ $editingStepId ? 'Save Changes' : 'Add to Pipeline' }}</span>
                        <span wire:loading wire:target="saveStep">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Version Warning Modal --}}
    <x-modal wire:model="showVersionWarningModal" maxWidth="sm">
        <div class="p-6">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 mb-4">
                <x-bx-error class="w-6 h-6 text-amber-600" />
            </div>
            <h3 class="text-lg font-bold text-center text-slate-900 mb-2">Active Requests Running</h3>
            <p class="text-sm text-center text-slate-500 mb-6">
                This rule is currently being used by <strong>{{ $activeRequestsCount }}</strong> active approval request(s). 
                <br><br>
                Updating this rule will create a <strong>new version</strong>. The ongoing requests will continue to use the old version's pipeline.
            </p>
            <div class="flex flex-col gap-2">
                <button wire:click="confirmForceNewVersion" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md bg-amber-600 text-white text-sm font-medium hover:bg-amber-700 transition-colors shadow-sm">
                    Create New Version
                </button>
                <button wire:click="$set('showVersionWarningModal', false)" class="w-full px-4 py-2 rounded-md border border-slate-200 text-slate-900 text-sm font-medium hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </x-modal>
</div>
