@section('title', 'Smart Bulk Overtime Entry')
@section('page-title', 'Smart Bulk Overtime Entry')
@section('page-subtitle', 'Import and stage multiple requests simultaneously')

<div class="bg-slate-50 min-h-screen pb-20 font-sans" x-data="{ uploading: false, confirming: false }" x-on:livewire-upload-start="uploading = true" x-on:livewire-upload-finish="uploading = false" x-on:livewire-upload-error="uploading = false">
    
    {{-- SUBMITTING OVERLAY --}}
    @if ($isSubmitting)
    <div class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl p-10 text-center shadow-2xl border border-white/20 max-w-sm mx-auto animate-in zoom-in duration-300">
            <div class="mb-6 relative">
                <div class="h-20 w-20 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mx-auto shadow-inner">
                    <i class='bx bx-loader-alt animate-spin text-4xl'></i>
                </div>
                <div class="absolute -bottom-2 -right-2 h-8 w-8 rounded-full bg-emerald-500 border-4 border-white flex items-center justify-center text-white shadow-sm">
                    <i class='bx bx-check text-lg'></i>
                </div>
            </div>
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">Generating Batch Forms</h3>
            <p class="text-xs font-bold text-slate-500 leading-relaxed uppercase tracking-widest">
                We are grouping assets and registering required signatures. This may take a moment.
            </p>
        </div>
    </div>
    @endif
    {{-- TOP NAV --}}
    <div class="sticky top-0 z-[100] bg-white/80 backdrop-blur-xl border-b border-slate-200/50 px-6 py-3 transition-all">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('overtime.index') }}"
                    class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                    <i class='bx bx-arrow-back text-lg'></i>
                </a>
                <div class="h-4 w-px bg-slate-200"></div>
                <h1 class="text-sm font-black text-slate-900 tracking-tight uppercase">
                    Bulk Import Staging
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="h-2 w-2 rounded-full {{ $totalErrors > 0 ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' }}"></div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    {{ count($stagedData) > 0 ? 'Staging Active' : 'Waiting for Data' }}
                </span>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto mt-10 px-6 space-y-8">

        {{-- UPLOAD ZONE --}}
        @if (count($stagedData) === 0)
        <div class="bg-white rounded-[3rem] border-4 border-dashed border-slate-200 text-center shadow-sm relative overflow-hidden group hover:border-indigo-300 transition-colors min-h-[450px] flex flex-col">
            
            <input type="file" wire:model="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-40">

            {{-- Floating Download Button --}}
            <div class="absolute top-6 right-6 z-[60]">
                <button type="button" wire:click="downloadTemplate" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 text-white px-5 py-2 text-xs font-black shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all hover:-translate-y-0.5 active:translate-y-0">
                    <i class='bx bx-download text-lg'></i> Template
                </button>
            </div>

            <div x-show="!uploading && !$wire.isAnalyzing" class="p-16 pointer-events-none transition-transform group-hover:scale-105 flex-1 flex flex-col items-center justify-center">
                <div class="h-24 w-24 bg-indigo-50 rounded-3xl flex items-center justify-center mx-auto mb-8 text-indigo-600 shadow-inner group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <i class='bx bx-cloud-upload text-5xl'></i>
                </div>
                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-3">Upload Excel Roster</h3>
                <p class="text-sm text-slate-400 font-bold max-w-lg mx-auto leading-relaxed mb-6">
                    Upload your daily overtime reference sheet. Department, Branch and Session type are <span class="text-indigo-600">auto-detected</span> from each employee's profile.
                </p>

                <div class="mt-8 grid grid-cols-3 md:grid-cols-5 gap-3 max-w-3xl mx-auto text-left">
                    <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                        <span class="block text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Column A</span>
                        <span class="text-xs font-black text-indigo-700">Employee ID (NIK)</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Column B</span>
                        <span class="text-xs font-black text-slate-700">Overtime Date</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Column C</span>
                        <span class="text-xs font-black text-slate-700">Job Description</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Col D–H</span>
                        <span class="text-xs font-black text-slate-700">Start/End Date & Time, Break</span>
                    </div>
                    <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                        <span class="block text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1">Auto-Detected</span>
                        <span class="text-xs font-black text-emerald-700">Dept · Branch · Session</span>
                    </div>
                </div>
                <p class="text-[10px] font-bold text-slate-300 mt-4 uppercase tracking-widest">
                    Column I: Remarks (Optional)
                </p>
            </div>

            <div x-show="uploading || $wire.isAnalyzing" x-cloak 
                 class="absolute inset-0 z-50 flex flex-col items-center justify-center text-center p-16 bg-white animate-pulse">
                <i class='bx bx-loader-alt animate-spin text-7xl text-indigo-600 mb-6'></i>
                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Analyzing Data...</h3>
                <p class="text-sm text-slate-500 font-bold mt-2 uppercase tracking-widest">Verifying employee records and schedules</p>
            </div>
        </div>
        @error('file') <div class="mt-4 p-4 bg-rose-50 rounded-2xl text-rose-600 text-xs font-black uppercase tracking-widest text-center border border-rose-200"><i class='bx bx-error-circle'></i> {{ $message }}</div> @enderror
        @endif

        {{-- STAGING AREA --}}
        @if (count($stagedData) > 0)
        <div>
            {{-- SUMMARY DASHBOARD --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Parsed</p>
                        <p class="text-3xl font-black text-slate-900">{{ count($stagedData) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 text-2xl"><i class='bx bx-list-ol'></i></div>
                </div>
                
                <div class="bg-indigo-600 rounded-3xl p-6 border border-indigo-500 shadow-xl shadow-indigo-100 flex items-center justify-between group">
                    <div>
                        <p class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-1">Grouping Strategy</p>
                        <p class="text-3xl font-black text-white group-hover:scale-105 transition-transform">{{ count($groupedHeaders) }} <span class="text-xs opacity-60">Forms</span></p>
                    </div>
                    <div class="h-12 w-12 rounded-xl bg-white/10 flex items-center justify-center text-white text-2xl"><i class='bx bx-git-branch'></i></div>
                </div>
            </div>

            {{-- INTEGRITY GUARD --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-200 p-8 shadow-sm mb-8 relative overflow-hidden">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-slate-50 blur-3xl pointer-events-none"></div>
                
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                        <div class="flex items-center gap-4">
                            <div class="h-14 w-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg">
                                <i class='bx bx-shield-quarter text-3xl'></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight">Integrity Guard</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Pre-submission health & duplicate verification</p>
                            </div>
                        </div>

                        <button type="button" 
                            wire:click="runIntegrityCheck"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-8 py-3.5 text-xs font-black text-white shadow-xl shadow-indigo-100 hover:bg-slate-900 transition-all hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="bx {{ $isCheckingPayroll ? 'bx-loader-alt animate-spin' : 'bx-zap' }} text-lg"></i>
                            {{ $isCheckingPayroll ? 'Running Guards...' : 'Run Integrity Check' }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach([
                            ['key' => 'structural', 'label' => 'Structural Integrity', 'desc' => 'Schema & Master Data resolution'],
                            ['key' => 'local',      'label' => 'Local Database',    'desc' => 'Duplicate check in pending forms'],
                            ['key' => 'payroll',    'label' => 'JPayroll History',   'desc' => 'Verification against external system'],
                        ] as $check)
                            @php 
                                $status = $integrityResults[$check['key']] ?? 'pending';
                                $config = [
                                    'pending' => ['bg' => 'bg-slate-50',     'border' => 'border-slate-100', 'icon' => 'bx-dots-horizontal-rounded', 'text' => 'text-slate-400', 'badge' => 'Pending'],
                                    'loading' => ['bg' => 'bg-indigo-50/50', 'border' => 'border-indigo-100','icon' => 'bx-loader-alt animate-spin',  'text' => 'text-indigo-500', 'badge' => 'Checking'],
                                    'passed'  => ['bg' => 'bg-emerald-50',   'border' => 'border-emerald-100','icon' => 'bx-check-circle',           'text' => 'text-emerald-600', 'badge' => 'Passed'],
                                    'failed'  => ['bg' => 'bg-rose-50',      'border' => 'border-rose-100',  'icon' => 'bx-x-circle',               'text' => 'text-rose-600',    'badge' => 'Conflict'],
                                ][$status];
                            @endphp
                            <div class="p-5 rounded-2xl border {{ $config['border'] }} {{ $config['bg'] }} transition-all">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="h-8 w-8 rounded-lg bg-white flex items-center justify-center {{ $config['text'] }} shadow-sm">
                                        <i class="bx {{ $config['icon'] }} text-xl"></i>
                                    </div>
                                    <span class="text-[9px] font-black uppercase tracking-widest {{ $config['text'] }}">{{ $config['badge'] }}</span>
                                </div>
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-tight">{{ $check['label'] }}</h4>
                                <p class="text-[10px] font-medium text-slate-400 mt-1">{{ $check['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- GROUPING PREVIEW --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-200 p-8 shadow-sm mb-8 overflow-hidden">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Grouped Output Preview</h3>
                <div class="flex flex-wrap gap-4">
                    {{-- All Data Card --}}
                    <div wire:click="setFilter(null)" 
                        class="inline-flex items-center gap-3 rounded-2xl border px-5 py-3 shadow-sm transition-all cursor-pointer {{ !$activeFilter ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-white border-slate-100 text-slate-900 group hover:border-indigo-300' }}">
                        <div class="h-9 w-9 rounded-xl flex items-center justify-center text-lg {{ !$activeFilter ? 'bg-white/20' : 'bg-slate-50 border border-slate-100 text-slate-400 group-hover:text-indigo-600' }}">
                            <i class='bx bx-layer'></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-tight">Show All Data</p>
                            <p class="text-[8px] font-black opacity-60 uppercase tracking-widest mt-0.5">{{ count($stagedData) }} Records</p>
                        </div>
                    </div>

                    @foreach($groupedHeaders as $sig => $header)
                        <div wire:click="setFilter('{{ $sig }}')" 
                            class="inline-flex items-center gap-3 rounded-2xl border px-5 py-3 shadow-sm transition-all cursor-pointer {{ $activeFilter === $sig ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-white border-slate-100 text-slate-900 group hover:border-indigo-300' }}">
                            <div class="h-9 w-9 rounded-xl flex items-center justify-center text-lg {{ $activeFilter === $sig ? 'bg-white/20' : 'bg-slate-50 border border-slate-100 text-slate-400 group-hover:text-indigo-600' }}">
                                <i class='bx bxs-folder-open'></i>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-0.5">
                                    <p class="text-[10px] font-black uppercase tracking-tight">{{ $header['department'] }} ({{ $header['branch'] }})</p>
                                    <span class="text-[9px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg">{{ \Carbon\Carbon::parse($header['date'])->format('d M') }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[8px] font-black uppercase tracking-widest {{ $activeFilter === $sig ? 'text-white/80' : ($header['is_after_hour'] ? 'text-indigo-500' : 'text-emerald-500') }}">{{ $header['session'] }}</span>
                                    <span class="h-1 W-1 rounded-full {{ $activeFilter === $sig ? 'bg-white/40' : 'bg-slate-300' }}"></span>
                                    <span class="text-[8px] font-black uppercase tracking-widest {{ $activeFilter === $sig ? 'text-white/60' : 'text-slate-400' }}">{{ $header['count'] }} rows</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- THE GRID --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden mb-12">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @php
                            $visibleCount = $activeFilter 
                                ? collect($stagedData)->where('group_signature', $activeFilter)->count() 
                                : count($stagedData);
                        @endphp
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">
                            Staging Inspector 
                            <span class="ml-2 px-2 py-0.5 rounded-lg bg-slate-200 text-slate-500 text-[10px]">{{ $visibleCount }} Items</span>
                        </h2>
                        @if($activeFilter)
                             <span class="h-5 px-3 rounded-full bg-indigo-600 text-[9px] font-black text-white uppercase tracking-widest flex items-center gap-1.5 animate-in slide-in-from-left-2 transition-all">
                                <i class='bx bx-filter-alt'></i>
                                Filtered: {{ $groupedHeaders[$activeFilter]['department'] }} ({{ $groupedHeaders[$activeFilter]['branch'] }})
                                <button wire:click="setFilter(null)" class="hover:text-white/60 transition-colors"><i class='bx bx-x text-xs'></i></button>
                             </span>
                        @endif
                    </div>
                    <button @click="uploading = false; confirming = false" wire:click="cancel" class="text-[10px] font-black text-slate-400 hover:text-rose-500 uppercase tracking-widest transition-colors"><i class='bx bx-x'></i> Cancel & Clear</button>
                </div>
                
                <div class="overflow-x-auto max-h-[600px] overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead class="sticky top-0 bg-white z-10 shadow-sm">
                            <tr>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 whitespace-nowrap bg-slate-50">Row</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 whitespace-nowrap bg-slate-50">Identity</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 whitespace-nowrap bg-slate-50">Context Group</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 whitespace-nowrap bg-slate-50">Schedule</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 whitespace-nowrap bg-slate-50 min-w-[200px]">Task</th>
                                <th class="px-6 py-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 whitespace-nowrap bg-slate-50 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($stagedData as $index => $row)
                                @if(!$activeFilter || $row['group_signature'] === $activeFilter)
                                <tr class="transition-colors hover:bg-slate-50/30 {{ count($row['errors']) > 0 ? 'bg-rose-50/20' : '' }}">
                                    <td class="px-6 py-4 align-top">
                                        <div class="h-6 w-6 rounded flex items-center justify-center text-[9px] font-black {{ count($row['errors']) > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $row['original_index'] }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 align-top">
                                        <p class="text-xs font-black text-slate-900">{{ $row['employee_name'] ?? 'Unknown' }}</p>
                                        <p class="text-[9px] font-mono font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $row['nik'] ?: 'MISSING NIK' }}</p>
                                        @if(count($row['errors']) > 0)
                                            <div class="mt-2 space-y-1">
                                                @foreach($row['errors'] as $err)
                                                    <p class="text-[8px] font-black text-rose-600 uppercase tracking-wider flex items-center gap-1"><i class='bx bxs-error'></i> {{ $err }}</p>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-slate-900 text-white w-fit">{{ \Carbon\Carbon::parse($row['overtime_date'])->format('d M') }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-slate-100 text-slate-600 w-fit">{{ $row['branch'] ?: '?' }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-slate-100 text-slate-600 w-fit">{{ $row['department'] ?: '?' }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest {{ $row['is_after_hour'] ? 'bg-indigo-100 text-indigo-600' : 'bg-emerald-100 text-emerald-600' }} w-fit">{{ $row['session_type'] }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $row['overtime_date'] ?: 'Invalid Date' }}</p>
                                        <div class="flex items-center gap-1 text-[10px] font-mono font-black text-slate-900 bg-slate-100 px-2 py-1 rounded w-fit">
                                            <span>{{ $row['start_time'] ?: '--:--' }}</span>
                                            <i class='bx bx-right-arrow-alt text-slate-400 text-[8px]'></i>
                                            <span>{{ $row['end_time'] ?: '--:--' }}</span>
                                        </div>
                                        @if($row['start_date'] !== $row['end_date'])
                                            <p class="text-[8px] font-black text-indigo-500 uppercase tracking-widest mt-1">+1 Day cross</p>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <p class="text-[10px] font-medium text-slate-600 leading-relaxed max-w-xs break-words">{{ $row['task'] ?: 'No task defined' }}</p>
                                    </td>

                                    <td class="px-6 py-4 align-top text-right">
                                        <button wire:click="removeRow({{ $index }})" title="Remove Row" class="h-8 w-8 inline-flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-rose-100 hover:text-rose-600 transition-colors">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SUBMIT BAR --}}
            <div class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-xl border-t border-slate-200/50 p-4 z-50 transform transition-transform shadow-[0_-8px_30px_rgb(0,0,0,0.04)]">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl {{ $isIntegrityChecked ? 'bg-emerald-500' : 'bg-slate-200' }} text-white flex items-center justify-center shadow-lg transition-colors">
                            <i class='bx {{ $isIntegrityChecked ? 'bx-check-shield' : 'bx-lock-alt' }} text-xl'></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight line-clamp-1">
                                @if(!$isIntegrityChecked)
                                    Integrity Check Required
                                @else
                                    Ready to Group into <span class='text-emerald-600'>{{ count($groupedHeaders) }}</span> Batches
                                @endif
                            </h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                @if($totalErrors > 0)
                                    {{ $totalErrors }} conflicts detected in staging
                                @elseif(!$isIntegrityChecked)
                                    Verification pending
                                @else
                                    {{ $totalValid }} rows cleared for insertion
                                @endif
                            </p>
                        </div>
                    </div>

                    <div>
                        @if ($isIntegrityChecked && $isReady)
                            <button type="button" 
                                wire:key="btn-register"
                                @click="confirming = true" 
                                x-show="!confirming" 
                                class="h-12 px-10 rounded-2xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:-translate-y-0.5 transition-all">
                                Register Bulk Data
                            </button>
                            <button type="button" 
                                wire:key="btn-confirm"
                                wire:click="submitBulk" 
                                wire:loading.attr="disabled"
                                x-show="confirming" x-cloak 
                                class="h-12 px-10 rounded-2xl bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-emerald-100 transition-all flex items-center gap-2 animate-pulse disabled:opacity-50">
                                <i class='bx bx-check-double text-lg'></i> Confirm & Submit
                            </button>
                        @elseif($totalErrors > 0)
                            <button disabled 
                                wire:key="btn-error"
                                class="h-12 px-10 rounded-2xl bg-slate-100 text-slate-400 text-[10px] font-black uppercase tracking-widest cursor-not-allowed">
                                Resolve Errors First
                            </button>
                        @else
                            <button type="button" 
                                wire:key="btn-guard"
                                wire:click="runIntegrityCheck" 
                                class="h-12 px-10 rounded-2xl bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200 transition-all">
                                Run Integrity Guard
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="h-20"></div> {{-- Spacer for fixed footer --}}
        </div>
        @endif

    </div>
</div>
