@section('title', $formId ? "Edit Form Overtime #{$formId}" : 'Create Form Overtime')
@section('page-title', $formId ? "Edit Form Overtime" : 'Create Form Overtime')
@section('page-subtitle', 'Manage Overtime Requests')

<div class="bg-transparent" x-data="overtimeForm($wire)" x-on:toast.window="window.dispatchEvent(new CustomEvent('notify', { detail: $event.detail }))">
    {{-- ======================================================== STICKY PAGE HEADER --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ $formId ? route('overtime.detail', $formId) : route('overtime.index') }}"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-200/60 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all">
                <i class='bx bx-arrow-back text-xl'></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-slate-800 tracking-tight">
                        {{ $formId ? "Edit OT-{$formId}" : 'Create New Overtime' }}
                    </h1>
                    @if ($formId && $form)
                        <span class="rounded-lg bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 uppercase tracking-widest border border-amber-200/50">
                            {{ str_replace('-', ' ', $form->status) }}
                        </span>
                    @endif
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">
                    {{ $formId ? 'Update existing form details' : 'Draft a new overtime request' }}
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <template x-if="!excel">
                <div class="glass-panel px-4 py-2 rounded-xl flex items-center gap-2 shadow-sm">
                    <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                    <span class="text-xs font-bold text-slate-700 tracking-wider">
                        <span x-text="items.length"></span> EMPLOYEE<span x-show="items.length !== 1">S</span>
                    </span>
                </div>
            </template>
        </div>
    </div>

    <form wire:submit.prevent="submit" enctype="multipart/form-data">
        <div class="space-y-6">

            {{-- ================================================== GENERAL INFO --}}
            <div class="glass-card overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-slate-200/50">
                <div class="px-6 py-4 border-b border-slate-100/60 flex items-center gap-3 bg-white/50">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <i class='bx bx-info-circle text-lg'></i>
                    </div>
                    <h2 class="text-sm font-extrabold text-slate-800 tracking-tight">Form Settings & Purpose</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- Department --}}
                        <div class="md:col-span-2 lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Department <span class="text-rose-500">*</span>
                            </label>
                            @if (! $formId && $canOverrideDept)
                                <select wire:model.live="dept_id" id="dept_id"
                                    class="block w-full rounded-xl border border-slate-200 text-sm px-4 py-2.5 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white
                                        {{ $errors->has('dept_id') ? 'border-rose-300 focus:ring-rose-500' : '' }}">
                                    <option value="">— Select Department —</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-600">
                                    {{ $formId ? ($form->department?->name ?? '—') : (auth()->user()->department?->name ?? '—') }}
                                </div>
                                @if(! $formId)
                                    <input type="hidden" wire:model="dept_id">
                                @endif
                            @endif
                            @error('dept_id') <p class="mt-1.5 text-[11px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Branch --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Branch <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model.live="branch"
                                class="block w-full rounded-xl border border-slate-200 text-sm px-4 py-2.5 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 border-blue-500 bg-white
                                    {{ $errors->has('branch') ? 'border-rose-300 focus:ring-rose-500' : '' }}">
                                <option value="">— Select Branch —</option>
                                <option value="Jakarta">Jakarta</option>
                                <option value="Karawang">Karawang</option>
                            </select>
                            @error('branch') <p class="mt-1.5 text-[11px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- After Hour --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                After-Hour OT? <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="$set('is_after_hour', 1)"
                                    class="flex-1 rounded-xl border py-2.5 text-xs font-bold transition focus:outline-none
                                        {{ $is_after_hour == 1 ? 'border-blue-500 bg-blue-50 text-blue-700 ring-2 ring-blue-500/20' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}">YES</button>
                                <button type="button" wire:click="$set('is_after_hour', 0)"
                                    class="flex-1 rounded-xl border py-2.5 text-xs font-bold transition focus:outline-none
                                        {{ $is_after_hour == 0 ? 'border-slate-700 bg-slate-800 text-white shadow-md' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}">NO</button>
                            </div>
                        </div>

                        {{-- Designation --}}
                        <div class="md:col-span-2 lg:col-span-4 mt-2">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Form Notes / Memo (Optional)
                            </label>
                            <input type="text" wire:model.defer="description"
                                placeholder="Describe the purpose of this overtime..."
                                class="block w-full rounded-xl border border-slate-200 text-sm px-4 py-2.5 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mode Switcher (Create only) --}}
            @if (! $formId)
            <div class="flex justify-end pr-2">
                <div class="flex items-center p-1 rounded-xl bg-slate-100/80 border border-slate-200/50 shadow-inner">
                    <button type="button" @click="excel = false; $wire.set('isExcelMode', false)"
                        :class="!excel ? 'bg-white text-slate-800 shadow-sm border-slate-200/50' : 'text-slate-500 hover:text-slate-700 border-transparent'"
                        class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider transition-all">
                        <i class='bx bx-edit text-sm'></i> NORMAL ENTRY
                    </button>
                    <button type="button" @click="excel = true; $wire.set('isExcelMode', true)"
                        :class="excel ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200 border-emerald-500' : 'text-slate-500 hover:text-slate-700 border-transparent'"
                        class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider transition-all">
                        <i class='bx bx-file text-sm'></i> EXCEL UPLOAD
                    </button>
                </div>
            </div>
            @endif

            {{-- Excel Upload Mode --}}
            @if (! $formId)
            <div x-show="excel" x-cloak class="glass-card p-10 text-center">
                <div class="relative max-w-xl mx-auto rounded-2xl border-2 border-dashed transition-colors duration-300 p-10" :class="excel_file_loaded ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-300 bg-slate-50/50 hover:bg-slate-50'">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl shadow-sm text-2xl transition-colors" :class="excel_file_loaded ? 'bg-emerald-100 text-emerald-600' : 'bg-white text-slate-400'">
                        <i class='bx' :class="excel_file_loaded ? 'bx-check' : 'bx-cloud-upload'"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1" x-text="excel_file_loaded ? 'File Ready' : 'Upload Excel Sheet'"></h3>
                    <p class="text-[11px] font-medium text-slate-500 mb-6">Formats accepted: .xlsx, .xls — Maximum size: 5 MB</p>
                    <div class="flex items-center justify-center gap-4">
                        <label for="excel_upload" class="cursor-pointer inline-flex items-center gap-2 rounded-xl bg-slate-800 px-6 py-2.5 text-xs font-bold text-white shadow-md hover:bg-slate-700 transition-all focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                            <i class='bx bx-file shadow-sm'></i> Browse File
                        </label>
                        <input wire:model.defer="excel_file" type="file" accept=".xlsx,.xls" id="excel_upload" class="sr-only" @change="excel_file_loaded = $event.target.files.length > 0">
                        <div class="h-8 w-px bg-slate-300"></div>
                        <button type="button" wire:click="downloadTemplate" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">
                            <i class='bx bx-download text-lg'></i> Download Template
                        </button>
                    </div>
                    @error('excel_file') <p class="mt-4 text-xs font-bold text-rose-500 bg-rose-50 py-2 rounded-lg">{{ $message }}</p> @enderror
                </div>
            </div>
            @endif

            {{-- Normal Entry: Header & Roster --}}
            <div x-show="!excel">
                {{-- 1. Shared Defaults --}}
                <div class="glass-card overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-slate-200/50 mb-6 border-indigo-200/60 ring-4 ring-indigo-50">
                    <div class="px-6 py-4 border-b border-indigo-100/60 flex items-center justify-between bg-indigo-50/50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 shadow-inner border border-indigo-200">
                                <i class='bx bx-calendar-edit text-lg'></i>
                            </div>
                            <h2 class="text-sm font-black text-indigo-900 tracking-tight">Shared Time & Job Details</h2>
                        </div>
                        <span class="text-[10px] font-bold text-indigo-500 bg-indigo-100 px-2.5 py-1 rounded-full uppercase tracking-wider border border-indigo-200">Applies to all employees</span>
                    </div>
                    <div class="p-6 bg-white">
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-5">
                            
                            <div class="col-span-2 lg:col-span-2">
                                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">OT Date <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i class='bx bx-calendar'></i></div>
                                    <input type="date" wire:model.live.debounce.300ms="global_overtime_date" class="block w-full rounded-xl border border-slate-200 text-sm pl-9 pr-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                </div>
                            </div>
                            
                            <div class="col-span-2 lg:col-span-2">
                                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">End Date <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i class='bx bx-calendar-event'></i></div>
                                    <input type="date" wire:model.live.debounce.300ms="global_end_date" class="block w-full rounded-xl border border-slate-200 text-sm pl-9 pr-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                </div>
                            </div>

                            <div class="col-span-1 lg:col-span-1">
                                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Start Time <span class="text-rose-500">*</span></label>
                                <input type="time" wire:model.live.debounce.300ms="global_start_time" class="block w-full rounded-xl border border-slate-200 text-sm px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            </div>

                            <div class="col-span-1 lg:col-span-1">
                                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">End Time <span class="text-rose-500">*</span></label>
                                <input type="time" wire:model.live.debounce.300ms="global_end_time" class="block w-full rounded-xl border border-slate-200 text-sm px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            </div>

                            <div class="col-span-2 md:col-span-1 lg:col-span-1">
                                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Break <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input type="number" wire:model.live.debounce.300ms="global_break" min="0" max="180" class="block w-full rounded-xl border border-slate-200 text-sm pl-3 pr-10 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white text-center font-black">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[9px] font-black text-slate-400">MINS</div>
                                </div>
                            </div>
                            
                            <div class="col-span-2 md:col-span-4 lg:col-span-7 mt-2">
                                <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Job Description <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class='bx bx-task'></i></div>
                                    <input type="text" wire:model.live.debounce.500ms="global_job_desc" list="job-suggestions" placeholder="Standard details of work for everyone..." class="block w-full rounded-xl border border-slate-200 text-sm pl-9 pr-3 py-2.5 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- 2. Roster --}}
                <div class="glass-card overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-slate-200/50">
                    <div class="px-6 py-4 border-b border-slate-100/60 flex items-center justify-between bg-white/50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                <i class='bx bx-group text-lg'></i>
                            </div>
                            <h2 class="text-sm font-extrabold text-slate-800 tracking-tight">Employee Roster</h2>
                        </div>
                        <button type="button" @click="addRow()" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-4 py-2 text-[11px] font-black text-white shadow-md hover:bg-slate-700 transition-all focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                            <i class='bx bx-plus text-sm'></i> ADD EMPLOYEE
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/80 border-b border-slate-100/60">
                                <tr>
                                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 w-12">#</th>
                                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[200px]">Employee details <span class="text-rose-500">*</span></th>
                                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 min-w-[200px]">Custom Job Desc <span class="text-rose-500">*</span></th>
                                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 w-[120px] text-center">Overrides Time/Date <span class="text-rose-500">*</span></th>
                                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 w-24 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/60">
                                <template x-for="(row, index) in items" :key="row.id || ('new-' + index)">
                                    <tr class="hover:bg-slate-50/50 transition-colors" x-data="{ open: false, q: '' }">
                                        {{-- Index --}}
                                        <td class="px-6 py-4">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-black shadow-sm" :class="row.id ? 'bg-amber-100 text-amber-700 border border-amber-200/50' : 'bg-slate-100 text-slate-600 border border-slate-200'"><span x-text="index + 1"></span></div>
                                        </td>
                                        
                                        {{-- Employee Search (NIK + Name) --}}
                                        <td class="px-4 py-4 relative">
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i class='bx bx-user'></i></div>
                                                <input type="text"
                                                    class="block w-full rounded-xl border text-sm pl-9 pr-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                                    :class="hasError(index,'nik') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'"
                                                    placeholder="Search NIK or Name"
                                                    x-model="row.name"
                                                    @focus="open = true" @input="q = $event.target.value; open = true" @keydown.escape="open = false" @click.outside="open = false">
                                            </div>
                                            <input type="hidden" x-model="row.nik">
                                            
                                            <ul x-show="open && filteredBy('name', q).length" x-cloak x-transition
                                                class="absolute z-40 top-full mt-1 max-h-48 w-[300px] overflow-y-auto custom-scrollbar rounded-xl border border-slate-200 bg-white shadow-xl p-1">
                                                <template x-for="emp in filteredBy('name', q)" :key="emp.nik">
                                                    <li class="cursor-pointer px-3 py-2 rounded-lg hover:bg-blue-50 flex items-center gap-3 transition-colors"
                                                        @click="pick(index, emp); open=false; q=''">
                                                        <div class="h-8 w-8 rounded bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500" x-text="emp.name.substring(0,2)"></div>
                                                        <div class="flex-1 min-w-0">
                                                              <p class="text-xs font-bold text-slate-800 truncate" x-text="emp.name"></p>
                                                              <p class="text-[10px] font-mono text-slate-400" x-text="emp.nik"></p>
                                                        </div>
                                                    </li>
                                                </template>
                                            </ul>
                                            <p x-show="hasError(index,'nik')" x-text="getError(index,'nik')" class="mt-1 flex items-center gap-1 text-[9px] font-bold text-rose-500"></p>
                                            <p x-show="hasError(index,'name')" x-text="getError(index,'name')" class="mt-1 flex items-center gap-1 text-[9px] font-bold text-rose-500"></p>
                                        </td>
                                        
                                        {{-- Job Desc Override --}}
                                        <td class="px-4 py-4">
                                            <input type="text" x-model="row.job_desc" class="block w-full rounded-xl border text-sm px-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                                :class="hasError(index,'job_desc') ? 'border-rose-400 focus:border-rose-500 bg-rose-50/20' : 'border-slate-200 focus:border-blue-500'">
                                            <p x-show="hasError(index,'job_desc')" x-text="getError(index,'job_desc')" class="mt-1 flex items-center gap-1 text-[9px] font-bold text-rose-500"></p>
                                        </td>

                                        {{-- Times Config: We just show a summary and a popover button to edit them so we don't clutter the table --}}
                                        <td class="px-4 py-4 text-center relative" x-data="{ editingTimes: false }">
                                            <button type="button" @click="editingTimes = !editingTimes" @click.outside="editingTimes = false" 
                                                class="inline-flex w-full items-center justify-center gap-1 rounded-lg border px-3 py-2 text-xs font-bold transition hover:bg-slate-50 shadow-sm" 
                                                :class="hasTimeError(index) ? 'border-rose-300 bg-rose-50 text-rose-700 ring-1 ring-rose-200' : (editingTimes ? 'bg-slate-100 border-slate-300 ring-1 ring-slate-200' : 'bg-white border-slate-200')">
                                                <span class="font-mono" x-text="row.start_time ? row.start_time.substring(0,5) : '--:--'"></span>
                                                <span class="mx-1 opacity-40">-</span>
                                                <span class="font-mono" x-text="row.end_time ? row.end_time.substring(0,5) : '--:--'"></span>
                                            </button>
                                            
                                            <div x-show="editingTimes" x-cloak x-transition class="absolute z-30 top-full mt-2 w-[350px] right-0 -mr-[175px] rounded-2xl border border-slate-200 bg-white shadow-2xl p-4 text-left">
                                                <div class="mb-3 pb-3 border-b border-slate-100 flex items-center justify-between">
                                                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest"><i class='bx bx-time-five text-indigo-500 mr-1'></i> Custom Overwrite</h4>
                                                    <button type="button" @click="editingTimes=false" class="text-slate-400 hover:text-rose-500"><i class='bx bx-x text-lg'></i></button>
                                                </div>
                                                <div class="mb-2 pb-2 border-b border-slate-100">
                                                    <label class="block text-[9px] font-black uppercase text-slate-500 mb-1">OT Date</label>
                                                    <input type="date" x-model="row.overtime_date" :class="hasError(index,'overtime_date') ? 'border-rose-400 bg-rose-50' : 'border-slate-200'" class="w-full rounded-lg text-xs px-2 py-1 shadow-sm">
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-[9px] font-black uppercase text-slate-500 mb-1">Start Date/Time</label>
                                                        <input type="date" x-model="row.start_date" :class="hasError(index,'start_date') ? 'border-rose-400 bg-rose-50' : 'border-slate-200'" class="w-full rounded-lg text-xs px-2 py-1 mb-1 shadow-sm">
                                                        <input type="time" x-model="row.start_time" :class="hasError(index,'start_time') ? 'border-rose-400 bg-rose-50' : 'border-slate-200'" class="w-full rounded-lg text-xs px-2 py-1 shadow-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[9px] font-black uppercase text-slate-500 mb-1">End Date/Time</label>
                                                        <input type="date" x-model="row.end_date" :class="hasError(index,'end_date') ? 'border-rose-400 bg-rose-50' : 'border-slate-200'" class="w-full rounded-lg text-xs px-2 py-1 mb-1 shadow-sm">
                                                        <input type="time" x-model="row.end_time" :class="hasError(index,'end_time') ? 'border-rose-400 bg-rose-50' : 'border-slate-200'" class="w-full rounded-lg text-xs px-2 py-1 shadow-sm">
                                                    </div>
                                                    <div class="col-span-2 flex items-center gap-2 mt-1 pt-3 border-t border-slate-100">
                                                        <div class="w-1/2">
                                                            <label class="block text-[9px] font-black uppercase text-slate-500 mb-1">Break (Mins)</label>
                                                            <input type="number" x-model="row.break" min="0" max="180" :class="hasError(index,'break') ? 'border-rose-400 bg-rose-50' : 'border-slate-200'" class="w-full rounded-lg text-xs px-2 py-1 font-black text-right shadow-sm">
                                                        </div>
                                                        <div class="w-1/2 pl-2 border-l border-slate-100 text-center">
                                                            <span class="block text-[9px] font-black uppercase text-slate-400 mb-1">Net OT</span>
                                                            <span class="text-sm font-black text-indigo-600" x-text="calculateNet(row)"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div x-show="hasTimeError(index)" class="mt-3 p-2 bg-rose-50 rounded-lg border border-rose-100">
                                                    <template x-for="f in ['overtime_date', 'start_date', 'start_time', 'end_date', 'end_time', 'break']">
                                                        <p x-show="hasError(index, f)" x-text="getError(index, f)" class="text-[9px] font-bold text-rose-600 mb-0.5"></p>
                                                    </template>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        {{-- Remove Action --}}
                                        <td class="px-6 py-4 text-right">
                                            <button type="button" @click="removeRow(index)" x-show="items.length > 1"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all shadow-sm" title="Remove Employee">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- ======================================================== STICKY FOOTER --}}
        <div class="sticky bottom-0 z-40 mt-8 -mx-6 md:-mx-10 px-6 md:px-10 py-5 bg-white/90 backdrop-blur-xl border-t border-slate-200/60 premium-shadow flex items-center justify-between">
            <div class="flex flex-col hidden sm:flex">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest leading-tight">Status</span>
                <span class="text-sm font-bold transition-colors duration-300" :class="['Ready to Submit', 'File Ready'].includes(formStatus) ? 'text-emerald-600' : 'text-slate-500'" x-text="formStatus"></span>
            </div>
            
            <div x-show="hasAnyError" class="text-rose-500 text-xs font-bold bg-rose-50 border border-rose-100 rounded-lg px-3 py-1 mr-auto flex items-center gap-2 animate-bounce">
                                <i class='bx bx-error-circle'></i> Validation errors exist
                            </div>

            <div class="flex items-center gap-3 ml-auto">
                <a href="{{ $formId ? route('overtime.detail', $formId) : route('overtime.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-2.5 text-xs font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                    CANCEL
                </a>
                
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-2 rounded-xl px-8 py-2.5 text-xs font-black text-white shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-70"
                    :class="excel_file_loaded && excel ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/30' : 'bg-gradient-to-r {{ $formId ? 'from-amber-500 to-amber-600 shadow-amber-500/30' : 'from-blue-600 to-indigo-600 shadow-blue-500/30' }}'">
                    
                    <span wire:loading.remove class="flex items-center gap-2">
                        <i class='bx bx-check-circle text-lg'></i>
                        {{ $formId ? 'SAVE CHANGES' : 'SUBMIT FORM' }}
                    </span>
                    <span wire:loading class="flex items-center gap-2">
                        <i class='bx bx-loader-alt animate-spin text-lg'></i>
                        PROCESSING...
                    </span>
                </button>
            </div>
        </div>
    </form>
    
    <datalist id="job-suggestions">
        @foreach($recentJobs as $job)
            <option value="{{ $job }}"></option>
        @endforeach
    </datalist>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('overtimeForm', ($wire) => ({
        items:     $wire.entangle('items', true),
        excel:     $wire.entangle('isExcelMode', true),
        employees: $wire.entangle('employees', true),
        hasError(index, field) { return !!this.errors[`items.${index}.${field}`]; },
        getError(index, field) { const e = this.errors[`items.${index}.${field}`]; return Array.isArray(e) ? e[0] : (e || ''); },
        
        hasRowError(index) {
            return Object.keys(this.errors).some(k => k.startsWith(`items.${index}.`));
        },
        
        hasTimeError(index) {
            const fields = ['overtime_date', 'start_date', 'start_time', 'end_date', 'end_time', 'break'];
            return fields.some(f => !!this.errors[`items.${index}.${f}`]);
        },

        get hasAnyError() {
            return Object.keys(this.errors).length > 0;
        },

        get formStatus() {
            if (this.excel) return this.excel_file_loaded ? 'File Ready' : 'Awaiting Upload';
            let valid = 0; let total = this.items.length;
            this.items.forEach(i => { if (i.nik && i.start_date && i.start_time && i.end_date && i.end_time && i.job_desc && i.break !== '') valid++; });
            if (valid === 0) return 'Incomplete Fields';
            if (valid < total) return `${valid} Ready, ${total - valid} Incomplete`;
            return 'Ready to Submit';
        },

        addRow() {
            $wire.call('addEmptyRow');
        },
        removeRow(i) {
            $wire.call('removeRow', i);
        },

        filteredBy(field, q) {
            q = (q || '').toLowerCase();
            if (!q) return [];
            return this.employees.filter(e => String(e[field]).toLowerCase().includes(q) || String(e.name).toLowerCase().includes(q)).slice(0, 15);
        },
        pick(index, emp) {
            this.items[index].nik  = emp.nik;
            this.items[index].name = emp.name;
        },
        calculateNet(row) {
            if (!row.start_date || !row.start_time || !row.end_date || !row.end_time) return '—';
            const start = new Date(`${row.start_date}T${row.start_time}`);
            const end = new Date(`${row.end_date}T${row.end_time}`);
            if (isNaN(start) || isNaN(end) || end <= start) return '—';
            let diffMins = Math.floor((end - start) / 60000);
            let breakMins = parseInt(row.break) || 0;
            let netMins = diffMins - breakMins;
            if (netMins <= 0) return '0h';
            let hrs = Math.floor(netMins / 60);
            let mins = netMins % 60;
            return `${hrs}h ${mins > 0 ? mins + 'm' : ''}`;
        }
    }));
});
</script>
@endpush
