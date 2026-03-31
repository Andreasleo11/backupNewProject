<div>
    <div class="space-y-6">
        {{-- Global Settings Card --}}
        <div class="glass-card p-6 shadow-sm border border-slate-200/60 overflow-hidden relative">
            <div class="flex items-center gap-4 mb-6">
                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-sm border border-blue-100">
                    <i class='bx bx-globe text-2xl'></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Global Preference</h3>
                    <p class="text-xs text-slate-500 font-medium">This serves as the default for all modules unless overridden below.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button type="button" 
                    wire:click="$set('global_mode', 'immediate')"
                    class="relative flex flex-col items-center gap-3 p-5 rounded-2xl border-2 transition-all duration-300 {{ $global_mode === 'immediate' ? 'border-blue-500 bg-blue-50/50 ring-4 ring-blue-500/10' : 'border-slate-100 bg-slate-50/50 hover:border-slate-200' }}">
                    <div class="h-10 w-10 rounded-full flex items-center justify-center {{ $global_mode === 'immediate' ? 'bg-blue-500 text-white shadow-lg shadow-blue-200' : 'bg-slate-200 text-slate-500 font-bold' }}">
                        <i class='bx bx-bolt-circle text-xl'></i>
                    </div>
                    <div class="text-center">
                        <span class="block text-sm font-bold text-slate-900 uppercase tracking-tight">Immediate</span>
                        <span class="text-[10px] text-slate-500 leading-tight block mt-1">Receive emails as soon as action is required</span>
                    </div>
                    @if($global_mode === 'immediate')
                        <div class="absolute top-3 right-3 text-blue-500">
                            <i class='bx bxs-check-circle text-lg'></i>
                        </div>
                    @endif
                </button>

                <button type="button" 
                    wire:click="$set('global_mode', 'daily_summary')"
                    class="relative flex flex-col items-center gap-3 p-5 rounded-2xl border-2 transition-all duration-300 {{ $global_mode === 'daily_summary' ? 'border-amber-500 bg-amber-50/50 ring-4 ring-amber-500/10' : 'border-slate-100 bg-slate-50/50 hover:border-slate-200' }}">
                    <div class="h-10 w-10 rounded-full flex items-center justify-center {{ $global_mode === 'daily_summary' ? 'bg-amber-500 text-white shadow-lg shadow-amber-200' : 'bg-slate-200 text-slate-500' }}">
                        <i class='bx bx-calendar-star text-xl'></i>
                    </div>
                    <div class="text-center">
                        <span class="block text-sm font-bold text-slate-900 uppercase tracking-tight">Daily Summary</span>
                        <span class="text-[10px] text-slate-500 leading-tight block mt-1">Consolidated morning digest of all pending tasks</span>
                    </div>
                    @if($global_mode === 'daily_summary')
                        <div class="absolute top-3 right-3 text-amber-500">
                            <i class='bx bxs-check-circle text-lg'></i>
                        </div>
                    @endif
                </button>

                <button type="button" 
                    wire:click="$set('global_mode', 'none')"
                    class="relative flex flex-col items-center gap-3 p-5 rounded-2xl border-2 transition-all duration-300 {{ $global_mode === 'none' ? 'border-rose-500 bg-rose-50/50 ring-4 ring-rose-500/10' : 'border-slate-100 bg-slate-50/50 hover:border-slate-200' }}">
                    <div class="h-10 w-10 rounded-full flex items-center justify-center {{ $global_mode === 'none' ? 'bg-rose-500 text-white shadow-lg shadow-rose-200' : 'bg-slate-200 text-slate-500' }}">
                        <i class='bx bx-bell-off text-xl'></i>
                    </div>
                    <div class="text-center">
                        <span class="block text-sm font-bold text-slate-900 uppercase tracking-tight">No Emails</span>
                        <span class="text-[10px] text-slate-500 leading-tight block mt-1">Only view notifications via dashboard bell icon</span>
                    </div>
                    @if($global_mode === 'none')
                        <div class="absolute top-3 right-3 text-rose-500">
                            <i class='bx bxs-check-circle text-lg'></i>
                        </div>
                    @endif
                </button>
            </div>
        </div>

        {{-- Per-Module Overrides Card --}}
        <div class="glass-card shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="px-6 py-6 border-b border-slate-100 bg-slate-50/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center border border-violet-100">
                            <i class='bx bx-category text-xl'></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 leading-tight">Module Overrides</h3>
                            <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Customize per business area</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-slate-100 text-[10px] font-bold text-slate-500 rounded-full uppercase tracking-widest border border-slate-200">Dynamic List</span>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($available_modules as $class => $label)
                    <div class="p-6 transition-colors hover:bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                                <i class='bx bx-file text-xl'></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">{{ $label }}</h4>
                                <p class="text-[10px] font-medium text-slate-400 break-all opacity-60">{{ $class }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <select wire:model.defer="module_preferences.{{ $class }}" 
                                class="text-[11px] font-bold text-slate-700 bg-white border-slate-200 rounded-xl px-4 py-2 hover:border-blue-400 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                                <option value="">(Inherit Global Default)</option>
                                <option value="immediate">Immediate Email</option>
                                <option value="daily_summary">Daily Summary</option>
                                <option value="none">No Emails</option>
                            </select>
                        </div>
                    </div>
                @endforeach
                
                @if(empty($available_modules))
                    <div class="p-12 text-center">
                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-400 mb-4">
                            <i class='bx bx-search text-3xl'></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">No Modules Detected</h4>
                        <p class="text-xs text-slate-500 mt-2">No classes implementing the Approvable interface were found.</p>
                    </div>
                @endif
            </div>

            {{-- Footer Action --}}
            <div class="px-6 py-6 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-blue-600">
                    <i class='bx bx-info-circle text-lg'></i>
                    <span class="text-[11px] font-bold">Preferences are saved instantly when clicking the Save button.</span>
                </div>
                <button wire:click="save" wire:loading.attr="disabled"
                    class="px-8 h-12 bg-gradient-to-r from-blue-600 to-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-100 hover:shadow-blue-200 hover:-translate-y-0.5 transition-all active:scale-95 flex items-center gap-3">
                    <span wire:loading.remove>Save Changes</span>
                    <span wire:loading>Processing...</span>
                    <i class='bx bx-save text-lg' wire:loading.remove></i>
                </button>
            </div>
        </div>
    </div>
</div>
