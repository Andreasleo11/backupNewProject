{{-- Department Compliance Detail — Livewire component view --}}
{{-- Tailwind, synced with new.layouts.app --}}

@section('title', $department->name . ' — Compliance')
@section('page-title', $department->name)
@section('page-subtitle', 'Document compliance requirements')

<div>
    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div
                class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0 font-bold text-white text-base">
                {{ strtoupper(mb_substr($department->name, 0, 2)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">{{ $department->name }}</h1>
                @if ($department->code)
                    <p class="text-sm text-slate-500 mt-0.5">{{ $department->code }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            {{-- Overall compliance bar --}}
            <div class="flex items-center gap-2">
                <div class="w-32 h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $percent >= 100 ? 'bg-emerald-500' : ($percent < 50 ? 'bg-rose-500' : 'bg-amber-500') }}"
                        style="width: {{ $percent }}%"></div>
                </div>
                <span
                    class="text-sm font-bold {{ $percent >= 100 ? 'text-emerald-600' : ($percent < 50 ? 'text-rose-600' : 'text-amber-600') }}">
                    {{ $percent }}%
                </span>
            </div>
            <a href="{{ route('departments.index') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 shadow-sm transition-all">
                <i class="bx bx-arrow-back text-base"></i> Departments
            </a>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="glass-card px-5 py-4 mb-5 flex flex-wrap items-center gap-3">
        {{-- Search --}}
        <div class="relative flex-1 min-w-[180px]">
            <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by code or name…"
                class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
        </div>

        {{-- Status filter chips --}}
        <div class="flex flex-wrap gap-2">
            @foreach (['all' => 'All', 'ok' => 'OK', 'pending' => 'Pending', 'missing' => 'Missing'] as $val => $label)
                @php
                    $active = $status === $val;
                    $colors = match ($val) {
                        'ok' => $active
                            ? 'bg-emerald-500 text-white border-emerald-500'
                            : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50',
                        'pending' => $active
                            ? 'bg-amber-500 text-white border-amber-500'
                            : 'border-amber-200 text-amber-600 hover:bg-amber-50',
                        'missing' => $active
                            ? 'bg-rose-500 text-white border-rose-500'
                            : 'border-rose-200 text-rose-600 hover:bg-rose-50',
                        default => $active
                            ? 'bg-slate-700 text-white border-slate-700'
                            : 'border-slate-200 text-slate-600 hover:bg-slate-50',
                    };
                @endphp
                <button wire:click="$set('status', '{{ $val }}')"
                    class="rounded-full border px-3 py-1 text-xs font-semibold transition-all {{ $colors }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Sort --}}
        <div class="flex items-center gap-2">
            <select wire:model.live="sort"
                class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
                <option value="code">Code</option>
                <option value="name">Name</option>
                <option value="percent">% Complete</option>
                <option value="expires">Expires</option>
            </select>
            <button wire:click="sortBy('{{ $sort }}')"
                class="h-9 w-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
                <i class="bx {{ $dir === 'asc' ? 'bx-sort-up' : 'bx-sort-down' }} text-lg"></i>
            </button>
        </div>

        {{-- Unmet toggle --}}
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <div class="relative">
                <input type="checkbox" wire:model.live="onlyUnmet" class="sr-only peer">
                <div class="w-9 h-5 rounded-full bg-slate-200 peer-checked:bg-rose-500 transition-colors"></div>
                <div
                    class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4">
                </div>
            </div>
            <span class="text-xs font-medium text-slate-600">Unmet only</span>
        </label>
    </div>

    {{-- Requirements list --}}
    <div class="glass-card overflow-hidden">
        <div class="divide-y divide-slate-50">
            @php $rows = $this->filteredSortedRows; @endphp
            @forelse($rows as $r)
                @php
                    $p = (int) $r['percent'];
                    $expires = $r['last_valid_until']?->format('d M Y');
                    $due = $r['next_due']?->format('d M Y');
                    $statusColors = [
                        'OK' => ['bg-emerald-100', 'text-emerald-700'],
                        'Pending' => ['bg-amber-100', 'text-amber-700'],
                        'Missing' => ['bg-rose-100', 'text-rose-700'],
                    ];
                    [$sbg, $stxt] = $statusColors[$r['status']] ?? ['bg-slate-100', 'text-slate-600'];
                    $barColor = $p >= 100 ? 'bg-emerald-500' : ($p < 50 ? 'bg-rose-500' : 'bg-amber-400');
                @endphp
                <div class="px-5 py-4 hover:bg-slate-50/50 transition-colors" wire:key="req-{{ $r['id'] }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">

                        {{-- Requirement info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-xs font-mono font-semibold text-slate-400">{{ $r['code'] }}</span>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $sbg }} {{ $stxt }}">
                                    {{ $r['status'] }}
                                </span>
                                @if ($r['pending'] > 0)
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-amber-50 text-amber-600 border border-amber-200">
                                        {{ $r['pending'] }} pending
                                    </span>
                                @endif
                                @if ($r['requires_approval'])
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs bg-indigo-50 text-indigo-600 border border-indigo-200">
                                        <i class="bx bx-shield-quarter text-xs"></i> Approval required
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-slate-800" title="{{ $r['allowed_summary'] }}">
                                {{ $r['name'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Min {{ $r['min'] }} document(s)</p>
                        </div>

                        {{-- Progress + actions --}}
                        <div class="flex flex-col items-end gap-3 shrink-0">
                            {{-- Progress bar --}}
                            <div class="flex items-center gap-2 w-44">
                                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="{{ $barColor }} h-full rounded-full transition-all"
                                        style="width: {{ $p }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-500 w-12 text-right">
                                    {{ $r['valid_count'] }}/{{ $r['min'] }}
                                </span>
                            </div>

                            {{-- Expiry / due --}}
                            @if ($expires)
                                <span class="text-xs text-slate-400">Expires
                                    <strong>{{ $expires }}</strong></span>
                            @elseif($due)
                                <span class="text-xs font-semibold text-rose-500">Due {{ $due }}</span>
                            @endif

                            {{-- Actions --}}
                            <div class="flex gap-2" x-data>
                                <button type="button"
                                    @click="$dispatch('trigger-upload-modal', { reqId: {{ $r['id'] }}, deptId: {{ $department->id }} })"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-xs font-semibold shadow-sm shadow-indigo-200 transition-all">
                                    <i class="bx bx-upload text-sm"></i> Upload
                                </button>
                                <button type="button"
                                    @click="$dispatch('trigger-history-modal', { reqId: {{ $r['id'] }}, deptId: {{ $department->id }} })"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-3 py-1.5 text-xs font-semibold transition-all">
                                    <i class="bx bx-history text-sm"></i> History
                                    @if ($r['pending'] > 0)
                                        <span
                                            class="inline-flex items-center justify-center h-4 w-4 rounded-full bg-amber-400 text-white text-[10px] font-bold">{{ $r['pending'] }}</span>
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center">
                    <i class="bx bx-search text-4xl text-slate-300"></i>
                    <p class="text-sm text-slate-400 mt-2">No requirements match your filters.</p>
                    <button wire:click="$set('search', ''); $set('status', 'all'); $set('onlyUnmet', false)"
                        class="mt-3 text-xs text-indigo-600 hover:underline">Clear filters</button>
                </div>
            @endforelse
        </div>
    </div>
</div>
@push('modals')
    {{-- Sub-components (upload slide-over, recent uploads modal) --}}
    <livewire:requirements.upload :key="'uploader-' . $department->id" />
    <livewire:requirements.recent-uploads />
@endpush
