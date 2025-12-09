@php
    use Illuminate\Support\Carbon;

    $monthLabel   = Carbon::parse(($month ?? now()->format('Y-m')) . '-01')->isoFormat('MMMM YYYY');
    $grandTotal   = $totals->sum('total_expense');
    $deptSelected = $deptId ? optional($totals->firstWhere('dept_id', $deptId))->dept_name : null;
@endphp

<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-4 space-y-4">

    {{-- Hero / toolbar --}}
    <section
        class="design-hero rounded-xl border border-slate-200 bg-white/90 shadow-sm px-4 py-3 sm:px-6 sm:py-4">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Left: title --}}
            <div class="flex items-center gap-3">
                <div class="hero-avatar hidden sm:inline-flex">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-slate-900">
                        Department Expenses
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500">
                        Unified from Purchase Requests &amp; Monthly Budget summary
                    </p>
                </div>
            </div>

            {{-- Right: filters --}}
            <div
                class="ml-auto flex flex-wrap items-center gap-2"
                x-data="tomMonthSelect({
                    value: @entangle('month').live,
                    options: @js($monthOptions),
                    placeholder: 'Select month…'
                })"
                x-init="mount()"
            >
                {{-- Month select (TomSelect) --}}
                <div wire:ignore class="w-56">
                    <div
                        class="flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs sm:text-sm text-slate-700 shadow-sm">
                        <span class="mr-2 text-slate-400">
                            <i class="bi bi-calendar2-month"></i>
                        </span>
                        <select x-ref="select" class="flex-1 bg-transparent text-xs sm:text-sm"></select>
                    </div>
                </div>

                {{-- PR signer select --}}
                <div class="w-64">
                    <div class="flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs sm:text-sm text-slate-700 shadow-sm">
                        <span class="mr-2 text-slate-400">
                            <i class="bi bi-person-check"></i>
                        </span>
                        <select
                            class="flex-1 bg-transparent text-xs sm:text-sm focus:outline-none"
                            wire:model.live="prSigner"
                        >
                            <option value="">All PR approvers</option>
                            @foreach ($prSigners as $signer)
                                <option value="{{ $signer }}">{{ $signer }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI chips --}}
        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs sm:text-[13px]">
            <span
                class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-slate-700">
                <i class="bi bi-calendar2-week mr-1 text-slate-400"></i>
                Period:
                <span class="ml-1 font-semibold">{{ $monthLabel }}</span>
            </span>

            <span
                class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-slate-700">
                <i class="bi bi-cash-coin mr-1 text-emerald-500"></i>
                Total:
                <span class="ml-1 font-semibold">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </span>
            </span>

            <span
                class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-slate-700">
                <i class="bi bi-diagram-3 mr-1 text-indigo-500"></i>
                Departments:
                <span class="ml-1 font-semibold">{{ $totals->count() }}</span>
            </span>

            @if ($deptSelected)
                <span
                    class="inline-flex items-center rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-indigo-700">
                    <i class="bi bi-pin-angle mr-1"></i>
                    Selected:
                    <span class="ml-1 font-semibold">{{ $deptSelected }}</span>
                </span>
            @endif

            @if ($prSigner)
                <span
                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-slate-700">
                    <i class="bi bi-person-check mr-1 text-slate-400"></i>
                    PR Approver:
                    <span class="ml-1 font-semibold">{{ $prSigner }}</span>
                </span>
            @endif
        </div>
    </section>

    {{-- Chart card --}}
    <section
        class="rounded-xl border border-slate-200 bg-white shadow-sm"
        wire:key="dept-expenses-chart"
        x-data="departmentChart()"
        x-init="init({
            labels: @js($totals->pluck('dept_name')->values()),
            data: @js($totals->pluck('total_expense')->map(fn($v) => (float) $v)->values()),
            deptIds: @js($totals->pluck('dept_id')->map(fn($v) => (int) $v)->values())
        })"
    >
        <div class="px-4 py-3 sm:px-6 sm:py-4">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">
                    Expenses by Department
                </h3>
                <span class="text-xs text-slate-500">
                    {{ \Illuminate\Support\Carbon::parse($month . '-01')->isoFormat('MMMM YYYY') }}
                </span>
            </div>

            <div class="chart-shell relative">
                <div wire:ignore class="h-full">
                    <canvas x-ref="canvas" role="img" aria-label="Department expenses bar chart"></canvas>
                </div>

                {{-- overlay loading --}}
                <div
                    class="chart-overlay pointer-events-none absolute inset-0 bg-white/50 backdrop-blur-sm"
                    wire:loading
                    wire:target="month,showDetail,clearDetail"
                ></div>
            </div>

            @if ($deptId)
                <div class="mt-2 flex justify-end">
                    <button
                        type="button"
                        class="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-700"
                        wire:click="clearDetail"
                    >
                        <i class="bi bi-x-circle mr-1"></i>
                        Clear highlight
                    </button>
                </div>
            @endif
        </div>
    </section>

    {{-- Content area: tabs + content --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- Tabs --}}
        <div class="border-b border-slate-200 px-4 pt-3 sm:px-6">
            <div class="flex space-x-4 text-xs sm:text-sm">
                <button
                    type="button"
                    wire:click="$set('activeTab','overview')"
                    @class([
                        'inline-flex items-center border-b-2 px-2 pb-2',
                        $activeTab === 'overview'
                            ? 'border-indigo-500 text-indigo-600 font-semibold'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                    ])
                >
                    <i class="bi bi-table mr-1"></i>
                    Overview
                </button>

                <button
                    type="button"
                    wire:click="$set('activeTab','detail')"
                    @class([
                        'inline-flex items-center border-b-2 px-2 pb-2',
                        $activeTab === 'detail'
                            ? 'border-indigo-500 text-indigo-600 font-semibold'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                    ])
                >
                    <i class="bi bi-list-ul mr-1"></i>
                    Details
                </button>

                <button
                    type="button"
                    wire:click="$set('activeTab','compare')"
                    @class([
                        'inline-flex items-center border-b-2 px-2 pb-2',
                        $activeTab === 'compare'
                            ? 'border-indigo-500 text-indigo-600 font-semibold'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                    ])
                >
                    <i class="bi bi-arrow-left-right mr-1"></i>
                    Compare
                </button>
            </div>
        </div>

        {{-- Tab panes --}}
        <div class="px-4 pb-4 pt-3 sm:px-6">

            {{-- OVERVIEW --}}
            @if ($activeTab === 'overview')
                <div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead>
                                <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
                                    <th class="px-3 py-2 font-medium">Department</th>
                                    <th class="px-3 py-2 font-medium">Dept No</th>
                                    <th class="px-3 py-2 text-right font-medium">Total Expense</th>
                                    <th class="px-3 py-2 text-right font-medium"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($totals as $r)
                                    <tr class="hover:bg-indigo-50/40">
                                        <td class="px-3 py-2 font-medium text-slate-900">
                                            {{ $r->dept_name }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-500">
                                            {{ $r->dept_no }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold">
                                            Rp {{ number_format($r->total_expense, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 hover:border-indigo-300 hover:text-indigo-600"
                                                wire:click="showDetail({{ (int) $r->dept_id }})"
                                            >
                                                View lines
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-slate-400 text-sm">
                                            <i class="bi bi-inbox mr-1"></i>
                                            No data for {{ $monthLabel }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($totals->count())
                                <tfoot>
                                    <tr class="border-t border-slate-200 bg-slate-50/80 text-sm">
                                        <th colspan="2" class="px-3 py-2 text-right font-medium text-slate-700">
                                            Grand Total
                                        </th>
                                        <th class="px-3 py-2 text-right font-semibold text-slate-900">
                                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                        </th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            @endif

            {{-- DETAIL --}}
            @if ($activeTab === 'detail')
                <div>
                    @if (!$deptId)
                        <div
                            class="rounded-lg border border-sky-100 bg-sky-50 px-3 py-2 text-xs text-sky-700">
                            <i class="bi bi-info-circle mr-1"></i>
                            Click a bar in the chart or the “View lines” button in the table to see details.
                        </div>
                    @else
                        <livewire:reports.department-expense-detail-table
                            :dept-id="$deptId"
                            :month="$month"
                            :dept-name="$deptSelected ?? $deptId"
                            :month-label="$monthLabel"
                            :pr-signer="$prSigner"
                            :key="'detail-' . $deptId . '-' . $month . '-' . ($prSigner ?? 'all')"
                        />
                    @endif
                </div>
            @endif

            {{-- COMPARE --}}
            @if ($activeTab === 'compare')
                <div
                    class="rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 sm:px-5 sm:py-4 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2 mb-3" x-data="compareChart()" x-init="mount()">
                        <h3 class="text-sm font-semibold text-slate-900 mr-2">
                            Compare
                        </h3>

                        {{-- Mode toggle --}}
                        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-xs">
                            <label class="inline-flex items-center rounded-md px-2 py-1 cursor-pointer"
                                :class="{'bg-indigo-50 text-indigo-700': @js($compareMode) === 'range'}">
                                <input
                                    type="radio"
                                    class="sr-only"
                                    value="range"
                                    wire:model.live="compareMode"
                                >
                                <i class="bi bi-arrow-left-right mr-1"></i> Range
                            </label>
                            <label class="inline-flex items-center rounded-md px-2 py-1 cursor-pointer"
                                :class="{'bg-indigo-50 text-indigo-700': @js($compareMode) === 'rolling'}">
                                <input
                                    type="radio"
                                    class="sr-only"
                                    value="rolling"
                                    wire:model.live="compareMode"
                                >
                                <i class="bi bi-graph-up-arrow mr-1"></i> Rolling
                            </label>
                        </div>

                        <div class="hidden h-5 w-px bg-slate-200 sm:inline-block mx-2"></div>

                        {{-- Range controls --}}
                        @if ($compareMode === 'range')
                            <div class="flex flex-wrap items-center gap-2">
                                <div
                                    x-data="tomMonthSelect({
                                        value: @entangle('startMonth').live,
                                        options: @js($monthOptions),
                                        placeholder: 'Start…'
                                    })"
                                    x-init="mount()"
                                    wire:key="cmp-range-start"
                                >
                                    <div wire:ignore class="w-44">
                                        <div
                                            class="flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 shadow-sm">
                                            <span class="mr-1 text-slate-400">Start</span>
                                            <select x-ref="select" class="flex-1 bg-transparent"></select>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    x-data="tomMonthSelect({
                                        value: @entangle('endMonth').live,
                                        options: @js($monthOptions),
                                        placeholder: 'End…'
                                    })"
                                    x-init="mount()"
                                    wire:key="cmp-range-end"
                                >
                                    <div wire:ignore class="w-44">
                                        <div
                                            class="flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 shadow-sm">
                                            <span class="mr-1 text-slate-400">End</span>
                                            <select x-ref="select" class="flex-1 bg-transparent"></select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Rolling controls --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <div
                                    x-data="tomMonthSelect({
                                        value: @entangle('endMonth').live,
                                        options: @js($monthOptions),
                                        placeholder: 'End…'
                                    })"
                                    x-init="mount()"
                                    wire:key="cmp-rolling-end"
                                >
                                    <div wire:ignore class="w-56">
                                        <div
                                            class="flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 shadow-sm">
                                            <span class="mr-2 text-slate-400">
                                                <i class="bi bi-calendar2-month"></i>
                                            </span>
                                            <select x-ref="select" class="flex-1 bg-transparent"></select>
                                        </div>
                                    </div>
                                </div>

                                <select
                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    wire:model.live="rollingN"
                                >
                                    <option value="3">Last 3</option>
                                    <option value="4">Last 4</option>
                                    <option value="5">Last 5</option>
                                    <option value="6">Last 6</option>
                                </select>
                            </div>
                        @endif

                        <div class="hidden h-5 w-px bg-slate-200 sm:inline-block mx-2"></div>

                        {{-- View toggles --}}
                        <label class="inline-flex items-center gap-1 text-xs text-slate-600">
                            <input
                                type="checkbox"
                                class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                x-model="stacked"
                                @change="update()"
                            >
                            <span>Stacked</span>
                        </label>

                        <label class="inline-flex items-center gap-1 text-xs text-slate-600">
                            <input
                                type="checkbox"
                                class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                x-model="normalized"
                                @change="update()"
                            >
                            <span>Normalize (%)</span>
                        </label>

                        <div class="ml-auto hidden text-xs text-slate-400 sm:inline">
                            Click any bar to open department details.
                        </div>
                    </div>

                    {{-- Compare chart --}}
                    <div class="chart-shell relative mt-2">
                        <div wire:ignore class="h-full">
                            <canvas x-ref="canvas"></canvas>
                        </div>
                        <div
                            class="chart-overlay pointer-events-none absolute inset-0 bg-white/50 backdrop-blur-sm"
                            wire:loading
                            wire:target="compareMode,startMonth,endMonth,rollingN,prSigner"
                        ></div>
                    </div>

                    {{-- Δ table (range & 2 months) --}}
                    @if (($compareMonths ?? null) && count($compareMonths) === 2 && ($compareDeltas ?? null))
                        @php
                            [$m0, $m1] = $compareMonths;
                            $ml0 = Carbon::parse($m0 . '-01')->isoFormat('MMM YYYY');
                            $ml1 = Carbon::parse($m1 . '-01')->isoFormat('MMM YYYY');
                        @endphp

                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-left text-xs sm:text-sm text-slate-700">
                                <thead>
                                    <tr class="border-b border-slate-200 text-[11px] uppercase text-slate-500">
                                        <th class="px-3 py-2 font-medium">Department</th>
                                        <th class="px-3 py-2 text-right font-medium">{{ $ml0 }}</th>
                                        <th class="px-3 py-2 text-right font-medium">{{ $ml1 }}</th>
                                        <th class="px-3 py-2 text-right font-medium">Δ</th>
                                        <th class="px-3 py-2 text-right font-medium">Δ%</th>
                                        <th class="px-3 py-2 text-right font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($compareDeltas as $row)
                                        @php
                                            $pos  = $row->diff > 0;
                                            $zero = $row->diff == 0;
                                            $badgeClasses = $zero
                                                ? 'bg-slate-100 text-slate-700'
                                                : ($pos
                                                    ? 'bg-emerald-50 text-emerald-700'
                                                    : 'bg-rose-50 text-rose-700');
                                        @endphp
                                        <tr class="hover:bg-indigo-50/40">
                                            <td class="px-3 py-2">{{ $row->dept_name }}</td>
                                            <td class="px-3 py-2 text-right">
                                                Rp {{ number_format($row->a, 0, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                Rp {{ number_format($row->b, 0, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClasses }}">
                                                    {{ $pos ? '+' : '' }}Rp
                                                    {{ number_format($row->diff, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                @if ($row->pct === null)
                                                    <span class="text-slate-400">—</span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClasses }}">
                                                        {{ ($row->pct > 0 ? '+' : '') . number_format($row->pct, 1, ',', '.') }}%
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 hover:border-indigo-300 hover:text-indigo-600"
                                                    wire:click="showDetail({{ $row->dept_id }})"
                                                >
                                                    View lines
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
</div>

@pushOnce('head')
    <style>
        .design-hero {
            background:
                radial-gradient(1200px 200px at -10% -20%, rgba(99, 102, 241, 0.15), transparent 60%),
                radial-gradient(800px 200px at 110% 120%, rgba(34, 197, 94, 0.12), transparent 60%),
                linear-gradient(0deg, #fff, #fff);
        }

        .hero-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6366f1 0%, #22c55e 100%);
            color: #fff;
            font-size: 1.2rem;
            box-shadow: 0 8px 24px rgba(99, 102, 241, .15);
        }

        .chart-shell {
            height: 380px;
        }

        .chart-overlay {
            transition: opacity 0.15s ease-out;
        }
    </style>
@endPushOnce

{{-- TomSelect helper --}}
@push('scripts')
    <script>
        function tomMonthSelect({ value, options, placeholder = 'Select…' }) {
            return {
                ts: null,
                value,
                options,
                mount() {
                    const sel = this.$refs.select;
                    sel.innerHTML = '';
                    for (const opt of (this.options || [])) {
                        const o = document.createElement('option');
                        o.value = opt.value;
                        o.textContent = opt.label;
                        sel.appendChild(o);
                    }

                    this.ts = new TomSelect(sel, {
                        maxItems: 1,
                        create: false,
                        allowEmptyOption: false,
                        persist: true,
                        selectOnTab: true,
                        placeholder,
                        plugins: ['dropdown_input'],
                        render: {
                            option: (data, escape) => `<div>${escape(data.text)}</div>`,
                            item: (data, escape)   => `<div>${escape(data.text)}</div>`,
                        },
                    });

                    if (this.value) {
                        this.ts.setValue(this.value, true);
                    }

                    this.ts.on('change', (v) => {
                        if (this.value !== v) this.value = v;
                    });

                    this.$watch('value', (v) => {
                        if (!this.ts) return;
                        const current = this.ts.getValue();
                        if (current !== v) {
                            if (v && !this.ts.options[v]) {
                                this.ts.addOption({ value: v, text: v });
                            }
                            this.ts.setValue(v || '', true);
                        }
                    });
                },
                destroy() {
                    this.ts?.destroy();
                    this.ts = null;
                },
            }
        }
    </script>
@endpush

{{-- Chart.js + Alpine controllers --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        function departmentChart() {
            return {
                chart: null,
                labels: [],
                data: [],
                deptIds: [],
                selectedDeptId: null,

                init(initial = {}) {
                    this.setData(initial);
                    this.mountChart();

                    // Livewire -> refresh dataset when month changes
                    window.addEventListener('chart:render', (e) => {
                        const payload = e.detail?.data ?? {};
                        requestAnimationFrame(() => {
                            this.setData(payload); // reset selection on new month
                            this.updateChart();
                        });
                    });

                    if (!this._boundClear) {
                        this._boundClear = true;
                        window.addEventListener('chart:clearSelection', () => this.clearSelection(), {
                            passive: true
                        });
                    }

                    if (!this._boundHighlight) {
                        this._boundHighlight = true;
                        window.addEventListener('chart:highlightDept', (e) => {
                            this.selectedDeptId = Number(e.detail?.deptId ?? null);
                            this.updateChart();
                        }, {
                            passive: true
                        });
                    }
                },

                clearSelection() {
                    this.selectedDeptId = null;
                    this.updateChart();
                },

                setData(payload = {}) {
                    this.labels = Array.isArray(payload.labels) ? payload.labels : [];
                    this.data = Array.isArray(payload.data) ? payload.data.map(Number) : [];
                    this.deptIds = Array.isArray(payload.deptIds) ? payload.deptIds.map(Number) : [];
                    this.selectedDeptId = null;
                },

                // Strong color for selected, soft for others
                makeColors() {
                    const idxSel = this.deptIds.findIndex(id => id === this.selectedDeptId);
                    const base = '54, 162, 235'; // blue-ish
                    const hiBg = `rgba(${base}, 0.9)`;
                    const hiBd = `rgba(${base}, 1)`;
                    const loBg = `rgba(${base}, 0.25)`;
                    const loBd = `rgba(${base}, 0.5)`;

                    return {
                        backgroundColor: this.labels.map((_, i) => i === idxSel ? hiBg : loBg),
                        borderColor: this.labels.map((_, i) => i === idxSel ? hiBd : loBd),
                    };
                },

                mountChart() {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;

                    const existing = Chart.getChart(canvas);
                    if (existing) existing.destroy();
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }

                    const ctx = canvas.getContext('2d');
                    const fmt = new Intl.NumberFormat('id-ID');
                    const col = this.makeColors();

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.labels,
                            datasets: [{
                                label: 'Total Expense (IDR)',
                                data: this.data,
                                borderWidth: 1,
                                backgroundColor: col.backgroundColor,
                                borderColor: col.borderColor,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (c) => ' ' + c.dataset.label + ': Rp ' + fmt.format(c.parsed.y)
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (v) => 'Rp ' + fmt.format(v)
                                    }
                                },
                                x: {
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                }
                            },
                            onClick: (evt) => {
                                const pts = this.chart.getElementsAtEventForMode(evt, 'nearest', {
                                    intersect: true
                                }, true);
                                if (!pts.length) return;
                                const idx = pts[0].index;
                                const deptId = this.deptIds[idx];

                                // 1) highlight instantly
                                this.selectedDeptId = deptId;
                                this.updateChart(); // recolor only, no data change

                                // 2) defer Livewire call (avoid re-entrancy)
                                const call = () => this.$wire.showDetail(deptId);
                                if ('requestIdleCallback' in window) requestIdleCallback(call);
                                else setTimeout(call, 0);
                            }
                        }
                    });
                },

                updateChart() {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;

                    const current = Chart.getChart(canvas);
                    if (!current || current !== this.chart) {
                        this.mountChart();
                        return;
                    }

                    current.data.labels = this.labels;
                    current.data.datasets[0].data = this.data;

                    const col = this.makeColors();
                    current.data.datasets[0].backgroundColor = col.backgroundColor;
                    current.data.datasets[0].borderColor = col.borderColor;

                    current.update('none');
                }
            }
        }
        // Alpine controller for the Compare chart
        function compareChart() {
            return {
                chart: null,
                raw: null, // { labels, deptIds, months, datasets:[{label, data:[]}, ...] }
                stacked: false,
                normalized: false,
                selDatasetIdx: null,
                selDeptIdx: null,

                mount() {
                    if (!this._bound) {
                        this._bound = true;
                        window.addEventListener('compare:render', (e) => {
                            this.raw = e.detail?.data || null;
                            this.buildOrUpdate();
                        }, {
                            passive: true
                        });
                    }
                },

                // per-dataset/per-bar colors with selection emphasis
                colorFor(datasetIdx, barIdx) {
                    const selected = this.selDatasetIdx === datasetIdx && this.selDeptIdx === barIdx;
                    const hue = Math.round((datasetIdx * 53) % 360);
                    const baseBg = `hsla(${hue}, 70%, 55%,`;
                    const baseBd = `hsla(${hue}, 70%, 40%, 1)`;
                    return {
                        bg: selected ? `${baseBg} 0.90)` : `${baseBg} 0.35)`,
                        bd: baseBd,
                    };
                },

                // Build a pleasant categorical palette
                color(i) {
                    // evenly-spaced hues, good contrast
                    const h = Math.round((i * 53) % 360);
                    return {
                        bg: `hsla(${h}, 70%, 55%, 0.75)`,
                        bd: `hsla(${h}, 70%, 40%, 1)`,
                    };
                },

                // Compute normalized datasets (% of month total) if toggled
                normalizedDatasets() {
                    if (!this.raw) return [];
                    const base = this.raw.datasets || [];
                    const mCount = base.length;
                    if (mCount === 0) return [];

                    // totals per month (sum of all departments)
                    const totals = [];
                    for (let j = 0; j < mCount; j++) {
                        totals[j] = (base[j].data || []).reduce((a, b) => a + (Number(b) || 0), 0);
                    }

                    return base.map((ds, j) => ({
                        label: ds.label + ' (%)',
                        data: (ds.data || []).map(v => {
                            const val = Number(v) || 0;
                            const t = totals[j] || 1;
                            return (val / t) * 100;
                        }),
                    }));
                },

                buildOrUpdate() {
                    if (!this.raw) return;

                    const canvas = this.$refs.canvas;
                    if (!canvas) return;

                    // empty state
                    if (!this.raw || !Array.isArray(this.raw.datasets) || this.raw.datasets.length === 0) {
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.font = '12px system-ui';
                        ctx.fillStyle = '#6c757d';
                        ctx.fillText('No comparison data', 12, 24);
                        if (this.chart) {
                            this.chart.destroy();
                            this.chart = null;
                        }
                        return;
                    }

                    // destroy if exists
                    const existing = Chart.getChart(canvas);
                    if (existing) existing.destroy();
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }

                    const ctx = canvas.getContext('2d');
                    const fmt = new Intl.NumberFormat('id-ID');

                    const labels = this.raw.labels || [];
                    const deptIds = (this.raw.deptIds || []).map(Number);
                    const datasets = this.normalized ? this.normalizedDatasets() : (this.raw.datasets || []);

                    // decorate datasets with palette
                    const chartDs = datasets.map((ds, i) => {
                        const c = this.color(i);
                        return {
                            label: ds.label,
                            data: (ds.data || []).map(Number),
                            backgroundColor: c.bg,
                            borderColor: c.bd,
                            borderWidth: 1,
                        };
                    });

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: chartDs
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const v = ctx.parsed.y;
                                            return this.normalized ?
                                                ` ${ctx.dataset.label}: ${v.toFixed(1)}%` :
                                                ` ${ctx.dataset.label}: Rp ${fmt.format(v)}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: this.stacked,
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                },
                                y: {
                                    stacked: this.stacked,
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (v) => this.normalized ? v + '%' : 'Rp ' + fmt.format(v)
                                    }
                                }
                            },
                            onClick: (evt) => this.handleClick(evt, deptIds),
                        }
                    });
                },

                handleClick(evt, deptIds) {
                    if (!this.chart) return;
                    const pts = this.chart.getElementsAtEventForMode(evt, 'nearest', {
                        intersect: true
                    }, true);
                    if (!pts.length) return;

                    const {
                        datasetIndex: di,
                        index: bi
                    } = pts[0]; // which dataset (month) and which bar (dept)
                    this.selDatasetIdx = di;
                    this.selDeptIdx = bi;

                    // Re-build to apply selection colors
                    this.buildOrUpdate();

                    const deptId = deptIds[bi];
                    const ym = (this.raw.months || [])[di]; // e.g. '2025-03'
                    const canvas = this.$refs.canvas;
                    const host = canvas?.closest('[wire\\:id]');
                    if (!host) return;
                    const comp = Livewire.find(host.getAttribute('wire:id'));
                    if (!comp) return;

                    comp.set('skipChartClearOnce', true);
                    comp.set('month', ym);
                    comp.set('activeTab', 'detail');

                    setTimeout(() => {
                        window.dispatchEvent(new CustomEvent('chart:highlightDept', {
                            detail: {
                                deptId
                            }
                        }));
                        comp.call('showDetail', deptId);
                    }, 50);
                },


                update() {
                    // rebuild with toggles applied
                    this.buildOrUpdate();
                }
            }
        }
    </script>
@endpush
