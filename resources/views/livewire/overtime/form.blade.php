@section('title', $formId ? "Edit Form Overtime #{$formId}" : 'Create Form Overtime')
@section('page-title', $formId ? "Edit Form Overtime" : 'Create Form Overtime')
@section('page-subtitle', 'Manage Overtime Requests')

<div
    class="bg-transparent"
    x-data="overtimeForm($wire)"
>
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
                    <h2 class="text-sm font-extrabold text-slate-800 tracking-tight">General Information</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                        {{-- Department --}}
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Department <span class="text-rose-500">*</span>
                            </label>
                            @if (! $formId && $canOverrideDept)
                                <select wire:model.live="dept_id" id="dept_id"
                                    class="block w-full rounded-xl border border-slate-200 text-sm px-4 py-2.5 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white
                                        {{ $errors->has('dept_id') ? 'border-rose-300 focus:ring-rose-500 focus:border-rose-500' : '' }}">
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
                                <p class="mt-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    {{ $formId ? 'Cannot be changed' : 'Auto-set from your department' }}
                                </p>
                            @endif
                            @error('dept_id') <p class="mt-1.5 text-[11px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Branch --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Branch <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model.live="branch"
                                class="block w-full rounded-xl border border-slate-200 text-sm px-4 py-2.5 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white
                                    {{ $errors->has('branch') ? 'border-rose-300 focus:ring-rose-500 focus:border-rose-500' : '' }}">
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
                                        {{ $is_after_hour == 1 
                                            ? ($formId ? 'border-amber-500 bg-amber-50 text-amber-700 ring-2 ring-amber-500/20' : 'border-blue-500 bg-blue-50 text-blue-700 ring-2 ring-blue-500/20') 
                                            : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}">
                                    YES
                                </button>
                                <button type="button" wire:click="$set('is_after_hour', 0)"
                                    class="flex-1 rounded-xl border py-2.5 text-xs font-bold transition focus:outline-none
                                        {{ $is_after_hour == 0 ? 'border-slate-700 bg-slate-800 text-white shadow-md' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}">
                                    NO
                                </button>
                            </div>
                            @error('is_after_hour') <p class="mt-1.5 text-[11px] font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Design (MOULDING only) --}}
                        @if ($isMoulding)
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Design Job?
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="$set('design', '1')"
                                    class="flex-1 rounded-xl border py-2.5 text-xs font-bold transition focus:outline-none
                                        {{ $design === '1' 
                                            ? ($formId ? 'border-amber-500 bg-amber-50 text-amber-700 ring-2 ring-amber-500/20' : 'border-blue-500 bg-blue-50 text-blue-700 ring-2 ring-blue-500/20') 
                                            : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}">
                                    YES
                                </button>
                                <button type="button" wire:click="$set('design', '0')"
                                    class="flex-1 rounded-xl border py-2.5 text-xs font-bold transition focus:outline-none
                                        {{ $design === '0' ? 'border-slate-700 bg-slate-800 text-white shadow-md' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}">
                                    NO
                                </button>
                            </div>
                        </div>
                        @endif

                        {{-- Description / Remarks (always at the bottom or taking remainder) --}}
                        <div class="col-span-1 sm:col-span-2 lg:col-span-4">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Note / Description
                            </label>
                            <input type="text" wire:model.defer="description"
                                placeholder="Optional description detailing the purpose of this form..."
                                class="block w-full rounded-xl border border-slate-200 text-sm px-4 py-2.5 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================================================== INPUT MODE / ROWS --}}
            <div class="glass-card overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-slate-200/50">
                <div class="px-6 py-4 border-b border-slate-100/60 flex items-center justify-between bg-white/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <i class='bx bx-group text-lg'></i>
                        </div>
                        <h2 class="text-sm font-extrabold text-slate-800 tracking-tight">Employee Details</h2>
                    </div>

                    {{-- Form Mode Switcher (Create only) --}}
                    @if (! $formId)
                    <div class="flex items-center p-1 rounded-xl bg-slate-100/80 border border-slate-200/50">
                        <button type="button" @click="excel = false; $wire.set('isExcelMode', false)"
                            :class="!excel ? 'bg-white text-slate-800 shadow-sm border-slate-200/50' : 'text-slate-500 hover:text-slate-700 border-transparent'"
                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider transition-all">
                            <i class='bx bx-edit text-sm'></i> MANUAL
                        </button>
                        <button type="button" @click="excel = true; $wire.set('isExcelMode', true)"
                            :class="excel ? 'bg-emerald-500 text-white shadow-md shadow-emerald-200 border-emerald-500' : 'text-slate-500 hover:text-slate-700 border-transparent'"
                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider transition-all">
                            <i class='bx bx-file text-sm'></i> EXCEL
                        </button>
                    </div>
                    @endif
                </div>

                {{-- Excel Mode View --}}
                @if (! $formId)
                <div x-show="excel" x-cloak x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="p-6">
                    <div class="max-w-xl mx-auto">
                        <div class="relative rounded-2xl border-2 border-dashed transition-colors duration-300 p-10 text-center"
                            :class="excel_file_loaded ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-300 bg-slate-50/50 hover:bg-slate-50'">
                            
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl shadow-sm text-2xl transition-colors"
                                :class="excel_file_loaded ? 'bg-emerald-100 text-emerald-600' : 'bg-white text-slate-400'">
                                <i class='bx' :class="excel_file_loaded ? 'bx-check' : 'bx-cloud-upload'"></i>
                            </div>
                            
                            <h3 class="text-sm font-bold text-slate-800 mb-1">
                                <span x-text="excel_file_loaded ? 'File Ready' : 'Upload Excel Sheet'"></span>
                            </h3>
                            <p class="text-[11px] font-medium text-slate-500 mb-6">
                                Formats accepted: .xlsx, .xls — Maximum size: 5 MB
                            </p>
                            
                            <div class="flex items-center justify-center gap-4">
                                <div class="relative group">
                                    <label for="excel_upload" class="cursor-pointer inline-flex items-center gap-2 rounded-xl bg-slate-800 px-6 py-2.5 text-xs font-bold text-white shadow-md hover:bg-slate-700 transition-all focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                                        <i class='bx bx-file shadow-sm'></i>
                                        Browse File
                                    </label>
                                    <input wire:model.defer="excel_file" type="file" accept=".xlsx,.xls" id="excel_upload" class="sr-only" @change="excel_file_loaded = $event.target.files.length > 0">
                                </div>
                                <div class="h-8 w-px bg-slate-300"></div>
                                <button type="button" wire:click="downloadTemplate"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">
                                    <i class='bx bx-download text-lg'></i>
                                    Download Template
                                </button>
                            </div>

                            @error('excel_file') <p class="mt-4 text-xs font-bold text-rose-500 bg-rose-50 py-2 rounded-lg">{{ $message }}</p> @enderror
                        </div>
                        
                        <div wire:loading wire:target="excel_file" class="mt-4 rounded-xl bg-blue-50 border border-blue-100 p-3 text-center">
                            <div class="flex items-center justify-center gap-2 text-xs font-bold text-blue-700">
                                <i class='bx bx-loader-alt animate-spin text-base'></i>
                                UPLOADING FILE...
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Manual Entry Mode View --}}
                <div x-show="!excel" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="divide-y divide-slate-100/60">
                        <template x-for="(row, index) in items" :key="row.id || ('new-' + index)">
                            <div class="p-6 transition-colors duration-200" :class="index % 2 === 1 ? 'bg-slate-50/30' : 'bg-white'">
                                
                                {{-- Row header --}}
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-black shadow-sm"
                                            :class="row.id ? 'bg-amber-100 text-amber-700 border border-amber-200/50' : 'bg-slate-100 text-slate-600 border border-slate-200'">
                                            <span x-text="index + 1"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Employee</span>
                                            <span class="text-sm font-black text-slate-800" x-text="row.name ? row.name : '—'"></span>
                                            <template x-if="row.id">
                                                <span class="ml-2 rounded-md bg-amber-50 px-1.5 py-0.5 text-[9px] font-extrabold text-amber-600 uppercase tracking-wider border border-amber-200/50">Existing</span>
                                            </template>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeRow(index)" x-show="items.length > 1"
                                        class="inline-flex items-center justify-center gap-1 h-8 px-3 rounded-lg border border-rose-200 bg-rose-50 text-[10px] font-bold text-rose-600 uppercase tracking-wider hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                                        <i class='bx bx-trash text-sm'></i> Remove
                                    </button>
                                </div>

                                {{-- Fields grid --}}
                                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-x-5 gap-y-4">

                                    {{-- NIK --}}
                                    <div class="relative xl:col-span-1" x-data="{ open: false, q: '' }">
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">NIK <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <i class='bx bx-id-card'></i>
                                            </div>
                                            <input type="text"
                                                class="block w-full rounded-xl border text-sm pl-9 pr-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                                :class="hasError(index,'nik') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'"
                                                placeholder="Search NIK"
                                                x-model="row.nik"
                                                @focus="open = true" @input="q = $event.target.value; open = true" @keydown.escape="open = false" @click.outside="open = false">
                                        </div>
                                        <ul x-show="open && filteredBy('nik', q).length" x-cloak x-transition
                                            class="absolute z-40 mt-1 max-h-48 w-64 overflow-y-auto custom-scrollbar rounded-xl border border-slate-200 bg-white shadow-xl p-1">
                                            <template x-for="emp in filteredBy('nik', q)" :key="emp.nik">
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
                                        <p x-show="hasError(index,'nik')" x-text="getError(index,'nik')" class="mt-1 flex items-center gap-1 text-[10px] font-bold text-rose-500"><i class='bx bx-error'></i></p>
                                    </div>

                                    {{-- Name --}}
                                    <div class="relative xl:col-span-1" x-data="{ open: false, q: '' }">
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Name <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <i class='bx bx-user'></i>
                                            </div>
                                            <input type="text"
                                                class="block w-full rounded-xl border text-sm pl-9 pr-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                                :class="hasError(index,'name') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'"
                                                placeholder="Search Name"
                                                x-model="row.name"
                                                @focus="open = true" @input="q = $event.target.value; open = true" @keydown.escape="open = false" @click.outside="open = false">
                                        </div>
                                        <ul x-show="open && filteredBy('name', q).length" x-cloak x-transition
                                            class="absolute z-40 mt-1 max-h-48 w-64 overflow-y-auto custom-scrollbar rounded-xl border border-slate-200 bg-white shadow-xl p-1">
                                            <template x-for="emp in filteredBy('name', q)" :key="emp.nik">
                                                <li class="cursor-pointer px-3 py-2 rounded-lg hover:bg-blue-50 flex items-center gap-3 transition-colors"
                                                    @click="pick(index, emp); open=false; q=''">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-bold text-slate-800 truncate" x-text="emp.name"></p>
                                                        <p class="text-[10px] font-mono text-slate-400" x-text="emp.nik"></p>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                        <p x-show="hasError(index,'name')" x-text="getError(index,'name')" class="mt-1 flex items-center gap-1 text-[10px] font-bold text-rose-500"><i class='bx bx-error'></i></p>
                                    </div>

                                    {{-- Overtime Date --}}
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">OT Date <span class="text-rose-500">*</span></label>
                                        <input type="date" x-model="row.overtime_date"
                                            class="block w-full rounded-xl border text-sm px-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                            :class="hasError(index,'overtime_date') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'">
                                        <p x-show="hasError(index,'overtime_date')" x-text="getError(index,'overtime_date')" class="mt-1 text-[10px] font-bold text-rose-500"></p>
                                    </div>

                                    {{-- Job Desc --}}
                                    <div class="col-span-2 xl:col-span-2">
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Job Description <span class="text-rose-500">*</span></label>
                                        <input type="text" x-model="row.job_desc" list="job-suggestions" placeholder="Details of work to be performed..."
                                            class="block w-full rounded-xl border text-sm px-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                            :class="hasError(index,'job_desc') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'">
                                        <p x-show="hasError(index,'job_desc')" x-text="getError(index,'job_desc')" class="mt-1 text-[10px] font-bold text-rose-500"></p>
                                    </div>
                                    
                                    {{-- DIVIDER --}}
                                    <div class="col-span-full border-t border-dashed border-slate-200 my-1"></div>

                                    {{-- Start --}}
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Start Date <span class="text-rose-500">*</span></label>
                                        <input type="date" x-model="row.start_date"
                                            class="block w-full rounded-xl border text-sm px-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                            :class="hasError(index,'start_date') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'">
                                        <p x-show="hasError(index,'start_date')" x-text="getError(index,'start_date')" class="mt-1 text-[10px] font-bold text-rose-500"></p>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Start Time <span class="text-rose-500">*</span></label>
                                        <input type="time" x-model="row.start_time"
                                            class="block w-full rounded-xl border text-sm px-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                            :class="hasError(index,'start_time') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'">
                                        <p x-show="hasError(index,'start_time')" x-text="getError(index,'start_time')" class="mt-1 text-[10px] font-bold text-rose-500"></p>
                                    </div>

                                    {{-- End --}}
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">End Date <span class="text-rose-500">*</span></label>
                                        <input type="date" x-model="row.end_date" :min="row.start_date"
                                            class="block w-full rounded-xl border text-sm px-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                            :class="hasError(index,'end_date') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'">
                                        <p x-show="hasError(index,'end_date')" x-text="getError(index,'end_date')" class="mt-1 text-[10px] font-bold text-rose-500"></p>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">End Time <span class="text-rose-500">*</span></label>
                                        <input type="time" x-model="row.end_time"
                                            class="block w-full rounded-xl border text-sm px-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                            :class="hasError(index,'end_time') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'">
                                        <p x-show="hasError(index,'end_time')" x-text="getError(index,'end_time')" class="mt-1 text-[10px] font-bold text-rose-500"></p>
                                    </div>

                                    {{-- Break --}}
                                    <div class="xl:col-span-1">
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5 flex justify-between">
                                            <span>Break (mins) <span class="text-rose-500">*</span></span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" x-model="row.break" min="0" max="180" placeholder="0"
                                                class="block w-full rounded-xl border text-sm pl-3 pr-10 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                                :class="hasError(index,'break') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[10px] font-bold text-slate-400">MINS</div>
                                        </div>
                                        <div class="mt-1.5 flex items-center justify-between text-[10px] font-bold">
                                            <span class="text-slate-500 tracking-wider">NET OT:</span>
                                            <span class="text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded shadow-sm border border-indigo-100" x-text="calculateNet(row)"></span>
                                        </div>
                                        <p x-show="hasError(index,'break')" x-text="getError(index,'break')" class="mt-1 text-[10px] font-bold text-rose-500"></p>
                                    </div>
                                    
                                    {{-- Remarks --}}
                                    <div class="col-span-full xl:col-span-5 hidden"></div> {{-- Spacer strategy --}}
                                    <div class="col-span-2 sm:col-span-full xl:col-span-5">
                                        <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Remarks <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <i class='bx bx-message-square-dots'></i>
                                            </div>
                                            <input type="text" x-model="row.remarks" placeholder="Notes or additional comments..."
                                                class="block w-full rounded-xl border text-sm pl-9 pr-3 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                                                :class="hasError(index,'remarks') ? 'border-rose-400 focus:border-rose-500' : 'border-slate-200 focus:border-blue-500'">
                                        </div>
                                        <p x-show="hasError(index,'remarks')" x-text="getError(index,'remarks')" class="mt-1 flex items-center gap-1 text-[10px] font-bold text-rose-500"><i class='bx bx-error'></i></p>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Add row CTA --}}
                    <div class="px-6 py-4 border-t border-slate-100/60 bg-slate-50/50 flex items-center justify-between">
                        <button type="button" @click="addRow()"
                            class="inline-flex items-center gap-2 rounded-xl border border-dashed border-blue-300 bg-blue-50 px-4 py-2.5 text-xs font-black text-blue-700 uppercase tracking-widest shadow-sm hover:bg-blue-100 hover:border-blue-400 transition-all">
                            <i class='bx bx-plus-circle text-lg'></i> Add Employee Row
                        </button>
                    </div>
                </div>

            </div>

        </div>

        {{-- ======================================================== STICKY FOOTER --}}
        <div class="sticky bottom-0 z-40 mt-8 -mx-6 md:-mx-10 px-6 md:px-10 py-5 bg-white/80 backdrop-blur-xl border-t border-slate-200/60 premium-shadow flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest leading-tight">Status</span>
                <span class="text-sm font-bold transition-colors duration-300" 
                      :class="['Ready to Submit', 'File Ready'].includes(formStatus) ? 'text-emerald-600' : 'text-slate-500'" 
                      x-text="formStatus"></span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ $formId ? route('overtime.detail', $formId) : route('overtime.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                    CANCEL
                </a>
                
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-xs font-black text-white shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-70"
                    :class="excel_file_loaded && excel ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/30 focus:ring-emerald-500' : 'bg-gradient-to-r {{ $formId ? 'from-amber-500 to-amber-600 shadow-amber-500/30 focus:ring-amber-500' : 'from-blue-600 to-indigo-600 shadow-blue-500/30 focus:ring-blue-500' }}'">
                    
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
        items:     $wire.entangle('items'),
        excel:     $wire.entangle('isExcelMode'),
        employees: $wire.entangle('employees'),
        errors:    $wire.entangle('validationErrors'),
        excel_file_loaded: false,

        hasError(index, field) {
            return !!this.errors[`items.${index}.${field}`];
        },
        getError(index, field) {
            const e = this.errors[`items.${index}.${field}`];
            return Array.isArray(e) ? e[0] : (e || '');
        },

        get formStatus() {
            if (this.excel) {
                return this.excel_file_loaded ? 'File Ready' : 'Awaiting Upload';
            }

            let valid = 0;
            let total = this.items.length;
            this.items.forEach(i => {
                if (i.nik && i.start_date && i.start_time && i.end_date && i.end_time && i.job_desc && i.break !== '') {
                    valid++;
                }
            });

            if (valid === 0) return 'Incomplete Fields';
            if (valid < total) return `${valid} Ready, ${total - valid} Incomplete`;
            return 'Ready to Submit';
        },

        addRow() {
            this.items.push({
                id: null, nik: '', name: '', overtime_date: '', job_desc: '',
                start_date: '', start_time: '', end_date: '', end_time: '',
                break: '', remarks: '',
            });
            $wire.set('items', this.items);
        },
        removeRow(i) {
            if (this.items.length > 1) {
                this.items.splice(i, 1);
                $wire.set('items', this.items);
            }
        },

        filteredBy(field, q) {
            q = (q || '').toLowerCase();
            if (!q) return [];
            return this.employees
                .filter(e => String(e[field]).toLowerCase().includes(q) || String(e.name).toLowerCase().includes(q))
                .slice(0, 15);
        },
        pick(index, emp) {
            this.items[index].nik  = emp.nik;
            this.items[index].name = emp.name;
            $wire.set('items', this.items);
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
