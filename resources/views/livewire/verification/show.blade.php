<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    {{-- Header Bar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-200">
        <!-- Left: Back link, Title, Doc ID, Status -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('verification.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
                <span class="text-slate-300">|</span>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Verification Report</h1>
                
                @php
                    $statusColor = [
                        'DRAFT' => 'bg-slate-100 text-slate-800 border-slate-200',
                        'IN_REVIEW' => 'bg-amber-50 text-amber-800 border-amber-200/60',
                        'APPROVED' => 'bg-emerald-50 text-emerald-800 border-emerald-200/60',
                        'REJECTED' => 'bg-rose-50 text-rose-800 border-rose-200/60',
                    ][$report->status] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold tracking-wide border {{ $statusColor }}">
                    {{ str_replace('_', ' ', ucwords(strtolower($report->status), '_')) }}
                </span>
            </div>
            <div class="mt-1.5 flex items-center gap-2 text-xs text-slate-500 flex-wrap">
                <span>Doc#: <strong class="text-slate-800">{{ $report->document_number }}</strong></span>
                <span>•</span>
                <span>Created {{ $report->created_at->format('d M Y H:i') }}</span>
            </div>
        </div>

        <!-- Right: Actions Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Secondary operational actions -->
            @if ($this->legacyId && $this->areAllDoNumbersFilled)
                @if ($this->hasAdjustForm)
                    <form action="{{ route('adjustview') }}" method="get" class="inline-block m-0">
                        <input type="hidden" name="report_id" value="{{ $this->legacyId }}">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition">
                            <i class="bi bi-file-earmark-spreadsheet text-emerald-600"></i> View Adjust Form
                        </button>
                    </form>
                @else
                    <a href="{{ route('adjust.index', ['reports' => $this->legacyId]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition">
                        <i class="bi bi-sliders text-indigo-600"></i> Adjust Form
                    </a>
                @endif
            @endif
            
            {{-- PDF download: always available once report exists --}}
            <a href="{{ route('verification.download', $report->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="bi bi-file-earmark-pdf text-rose-600"></i> Export PDF
            </a>
            @if ($report->status !== 'DRAFT')
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition" x-data @click="$dispatch('open-mail-modal')">
                    <i class="bi bi-envelope"></i> Send Mail
                </button>
            @endif

            @can('update', $report)
                <a href="{{ route('verification.edit', $report->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
            @endcan
        </div>
    </div>

    <x-approval-status-banner :model="$report" />

    {{-- Metadata Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            {{-- Receive Date --}}
            <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-xs flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-calendar2-check"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1.5">Receive Date</div>
                    <div class="text-xs font-bold text-slate-800 truncate">{{ optional($report->rec_date)?->format('d M Y') ?? '—' }}</div>
                </div>
            </div>

            {{-- Verify Date --}}
            <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-xs flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-calendar2-event"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1.5">Verify Date</div>
                    <div class="text-xs font-bold text-slate-800 truncate">{{ optional($report->verify_date)?->format('d M Y') ?? '—' }}</div>
                </div>
            </div>

            {{-- Customer --}}
            <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-xs flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-building"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1.5">Customer</div>
                    <div class="text-xs font-bold text-slate-800 truncate" title="{{ $report->customer }}">
                        {{ $report->customer ?: '—' }}
                    </div>
                </div>
            </div>

            {{-- Invoice Number --}}
            <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-xs flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1.5">Invoice #</div>
                    <div class="text-xs font-bold text-slate-850 truncate" title="{{ $report->invoice_number }}">
                        {{ $report->invoice_number ?: '—' }}
                    </div>
                </div>
            </div>

            {{-- Department --}}
            <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-xs flex items-center gap-3 col-span-2 md:col-span-1">
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-shield-shaded"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1.5">Department</div>
                    <div class="text-xs font-bold text-slate-800 truncate">
                        {{ data_get($report->meta, 'department', '—') }}
                    </div>
                </div>
            </div>
        </div>

        @if (is_array($report->meta) && count(array_diff(array_keys($report->meta), ['department'])))
            <div class="mt-4 pt-4 border-t border-slate-100">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Extended Metadata</span>
                <div class="flex flex-wrap gap-x-6 gap-y-2">
                    @foreach ($report->meta as $key => $val)
                        @if ($key !== 'department')
                            <div class="text-xs">
                                <span class="text-slate-400 font-medium capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                <span class="text-slate-700 font-semibold ml-1">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Items Table --}}
    @php
        $byCurr = $report->items->groupBy(fn($i) => trim($i->currency ?? 'IDR') ?: 'IDR');
        $grandTotals = $byCurr
            ->map(function ($rows, $cur) {
                return [
                    'currency' => $cur,
                    'sum' => $rows->sum(fn($i) => (float) ($i->verify_quantity ?? 0) * (float) ($i->price ?? 0)),
                ];
            })
            ->values();
    @endphp

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <!-- Table Header Summary -->
        <div x-data class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/40 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-semibold text-slate-800 flex items-center gap-1.5">
                    <i class="bi bi-list-task text-slate-500"></i> Report Items
                </h3>
                <div class="text-xs text-slate-500 font-semibold flex items-center gap-2">
                    <span>Total Value:</span>
                    @foreach ($grandTotals as $gr)
                        <span class="bg-white border border-slate-200 text-slate-800 px-2.5 py-1 rounded font-mono text-xs font-bold shadow-xs">
                            {{ $gr['currency'] }} {{ number_format($gr['sum'], 2) }}
                        </span>
                    @endforeach
                </div>
            </div>
            
            <div class="flex items-center gap-2 ml-auto">
                <button type="button" class="inline-flex items-center justify-center font-semibold rounded border border-slate-200 text-slate-650 bg-white hover:bg-slate-50 text-[10px] px-2.5 py-1 transition-colors shadow-xs"
                    @click="$dispatch('toggle-defects', { open: true })">
                    <i class="bi bi-arrows-expand mr-1"></i>Expand all
                </button>
                <button type="button" class="inline-flex items-center justify-center font-semibold rounded border border-slate-200 text-slate-650 bg-white hover:bg-slate-50 text-[10px] px-2.5 py-1 transition-colors shadow-xs"
                    @click="$dispatch('toggle-defects', { open: false })">
                    <i class="bi bi-arrows-collapse mr-1"></i>Collapse all
                </button>
            </div>
        </div>

        <!-- Table Responsive Wrapper -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse align-middle">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-5 py-3 font-semibold text-center w-12">#</th>
                        <th class="px-5 py-3 font-semibold">Part Name</th>
                        <th class="px-4 py-3 text-end font-semibold">Rec Qty</th>
                        <th class="px-4 py-3 text-end font-semibold">Verify Qty</th>
                        <th class="px-4 py-3 text-end font-semibold">Can Use</th>
                        <th class="px-4 py-3 text-end font-semibold">Can't Use</th>
                        <th class="px-4 py-3 text-end font-semibold">Price</th>
                        <th class="px-4 py-3 font-semibold">Cur</th>
                        <th class="px-4 py-3 font-semibold">DO Number</th>
                        <th class="px-5 py-3 text-end font-semibold">Line Total</th>
                    </tr>
                </thead>
                @forelse($report->items as $idx => $i)
                    @php $line = (float)$i->verify_quantity * (float)$i->price; @endphp
                    
                    <tbody x-data="{ showDefects: false }"
                           @toggle-defects.window="showDefects = $event.detail.open"
                           class="divide-y divide-slate-100 border-b border-slate-100 last:border-0">
                        
                        {{-- Row with scoped loading target --}}
                        <tr @if($editDoItemId === $i->id) 
                                wire:loading.class="opacity-50" wire:target="saveDoNumber, cancelEditDoNumber" 
                            @else 
                                wire:loading.class="opacity-50" wire:target="startEditDoNumber({{ $i->id }})" 
                            @endif 
                            class="hover:bg-slate-50/10 text-xs text-slate-700 transition">
                            
                            <td class="px-5 py-3.5 text-center font-mono text-slate-400 font-bold w-12">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-col gap-1">
                                    <span class="font-semibold text-slate-900 text-sm">{{ $i->part_name }}</span>
                                    
                                    @if ($i->defects->count())
                                        <div class="flex items-center justify-between gap-2 mt-1">
                                            <button type="button" class="text-slate-500 hover:text-slate-850 cursor-pointer font-bold text-[9px] uppercase tracking-wider inline-flex items-center gap-1.5 select-none focus:outline-none"
                                                @click="showDefects = !showDefects">
                                                <i class="bi text-[9px] transition-transform duration-200" :class="showDefects ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                                                <span>Defect Details ({{ $i->defects->count() }})</span>
                                            </button>
                                            
                                            {{-- Inline Source Summary (shown only when collapsed) --}}
                                            <div x-show="!showDefects" class="flex gap-1 text-[8px] font-semibold text-slate-450">
                                                @php
                                                    $srcCounts = $i->defects->groupBy('source')->map->count();
                                                @endphp
                                                @foreach ($srcCounts as $sKey => $cnt)
                                                    <span>{{ ucfirst(strtolower($sKey)) }}: {{ $cnt }}</span>
                                                    @if(!$loop->last) <span class="text-slate-200">|</span> @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-end font-mono">
                                {{ number_format((int) $i->rec_quantity) }}
                            </td>
                            <td class="px-4 py-3.5 text-end font-mono font-semibold text-slate-900">
                                {{ number_format((int) $i->verify_quantity) }}
                            </td>
                            <td class="px-4 py-3.5 text-end font-mono text-emerald-600 font-medium">
                                {{ number_format((int) $i->can_use) }}
                            </td>
                            <td class="px-4 py-3.5 text-end font-mono text-rose-600 font-medium">
                                {{ number_format((int) $i->cant_use) }}
                            </td>
                            <td class="px-4 py-3.5 text-end font-mono">{{ number_format($i->price, 2) }}</td>
                            <td class="px-4 py-3.5 font-medium text-slate-500">{{ $i->currency }}</td>
                            <td class="px-4 py-3.5">
                                @if ($editDoItemId === $i->id)
                                    <div class="flex items-center gap-1">
                                        <input type="text" 
                                               class="text-xs bg-white rounded-lg border border-slate-200 py-1 px-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-28" 
                                               wire:model.defer="editDoNumber" 
                                               placeholder="DO Number">
                                        <button class="p-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition" 
                                                wire:click="saveDoNumber"
                                                title="Save">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                        <button class="p-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-lg shadow-sm transition" 
                                                wire:click="cancelEditDoNumber"
                                                title="Cancel">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-medium {{ $i->do_number ? 'text-slate-800' : 'text-slate-400 italic' }}">
                                            {{ $i->do_number ?: '—' }}
                                        </span>
                                        @can('update', $report)
                                            <button class="text-indigo-600 hover:text-indigo-800 transition p-0.5" 
                                                    wire:click="startEditDoNumber({{ $i->id }})"
                                                    title="Edit DO Number">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        @endcan
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-end font-mono font-semibold text-slate-900">
                                {{ $i->currency }} {{ number_format($line, 2) }}
                            </td>
                        </tr>
                        
                        {{-- Collapsible Defects Row --}}
                        @if ($i->defects->count())
                            <tr x-show="showDefects" class="bg-slate-50/20">
                                <td colspan="10" class="px-5 py-0 border-none">
                                    <div x-show="showDefects" x-collapse class="py-3 space-y-2">
                                        <div class="text-[9px] font-bold text-slate-450 uppercase tracking-widest mb-1.5">
                                            Logged Defects
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach ($i->defects as $d)
                                                <div class="flex items-start justify-between gap-3 p-2.5 rounded-lg border border-slate-100 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                                                    <div class="flex flex-col gap-0.5">
                                                        <div class="flex items-center gap-2">
                                                            {{-- Source Badge --}}
                                                            @php
                                                                $srcColors = match($d->source) {
                                                                    'CUSTOMER' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                                    'SUPPLIER' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                                    default => 'bg-blue-50 text-blue-700 border-blue-200',
                                                                };
                                                                $srcIcons = match($d->source) {
                                                                    'CUSTOMER' => 'bi-person-badge',
                                                                    'SUPPLIER' => 'bi-box-seam',
                                                                    default => 'bi-building',
                                                                };
                                                            @endphp
                                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[8px] font-bold border {{ $srcColors }}">
                                                                <i class="bi {{ $srcIcons }} text-[8px]"></i>
                                                                {{ $d->source }}
                                                            </span>
                                                            <span class="font-semibold text-slate-800 text-[11px]">{{ $d->name }}</span>
                                                        </div>
                                                        @if(!empty($d->notes))
                                                            <div class="text-[10px] text-slate-450 italic pl-1 mt-0.5">
                                                                <i class="bi bi-chat-left-text text-[9px] mr-1"></i>"{{ $d->notes }}"
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="text-right flex items-center gap-1 font-mono text-[10px] font-bold text-slate-900 bg-white border border-slate-200 px-1.5 py-0.5 rounded shadow-sm self-center">
                                                        <span class="text-slate-400 text-[9px] font-medium">Qty:</span>
                                                        <span>{{ number_format((int) ($d->quantity ?? 0)) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-slate-400">
                                <i class="bi bi-exclamation-circle text-lg block mb-1 text-slate-300"></i>
                                No items found in this verification report.
                            </td>
                        </tr>
                    </tbody>
                @endforelse
                @if ($report->items->count())
                    <tfoot>
                        @foreach ($grandTotals as $gr)
                            <tr class="bg-slate-50/20 text-xs font-bold text-slate-700 border-t border-slate-100">
                                <td colspan="9" class="px-5 py-3.5 text-end uppercase tracking-wider text-[10px] text-slate-400">Grand Total ({{ $gr['currency'] }})</td>
                                <td class="px-5 py-3.5 text-end font-mono text-sm text-slate-900">{{ $gr['currency'] }} {{ number_format($gr['sum'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tfoot>
                @endif
            </table>
        </div>

        @if ($report->status !== 'DRAFT')
            @include('partials.workflow-digital-signatures', ['record' => $report])
        @endif
    </div>

    {{-- Contextual Workflow Actions Section --}}
    @if ($report->status === 'IN_REVIEW' || auth()->user()?->can('update', $report))
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-1.5">
                <i class="bi bi-shield-check text-indigo-500"></i> Workflow Actions
            </h3>
            
            @can('update', $report)
                {{-- DRAFT Submission workflow --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Remarks (optional)</label>
                        <textarea rows="2" 
                                  class="block w-full text-sm bg-white rounded-lg border border-slate-200 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-400" 
                                  wire:model="remarks" 
                                  placeholder="Add notes for the approvers..."></textarea>
                    </div>
                    <button class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold rounded-xl shadow-sm transition disabled:opacity-50" 
                            wire:click="submit"
                            wire:loading.attr="disabled"
                            wire:target="submit">
                        <span wire:loading.remove wire:target="submit" class="flex items-center gap-2"><i class="bi bi-send"></i> Submit for Approval</span>
                        <span wire:loading wire:target="submit" class="flex items-center gap-2">
                            <span class="animate-spin inline-block w-3 h-3 border-2 border-current border-t-transparent text-white rounded-full"></span>
                            Submitting...
                        </span>
                    </button>
                </div>
            @elsecan('approve', $report)
                {{-- Active step approver workflow --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Remarks (optional)</label>
                        <textarea rows="2" 
                                  class="block w-full text-sm bg-white rounded-lg border border-slate-200 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-400" 
                                  wire:model="remarks" 
                                  placeholder="Reason or note for approval/rejection..."></textarea>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <button class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition min-w-[140px] disabled:opacity-50" 
                                wire:click="approve"
                                wire:loading.attr="disabled"
                                wire:target="approve">
                            <span wire:loading.remove wire:target="approve" class="flex items-center gap-2"><i class="bi bi-check2-circle"></i> Approve</span>
                            <span wire:loading wire:target="approve" class="flex items-center gap-2">
                                <span class="animate-spin inline-block w-3 h-3 border-2 border-current border-t-transparent text-white rounded-full"></span>
                                Approving...
                            </span>
                        </button>
                        
                        <button class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:bg-rose-50 hover:text-rose-600 active:bg-rose-100 text-rose-600 text-xs font-semibold rounded-xl shadow-sm transition disabled:opacity-50 sm:ml-auto" 
                                wire:click="reject"
                                wire:loading.attr="disabled"
                                wire:target="reject">
                            <span wire:loading.remove wire:target="reject" class="flex items-center gap-2"><i class="bi bi-x-circle"></i> Reject Report</span>
                            <span wire:loading wire:target="reject" class="flex items-center gap-2">
                                <span class="animate-spin inline-block w-3 h-3 border-2 border-current border-t-transparent text-rose-600 rounded-full"></span>
                                Rejecting...
                            </span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400">
                        Only the assigned approver for the current step can act; others will be blocked by the engine.
                    </p>
                </div>
            @else
                {{-- Read-only message for non-approvers during IN_REVIEW --}}
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-center">
                    <p class="text-xs text-slate-500">
                        <i class="bi bi-info-circle text-slate-400 mr-1"></i> This report is currently under review by the assigned approver.
                    </p>
                </div>
            @endcan
        </div>
    @endif

    {{-- Bottom Section: Side by Side Timeline and File Attachments --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Approval Timeline --}}
        @livewire('approval.timeline', [
            'approvableType' => \App\Infrastructure\Persistence\Eloquent\Models\VerificationReport::class,
            'approvableId' => $report->id,
        ])

        {{-- Related Documents Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 h-full">
            @include('partials.file-attachments', [
                'files' => $report->files,
                'showDelete' => auth()->user()?->can('update', $report) && $report->status === 'DRAFT',
                'showUpload' => auth()->user()?->can('update', $report) || auth()->user()?->can('approve', $report),
                'title' => 'Related Documents',
            ])
        </div>
    </div>
    {{-- Upload Files Modal --}}
    @include('partials.upload-files-modal', ['doc_id' => $report->document_number])

    {{-- Send Mail Modal --}}
    @if ($report->status !== 'DRAFT')
        @include('partials.mail-modal', [
            'report' => (object)[
                'id'              => $report->id,
                'customer'        => $report->customer,
                'files'           => $report->files,
                'has_been_emailed' => false,
            ]
        ])
    @endif
</div>
