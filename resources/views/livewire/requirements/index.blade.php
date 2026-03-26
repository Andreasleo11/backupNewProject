{{-- Requirements Index — Livewire component view --}}
{{-- Tailwind, synced with new.layouts.app --}}

@section('title', 'Requirements')
@section('page-title', 'Requirements')
@section('page-subtitle', 'Manage document compliance requirements catalogue')

<div>
    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-200 shrink-0">
                <i class="bx bx-clipboard text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Requirements</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ $items->total() }} requirements in catalogue</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('requirements.assign') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 shadow-sm transition-all">
                <i class="bx bx-link-alt text-base"></i> Assign to Depts
            </a>
            <a href="{{ route('requirements.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-semibold shadow-sm shadow-indigo-200 transition-all">
                <i class="bx bx-plus text-base"></i> New Requirement
            </a>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="glass-card px-5 py-4 mb-5 flex flex-wrap items-center gap-3">
        {{-- Search --}}
        <div class="relative flex-1 min-w-[200px]">
            <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
            <input type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search by code or name…"
                class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
        </div>

        {{-- Frequency filter --}}
        <select wire:model.live="filterFreq"
            class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
            <option value="">All frequencies</option>
            <option value="once">Once</option>
            <option value="yearly">Yearly</option>
            <option value="quarterly">Quarterly</option>
            <option value="monthly">Monthly</option>
        </select>

        {{-- Approval filter --}}
        <select wire:model.live="filterApproval"
            class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
            <option value="">Approval: All</option>
            <option value="1">Required</option>
            <option value="0">Not required</option>
        </select>

        {{-- Sort + direction --}}
        <div class="flex items-center gap-2">
            <select wire:model.live="sort"
                class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
                <option value="code">Code</option>
                <option value="name">Name</option>
                <option value="min_count">Min count</option>
                <option value="frequency">Frequency</option>
                <option value="requires_approval">Approval</option>
            </select>
            <button wire:click="toggleDir"
                class="h-9 w-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
                <i class="bx {{ $dir === 'asc' ? 'bx-sort-up' : 'bx-sort-down' }} text-lg"></i>
            </button>
        </div>

        {{-- Per page --}}
        <select wire:model.live="perPage"
            class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
            <option>10</option>
            <option>25</option>
            <option>50</option>
        </select>
    </div>

    {{-- Requirements list --}}
    <div class="glass-card overflow-hidden">
        <div class="divide-y divide-slate-50">
            @forelse($items as $r)
                @php
                    $freqColors = [
                        'once'      => 'bg-slate-100 text-slate-600',
                        'yearly'    => 'bg-sky-100 text-sky-700',
                        'quarterly' => 'bg-amber-100 text-amber-700',
                        'monthly'   => 'bg-emerald-100 text-emerald-700',
                    ];
                    $freqColor = $freqColors[$r->frequency] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <div class="px-5 py-4 hover:bg-slate-50/50 transition-colors" wire:key="req-{{ $r->id }}">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        {{-- Info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-xs font-mono font-semibold text-slate-400">{{ $r->code }}</span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $freqColor }}">
                                    {{ ucfirst($r->frequency) }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-indigo-50 text-indigo-600 border border-indigo-100">
                                    Min {{ $r->min_count }}
                                </span>
                                @if($r->requires_approval)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs bg-violet-50 text-violet-700 border border-violet-200">
                                        <i class="bx bx-shield-alt-2 text-xs"></i> Approval
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-slate-800">{{ $r->name }}</p>
                            @if($r->description)
                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $r->description }}</p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="{{ route('requirements.departments', $r) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-3 py-1.5 text-xs font-semibold transition-all">
                                <i class="bx bx-buildings text-sm"></i> Departments
                            </a>
                            <a href="{{ route('requirements.edit', $r) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 text-xs font-semibold transition-all">
                                <i class="bx bx-pencil text-sm"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center">
                    <i class="bx bx-clipboard text-4xl text-slate-300"></i>
                    <p class="text-sm font-medium text-slate-500 mt-2">No requirements found{{ $search ? " for \"{$search}\"" : '' }}.</p>
                    @if($search || $filterFreq || $filterApproval !== '')
                        <button wire:click="$set('search', ''); $set('filterFreq', ''); $set('filterApproval', '')"
                            class="mt-3 text-xs text-indigo-600 hover:underline">Clear filters</button>
                    @else
                        <a href="{{ route('requirements.create') }}"
                            class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:underline">
                            <i class="bx bx-plus"></i> Add first requirement
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-between mt-4">
        <p class="text-xs text-slate-400">
            Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}
        </p>
        {{ $items->onEachSide(1)->links() }}
    </div>
</div>
