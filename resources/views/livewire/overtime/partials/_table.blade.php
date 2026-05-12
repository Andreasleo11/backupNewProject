{{-- ===== DATA TABLE — PR-synced style =====
     - Table card: bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden relative
     - Loading: absolute inset-0 overlay with spinner (not skeleton rows)
     - Table: w-full text-left border-separate border-spacing-0
     - Header: sticky top-0 z-10, bg-white shadow-sm ring-1 ring-slate-100
     - Header cells: px-4 py-4 border-b border-slate-100
     - Rows: hover:bg-slate-50/50 transition-colors
     - Cells: px-4 py-3
     - Pagination: inside table card footer
--}}
@php
    use App\Application\Overtime\Presenters\OvertimePresenter;

    if (!function_exists('sortIcon')) {
        function sortIcon($field, $current, $dir) {
            if ($current !== $field) return "<i class='bx bx-sort text-slate-300'></i>";
            return $dir === 'asc'
                ? "<i class='bx bx-sort-up text-indigo-500 ml-1'></i>"
                : "<i class='bx bx-sort-down text-indigo-500 ml-1'></i>";
        }
    }

    $anyChip = $range || ($startDate && $endDate) || $dept || $infoStatus || $search;
@endphp

@if ($dataheader->total() === 0 && !$anyChip)
    {{-- ===== ZERO-RECORD EMPTY STATE ===== --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm py-20 px-6 text-center">
        <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
            <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-4 border-2 border-dashed border-slate-100">
                <i class='bx bx-time text-3xl opacity-50'></i>
            </div>
            <h5 class="text-sm font-black text-slate-800 uppercase tracking-tight">No overtime requests yet</h5>
            <p class="text-[11px] text-slate-400 mt-1 font-medium leading-relaxed">
                Submit your first overtime request to get started. It only takes a minute.
            </p>
            @if (Auth::user()->department?->name !== 'MANAGEMENT')
                <a href="{{ route('overtime.create') }}"
                    class="mt-6 px-5 py-2 rounded-xl bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 transition-all hover:scale-105 active:scale-95 shadow-sm">
                    Create First Request
                </a>
            @endif
        </div>
    </div>

@else
    {{-- ===== DATA TABLE CARD ===== --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden relative">

        {{-- PR-style loading overlay (replaces skeleton rows) --}}
        <div wire:loading
            wire:target="resetFilters,setRange,sortBy,perPage,search,dept,startDate,endDate,infoStatus,clearFilter,gotoPage,nextPage,previousPage,toggleGroupByDate"
            class="absolute inset-0 z-20 bg-white/60 backdrop-blur-[2px] flex items-center justify-center rounded-2xl">
            <div class="flex items-center gap-3 bg-white rounded-2xl px-5 py-3 shadow-xl border border-slate-100">
                <div class="h-5 w-5 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">Loading…</span>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-separate border-spacing-0">

                {{-- Sticky header (PR style) --}}
                <thead class="sticky top-0 z-10">
                    <tr class="bg-white shadow-sm ring-1 ring-slate-100">
                        @if ($canApprove)
                            <th class="w-12 px-4 py-4 border-b border-slate-100 text-center">
                                <input type="checkbox" :checked="isAllSelected" @change="toggleAll"
                                    class="form-checkbox h-4 w-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer transition-all">
                            </th>
                        @endif
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition-colors"
                            wire:click="sortBy('id')">
                            # &amp; Type {!! sortIcon('id', $sortField, $sortDirection) !!}
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Submitted By
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Department
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-indigo-600 transition-colors"
                            wire:click="sortBy('first_overtime_date')">
                            Date {!! sortIcon('first_overtime_date', $sortField, $sortDirection) !!}
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                            Status
                        </th>
                        <th class="px-4 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50">

                    {{-- ── GROUP MODE ── --}}
                    @if ($groupByDate)
                        @forelse ($dataheader as $group)
                            <tr wire:key="group-{{ $group->date }}" class="hover:bg-slate-50/50 transition-colors group">

                                @if ($canApprove)
                                    <td class="px-4 py-3 text-center">
                                        <div class="w-4 h-4 mx-auto"></div>
                                    </td>
                                @endif

                                {{-- # & Type --}}
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-900 tracking-tight">GROUP</span>
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 w-fit mt-0.5">
                                            {{ $group->total_forms }} Forms
                                        </span>
                                    </div>
                                </td>

                                {{-- Submitted By --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-700 text-xs">{{ $group->creators }}</div>
                                </td>

                                {{-- Department --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-700 text-xs">{{ $group->departments ?: 'Multiple' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $group->branches }} · {{ $group->total_details }} employees
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-700 text-xs">
                                        {{ $group->date ? date('D, d M Y', strtotime($group->date)) : '—' }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">Multiple forms</div>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wide {{ $group->consolidated_status['classes'] }}">
                                        <i class="bx {{ $group->consolidated_status['icon'] }} text-xs"></i>
                                        {{ $group->consolidated_status['label'] }}
                                        @if($group->consolidated_status['stage'] != 'signing')
                                            <span class="opacity-50">
                                               {{ $group->total_approved_details }} / {{ $group->total_details }}
                                            </span>
                                        @endif
                                    </span>
                                </td>

                                {{-- Action --}}
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $consolidatedFilters = array_filter([
                                            'dept'        => $dept,
                                            'branch'      => $group->branches,
                                            'infoStatus'  => $infoStatus,
                                            'startDate'   => $startDate,
                                            'endDate'     => $endDate,
                                            'search'      => $search,
                                            'per_page'    => $perPage,
                                            'sort'        => $sortField,
                                            'dir'         => $sortDirection,
                                            'range'       => $range,
                                            'group_date'  => $groupByDate ? 1 : 0,
                                            'hide_signed' => $hideSigned ? 1 : 0,
                                        ]);
                                    @endphp
                                    <a href="{{ route('overtime.consolidated', ['date' => $group->date] + $consolidatedFilters) }}"
                                        class="inline-flex items-center gap-1 rounded-lg bg-slate-50 border border-slate-200 px-3 py-1.5 text-[10px] font-black text-slate-600 hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all">
                                        View <i class='bx bx-right-arrow-alt'></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canApprove ? 7 : 6 }}" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-4 border-2 border-dashed border-slate-100">
                                            <i class='bx bx-calendar-x text-3xl opacity-50'></i>
                                        </div>
                                        <h5 class="text-sm font-black text-slate-800 uppercase tracking-tight">No groups found</h5>
                                        <p class="text-[11px] text-slate-400 mt-1 font-medium leading-relaxed">Try adjusting your date range or filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                    {{-- ── INDIVIDUAL ROW MODE ── --}}
                    @else
                        @forelse ($dataheader as $fot)
                            @php $smart = OvertimePresenter::smartState($fot); @endphp
                            <tr wire:key="row-{{ $fot->id }}"
                                class="hover:bg-slate-50/50 transition-colors group"
                                :class="selectedIds.includes('{{ $fot->id }}') ? 'bg-indigo-50/40' : ''">

                                @if ($canApprove)
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" x-model="selectedIds" value="{{ $fot->id }}"
                                            class="row-checkbox form-checkbox h-4 w-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer transition-all">
                                    </td>
                                @endif

                                {{-- # & Type --}}
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-900 tracking-tight">#{{ $fot->id }}</span>
                                        <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[9px] font-bold mt-0.5 w-fit
                                            {{ $fot->is_planned ? 'bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-600/10' : 'bg-rose-50 text-rose-600 ring-1 ring-inset ring-rose-600/10' }}">
                                            {{ $fot->is_planned ? 'Planned' : 'Urgent' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Submitted By --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-700 text-xs">{{ $fot->user?->name ?? 'Unknown' }}</div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">{{ $fot->created_at?->diffForHumans() }}</div>
                                </td>

                                {{-- Department --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-700 text-xs">{{ $fot->department?->name ?? '—' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $fot->details_count }} {{ $fot->details_count === 1 ? 'employee' : 'employees' }}
                                        {{ $fot->is_after_hour ? '· After-Hour' : '' }}
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-700 text-xs">
                                        {{ $fot->first_overtime_date ? date('D, d M Y', strtotime($fot->first_overtime_date)) : '—' }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">
                                        {{ $fot->first_overtime_date ? \Carbon\Carbon::parse($fot->first_overtime_date)->diffForHumans() : '' }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wide {{ $smart['classes'] }}">
                                        <i class="bx {{ $smart['icon'] }} text-xs"></i>
                                        {{ $smart['label'] }}
                                        @if ($smart['stage'] === 'signing' && isset($smart['current_role']))
                                            <span class="opacity-50">{{ ucwords(str_replace(['_', '-'], ' ', $smart['current_role'])) }}</span>
                                        @elseif (in_array($smart['stage'], ['audit', 'sync', 'rejected']))
                                            <span class="opacity-50">{{ $fot->approved_count }}/{{ $fot->details_count }}</span>
                                        @endif
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('overtime.detail', $fot->id) }}"
                                            class="inline-flex items-center gap-1 rounded-lg bg-slate-50 border border-slate-200 px-3 py-1.5 text-[10px] font-black text-slate-600 hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all">
                                            Manage <i class='bx bx-right-arrow-alt'></i>
                                        </a>
                                        @can('delete', $fot)
                                            <button wire:click="$dispatch('confirm-delete', { id: {{ $fot->id }} })"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-300 hover:bg-rose-50 hover:text-rose-500 transition-all">
                                                <i class='bx bx-trash text-sm'></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canApprove ? 7 : 6 }}" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-4 border-2 border-dashed border-slate-100">
                                            <i class="bx bx-search-alt text-3xl opacity-50"></i>
                                        </div>
                                        <h5 class="text-sm font-black text-slate-800 uppercase tracking-tight">No matching requests found</h5>
                                        <p class="text-[11px] text-slate-400 mt-1 font-medium leading-relaxed">
                                            We couldn't find any overtime forms for the current filters or your visibility scope.
                                        </p>
                                        @if ($anyChip)
                                            <button wire:click="resetFilters"
                                                class="mt-6 px-5 py-2 rounded-xl bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 transition-all hover:scale-105 active:scale-95 shadow-sm">
                                                Clear all filters
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination footer (inside card — PR style) --}}
        @if ($dataheader->hasPages() || $dataheader->total() > 0)
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                    @if ($dataheader->firstItem())
                        Showing {{ $dataheader->firstItem() }} to {{ $dataheader->lastItem() }} of {{ number_format($dataheader->total()) }} total
                    @else
                        {{ number_format($dataheader->total()) }} records
                    @endif
                </div>
                @if ($dataheader->hasPages())
                    <div>{{ $dataheader->links() }}</div>
                @endif
            </div>
        @endif

    </div>
@endif
