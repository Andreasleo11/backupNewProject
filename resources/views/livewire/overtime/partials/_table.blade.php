{{-- ===== DATA TABLE =====
     Includes: empty state, grouped mode, individual row mode, skeleton loader.
     sortIcon() helper defined here since it's only used in this partial.
--}}
@php
    use App\Application\Overtime\Presenters\OvertimePresenter;

    $rp  = 'py-2.5 px-4';   // row padding
    $txt = 'text-[11px]';    // compact text

    if (!function_exists('sortIcon')) {
        function sortIcon($field, $current, $dir) {
            if ($current !== $field) return "<i class='bx bx-sort text-slate-300'></i>";
            return $dir === 'asc'
                ? "<i class='bx bx-sort-up text-indigo-600'></i>"
                : "<i class='bx bx-sort-down text-indigo-600'></i>";
        }
    }

    $anyChip = $range || ($startDate && $endDate) || $dept || $infoStatus || $search;
@endphp

@if ($dataheader->total() === 0 && !$anyChip)
    {{-- ===== ZERO-RECORD EMPTY STATE ===== --}}
    <div class="rounded-2xl bg-white border border-slate-100/80 shadow-sm py-16 px-6 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-slate-50 border border-slate-100 mb-4">
            <i class='bx bx-time text-4xl text-indigo-300'></i>
        </div>
        <h3 class="text-sm font-black text-slate-700 tracking-tight">No overtime requests yet</h3>
        <p class="mt-1.5 text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">
            Submit your first overtime request to get started.
        </p>
        @if (Auth::user()->department?->name !== 'MANAGEMENT')
            <a href="{{ route('overtime.create') }}"
                class="mt-5 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-black text-white shadow-sm shadow-indigo-300/40 hover:bg-indigo-700 transition-all">
                <i class='bx bx-plus-circle'></i> Create First Request
            </a>
        @endif
    </div>

@else
    {{-- ===== DATA TABLE WRAPPER ===== --}}
    <div class="overflow-hidden rounded-2xl bg-white border border-slate-200/60 shadow-sm">
        <div class="overflow-x-auto" wire:loading.class="opacity-60 pointer-events-none">

            {{-- ── Real table ── --}}
            <table class="min-w-full text-left align-middle"
                wire:loading.remove
                wire:target="resetFilters,setRange,sortBy,perPage,search,dept,startDate,endDate,infoStatus,clearFilter,gotoPage,nextPage,previousPage,toggleGroupByDate">

                <thead class="border-b border-slate-200/60 bg-slate-50/80 {{ $txt }} font-black uppercase tracking-widest text-slate-500">
                    <tr>
                        @if ($canApprove)
                            <th class="w-10 px-4 py-3">
                                <input type="checkbox" :checked="isAllSelected" @change="toggleAll"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                        @endif
                        <th wire:click="sortBy('id')" class="{{ $rp }} cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                            <div class="flex items-center gap-1"># {!! sortIcon('id', $sortField, $sortDirection) !!}</div>
                        </th>
                        <th class="{{ $rp }} whitespace-nowrap text-slate-400">Submitted By</th>
                        <th class="{{ $rp }} whitespace-nowrap">Department</th>
                        <th wire:click="sortBy('first_overtime_date')" class="{{ $rp }} cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                            <div class="flex items-center gap-1">Date {!! sortIcon('first_overtime_date', $sortField, $sortDirection) !!}</div>
                        </th>
                        <th wire:click="sortBy('workflow_status')" class="{{ $rp }} cursor-pointer hover:bg-slate-100 transition-colors whitespace-nowrap">
                            <div class="flex items-center gap-1">Status {!! sortIcon('workflow_status', $sortField, $sortDirection) !!}</div>
                        </th>
                        <th class="{{ $rp }} text-right pr-5 text-slate-400 whitespace-nowrap"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100/80 bg-white {{ $txt }}">

                    {{-- ── GROUP MODE ── --}}
                    @if ($groupByDate)
                        @forelse ($dataheader as $group)
                            <tr wire:key="group-{{ $group->date }}" class="hover:bg-indigo-50/20 transition-colors">

                                @if ($canApprove)
                                    <td class="px-4 py-3"><div class="w-4 h-4"></div></td>
                                @endif

                                {{-- # --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <span class="font-black text-slate-700">GROUP</span>
                                    <span class="mt-0.5 flex w-fit items-center rounded bg-indigo-50 px-1.5 py-0.5 text-[9px] font-black text-indigo-600">
                                        {{ $group->total_forms }} Forms
                                    </span>
                                </td>

                                {{-- Submitted By --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <span class="font-semibold text-slate-700">{{ $group->creators }}</span>
                                </td>

                                {{-- Department --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <span class="inline-flex rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-black text-slate-600">
                                        {{ $group->departments ?: 'Multiple' }}
                                    </span>
                                    <div class="text-[9px] text-slate-400 font-bold mt-0.5">
                                        {{ $group->branches }} · {{ $group->total_details }} employees
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <div class="font-bold text-slate-700">
                                        {{ $group->date ? date('D, d M Y', strtotime($group->date)) : '—' }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">Multiple forms</div>
                                </td>

                                {{-- Status --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wide {{ $group->consolidated_status['classes'] }}">
                                        <i class="bx {{ $group->consolidated_status['icon'] }} text-xs"></i>
                                        {{ $group->consolidated_status['label'] }}
                                    </span>
                                </td>

                                {{-- Action --}}
                                <td class="{{ $rp }} whitespace-nowrap text-right pr-5">
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
                                <td colspan="{{ $canApprove ? 7 : 6 }}" class="px-6 py-14 text-center">
                                    <i class='bx bx-calendar-x text-3xl text-slate-200 mb-2 block'></i>
                                    <p class="text-sm font-black text-slate-600">No overtime groups found</p>
                                    <p class="text-xs text-slate-400 mt-1">Try adjusting your date range or filters.</p>
                                </td>
                            </tr>
                        @endforelse

                    {{-- ── INDIVIDUAL ROW MODE ── --}}
                    @else
                        @forelse ($dataheader as $fot)
                            @php
                                $smart = OvertimePresenter::smartState($fot);
                            @endphp
                            <tr wire:key="row-{{ $fot->id }}"
                                class="hover:bg-indigo-50/20 transition-colors"
                                :class="selectedIds.includes('{{ $fot->id }}') ? 'bg-indigo-50/40' : ''">

                                @if ($canApprove)
                                    <td class="px-4 py-3">
                                        <input type="checkbox" x-model="selectedIds" value="{{ $fot->id }}"
                                            class="row-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                @endif

                                {{-- # --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <span class="font-black text-slate-800 tabular-nums">#{{ $fot->id }}</span>
                                    <span class="mt-0.5 flex w-fit rounded px-1.5 py-0.5 text-[9px] font-black
                                        {{ $fot->is_planned ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600 border border-rose-100/60' }}">
                                        {{ $fot->is_planned ? 'Planned' : 'Urgent' }}
                                    </span>
                                </td>

                                {{-- Submitted By --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">{{ $fot->user?->name ?? 'Unknown' }}</div>
                                </td>

                                {{-- Department --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <span class="inline-flex rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-black text-slate-600">
                                        {{ $fot->department?->name ?? '—' }}
                                    </span>
                                    <div class="text-[9px] text-slate-400 font-bold mt-0.5 uppercase">
                                        {{ $fot->branch }} · {{ $fot->is_after_hour ? 'After-Hour' : 'Standard' }} ({{ $fot->details_count }})
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <div class="font-bold text-slate-700 tabular-nums">
                                        {{ $fot->first_overtime_date ? date('D, d M Y', strtotime($fot->first_overtime_date)) : '—' }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">
                                        {{ $fot->created_at?->diffForHumans() }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="{{ $rp }} whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wide {{ $smart['classes'] }}">
                                        <i class="bx {{ $smart['icon'] }} text-xs"></i>
                                        {{ $smart['label'] }}
                                        @if ($smart['stage'] === 'signing' && isset($smart['current_role']))
                                            <span class="opacity-50 ml-0.5">{{ ucwords(str_replace(['_', '-'], ' ', $smart['current_role'])) }}</span>
                                        @elseif (in_array($smart['stage'], ['audit', 'sync', 'rejected']))
                                            <span class="opacity-50 ml-0.5">{{ $fot->approved_count + $fot->rejected_count }}/{{ $fot->details_count }}</span>
                                        @endif
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="{{ $rp }} whitespace-nowrap text-right pr-5">
                                    <div class="flex items-center justify-end gap-1">
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
                                <td colspan="{{ $canApprove ? 7 : 6 }}" class="px-6 py-14 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-200 border border-slate-100 mb-3">
                                        <i class='bx bx-filter-alt text-2xl'></i>
                                    </div>
                                    <p class="text-sm font-black text-slate-600">No results match your filters</p>
                                    <p class="text-xs text-slate-400 mt-1">Try adjusting the date range or removing filters.</p>
                                    <button wire:click="resetFilters"
                                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-600 hover:bg-slate-200 transition-all">
                                        <i class='bx bx-reset'></i> Clear Filters
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>

            {{-- ── Skeleton loader ── --}}
            @php $cols = $canApprove ? 7 : 6; @endphp
            <table class="min-w-full"
                wire:loading
                wire:target="resetFilters,setRange,sortBy,perPage,search,dept,startDate,endDate,infoStatus,clearFilter,gotoPage,nextPage,previousPage,toggleGroupByDate">
                <tbody class="divide-y divide-slate-100">
                    @for ($i = 0; $i < min(6, $perPage); $i++)
                        <tr>
                            @for ($j = 0; $j < $cols; $j++)
                                <td class="{{ $rp }} animate-pulse">
                                    <div class="h-3.5 rounded-lg bg-slate-100 {{ $j === 0 ? 'w-12' : ($j === $cols - 1 ? 'w-16 ml-auto' : 'w-full') }}"></div>
                                    @if ($j < 3)
                                        <div class="h-2.5 rounded-lg bg-slate-50 w-2/3 mt-1.5"></div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>

        </div>
    </div>
@endif
