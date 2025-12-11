<div class="max-w-6xl mx-auto mt-4">
    @php
        $approvalCount = $header->approvals->count();

        // Tentukan jumlah kolom maksimal 4
        $cols = min(4, max(1, $approvalCount));

        // Map ke class Tailwind yang statis (biar nggak kepurge)
        $lgColsClass = [
            1 => 'lg:grid-cols-1',
            2 => 'lg:grid-cols-2',
            3 => 'lg:grid-cols-3',
            4 => 'lg:grid-cols-4',
        ][$cols];
    @endphp

    <div class="grid gap-4 md:grid-cols-2 {{ $lgColsClass }}">
        @foreach ($header->approvals as $approval)
            <div class="flex flex-col items-center rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                {{-- Role --}}
                <div class="text-sm font-semibold text-slate-700">
                    {{ ucwords(str_replace('_', ' ', $approval->step->role_slug)) }}
                </div>

                {{-- Status badge --}}
                <div class="mt-1">
                    @if ($approval->status === 'approved')
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-800">
                            ✓ Signed {{ optional($approval->signed_at)->diffForHumans() }}
                        </span>
                    @elseif ($approval->status === 'rejected')
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-semibold text-rose-800">
                            ✗ Rejected
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                            ⏳ Pending
                        </span>
                    @endif
                </div>

                {{-- Signature box --}}
                <div class="mt-3 flex h-24 w-52 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                    @if ($approval->signature_path)
                        <img src="{{ asset('autographs/' . $approval->signature_path) }}"
                             alt="Signature"
                             class="h-full w-full object-contain">
                    @else
                        <span class="text-[11px] text-slate-400">No signature yet</span>
                    @endif
                </div>

                {{-- Approver info / buttons --}}
                <div class="mt-3 w-full">
                    @if ($approval->approver)
                        <div class="space-y-1 text-xs">
                            <div class="flex items-center justify-center gap-1 text-slate-500">
                                <i class="bi bi-calendar-event"></i>
                                <span>
                                    {{ optional($approval->signed_at)
                                        ? $approval->signed_at->timezone('Asia/Jakarta')->format('d-m-Y · H:i')
                                        : '—' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-center gap-1 text-slate-700">
                                <i class="bi bi-person-check-fill text-sky-500"></i>
                                <span class="font-semibold">
                                    {{ Str::title($approval->approver->name ?? 'Pending') }}
                                </span>
                            </div>
                        </div>
                    @else
                        @php
                            $isCurrentStep = optional($header->currentStep())->id === $approval->flow_step_id;
                            $isPending = $approval->status === 'pending';
                            $allowedByRole = false;
                            $user = auth()->user();

                            switch ($approval->step->role_slug) {
                                case 'dept_head':
                                    $allowedByRole =
                                        $user->is_head && $user->department->name === $approval->form->department->name;

                                    // Extra mapping LOGISTIC -> STORE, QC -> QA
                                    if (
                                        $user->is_head &&
                                        $user->department->name === 'LOGISTIC' &&
                                        $approval->form->department->name === 'STORE'
                                    ) {
                                        $allowedByRole = true;
                                    } elseif (
                                        $user->is_head &&
                                        $user->department->name === 'QC' &&
                                        $approval->form->department->name === 'QA'
                                    ) {
                                        $allowedByRole = true;
                                    }
                                    break;

                                case 'director':
                                    $allowedByRole = $user->specification->name === 'DIRECTOR';
                                    break;

                                case 'supervisor':
                                    $allowedByRole = $user->specification->name === 'SUPERVISOR';
                                    break;

                                case 'gm':
                                    $allowedByRole = $user->is_gm;
                                    break;

                                case 'creator':
                                    $allowedByRole = $approval->form->user_id === $user->id;
                                    break;
                            }

                            $showApprovalButtons = $isCurrentStep && $isPending && $allowedByRole;
                        @endphp

                        @if ($showApprovalButtons)
                            {{-- Tailwind + Alpine modal for Reject / Approve --}}
                            <div x-data="{ openReject: false }" class="mt-2 flex flex-col items-center gap-2">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        @click="openReject = true"
                                        class="inline-flex items-center justify-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1">
                                        Reject
                                    </button>

                                    <form
                                        action="{{ route('overtime.sign', ['id' => $approval->form->id, 'step_id' => $approval->flow_step_id]) }}"
                                        method="POST">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                                            Approve
                                        </button>
                                    </form>
                                </div>

                                {{-- Modal overlay --}}
                                <div
                                    x-show="openReject"
                                    x-cloak
                                    x-transition.opacity
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
                                    <div
                                        @click.away="openReject = false"
                                        x-transition
                                        class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl">
                                        <div class="mb-3 flex items-start justify-between gap-3">
                                            <div>
                                                <h2 class="text-base font-semibold text-slate-800">
                                                    Reason for Rejection
                                                </h2>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    Please provide a short explanation why this overtime form is rejected.
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                @click="openReject = false"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                                ✕
                                            </button>
                                        </div>

                                        <form action="{{ route('overtime.reject', $header->id) }}" method="POST" class="space-y-4">
                                            @method('PUT')
                                            @csrf

                                            <input type="hidden" name="approval_id" value="{{ $approval->id }}">

                                            <div>
                                                <label for="description-{{ $approval->id }}"
                                                       class="mb-1 block text-xs font-medium text-slate-700">
                                                    Description
                                                </label>
                                                <textarea
                                                    id="description-{{ $approval->id }}"
                                                    name="description"
                                                    required
                                                    rows="4"
                                                    class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-sky-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-300"></textarea>
                                            </div>

                                            <div class="flex justify-end gap-2 pt-1">
                                                <button
                                                    type="button"
                                                    @click="openReject = false"
                                                    class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                                    Cancel
                                                </button>
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1">
                                                    Confirm Reject
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
