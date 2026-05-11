@section('title', 'Consolidated Overtime - ' . date('d M Y', strtotime($date)))
@section('page-title', 'Consolidated Overtime View')
@section('page-subtitle', 'Review all overtime forms for ' . date('l, d F Y', strtotime($date)) . ($branch ? ' - ' . $branch : '') . ($dept ? ' (Dept: ' . $dept . ')' : ''))

<div class="bg-transparent">
    <!-- Top Action Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('overtime.index') }}"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-200/60 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all">
                <i class='bx bx-arrow-back text-xl'></i>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-800 tracking-tight">
                    Consolidated View
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-sm text-slate-600">
                        {{ date('l, d F Y', strtotime($date)) }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-700">
                        <i class='bx bx-group'></i>
                        {{ $totalForms }} Forms, {{ $totalDetails }} Employees
                    </span>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="flex items-center gap-4">
            <div class="text-right">
                <div class="text-xs text-slate-500 font-bold">APPROVED</div>
                <div class="text-lg font-black text-emerald-600">{{ $approvedDetails }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-slate-500 font-bold">PENDING</div>
                <div class="text-lg font-black text-amber-600">{{ $pendingDetails }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-slate-500 font-bold">REJECTED</div>
                <div class="text-lg font-black text-rose-600">{{ $rejectedDetails }}</div>
            </div>
        </div>
    </div>

    <!-- Forms List -->
    <div class="space-y-4">
        @forelse ($headers as $form)
            <div class="rounded-xl border border-slate-200/60 bg-white p-6 shadow-sm">
                <!-- Form Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <i class='bx bx-file text-xl'></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800">
                                Form #{{ $form->id }}
                            </h3>
                            <div class="text-sm text-slate-600">
                                Created by {{ $form->user->name ?? 'Unknown' }} •
                                {{ $form->department->name ?? 'Unknown Dept' }} •
                                {{ $form->branch }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold
                            {{ strtoupper($form->workflow_status) === 'APPROVED'
                                ? 'bg-emerald-100 text-emerald-700'
                                : (strtoupper($form->workflow_status) === 'REJECTED'
                                    ? 'bg-rose-100 text-rose-700'
                                    : 'bg-amber-100 text-amber-700') }}">
                            {{ strtoupper($form->workflow_status) }}
                        </span>

                        @can('approve', $form)
                            @if($form->workflow_status === 'IN_REVIEW')
                                @php
                                    $currentStep = $form->approvalRequest?->steps->where('sequence', $form->approvalRequest->current_step)->first();
                                    $canSign = $currentStep && $currentStep->approver_snapshot_role_slug;
                                @endphp
                                @if($canSign)
                                    <button wire:click="sign({{ $form->id }}, {{ $currentStep->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-200 transition-all">
                                        <i class='bx bx-check'></i>
                                        Approve
                                    </button>
                                    <button wire:click="openRejectModal({{ $form->id }}, {{ $currentStep->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-200 transition-all">
                                        <i class='bx bx-x'></i>
                                        Reject
                                    </button>
                                @endif
                            @endif
                        @endcan

                        @can('pushToPayroll', $form)
                            @if($form->workflow_status === 'APPROVED' && $form->pending_count > 0)
                                <button wire:click="pushAll({{ $form->id }})"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-200 transition-all">
                                    <i class='bx bx-cloud-upload'></i>
                                    Push All
                                </button>
                            @endif
                        @endcan

                        <a href="{{ route('overtime.detail', $form->id) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-200 transition-all">
                            <i class='bx bx-right-arrow-alt'></i>
                            View Details
                        </a>
                    </div>
                </div>

                <!-- Details Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div class="text-center">
                        <div class="text-2xl font-black text-slate-800">{{ $form->details_count }}</div>
                        <div class="text-xs text-slate-500 font-bold uppercase">Total Employees</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-black text-emerald-600">{{ $form->approved_count }}</div>
                        <div class="text-xs text-slate-500 font-bold uppercase">Approved</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-black text-rose-600">{{ $form->rejected_count }}</div>
                        <div class="text-xs text-slate-500 font-bold uppercase">Rejected</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-black text-amber-600">{{ $form->pending_count }}</div>
                        <div class="text-xs text-slate-500 font-bold uppercase">Pending</div>
                    </div>
                </div>

                <!-- Details Table -->
                <div class="border-t border-slate-100 pt-4">
                    <h4 class="font-bold text-slate-700 mb-3">Employee Details</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b border-slate-200">
                                <tr class="text-left">
                                    <th class="px-3 py-2 font-bold text-slate-600">Employee</th>
                                    <th class="px-3 py-2 font-bold text-slate-600">Time</th>
                                    <th class="px-3 py-2 font-bold text-slate-600">Task</th>
                                    <th class="px-3 py-2 font-bold text-slate-600">Status</th>
                                    <th class="px-3 py-2 font-bold text-slate-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($form->details as $detail)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <div class="font-bold text-slate-800">{{ $detail->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $detail->NIK }}</div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="text-slate-700">
                                                {{ date('H:i', strtotime($detail->start_time)) }} -
                                                {{ date('H:i', strtotime($detail->end_time)) }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ date('d/m', strtotime($detail->start_date)) }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">{{ Str::limit($detail->job_desc, 30) }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold
                                                {{ $detail->status === 'Approved'
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : ($detail->status === 'Rejected'
                                                        ? 'bg-rose-100 text-rose-700'
                                                        : 'bg-slate-100 text-slate-600') }}">
                                                {{ $detail->status ?? 'Pending' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-1">
                                                @can('pushToPayroll', $form)
                                                    @if($form->workflow_status === 'APPROVED' && !$detail->status)
                                                        <button wire:click="pushDetail({{ $form->id }}, {{ $detail->id }})"
                                                            class="inline-flex items-center justify-center w-6 h-6 rounded bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors"
                                                            title="Push to Payroll">
                                                            <i class='bx bx-cloud-upload text-xs'></i>
                                                        </button>
                                                        <button wire:click="rejectDetail({{ $form->id }}, {{ $detail->id }})"
                                                            class="inline-flex items-center justify-center w-6 h-6 rounded bg-rose-100 text-rose-600 hover:bg-rose-200 transition-colors"
                                                            title="Reject Detail">
                                                            <i class='bx bx-x text-xs'></i>
                                                        </button>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200/60 bg-white p-12 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 mx-auto mb-4">
                    <i class='bx bx-calendar-x text-3xl text-slate-300'></i>
                </div>
                <h3 class="text-lg font-black text-slate-700">No overtime forms found</h3>
                <p class="text-slate-500 mt-1">No forms were found for the selected date and filters.</p>
            </div>
        @endforelse
    </div>

    <!-- Reject Modal -->
    @if($showRejectModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Reject Overtime Form</h3>
                <p class="text-slate-600 mb-4">Please provide a reason for rejection:</p>

                <textarea wire:model="rejectReason"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    rows="4"
                    placeholder="Enter rejection reason..."></textarea>

                @error('rejectReason')
                    <p class="text-rose-600 text-sm mt-1">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showRejectModal', false)"
                        class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="submitReject"
                        class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors">
                        Reject Form
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>