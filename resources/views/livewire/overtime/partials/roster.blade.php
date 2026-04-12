  {{-- Roster Area --}}
 <div class="space-y-6" x-data="{
     showOverride: {},
     formatDateRange(startDate, endDate) {
         if (!startDate || !endDate) return '';
         if (startDate === endDate) return startDate;
         return startDate + ' → ' + endDate;
     }
 }">
    
    {{-- UNIFIED ROSTER TOOLBAR --}}
    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg"><i class='bx bx-group text-xl'></i></div>
            <div>
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-tight">3. Employee Roster</h2>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5" x-text="items.length + ' Members added'"></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
             <button type="button" @click="$wire.set('showBulkTray', !$wire.showBulkTray)" 
                class="h-10 px-6 rounded-xl border-2 font-black text-[9px] uppercase tracking-widest transition-all flex items-center gap-2 group"
                :class="$wire.showBulkTray ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white border-slate-100 text-slate-400 hover:border-indigo-100 hover:text-indigo-600'">
                <i class='bx' :class="$wire.showBulkTray ? 'bx-chevron-up' : 'bx-layer-plus'"></i> 
                Bulk Operations
             </button>
             <button type="button" @click="addRow()" class="h-10 px-6 rounded-xl bg-slate-900 text-white text-[9px] font-black hover:bg-slate-800 transition-all uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-slate-200">
                <i class='bx bx-plus'></i> Add Member
             </button>
        </div>
    </div>

    {{-- BULK UTILITY TRAY --}}
    <div x-show="$wire.showBulkTray" x-collapse x-cloak>
        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-200 shadow-2xl shadow-indigo-100/50">
            <div class="max-w-4xl mx-auto">
                {{-- HEADER --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Direct Multi-Selection</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Click to instantly add or remove members</p>
                    </div>
                </div>
                
                {{-- SEARCH --}}
                <div class="relative" x-data="{ q: '' }">
                    <div class="relative mb-6">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class='bx bx-search text-slate-400 text-lg'></i>
                        </div>
                        <input type="text" x-model="q" placeholder="Search by name or NIK..."
                            class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-3 pl-12 pr-6 text-sm font-bold text-slate-900 focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500/20 placeholder-slate-300 transition-all">
                    </div>

                    {{-- FILTERED GRID --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        <template x-for="emp in employees.filter(e => (!q || e.name.toLowerCase().includes(q.toLowerCase()) || e.nik.includes(q)))" :key="emp.nik">
                            <button type="button" @click="$wire.toggleEmployee(emp.nik)"
                                wire:loading.class="opacity-50 pointer-events-none" wire:target="toggleEmployee"
                                class="group relative flex items-center gap-4 p-4 rounded-2xl border-2 transition-all text-left"
                                :class="items.some(i => i.nik === emp.nik) ? 'border-indigo-600 bg-indigo-50/50 shadow-sm' : 'border-slate-50 hover:border-slate-200 hover:bg-slate-50/50'">
                                
                                <div class="relative flex-shrink-0">
                                    <div class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all"
                                        :class="items.some(i => i.nik === emp.nik) ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-200 bg-white group-hover:border-slate-300'">
                                        <i class='bx bx-check text-xs' x-show="items.some(i => i.nik === emp.nik)" wire:loading.remove wire:target="toggleEmployee"></i>
                                        <i class='bx bx-loader-alt animate-spin text-[10px]' wire:loading wire:target="toggleEmployee"></i>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-black text-slate-900 truncate" x-text="emp.name"></p>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <p class="text-[9px] font-mono font-bold text-slate-400 uppercase tracking-widest" x-text="emp.nik"></p>
                                        <template x-if="items.some(i => i.nik === emp.nik)">
                                            <span class="text-[7px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-100 px-1 py-0.5 rounded">In Roster</span>
                                        </template>
                                    </div>
                                </div>
                                <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-400 group-hover:bg-white transition-all transition-transform group-active:scale-95">
                                    <i class='bx bx-user'></i>
                                </div>
                            </button>
                        </template>

                        {{-- EMPTY STATE FOR FILTER --}}
                        <template x-if="employees.filter(e => (!q || e.name.toLowerCase().includes(q.toLowerCase()) || e.nik.includes(q))).length === 0">
                            <div class="col-span-full py-20 text-center">
                                <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300"><i class='bx bx-search-alt text-3xl'></i></div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">No matching employees found</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- THE ROSTER CONTENT --}}
    <div class="relative">
        {{-- QUICK START HERO (EMPTY STATE DISCOVERY) --}}
        <template x-if="items.length === 0">
            <div class="bg-white rounded-[3rem] border-4 border-dashed border-slate-100 p-16 text-center animate-in fade-in zoom-in duration-500">
                <div class="h-24 w-24 bg-indigo-50 rounded-3xl flex items-center justify-center mx-auto mb-8 text-indigo-600 shadow-inner">
                    <i class='bx bxs-group-plus text-5xl'></i>
                </div>
                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">Build Your Roster</h3>
                <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mb-12 max-w-sm mx-auto leading-relaxed">Choose an entry method to start your overtime request</p>
                
                <div class="flex flex-col md:flex-row items-center justify-center gap-6">
                    {{-- BULK PATH --}}
                    <button type="button" @click="$wire.set('showBulkTray', true)" 
                        class="group relative h-20 px-10 rounded-3xl bg-indigo-600 text-white shadow-2xl shadow-indigo-200 transition-all hover:scale-[1.03] active:scale-95 flex items-center gap-4">
                        <span class="absolute -top-2 -right-2 flex h-6 w-6">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-6 w-6 bg-indigo-500 border-2 border-white flex items-center justify-center text-[10px] font-black">!</span>
                        </span>
                        <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl group-hover:rotate-12 transition-transform">
                            <i class='bx bx-layer-plus'></i>
                        </div>
                        <div class="text-left">
                            <p class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em] leading-none mb-1">Recommended</p>
                            <p class="text-base font-black uppercase tracking-tight leading-none">Select from Directory</p>
                        </div>
                    </button>

                    <div class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">OR</div>

                    {{-- MANUAL PATH --}}
                    <button type="button" @click="addRow()" 
                        class="h-20 px-10 rounded-3xl bg-slate-900 text-white shadow-2xl shadow-slate-200 transition-all hover:scale-[1.03] active:scale-95 flex items-center gap-4 group">
                        <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl group-hover:translate-x-1 transition-transform">
                            <i class='bx bx-plus'></i>
                        </div>
                        <div class="text-left">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] leading-none mb-1">Standard</p>
                            <p class="text-base font-black uppercase tracking-tight leading-none">Add Member Manually</p>
                        </div>
                    </button>
                </div>
            </div>
        </template>

        {{-- THE TABLE --}}
        <div x-show="items.length > 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-visible">
        <div class="overflow-visible">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/50">
                         <th class="px-8 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 w-16">#</th>
                         <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 w-64">Employee</th>
                         <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 min-w-[200px]">Specific Task</th>
                         <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 w-44 text-center">Timing</th>
                         <th class="px-4 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 w-24 text-center">Status</th>
                         <th class="px-8 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 w-16"></th>
                     </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(row, index) in items" :key="row.id || ('new-' + index)">
                        <tr class="group hover:bg-slate-50/50 transition-all" x-data="{ open: false, q: '', editingTimes: false }">
                            {{-- Index --}}
                            <td class="px-8 py-6 align-top">
                                <div class="h-7 w-7 rounded-lg flex items-center justify-center text-[10px] font-black" 
                                    :class="row.nik ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-slate-100 text-slate-400'" 
                                    x-text="index + 1"></div>
                            </td>
                        
                            {{-- Identity Column --}}
                            <td class="px-4 py-4 align-top overflow-visible transition-all duration-300"
                                :class="hasError(index,'nik') ? 'bg-rose-50/30' : ''">
                                <div class="flex items-center gap-3 p-2 rounded-2xl transition-all border-2"
                                    :class="hasError(index,'nik') ? 'border-rose-500 bg-rose-50' : (!row.nik ? 'border-amber-100 bg-amber-50/30' : 'border-transparent')">
                                     <div class="h-10 w-10 rounded-xl border flex items-center justify-center text-lg shrink-0 transition-all shadow-sm" 
                                        :class="hasError(index,'nik') ? 'bg-rose-500 border-rose-600 text-white' : (!row.nik ? 'bg-amber-100 border-amber-200 text-amber-600' : 'bg-indigo-50 border-indigo-100 text-indigo-600')">
                                        <i class='bx' :class="hasError(index,'nik') ? 'bx-error-circle' : (!row.nik ? 'bx-user-plus' : 'bx-user')"></i>
                                     </div>
                                     <div class="flex-1 min-w-0 relative" @click.outside="open = false">
                                         <div class="relative">
                                            <input type="text"
                                                class="block w-full text-xs font-black p-0 border-none bg-transparent focus:ring-0 placeholder-slate-300 transition-colors"
                                                :class="hasError(index, 'nik') ? 'text-rose-700' : (row.name && !row.nik ? 'text-amber-700' : 'text-slate-900')"
                                                placeholder="Search Employee..."
                                                x-model.debounce.750ms="row.name"
                                                @focus="open = true" 
                                                @input="q = $event.target.value; open = true;"
                                                @keydown.escape="open = false">
                                            
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[8px] font-mono font-bold uppercase tracking-widest transition-colors" 
                                                    :class="hasError(index, 'nik') ? 'text-rose-500' : (!row.nik ? 'text-amber-500 font-black' : 'text-slate-400')"
                                                    x-text="hasError(index, 'nik') ? getError(index, 'nik') : (row.nik || 'Selection Required')"></span>
                                            </div>
                                         </div>

                                         {{-- DROPDOWN RESULTS --}}
                                         <div x-show="open && filteredBy('name', q).length" x-cloak
                                            class="absolute z-[200] mt-1 max-h-60 w-full min-w-[280px] overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl p-2 ring-1 ring-black/5">
                                            <template x-for="(emp, i) in filteredBy('name', q)" :key="emp.nik">
                                                <div class="cursor-pointer px-4 py-3 rounded-xl flex items-center gap-3 hover:bg-indigo-600 hover:text-white transition-all group/item"
                                                    @click="pick(index, emp); open=false; q=''">
                                                    <div class="h-8 w-8 rounded-lg bg-indigo-50 group-hover/item:bg-white/20 flex items-center justify-center text-indigo-600 group-hover/item:text-white">
                                                        <i class='bx bx-user'></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[11px] font-black truncate" x-text="emp.name"></p>
                                                        <p class="text-[9px] font-mono opacity-50" x-text="emp.nik"></p>
                                                    </div>
                                                </div>
                                            </template>
                                         </div>
                                     </div>
                                </div>
                            </td>

                            {{-- TASK COLUMN --}}
                            <td class="px-4 py-6 align-top">
                                <textarea x-model.debounce.1000ms="row.job_desc" rows="2" 
                                    class="w-full rounded-xl border p-2.5 text-[10px] font-medium resize-none placeholder-slate-300 transition-all leading-relaxed"
                                    :class="hasError(index, 'job_desc') ? 'border-rose-200 bg-rose-50/30 ring-2 ring-rose-500/5' : 'border-slate-100 bg-slate-50/30 focus:bg-white focus:border-indigo-200'"
                                    placeholder="Employee's objective..."></textarea>
                                <template x-if="hasError(index, 'job_desc')">
                                    <p class="text-[7px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1" x-text="getError(index, 'job_desc')"></p>
                                </template>
                            </td>
                        
                             {{-- TIMING CHIP --}}
                             <td class="px-4 py-6 align-top text-center overflow-visible">
                                 <div class="relative inline-block w-full" x-data="{ editingTimes: false }" @click.outside="editingTimes = false">
                                     <button type="button" @click="editingTimes = !editingTimes"
                                         class="inline-flex flex-col items-center justify-center gap-1 rounded-2xl border px-3 py-2 transition-all hover:bg-slate-50 shadow-sm w-full relative"
                                         :class="hasTimeError(index) ? 'border-rose-400 bg-rose-50 ring-4 ring-rose-500/10' : 'border-slate-200 bg-white'">

                                         {{-- DATE RANGE (shown only if different dates or multi-day mode) --}}
                                         <template x-if="row.start_date !== row.end_date || $wire.show_date_override">
                                             <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5" x-text="formatDateRange(row.start_date, row.end_date)"></div>
                                         </template>

                                         <div class="flex items-center gap-1 text-[10px] font-mono font-black transition-colors"
                                             :class="hasTimeError(index) ? 'text-rose-600' : 'text-slate-700'">
                                             <span x-text="row.start_time ? row.start_time.substring(0,5) : '--:--'"></span>
                                             <i class='bx bx-right-arrow-alt text-slate-300 text-[8px]' :class="hasTimeError(index) && 'text-rose-300'"></i>
                                             <span x-text="row.end_time ? row.end_time.substring(0,5) : '--:--'"></span>
                                         </div>

                                         {{-- ERROR DOT --}}
                                         <template x-if="hasTimeError(index)">
                                             <div class="absolute -top-1.5 -right-1.5 h-3 w-3 rounded-full bg-rose-500 border-2 border-white shadow-sm flex items-center justify-center">
                                                 <i class='bx bx-error text-[7px] text-white'></i>
                                             </div>
                                         </template>
                                     </button>
                                    
                                     {{-- TIME POPOVER --}}
                                     <div x-show="editingTimes" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         class="absolute z-[200] top-full mt-1 w-80 right-0 rounded-3xl border border-slate-200 bg-white shadow-2xl p-6 text-left ring-1 ring-black/5">
                                         <div class="flex items-center justify-between mb-4">
                                             <div class="flex items-center gap-2">
                                                 <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest">Time Override</span>
                                                 <template x-if="hasTimeError(index)">
                                                     <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-500 text-[7px] font-black uppercase tracking-tighter">Errors Found</span>
                                                 </template>
                                             </div>
                                             <button type="button" @click="syncToGlobal(index)" class="text-[9px] font-black text-emerald-600 hover:text-emerald-700 uppercase tracking-widest flex items-center gap-1"><i class='bx bx-sync'></i> Sync Global</button>
                                         </div>

                                         {{-- DATE CONTEXT --}}
                                         <template x-if="row.start_date !== row.end_date || $wire.show_date_override">
                                             <div class="mb-4 p-3 rounded-xl bg-indigo-50/50 border border-indigo-100">
                                                 <div class="text-[8px] font-black text-indigo-600 uppercase tracking-widest mb-1">Date Range</div>
                                                 <div class="text-[10px] font-black text-slate-700" x-text="formatDateRange(row.start_date, row.end_date)"></div>
                                             </div>
                                         </template>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[8px] font-black uppercase text-slate-400 mb-1.5 ml-1">Start</label>
                                                <input type="time" x-model="row.start_time" 
                                                    class="w-full rounded-xl py-2.5 px-3 text-xs font-mono font-black text-center transition-all border"
                                                    :class="hasError(index, 'start_time') ? 'border-rose-200 bg-rose-50 text-rose-500' : 'border-slate-200 bg-slate-50'">
                                                <template x-if="hasError(index, 'start_time')">
                                                    <p class="text-[7px] font-black text-rose-500 uppercase mt-1 ml-1" x-text="getError(index, 'start_time')"></p>
                                                </template>
                                            </div>
                                            <div>
                                                <label class="block text-[8px] font-black uppercase text-slate-400 mb-1.5 ml-1">End</label>
                                                <input type="time" x-model="row.end_time" 
                                                    class="w-full rounded-xl py-2.5 px-3 text-xs font-mono font-black text-center transition-all border"
                                                    :class="hasError(index, 'end_time') ? 'border-rose-200 bg-rose-50 text-rose-500' : 'border-slate-200 bg-slate-50'">
                                                <template x-if="hasError(index, 'end_time')">
                                                    <p class="text-[7px] font-black text-rose-500 uppercase mt-1 ml-1" x-text="getError(index, 'end_time')"></p>
                                                </template>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <label class="block text-[8px] font-black uppercase text-slate-400 mb-1 ml-1">Break (min)</label>
                                                    <input type="number" x-model="row.break" 
                                                        class="w-20 rounded-xl py-2 px-3 text-[10px] font-black text-center border"
                                                        :class="hasError(index, 'break') ? 'border-rose-200 bg-rose-50 text-rose-500' : 'border-slate-100 bg-slate-50/50'">
                                                </div>
                                                <div class="text-right">
                                                    <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Net OT</span>
                                                    <span class="text-lg font-black text-slate-900 leading-none" x-text="calculateNet(row)"></span>
                                                </div>
                                            </div>
                                            <template x-if="hasError(index, 'break')">
                                                <p class="text-[7px] font-black text-rose-500 uppercase ml-1" x-text="getError(index, 'break')"></p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            {{-- STATUS --}}
                            <td class="px-4 py-6 align-top text-center">
                                <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl transition-all"
                                    :class="row.payroll_status === 'safe' ? 'bg-emerald-50 text-emerald-500' : (row.payroll_status === 'exists' ? 'bg-rose-50 text-rose-500 animate-pulse' : 'bg-slate-50 text-slate-300')">
                                    <i class='bx' :class="row.payroll_status === 'safe' ? 'bxs-check-shield' : (row.payroll_status === 'exists' ? 'bxs-error-circle' : 'bx-hourglass-top')"></i>
                                </div>
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-8 py-6 align-top text-right">
                                <button type="button" @click="removeRow(index)" x-show="items.length > 1"
                                    class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                                    <i class='bx bx-trash-alt'></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        {{-- EMPTY STATE --}}
        <template x-if="items.length === 0">
             <div class="py-20 text-center">
                 <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300"><i class='bx bx-user-plus text-3xl'></i></div>
                 <p class="text-xs font-black text-slate-400 uppercase tracking-widest">No members added yet</p>
                 <button type="button" @click="addRow()" class="mt-6 text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline">Click here to add the first person</button>
             </div>
        </template>
    </div>
</div>
