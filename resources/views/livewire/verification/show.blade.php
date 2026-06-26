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
                    {{ $report->status }}
                </span>
            </div>
            <div class="mt-1.5 flex items-center gap-2 text-xs text-slate-500 flex-wrap">
                <span>Doc#: <strong class="text-slate-850">{{ $report->document_number }}</strong></span>
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

            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition" x-data @click="$dispatch('open-upload-modal')">
                <i class="bi bi-cloud-upload text-sky-600"></i> Upload
            </button>

            @if ($this->legacyId)
                <a href="{{ route('qaqc.report.savePdf', $this->legacyId) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition">
                    <i class="bi bi-file-earmark-pdf text-rose-600"></i> Export PDF
                </a>
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition" x-data @click="$dispatch('open-mail-modal')">
                    <i class="bi bi-envelope"></i> Send Mail
                </button>
            @endif

            <!-- Primary Edit Action (if draft) -->
            @can('update', $report)
                @if ($report->status === 'DRAFT')
                    <a href="{{ route('verification.edit', $report->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                @endif
            @endcan
        </div>
    </div>

    {{-- Metadata Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Receive Date</span>
                <span class="block text-sm font-semibold text-slate-800 mt-1">
                    {{ optional($report->rec_date)?->format('d M Y') ?? '—' }}
                </span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Verify Date</span>
                <span class="block text-sm font-semibold text-slate-800 mt-1">
                    {{ optional($report->verify_date)?->format('d M Y') ?? '—' }}
                </span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Customer</span>
                <span class="block text-sm font-semibold text-slate-800 mt-1 truncate" title="{{ $report->customer }}">
                    {{ $report->customer ?: '—' }}
                </span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Invoice #</span>
                <span class="block text-sm font-semibold text-slate-800 mt-1">
                    {{ $report->invoice_number ?: '—' }}
                </span>
            </div>
            <div class="col-span-2 md:col-span-1">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Department</span>
                <span class="block text-sm font-semibold text-slate-800 mt-1">
                    {{ data_get($report->meta, 'department', '—') }}
                </span>
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
                                <span class="text-slate-700 font-semibold ml-1">{{ is_array($val) ? json_encode($val) : $val }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Items Table --}}
    @php
        $monetary = (float) $report->items->sum(fn($i) => (float) $i->verify_quantity * (float) $i->price);
    @endphp

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <!-- Table Header Summary -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/40">
            <h3 class="text-sm font-semibold text-slate-800 flex items-center gap-1.5">
                <i class="bi bi-list-task text-slate-500"></i> Report Items
            </h3>
            <div class="text-xs text-slate-500 font-medium">
                Total Value: <span class="text-sm font-bold text-slate-850 ml-1 font-mono">{{ number_format($monetary, 2) }} {{ $report->items->first()?->currency ?? 'IDR' }}</span>
            </div>
        </div>

        <!-- Table Responsive Wrapper -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse align-middle">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
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
                <tbody class="divide-y divide-slate-100">
                    @forelse($report->items as $i)
                        @php $line = (float)$i->verify_quantity * (float)$i->price; @endphp
                        <tr wire:loading.class="opacity-50" wire:target="startEditDoNumber, cancelEditDoNumber, saveDoNumber" class="hover:bg-slate-50/10 text-xs text-slate-700 transition">
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $i->part_name }}</td>
                            <td class="px-4 py-3.5 text-end font-mono">
                                {{ rtrim(rtrim(number_format($i->rec_quantity, 4, '.', ''), '0'), '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-end font-mono font-semibold text-slate-900">
                                {{ rtrim(rtrim(number_format($i->verify_quantity, 4, '.', ''), '0'), '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-end font-mono text-emerald-600 font-medium">
                                {{ rtrim(rtrim(number_format($i->can_use, 4, '.', ''), '0'), '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-end font-mono text-rose-600 font-medium">
                                {{ rtrim(rtrim(number_format($i->cant_use, 4, '.', ''), '0'), '.') }}
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
                                            @if ($report->status !== 'APPROVED')
                                                <button class="text-indigo-650 hover:text-indigo-855 transition p-0.5" 
                                                        wire:click="startEditDoNumber({{ $i->id }})"
                                                        title="Edit DO Number">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-end font-mono font-semibold text-slate-900">
                                {{ number_format($line, 2) }}
                            </td>
                        </tr>
                        
                        {{-- Defects Row --}}
                        @if ($i->defects->count())
                            <tr class="bg-slate-50/20 border-b border-slate-100">
                                <td colspan="9" class="px-5 py-2.5">
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-2">Defects</span>
                                        @foreach ($i->defects as $d)
                                            <span class="inline-flex flex-wrap items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-white text-slate-700 border border-slate-100 shadow-xs">
                                                @if($d->code)
                                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 bg-slate-100 rounded text-slate-500 uppercase">{{ $d->code }}</span>
                                                @endif
                                                <strong class="text-slate-900 font-semibold">{{ $d->name }}</strong>
                                                <span class="text-slate-300">|</span>
                                                <span class="text-slate-500">{{ $d->severity }}</span>
                                                <span class="text-slate-300">|</span>
                                                <span class="text-slate-500">{{ $d->source }}</span>
                                                <span class="text-slate-300">|</span>
                                                <span class="font-bold text-indigo-600 font-mono">{{ rtrim(rtrim(number_format($d->quantity, 4, '.', ''), '0'), '.') }}</span>
                                                @if($d->notes)
                                                    <span class="text-slate-300">|</span>
                                                    <span class="text-slate-400 italic">"{{ $d->notes }}"</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-slate-450">
                                <i class="bi bi-exclamation-circle text-lg block mb-1 text-slate-300"></i>
                                No items found in this verification report.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($report->items->count())
                    <tfoot>
                        <tr class="bg-slate-50/20 text-xs font-bold text-slate-700 border-t border-slate-100">
                            <td colspan="8" class="px-5 py-3.5 text-end uppercase tracking-wider text-[10px] text-slate-400">Grand Total</td>
                            <td class="px-5 py-3.5 text-end font-mono text-sm text-slate-900">{{ number_format($monetary, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Contextual Workflow Actions Section --}}
    @if (($report->status === 'DRAFT' && auth()->user()?->can('update', $report)) || $report->status === 'IN_REVIEW')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-1.5">
                <i class="bi bi-shield-check text-indigo-500"></i> Workflow Actions
            </h3>
            
            @if ($report->status === 'DRAFT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Remarks (optional)</label>
                        <textarea rows="2" 
                                  class="block w-full text-sm bg-white rounded-lg border border-slate-250 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-405" 
                                  wire:model.live.defer="remarks" 
                                  placeholder="Add notes for the approvers..."></textarea>
                    </div>
                    <button class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold rounded-xl shadow-sm transition" 
                            wire:click="submit">
                        <i class="bi bi-send"></i> Submit for Approval
                    </button>
                </div>
            @elseif ($report->status === 'IN_REVIEW')
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Remarks (optional)</label>
                        <textarea rows="2" 
                                  class="block w-full text-sm bg-white rounded-lg border border-slate-250 py-2 px-3 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-slate-405" 
                                  wire:model.live.defer="remarks" 
                                  placeholder="Reason or note for approval/rejection..."></textarea>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <button class="inline-flex justify-center items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition min-w-[140px]" 
                                wire:click="approve">
                            <i class="bi bi-check2-circle"></i> Approve
                        </button>
                        
                        <button class="text-xs font-semibold text-rose-600 hover:text-rose-800 hover:underline transition" 
                                wire:click="reject">
                            Reject Report
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400">
                        Only the assigned approver for the current step can act; others will be blocked by the engine.
                    </p>
                </div>
            @endif
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
                'showDelete' => true,
                'title' => 'Related Documents',
            ])
        </div>
    </div>

    {{-- Modals --}}
    {{-- Upload Files Modal --}}
    @include('partials.upload-files-modal', ['doc_id' => $report->document_number])

    {{-- Send Mail Modal (Legacy) --}}
    @if ($this->legacyId)
        @include('partials.mail-modal', [
            'report' => (object)[
                'id' => $this->legacyId, 
                'customer' => $report->customer, 
                'files' => $report->files
            ]
        ])
    @endif
</div>
