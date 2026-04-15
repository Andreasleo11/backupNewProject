@section('title', 'Form Overtime #' . $form->id)
@section('page-title', 'Form Overtime OT-' . $form->id)
@section('page-subtitle', 'Review approval progress and overtime details')

<div class="bg-transparent" x-data="{
    rejectOpen: false,
    signLoading: false,
    pushOpen: false,
}"
    x-on:toast.window="window.dispatchEvent(new CustomEvent('notify', { detail: $event.detail }))">
    <!-- Top Action Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('overtime.index') }}"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-200/60 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all">
                <i class='bx bx-arrow-back text-xl'></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-slate-800 tracking-tight">
                        Approval Status
                    </h1>
                    <span
                        class="rounded-lg px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest border 
                        {{ strtoupper($form->workflow_status) === 'APPROVED'
                            ? 'bg-emerald-100 text-emerald-700 border-emerald-200/50'
                            : (strtoupper($form->workflow_status) === 'REJECTED'
                                ? 'bg-rose-100 text-rose-700 border-rose-200/50'
                                : 'bg-amber-100 text-amber-700 border-amber-200/50') }}">
                        {{ str_replace(['-', '_'], ' ', $form->workflow_status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-3">
            @can('update', $form)
                <a href="{{ route('overtime.edit', $form->id) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-xs font-bold text-amber-700 shadow-sm transition-all hover:bg-amber-100 hover:border-amber-400">
                    <i class='bx bx-edit text-base'></i>
                    EDIT FORM
                </a>
            @endcan

            @can('export', $form)
                <a href="{{ route('overtime.export', $form->id) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 shadow-sm transition-all hover:bg-slate-50 hover:text-slate-800 focus:ring-2 focus:ring-slate-200">
                    <i class='bx bx-export text-base'></i>
                    EXPORT EXCEL
                </a>
            @endcan

            @if (strtoupper($form->workflow_status) === 'APPROVED' && $canPush)
                <button type="button" wire:click="pushAll" wire:loading.attr="disabled"
                    wire:confirm="Push all pending details to JPayroll?"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-black text-white shadow-lg shadow-indigo-500/30 transition-all hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:shadow-none">
                    <i class='bx bx-cloud-upload text-lg'></i>
                    PUSH TO PAYROLL
                </button>
            @endif
        </div>
    </div>

    <div class="space-y-6">

        {{-- ======================================================== META INFO GRID --}}
        <div class="glass-card overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-slate-200/50">
            <div class="px-6 py-4 border-b border-slate-100/60 bg-white/50 flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                    <i class='bx bx-info-circle text-lg'></i>
                </div>
                <h2 class="text-sm font-extrabold text-slate-800 tracking-tight">General Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @php
                        $metas = [
                            ['label' => 'Department', 'value' => $form->department?->name ?? '-'],
                            ['label' => 'Branch', 'value' => $form->branch ?? '-'],
                            ['label' => 'Type / Reason', 'value' => $form->is_planned ? 'Planned' : 'Urgent'],
                            ['label' => 'After Hour', 'value' => $form->is_after_hour ? 'Yes' : 'No'],
                            ['label' => 'Created by', 'value' => $form->user?->name ?? '-'],
                            [
                                'label' => 'Created at',
                                'value' => $form->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                            ],
                            ['label' => 'Pushed Status', 'value' => $form->is_push ? '✔ Yes' : 'No'],
                            ['label' => 'Notes', 'value' => $form->description ?? '-'],
                        ];
                    @endphp
                    @foreach ($metas as $m)
                        <div class="rounded-xl bg-slate-50/50 border border-slate-100/60 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">
                                {{ $m['label'] }}</p>
                            <p class="text-sm font-bold text-slate-800">{{ $m['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ======================================================== APPROVAL TIMELINE --}}
        <div class="glass-card overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-slate-200/50">
            <div class="px-6 py-4 border-b border-slate-100/60 bg-white/50 flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                    <i class='bx bx-check-shield text-lg'></i>
                </div>
                <h2 class="text-sm font-extrabold text-slate-800 tracking-tight">Approval Signature Timeline</h2>
            </div>

            <div class="overflow-x-auto p-6 custom-scrollbar">
                <div class="flex items-start gap-0 min-w-max">
                    @forelse ($timeline as $idx => $step)
                        <div class="flex flex-col items-center" style="width: 180px">
                            {{-- Connector line --}}
                            <div class="relative flex w-full items-center">
                                @if ($idx > 0)
                                    <div
                                        class="h-1 flex-1 {{ $step['status'] === 'approved' ? 'bg-emerald-400' : 'bg-slate-200' }}">
                                    </div>
                                @else
                                    <div class="flex-1"></div>
                                @endif

                                {{-- Circle Badge --}}
                                <div
                                    class="relative z-10 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl border-2 transition-all shadow-sm
                                {{ $step['status'] === 'approved' ? 'border-emerald-500 bg-emerald-50 text-emerald-600' : '' }}
                                {{ $step['status'] === 'rejected' ? 'border-rose-500 bg-rose-50 text-rose-600' : '' }}
                                {{ $step['status'] === 'pending' && $step['is_current'] ? 'border-amber-500 bg-amber-50 text-amber-600 ring-4 ring-amber-100 shadow-amber-200/50' : '' }}
                                {{ $step['status'] === 'pending' && !$step['is_current'] ? 'border-slate-200 bg-slate-50 text-slate-400' : '' }}
                            ">
                                    @if ($step['status'] === 'approved')
                                        <i class='bx bx-check text-2xl'></i>
                                    @elseif ($step['status'] === 'rejected')
                                        <i class='bx bx-x text-2xl'></i>
                                    @elseif ($step['is_current'])
                                        <i class='bx bx-edit-alt text-xl'></i>
                                    @else
                                        <span class="text-xs font-black">{{ $step['step_order'] }}</span>
                                    @endif
                                </div>

                                @if (!$loop->last)
                                    <div
                                        class="h-1 flex-1 {{ $step['status'] === 'approved' ? 'bg-emerald-400' : 'bg-slate-200' }}">
                                    </div>
                                @else
                                    <div class="flex-1"></div>
                                @endif
                            </div>

                            {{-- Metadata --}}
                            <div class="mt-4 text-center px-2">
                                <p class="text-xs font-black text-slate-800 tracking-tight">{{ $step['label'] }}</p>
                                @if ($step['approver_name'])
                                    <p class="mt-1 text-[11px] font-bold text-slate-500">{{ $step['approver_name'] }}
                                    </p>
                                @endif
                                @if ($step['signed_at'])
                                    <p class="mt-0.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        {{ \Carbon\Carbon::parse($step['signed_at'])->format('d M — H:i') }}</p>
                                @endif
                                @if ($step['signature_path'])
                                    <div
                                        class="mt-2 rounded-lg bg-slate-50 p-1 border border-slate-100/50 inline-block">
                                        <img src="{{ $step['signature_path'] }}" alt="signature"
                                            class="h-8 w-auto mix-blend-multiply opacity-80">
                                    </div>
                                @endif

                                {{-- Sign / Reject Actions --}}
                                @if ($step['can_sign'] && $step['status'] === 'pending')
                                    <div class="mt-3 flex gap-2 justify-center">
                                        <button type="button" wire:click="sign({{ $step['step_id'] }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-500 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-white shadow-md shadow-emerald-500/20 hover:bg-emerald-600 disabled:opacity-50 transition-all">
                                            <i class='bx bx-check text-sm'></i> Sign
                                        </button>
                                        <button type="button"
                                            x-on:click="rejectOpen = true; $wire.openRejectModal({{ $step['approval_id'] }})"
                                            class="inline-flex items-center gap-1 rounded-lg border-2 border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-rose-600 shadow-sm hover:bg-rose-100 hover:border-rose-300 transition-all">
                                            <i class='bx bx-x text-sm'></i> Reject
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center w-full py-8">
                            <div
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 mb-3 border border-slate-100">
                                <i class='bx bx-question-mark text-2xl'></i>
                            </div>
                            <p class="text-sm font-bold text-slate-500">No approval flow is attached to this form.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ======================================================= OVERTIME DETAILS TABLE --}}
        <div class="glass-card overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-slate-200/50">
            <div
                class="px-6 py-4 border-b border-slate-100/60 bg-white/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <i class='bx bx-spreadsheet text-lg'></i>
                    </div>
                    <h2 class="text-sm font-extrabold text-slate-800 tracking-tight">
                        Overtime Details
                        <span
                            class="ml-2 rounded-md bg-slate-100 border border-slate-200 px-1.5 py-0.5 text-[10px] font-black text-slate-600">
                            {{ $form->details->count() }}
                        </span>
                    </h2>
                </div>
                {{-- Detail stats --}}
                <div class="flex items-center gap-2">
                    @php $approved = $form->details->where('status', 'Approved')->count(); @endphp
                    @php $rejected = $form->details->where('status', 'Rejected')->count(); @endphp
                    @php $pending  = $form->details->whereNull('status')->count(); @endphp
                    @if ($approved)
                        <span
                            class="rounded-lg border border-emerald-200/50 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 uppercase tracking-wider shadow-sm"><i
                                class='bx bx-check'></i> {{ $approved }} Approved</span>
                    @endif
                    @if ($rejected)
                        <span
                            class="rounded-lg border border-rose-200/50 bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-700 uppercase tracking-wider shadow-sm"><i
                                class='bx bx-x'></i> {{ $rejected }} Rejected</span>
                    @endif
                    @if ($pending)
                        <span
                            class="rounded-lg border border-amber-200/50 bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 uppercase tracking-wider shadow-sm"><i
                                class='bx bx-time-five'></i> {{ $pending }} Pending</span>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-slate-100/60 text-xs text-left">
                    <thead class="bg-slate-50/80 text-[10px] font-black uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-6 py-3 whitespace-nowrap">Employee</th>
                            <th class="px-4 py-3 whitespace-nowrap">OT Date</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">Start</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">End</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center" title="Break Duration">BRK</th>
                            <th class="px-4 py-3 max-w-[200px]">Job Desc</th>
                            @if ($canReview)
                                <th class="px-4 py-3 whitespace-nowrap text-center border-l border-slate-200/60"
                                    colspan="3">Actual Logs (In / Out / Net)</th>
                                <th class="px-4 py-3 whitespace-nowrap text-center border-l border-slate-200/60">Status
                                </th>
                            @endif
                            @if (strtoupper($form->workflow_status) === 'APPROVED' && $canPush)
                                <th class="px-4 py-3 whitespace-nowrap text-center text-indigo-600">Payroll Push</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/60">
                        @forelse ($form->details as $detail)
                            @php
                                $actual = $detail->actualOvertimeDetail;
                                $rowClass = match ($detail->status) {
                                    'Approved' => 'bg-emerald-50/30',
                                    'Rejected' => 'bg-rose-50/30',
                                    default => 'bg-white hover:bg-slate-50/50',
                                };
                            @endphp
                            <tr class="{{ $rowClass }} transition-colors" wire:key="detail-{{ $detail->id }}">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ $detail->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400 mt-0.5">{{ $detail->NIK }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 font-medium">
                                    {{ $detail->overtime_date ? \Carbon\Carbon::parse($detail->overtime_date)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-slate-600">
                                    <div class="font-bold">
                                        {{ $detail->start_time ? substr($detail->start_time, 0, 5) : '-' }}</div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">
                                        {{ $detail->start_date ? \Carbon\Carbon::parse($detail->start_date)->format('d M y') : '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-slate-600">
                                    <div class="font-bold">
                                        {{ $detail->end_time ? substr($detail->end_time, 0, 5) : '-' }}</div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">
                                        {{ $detail->end_date ? \Carbon\Carbon::parse($detail->end_date)->format('d M y') : '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center font-black text-slate-400">
                                    {{ $detail->break }}m
                                </td>
                                <td class="px-4 py-3 text-slate-600 max-w-[200px]" title="{{ $detail->job_desc }}">
                                    <div class="truncate text-[11px] leading-tight">{{ $detail->job_desc }}</div>
                                </td>

                                {{-- Actual attendance --}}
                                @if ($canReview)
                                    <td class="px-4 py-3 whitespace-nowrap text-center border-l border-slate-100/60">
                                        <span
                                            class="font-mono text-[11px] font-bold {{ $actual ? 'text-indigo-600' : 'text-slate-300' }}">
                                            {{ $actual?->in_time ? substr($actual->in_time, 0, 5) : '--:--' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span
                                            class="font-mono text-[11px] font-bold {{ $actual ? 'text-indigo-600' : 'text-slate-300' }}">
                                            {{ $actual?->out_time ? substr($actual->out_time, 0, 5) : '--:--' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span
                                            class="font-black text-[11px] rounded bg-indigo-50 px-1.5 py-0.5 border border-indigo-100 {{ $actual ? 'text-indigo-700' : 'text-slate-300' }}">
                                            {{ $actual?->nett_overtime ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- Status badge --}}
                                    <td class="px-4 py-3 whitespace-nowrap text-center border-l border-slate-100/60">
                                        @if ($detail->status === 'Approved')
                                            <span
                                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 shadow-sm"
                                                title="Approved"><i class='bx bx-check'></i></span>
                                        @elseif ($detail->status === 'Rejected')
                                            <span
                                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-rose-600 shadow-sm"
                                                title="Rejected: {{ $detail->reason }}"><i
                                                    class='bx bx-x'></i></span>
                                        @else
                                            <span
                                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-600 shadow-sm"
                                                title="Pending"><i class='bx bx-time-five'></i></span>
                                        @endif
                                    </td>
                                @endif

                                {{-- JPayroll actions --}}
                                @if (strtoupper($form->workflow_status) === 'APPROVED' && $canPush)
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        @if ($detail->is_processed == 0 && $detail->status !== 'Rejected')
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" wire:click="pushDetail({{ $detail->id }})"
                                                    wire:loading.attr="disabled" title="Approve & Push to JPayroll"
                                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 transition disabled:opacity-50">
                                                    <i class='bx bx-cloud-upload'></i>
                                                </button>
                                                <button type="button" wire:click="rejectDetail({{ $detail->id }})"
                                                    wire:loading.attr="disabled" title="Reject Manual"
                                                    class="flex h-7 w-7 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 shadow-sm hover:bg-rose-100 transition disabled:opacity-50">
                                                    <i class='bx bx-x'></i>
                                                </button>
                                            </div>
                                        @elseif ($detail->is_processed == 1)
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span
                                                    class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 uppercase tracking-tighter">Synced</span>
                                                <button type="button" wire:click="unpushDetail({{ $detail->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:confirm="Remove this record from JPayroll? This will use Choice 3 (Delete)."
                                                    title="Un-push / Delete from JPayroll"
                                                    class="flex h-6 w-6 items-center justify-center rounded-lg border border-rose-200 bg-white text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition shadow-sm">
                                                    <i class='bx bx-trash-alt'></i>
                                                </button>
                                            </div>
                                        @else
                                            <span
                                                class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">Ignored</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-6 py-12 text-center">
                                    <div
                                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 border border-slate-100 mb-3">
                                        <i class='bx bx-spreadsheet text-2xl text-slate-400'></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-500">No detail records found in this form.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /container --}}

    {{-- ======================================================= REJECT MODAL --}}
    <template x-teleport="body">
        <div x-cloak x-show="rejectOpen" class="relative z-[60]">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" x-show="rejectOpen" x-transition.opacity
                @click="rejectOpen = false"></div>
            <div class="fixed inset-0 z-[70] flex items-center justify-center px-4" x-show="rejectOpen" x-transition
                role="dialog" aria-modal="true">
                <div class="w-full max-w-md rounded-2xl bg-white/90 backdrop-blur-2xl shadow-2xl border border-white p-6"
                    @click.stop>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2 text-rose-600">
                            <i class='bx bx-error-circle text-2xl'></i>
                            <h3 class="text-base font-black tracking-tight">Reject Form Overtime</h3>
                        </div>
                        <button type="button" @click="rejectOpen = false"
                            class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all">
                            <i class='bx bx-x text-xl'></i>
                        </button>
                    </div>

                    <div class="mb-6">
                        <label
                            class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Rejection
                            Reason <span class="text-rose-500">*</span></label>
                        <textarea wire:model="rejectReason" rows="3"
                            placeholder="Provide a clear rejection reason (minimum 5 characters)…"
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200 bg-white"></textarea>
                        @error('rejectReason')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="rejectOpen = false"
                            class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                            CANCEL
                        </button>
                        <button type="button" wire:click="submitReject" wire:loading.attr="disabled"
                            @click="rejectOpen = false"
                            class="rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-black text-white shadow-lg shadow-rose-500/30 hover:bg-rose-700 transition-all disabled:opacity-50 inline-flex items-center gap-2">
                            <i class='bx bx-check'></i> CONFIRM REJECT
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>
