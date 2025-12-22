@props(['approval', 'purchaseRequest', 'canApprove' => false])

<div x-data="{ open: true }" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex cursor-pointer items-center justify-between px-4 py-3 sm:px-6" @click="open = !open">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                Approval Workflow
            </p>
            @if ($approval)
                <p class="text-xs text-slate-500">
                    Status:
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold
                        @class([
                            'bg-slate-100 text-slate-700' => $approval->status === 'DRAFT',
                            'bg-amber-100 text-amber-700' => $approval->status === 'IN_REVIEW',
                            'bg-emerald-100 text-emerald-700' => $approval->status === 'APPROVED',
                            'bg-rose-100 text-rose-700' => $approval->status === 'REJECTED',
                        ])">
                        {{ $approval->status }}
                    </span>
                </p>
            @else
                <p class="text-xs text-slate-400">
                    Belum ada workflow approval (draft atau belum disubmit).
                </p>
            @endif
        </div>

        <button type="button"
            class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50">
            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.208l3.71-3.977a.75.75 0 111.1 1.02l-4.25 4.56a.75.75 0 01-1.1 0l-4.25-4.56a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd" />
            </svg>
            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M14.77 12.79a.75.75 0 01-1.06-.02L10 8.792 6.29 12.77a.75.75 0 01-1.1-1.02l4.25-4.56a.75.75 0 011.1 0l4.25 4.56a.75.75 0 01-.02 1.06z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-collapse class="border-t border-slate-100 px-4 py-4 sm:px-6">
        @if (!$approval)
            <p class="text-xs text-slate-500">
                Tidak ada data approval. PR ini mungkin masih draft atau belum dikirim ke workflow baru.
            </p>
        @else
            @php
                $steps = $approval->steps->sortBy('sequence');
            @endphp

            <ol class="space-y-3">
                @foreach ($steps as $step)
                    @php
                        $isCurrent =
                            $approval->status === 'IN_REVIEW' &&
                            (int) $approval->current_step === (int) $step->sequence;

                        $status = $step->status ?? 'PENDING';

                        $pillClasses =
                            [
                                'PENDING' => 'bg-slate-100 text-slate-600',
                                'APPROVED' => 'bg-emerald-100 text-emerald-700',
                                'REJECTED' => 'bg-rose-100 text-rose-700',
                            ][$status] ?? 'bg-slate-100 text-slate-600';

                        if ($step->approver_type === 'role') {
                            $role = \Spatie\Permission\Models\Role::find($step->approver_id);
                            $actorLabel = $role?->name ?? 'Unknown role';
                        } else {
                            $u = \App\Infrastructure\Persistence\Eloquent\Models\User::find($step->approver_id);
                            $actorLabel = $u?->name ?? 'User #' . $step->approver_id;
                        }

                        $actedByName = null;
                        if ($step->acted_by) {
                            $userAct = \App\Infrastructure\Persistence\Eloquent\Models\User::find($step->acted_by);
                            $actedByName = $userAct?->name ?? 'User #' . $step->acted_by;
                        }
                    @endphp

                    <li class="flex gap-3">
                        {{-- Timeline bullet --}}
                        <div class="mt-1.5 flex flex-col items-center">
                            <span
                                class="inline-flex h-3 w-3 rounded-full border-2
                                @class([
                                    'border-slate-300 bg-white' => $status === 'PENDING' && !$isCurrent,
                                    'border-indigo-500 bg-indigo-500' => $isCurrent,
                                    'border-emerald-500 bg-emerald-500' => $status === 'APPROVED',
                                    'border-rose-500 bg-rose-500' => $status === 'REJECTED',
                                ])"></span>
                            @if (!$loop->last)
                                <span class="mt-1 h-full w-px bg-slate-200"></span>
                            @endif
                        </div>

                        {{-- Card --}}
                        <div class="flex-1 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        @php
                                            // Sedikit "pretty name" untuk role utama
                                            $pretty = $actorLabel;
                                            $map = [
                                                'pr-dept-head-office' => 'Dept Head (Office)',
                                                'pr-dept-head-factory' => 'Dept Head (Factory)',
                                                'pr-head-design' => 'Head Design',
                                                'pr-gm-factory' => 'General Manager',
                                                'pr-verificator-personalia' => 'Verificator Personalia',
                                                'pr-verificator-computer' => 'Verificator Computer',
                                                'pr-purchaser' => 'Purchaser',
                                                'pr-director' => 'Director',
                                            ];
                                            if (isset($map[$actorLabel])) {
                                                $pretty = $map[$actorLabel];
                                            }
                                        @endphp
                                        {{ $pretty }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Step {{ $step->sequence }}
                                        @if ($actedByName)
                                            • oleh {{ $actedByName }}
                                        @endif
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($isCurrent && $status === 'PENDING')
                                        <span
                                            class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-600">
                                            Current
                                        </span>
                                    @endif

                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $pillClasses }}">
                                        {{ $status }}
                                    </span>
                                </div>
                            </div>

                            @if ($step->acted_at)
                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ $step->acted_at->format('d M Y H:i') }}
                                </p>
                            @endif

                            @if ($step->remarks)
                                <p class="mt-1 text-xs italic text-slate-600">
                                    “{{ $step->remarks }}”
                                </p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
            @if ($approval && $canApprove && $approval->status === 'IN_REVIEW')
                <div class="mt-5 rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25L15 9.75" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-semibold text-slate-900">Your action required</h4>
                            <p class="mt-1 text-xs text-slate-500">
                                Approve untuk lanjut ke step berikutnya, atau Reject dengan alasan.
                            </p>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                {{-- APPROVE --}}
                                <form method="POST" action="{{ route('purchase-requests.approve', $purchaseRequest) }}"
                                    class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                                    @csrf

                                    <label class="block text-[11px] font-semibold text-emerald-900">
                                        Remarks (optional)
                                    </label>
                                    <input name="remarks"
                                        class="mt-2 w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                                        placeholder="Contoh: OK, lanjut proses" />

                                    <button type="submit"
                                        class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve
                                    </button>
                                </form>

                                {{-- REJECT --}}
                                <form method="POST" action="{{ route('purchase-requests.reject', $purchaseRequest) }}"
                                    class="rounded-xl border border-rose-200 bg-rose-50/60 p-3">
                                    @csrf

                                    <label class="block text-[11px] font-semibold text-rose-900">
                                        Reject reason
                                    </label>
                                    <input name="remarks" required
                                        class="mt-2 w-full rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-rose-400 focus:outline-none focus:ring-4 focus:ring-rose-100"
                                        placeholder="Contoh: spesifikasi kurang jelas / budget tidak sesuai" />

                                    <button type="submit"
                                        class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        @endif
    </div>
</div>
