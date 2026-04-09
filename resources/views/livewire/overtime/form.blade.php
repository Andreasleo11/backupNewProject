@section('title', $formId ? "Edit Form Overtime #{$formId}" : 'Create Form Overtime')
@section('page-title', $formId ? "Edit Form Overtime" : 'Create Form Overtime')
@section('page-subtitle', 'Manage Overtime Requests')

<div class="bg-slate-50 min-h-screen pb-20 font-sans" x-data="overtimeForm($wire)" x-on:toast.window="window.dispatchEvent(new CustomEvent('notify', { detail: $event.detail }))">
    
    {{-- ======================================================== MINIMALIST TOP NAV --}}
    <div class="sticky top-0 z-[100] bg-white/80 backdrop-blur-xl border-b border-slate-200/50 px-6 py-3 transition-all">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ $formId ? route('overtime.detail', $formId) : route('overtime.index') }}"
                    class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                    <i class='bx bx-arrow-back text-lg'></i>
                </a>
                <div class="h-4 w-px bg-slate-200"></div>
                <h1 class="text-sm font-black text-slate-900 tracking-tight uppercase">
                    {{ $formId ? "Edit OT-{$formId}" : 'New Overtime Request' }}
                </h1>
            </div>

            <div class="hidden md:flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <span class="h-5 w-5 rounded-full flex items-center justify-center text-[9px] font-black border-2" :class="stage >= 0 ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-200 text-slate-400'">1</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest" :class="stage >= 0 ? 'text-slate-900' : 'text-slate-400'">Context</span>
                </div>
                <i class='bx bx-chevron-right text-slate-300'></i>
                <div class="flex items-center gap-2">
                    <span class="h-5 w-5 rounded-full flex items-center justify-center text-[9px] font-black border-2" :class="stage >= 1 ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-200 text-slate-400'">2</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest" :class="stage >= 1 ? 'text-slate-900' : 'text-slate-400'">Schedule</span>
                </div>
                <i class='bx bx-chevron-right text-slate-300'></i>
                <div class="flex items-center gap-2">
                    <span class="h-5 w-5 rounded-full flex items-center justify-center text-[9px] font-black border-2" :class="stage >= 2 ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-200 text-slate-400'">3</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest" :class="stage >= 2 ? 'text-slate-900' : 'text-slate-400'">Roster</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="h-2 w-2 rounded-full" :class="hasAnyError ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500'"></div>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="submit" class="max-w-6xl mx-auto mt-10 px-6">
        <div class="space-y-8">

            <section class="transition-all duration-500" :class="stage > 0 ? 'opacity-40 grayscale-[0.3]' : ''">
                <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-8 py-8 flex items-center justify-between border-b border-slate-50 cursor-pointer" @click="stage = 0">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg"><i class='bx bx-buildings text-xl'></i></div>
                            <div>
                                <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">1. Assignment Context</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5" x-show="stage === 0">Base department & location</p>
                                <p class="text-[10px] text-indigo-600 font-black uppercase tracking-widest mt-0.5" x-show="stage > 0" x-text="'Branch: ' + branch"></p>
                            </div>
                        </div>
                        <i class='bx bx-chevron-down text-slate-300 text-xl transition-transform' :class="stage === 0 ? 'rotate-180' : ''"></i>
                    </div>

                    <div class="p-8 space-y-8" x-show="stage === 0" x-collapse>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                             <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Working Department</label>
                                @if (! $formId && $canOverrideDept)
                                    <select wire:model.live="dept_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-6 py-4 font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                        <option value="">— Select —</option>
                                        @foreach ($departments as $dept) <option value="{{ $dept->id }}">{{ $dept->name }}</option> @endforeach
                                    </select>
                                @else
                                    <div class="w-full rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 py-4 px-6 font-black text-slate-500">{{ $formId ? ($form->department?->name ?? '—') : (auth()->user()->department?->name ?? '—') }}</div>
                                @endif
                                @error('dept_id') <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-tight"><i class='bx bx-error-circle'></i> {{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Work Location</label>
                                <select wire:model.live="branch" class="w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-6 py-4 font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all {{ $errors->has('branch') ? 'border-rose-300' : '' }}">
                                    <option value="">— Select —</option>
                                    <option value="Jakarta">Jakarta</option>
                                    <option value="Karawang">Karawang</option>
                                </select>
                                @error('branch') <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-tight"><i class='bx bx-error-circle'></i> {{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-end border-t border-slate-50 pt-8">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Session Type</label>
                                <div class="flex p-1 bg-slate-100 rounded-2xl border border-slate-200">
                                    <button type="button" @click="$wire.set('is_after_hour', 1)" class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all" :class="$wire.is_after_hour == 1 ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-400'">After-Hour</button>
                                    <button type="button" @click="$wire.set('is_after_hour', 0)" class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all" :class="$wire.is_after_hour == 0 ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-400'">Standard</button>
                                </div>
                            </div>
                            <div class="flex items-center justify-end" x-data="{ validating: false }">
                                <button type="button" 
                                    @click="validating = true; $wire.validateStage0().then(ok => { validating = false; if(ok) { stage = 1; window.scrollTo({top: 0, behavior: 'smooth'}); } })"
                                    :disabled="validating"
                                    class="h-14 px-10 rounded-2xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-3 disabled:opacity-30 disabled:grayscale">
                                    <span x-show="!validating">Continue to Timing</span>
                                    <span x-show="validating">Verifying...</span>
                                    <i class='bx bx-loader-alt animate-spin text-lg' x-show="validating"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="transition-all duration-500" x-show="stage >= 1" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                     :class="stage > 1 ? 'opacity-40 grayscale-[0.3]' : ''">
                <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-8 py-8 flex items-center justify-between border-b border-slate-50 cursor-pointer" @click="stage = 1">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg"><i class='bx bx-calendar-event text-xl'></i></div>
                            <div>
                                <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">2. Schedule Settings</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Define global timing for this request</p>
                            </div>
                        </div>
                    </div>

                    {{-- STAGE 2: GLOBAL SCHEDULE SETTINGS --}}
                    <div class="p-8 space-y-10" x-show="stage === 1" x-collapse>
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pb-8 border-b border-slate-50">
                             <div class="space-y-3">
                                 <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Overtime Date</label>
                                 <input type="date" wire:model.live="global_overtime_date" 
                                     class="w-full rounded-2xl border px-6 py-4 font-black text-center transition-all {{ $errors->has('global_overtime_date') ? 'border-rose-300 bg-rose-50/30 text-rose-600' : 'border-slate-200 bg-slate-50/50 text-slate-900' }}">
                                 @error('global_overtime_date') <p class="text-[8px] font-black text-rose-500 uppercase tracking-widest text-center">{{ $message }}</p> @enderror
                             </div>
                             <div class="space-y-3">
                                 <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Start Time</label>
                                 <input type="time" wire:model.live="global_start_time" 
                                     class="w-full rounded-2xl border py-4 font-mono font-black text-center text-indigo-600 text-lg shadow-inner transition-all {{ $errors->has('global_start_time') ? 'border-rose-300 ring-4 ring-rose-500/5 bg-rose-50' : 'border-slate-200' }}">
                                 @error('global_start_time') <p class="text-[8px] font-black text-rose-500 uppercase tracking-widest text-center">{{ $message }}</p> @enderror
                             </div>
                             <div class="space-y-3">
                                 <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">End Time</label>
                                 <input type="time" wire:model.live="global_end_time" 
                                     class="w-full rounded-2xl border py-4 font-mono font-black text-center text-indigo-600 text-lg shadow-inner transition-all {{ $errors->has('global_end_time') ? 'border-rose-300 ring-4 ring-rose-500/5 bg-rose-50' : 'border-slate-200' }}">
                                 @error('global_end_time') <p class="text-[8px] font-black text-rose-500 uppercase tracking-widest text-center">{{ $message }}</p> @enderror
                             </div>
                         </div>

                         <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                             <div class="space-y-3">
                                  <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Break Duration (Min)</label>
                                  <input type="number" wire:model.live="global_break" 
                                     class="w-full rounded-2xl border px-6 py-4 font-black transition-all {{ $errors->has('global_break') ? 'border-rose-300 bg-rose-50' : 'border-slate-200 bg-slate-50/50' }}">
                                  @error('global_break') <p class="text-[8px] font-black text-rose-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                             </div>
                             <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Primary Task / Objective</label>
                                <textarea wire:model.live="global_job_desc" rows="5" 
                                    class="w-full rounded-2xl border p-3 font-bold resize-none transition-all {{ $errors->has('global_job_desc') ? 'border-rose-300 bg-rose-50' : 'border-slate-200 bg-slate-50/50' }}" 
                                    placeholder="What is the main goal?"></textarea>
                                @error('global_job_desc') <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest">{{ $message }}</p> @enderror
                             </div>
                         </div>

                        <div class="flex items-center justify-end border-t border-slate-50 pt-8" x-data="{ validating: false }">
                            <button type="button" @click="validating = true; $wire.validateStep1().then(ok => { validating = false; if(ok) { stage = 2; window.scrollTo({top: 0, behavior: 'smooth'}); } })"
                                :disabled="validating"
                                class="h-14 px-10 rounded-2xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:-translate-y-0.5 transition-all flex items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!validating">Continue to Roster</span>
                                <span x-show="validating">Verifying...</span>
                                <i class='bx bx-loader-alt animate-spin text-lg' x-show="validating"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- STAGE 3: THE ROSTER --}}
            <section x-show="stage >= 2" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                @include('livewire.overtime.partials.roster')

                <div x-show="items.length > 0" x-transition x-cloak class="pt-10 flex flex-col items-center gap-6" x-data="{ running: false }">
                    {{-- THE INTEGRITY CHECKER TRIGGER --}}
                    <div x-show="!isIntegrityChecked" x-collapse>
                        <button type="button" 
                            @click="running = true; $wire.runIntegrityCheck().then(() => running = false)"
                            
                            :disabled="running"
                            class="h-20 px-16 rounded-[2.5rem] bg-slate-900 text-white shadow-2xl transition-all flex items-center gap-4 group hover:scale-[1.02] active:scale-95 disabled:opacity-50">
                            <div class="flex flex-col text-left">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] leading-none mb-1">Step 3 of 3</span>
                                <span class="text-lg font-black uppercase tracking-tight leading-none" x-show="!running">Verify & Prepare Submission</span>
                                <span class="text-lg font-black uppercase tracking-tight leading-none" x-show="running">Checking Integrity...</span>
                            </div>
                            <i class='bx bx-shield-quarter text-3xl group-hover:rotate-12 transition-transform' x-show="!running"></i>
                            <i class='bx bx-loader-alt animate-spin text-3xl' x-show="running"></i>
                        </button>
                    </div>

                    {{-- THE READINESS SNAPSHOT (HUMAN-READABLE) --}}
                    <div x-show="Object.keys(integrityResults).length > 0" x-collapse
                        class="w-full max-w-xl bg-white rounded-[2.5rem] border border-slate-200 p-8 shadow-2xl relative overflow-hidden">
                        
                        {{-- STATUS PILL & SUMMARY --}}
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center gap-3 px-6 py-2 rounded-full mb-6 transition-all"
                                :class="isIntegrityChecked ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400'">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="isIntegrityChecked ? 'bg-emerald-400' : 'bg-slate-300'"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3" :class="isIntegrityChecked ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                </span>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em]" x-text="isIntegrityChecked ? 'Readiness Achieved' : 'Verifying Roster...'"></span>
                            </div>

                            <div class="flex flex-col items-center justify-center">
                                <template x-if="isIntegrityChecked">
                                    <div class="text-center animate-in fade-in slide-in-from-top-4 duration-500">
                                        <div class="h-16 w-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-500 shadow-inner">
                                            <i class='bx bx-check-shield text-4xl'></i>
                                        </div>
                                        <p class="text-2xl font-black text-slate-900 leading-none">Ready for Submission</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-3">
                                            All <span class="text-emerald-600 font-black">{{ $this->headcount }}</span> members verified successfully
                                        </p>
                                    </div>
                                </template>

                                <template x-if="!isIntegrityChecked && integrityResults.payroll === 'failed'">
                                    <div class="text-center animate-in fade-in slide-in-from-top-4 duration-500">
                                        <div class="h-16 w-16 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-500 shadow-inner">
                                            <i class='bx bx-error-alt text-4xl'></i>
                                        </div>
                                        <p class="text-2xl font-black text-slate-900 leading-none uppercase tracking-tight">Conflicts Detected</p>
                                        <p class="text-[10px] font-bold text-rose-400 uppercase tracking-[0.2em] mt-3">
                                            Found <span class="text-rose-600 font-black">{{ $this->conflictCount }}</span> critical issues in roster
                                        </p>
                                    </div>
                                </template>

                                <template x-if="!isIntegrityChecked && (integrityResults.payroll === 'loading' || integrityResults.local === 'loading')">
                                    <div class="text-center">
                                        <i class='bx bx-loader-alt animate-spin text-4xl text-slate-200 mb-4'></i>
                                        <p class="text-sm font-black text-slate-300 uppercase tracking-widest">Securing Connection...</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- COLLAPSIBLE TECHNICAL AUDIT --}}
                        <div x-data="{ showLogs: false }" class="border-t border-slate-50 pt-6">
                            <button type="button" @click="showLogs = !showLogs" class="w-full flex items-center justify-between group">
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] group-hover:text-slate-500 transition-colors">Technical Audit Logs</span>
                                <i class='bx transition-transform duration-300' :class="showLogs ? 'bx-chevron-up rotate-180' : 'bx-chevron-down text-slate-300'" ></i>
                            </button>

                            <div x-show="showLogs" x-collapse title="Audit Details" class="mt-6 space-y-3">
                                {{-- STRUCTURAL CHECK --}}
                                <div class="flex items-center justify-between p-4 rounded-2xl transition-all"
                                    :class="integrityResults.structural === 'passed' ? 'bg-emerald-50' : (integrityResults.structural === 'failed' ? 'bg-rose-50' : 'bg-slate-50')">
                                    <div class="flex items-center gap-4">
                                        <i class='bx' :class="integrityResults.structural === 'passed' ? 'bx-check-double text-emerald-500 text-xl' : (integrityResults.structural === 'failed' ? 'bx-error-circle text-rose-500 text-xl' : 'bx-list-check text-slate-400 text-xl')"></i>
                                        <p class="text-[10px] font-black uppercase tracking-tight text-slate-700">Structural integrity</p>
                                    </div>
                                    <span class="text-[8px] font-black uppercase" :class="integrityResults.structural === 'passed' ? 'text-emerald-600' : 'text-slate-400'" x-text="integrityResults.structural"></span>
                                </div>

                                {{-- LOCAL CONFLICT CHECK --}}
                                <div class="flex items-center justify-between p-4 rounded-2xl transition-all"
                                    :class="integrityResults.local === 'passed' ? 'bg-emerald-50' : (integrityResults.local === 'failed' ? 'bg-rose-50' : 'bg-slate-50')">
                                    <div class="flex items-center gap-4">
                                        <i class='bx' :class="integrityResults.local === 'passed' ? 'bx-data text-emerald-500 text-xl' : (integrityResults.local === 'failed' ? 'bx-block text-rose-500 text-xl' : 'bx-data text-slate-400 text-xl')"></i>
                                        <p class="text-[10px] font-black uppercase tracking-tight text-slate-700">Local database conflict</p>
                                    </div>
                                    <span class="text-[8px] font-black uppercase" :class="integrityResults.local === 'passed' ? 'text-emerald-600' : 'text-slate-400'" x-text="integrityResults.local"></span>
                                </div>

                                {{-- PAYROLL GUARD --}}
                                <div class="flex items-center justify-between p-4 rounded-2xl transition-all"
                                    :class="integrityResults.payroll === 'passed' ? 'bg-emerald-50' : (integrityResults.payroll === 'failed' ? 'bg-rose-50' : 'bg-slate-50')">
                                    <div class="flex items-center gap-4">
                                        <i class='bx' :class="integrityResults.payroll === 'passed' ? 'bx-network-chart text-emerald-500 text-xl' : (integrityResults.payroll === 'failed' ? 'bx-bolt-circle text-rose-500 text-xl' : 'bx-network-chart text-slate-400 text-xl')"></i>
                                        <p class="text-[10px] font-black uppercase tracking-tight text-slate-700">JPayroll live verify</p>
                                    </div>
                                    <span class="text-[8px] font-black uppercase" :class="integrityResults.payroll === 'passed' ? 'text-emerald-600' : 'text-slate-400'" x-text="integrityResults.payroll"></span>
                                </div>
                            </div>
                        </div>

                        {{-- THE FINAL SEAL --}}
                        <div x-show="isIntegrityChecked" x-collapse class="mt-10">
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full h-20 rounded-3xl bg-indigo-600 text-white shadow-2xl shadow-indigo-100/50 transition-all flex items-center justify-center gap-4 group hover:scale-[1.01] active:scale-95">
                                <div class="flex flex-col text-left">
                                    <span class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.3em] leading-none mb-1">Authenticated Snapshot</span>
                                    <span class="text-lg font-black uppercase tracking-tight leading-none" wire:loading.remove wire:target="submit">{{ $formId ? 'Update Record' : 'Confirm & Submit' }}</span>
                                    <span class="text-lg font-black uppercase tracking-tight leading-none" wire:loading wire:target="submit">Finalizing...</span>
                                </div>
                                <i class='bx bx-check-shield text-3xl text-indigo-300 group-hover:scale-110 transition-transform' wire:loading.remove></i>
                                <i class='bx bx-loader-alt animate-spin text-3xl' wire:loading></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </form>
    
    <datalist id="job-suggestions">
        @foreach($recentJobs as $job) <option value="{{ $job }}"></option> @endforeach
    </datalist>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('overtimeForm', ($wire) => ({
        items:     $wire.entangle('items', true),
        employees: $wire.entangle('employees', true),
        cardView:  false,
        stage:     0, // 0: Context, 1: Schedule, 2: Roster
        branch:    $wire.entangle('branch', true),
        dept_id:   $wire.entangle('dept_id', true),
        errors:    @entangle('validationErrors'),
        
        isIntegrityChecked: $wire.entangle('isIntegrityChecked', true),
        integrityResults:   $wire.entangle('integrityResults', true),

        init() {
            window.addEventListener('excel-imported', () => {
                this.stage = 2;
                this.excel = false;
                this.excel_file_loaded = true;
            });
            if(this.dept_id && this.branch) {
                if(this.items.length > 0) this.stage = 2;
                else this.stage = 1;
            }
        },

        global_date:  $wire.entangle('global_overtime_date', true),
        global_start: $wire.entangle('global_start_time', true),
        global_end_d: $wire.entangle('global_end_date', true),
        global_end_t: $wire.entangle('global_end_time', true),
        global_break: $wire.entangle('global_break', true),

        syncToGlobal(index) {
            this.items[index].overtime_date = this.global_date;
            this.items[index].start_date    = this.global_date;
            this.items[index].end_date      = this.global_date;
            this.items[index].start_time    = this.global_start;
            this.items[index].end_time      = this.global_end_t;
            this.items[index].break         = this.global_break;
        },

        addRow() { $wire.addEmptyRow(); },
        removeRow(index) { if(confirm('Remove?')) $wire.removeRow(index); },
        pick(index, emp) { this.items[index].nik = emp.nik; this.items[index].name = emp.name; },

        filteredBy(prop, q) {
            const usedNiks = this.items.map(i => i.nik).filter(n => n);
            const available = this.employees.filter(e => !usedNiks.includes(e.nik));
            if(!q) return available.slice(0, 10);
            const lowerQ = q.toLowerCase();
            return available.filter(e => e.name.toLowerCase().includes(lowerQ) || e.nik.toLowerCase().includes(lowerQ)).slice(0, 15);
        },

        calculateNet(row) {
            if(!row.start_time || !row.end_time) return '0h';
            try {
                const s = new Date(`2000-01-01T${row.start_time}`);
                const e = new Date(`2000-01-01T${row.end_time}`);
                let diff = (e - s) / 60000;
                if(diff < 0) diff += 1440;
                const net = diff - (parseInt(row.break) || 0);
                if(net <= 0) return '0h';
                const hours = Math.floor(net / 60);
                const mins = net % 60;
                return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
            } catch(e) { return '0h'; }
        },

        hasError(index, field) { return !!this.errors[`items.${index}.${field}`]; },
        getError(index, field) { 
            const e = this.errors[`items.${index}.${field}`]; 
            return Array.isArray(e) ? e[0] : (e || ''); 
        },
        hasTimeError(index) {
            const fields = ['overtime_date', 'start_date', 'start_time', 'end_date', 'end_time', 'break'];
            return fields.some(f => !!this.errors[`items.${index}.${f}`]);
        },

        get hasAnyError() { return Object.keys(this.errors).length > 0; }
    }));
});
</script>
@endpush
