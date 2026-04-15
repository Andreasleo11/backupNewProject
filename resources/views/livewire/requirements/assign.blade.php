{{-- Requirements Assign — Livewire component view --}}
{{-- Tailwind, synced with new.layouts.app --}}

@section('title', 'Assign Requirement')
@section('page-title', 'Assign Requirement')
@section('page-subtitle', 'Batch assign compliance requirements to departments')

<div>
    {{-- Breadcrumb Navigation --}}
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('requirements.index') }}"
                    class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
                    Requirements
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="bx bx-chevron-right text-slate-400 text-lg"></i>
                    <span class="ml-1 text-sm font-medium text-slate-800 md:ml-2">Assign to Departments</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Left Column: Form & Selection --}}
        <div class="flex-1 w-full lg:w-2/3 space-y-5">
            <div class="glass-card p-6">
                {{-- Top controls --}}
                <div
                    class="flex flex-col md:flex-row gap-6 items-start md:items-end border-b border-slate-100 pb-6 mb-6">
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Requirement <span
                                class="text-rose-500">*</span></label>
                        <select wire:model.live="requirement_id"
                            class="w-full rounded-xl border {{ $errors->has('requirement_id') ? 'border-rose-300 focus:ring-rose-400' : 'border-slate-200 focus:ring-indigo-400' }} text-sm py-2 px-3 focus:ring-2 outline-none">
                            <option value="">— choose —</option>
                            @foreach ($requirements as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->code }})</option>
                            @endforeach
                        </select>
                        @error('requirement_id')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full md:w-auto">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Options</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <div class="relative">
                                <input type="checkbox" wire:model.live="is_mandatory" class="sr-only peer">
                                <div
                                    class="w-9 h-5 rounded-full bg-slate-200 peer-checked:bg-indigo-500 transition-colors">
                                </div>
                                <div
                                    class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4">
                                </div>
                            </div>
                            <span class="text-sm font-medium text-slate-600">Mandatory requirement</span>
                        </label>
                    </div>
                </div>

                {{-- Department Assignment Section --}}
                <div class="mb-4 flex flex-wrap gap-3 items-center justify-between">
                    <div class="relative flex-1 min-w-[240px]">
                        <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input type="text" wire:model.live.debounce.250ms="deptSearch"
                            placeholder="Search departments by name or code…"
                            class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex bg-slate-100 p-0.5 rounded-xl border border-slate-200">
                            <button wire:click="selectAll" @disabled(!$requirement_id)
                                class="px-3 py-1 text-xs font-semibold rounded-lg hover:bg-white hover:text-indigo-600 hover:shadow-sm disabled:opacity-50 disabled:hover:bg-transparent disabled:hover:shadow-none disabled:cursor-not-allowed transition-all text-slate-600">All</button>
                            <button wire:click="selectNone"
                                class="px-3 py-1 text-xs font-semibold rounded-lg hover:bg-white hover:text-indigo-600 hover:shadow-sm transition-all text-slate-600">None</button>
                            <button wire:click="selectAssigned" @disabled(!$requirement_id)
                                class="px-3 py-1 text-xs font-semibold rounded-lg hover:bg-white hover:text-indigo-600 hover:shadow-sm disabled:opacity-50 disabled:hover:bg-transparent disabled:hover:shadow-none disabled:cursor-not-allowed transition-all text-slate-600">Assigned</button>
                            <button wire:click="selectUnassigned" @disabled(!$requirement_id)
                                class="px-3 py-1 text-xs font-semibold rounded-lg hover:bg-white hover:text-indigo-600 hover:shadow-sm disabled:opacity-50 disabled:hover:bg-transparent disabled:hover:shadow-none disabled:cursor-not-allowed transition-all text-slate-600">Unassigned</button>
                        </div>
                        <span
                            class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100">
                            {{ count($department_ids) }} selected
                        </span>
                    </div>
                </div>

                {{-- Department list --}}
                <div class="border border-slate-200 rounded-xl overflow-y-auto max-h-[400px] bg-slate-50/50">
                    <div class="divide-y divide-slate-100">
                        @forelse($departments as $d)
                            <label
                                class="flex items-center gap-3 p-3 hover:bg-white cursor-pointer transition-colors group">
                                <input type="checkbox" wire:model.live="department_ids" value="{{ $d->id }}"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 mt-0.5 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800">{{ $d->name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $d->code ?? '—' }}</p>
                                </div>
                                @if (in_array($d->id, $assignedDeptIds))
                                    <span
                                        class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold bg-slate-200 text-slate-700 shrink-0">
                                        assigned
                                    </span>
                                @endif
                            </label>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-sm">
                                No departments found matching your search.
                            </div>
                        @endforelse
                    </div>
                </div>
                @error('department_ids')
                    <p class="text-rose-500 text-xs mt-2">{{ $message }}</p>
                @enderror

                {{-- Action buttons --}}
                <div class="mt-6 flex flex-wrap items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <div wire:loading class="text-sm font-medium text-indigo-500 mr-auto flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Processing…
                    </div>

                    <button wire:click="selectNone"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                        Clear
                    </button>

                    @if ($requirement_id && count($department_ids) > 0)
                        <button wire:click="unassign"
                            wire:confirm="Are you sure you want to completely unassign the selected departments? This will detach them from this requirement."
                            wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl text-sm font-semibold border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 disabled:opacity-50 transition-colors flex items-center gap-1.5">
                            <i class="bx bx-unlink"></i> Unassign
                        </button>
                    @endif

                    <button wire:click="save" @disabled(!$requirement_id || count($department_ids) === 0) wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-xl text-sm font-bold bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all flex items-center gap-1.5">
                        <i class="bx bx-check"></i> Save Assignments
                    </button>
                </div>
            </div>
        </div>

        {{-- Right Column: Insights & Details --}}
        <div class="w-full lg:w-1/3 flex flex-col gap-5">
            {{-- Requirement Facts --}}
            <div class="glass-card p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-xs font-bold text-indigo-500 uppercase tracking-wide">Requirement Details</p>
                        <h2 class="text-base font-bold text-slate-800 mt-1 leading-tight">
                            {{ $req?->name ?? 'None selected' }}</h2>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $req?->code ?? '—' }}</p>
                    </div>
                </div>

                @if ($req)
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-slate-50">
                            <span class="text-sm font-medium text-slate-500">Minimum files</span>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-700">
                                {{ $req->min_count }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-slate-50">
                            <span class="text-sm font-medium text-slate-500">Validity</span>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $req->validity_days ? $req->validity_days . ' days' : 'No expiry' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-slate-50">
                            <span class="text-sm font-medium text-slate-500">Frequency</span>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                {{ ucfirst($req->frequency) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-slate-50">
                            <span class="text-sm font-medium text-slate-500">Approval</span>
                            @if ($req->requires_approval)
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="bx bx-shield-alt-2"></i> Required
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Not required
                                </span>
                            @endif
                        </div>

                        <div class="pt-2">
                            <p class="text-xs font-medium text-slate-500 mb-2">Allowed file types</p>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse(($req->allowed_mimetypes ?? []) as $m)
                                    <span
                                        class="inline-flex rounded text-[10px] font-mono font-medium bg-slate-100 text-slate-600 px-1.5 py-0.5 border border-slate-200 truncate max-w-[150px]"
                                        title="{{ $m }}">
                                        {{ Str::limit(str_replace('application/', '', $m), 15) }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-400 italic">Any format allowed</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-6 text-center border-t border-slate-100 mt-4">
                        <i class="bx bx-file-blank text-3xl text-slate-200"></i>
                        <p class="text-xs text-slate-400 mt-2">Select a requirement from the dropdown to see its
                            specifications here.</p>
                    </div>
                @endif
            </div>

            {{-- Impact Preview --}}
            <div class="glass-card p-5">
                <p class="text-sm font-bold text-slate-800 mb-3 border-b border-slate-100 pb-2">Impact Preview</p>
                <div class="space-y-2 mb-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-600">Will create new</span>
                        <span
                            class="inline-flex items-center justify-center min-w-[24px] rounded px-1.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-700">
                            {{ $willCreate }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-600">Will update existing</span>
                        <span
                            class="inline-flex items-center justify-center min-w-[24px] rounded px-1.5 py-0.5 text-xs font-bold bg-slate-100 text-slate-700">
                            {{ $willUpdate }}
                        </span>
                    </div>
                </div>
                <div class="rounded-lg bg-slate-50 border border-slate-100 p-3">
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        The assignments will be set as <strong
                            class="{{ $is_mandatory ? 'text-indigo-600' : 'text-slate-700' }}">{{ $is_mandatory ? 'mandatory' : 'optional' }}</strong>.
                        @if ($is_mandatory)
                            They will count towards the department's compliance score.
                        @else
                            They will act as optional reference documents.
                        @endif
                    </p>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="glass-card p-5">
                <p class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-3">Recent Activity</p>
                @if ($req && $recent->count())
                    <div class="space-y-4">
                        @foreach ($recent as $ra)
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                                        <i class="bx bx-buildings text-slate-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-700">
                                            {{ optional($ra->scope)->name ?? 'Dept #' . $ra->scope_id }}
                                        </p>
                                        <p
                                            class="text-[10px] uppercase font-bold {{ $ra->is_mandatory ? 'text-indigo-500' : 'text-slate-400' }}">
                                            {{ $ra->is_mandatory ? 'Mandatory' : 'Optional' }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] text-slate-400 whitespace-nowrap">{{ $ra->created_at?->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                @elseif($req)
                    <p class="text-xs text-slate-400 text-center py-4 italic">No recent assignments.</p>
                @else
                    <p class="text-xs text-slate-400 text-center py-4">Select a requirement to view activity.</p>
                @endif
            </div>

        </div>
    </div>
</div>
