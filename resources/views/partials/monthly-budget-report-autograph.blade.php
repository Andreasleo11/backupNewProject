<section x-data="{
    rejectOpen: false,
    rejectStep: 'question',
    approveOpen: false,
    approveFormId: null,

    openReject() {
        this.rejectOpen = true;
        this.rejectStep = 'question';
    },
    closeReject() {
        this.rejectOpen = false;
        this.rejectStep = 'question';
    },
    openApprove(formId) {
        this.approveFormId = formId;
        this.approveOpen = true;
    },
    closeApprove() {
        this.approveOpen = false;
        this.approveFormId = null;
    },
    submitApprove() {
        if (this.approveFormId) {
            document.getElementById(this.approveFormId)?.submit();
        }
    }
}" x-cloak class="mb-6">
    {{-- GRID 3 KOLOM: Dibuat / Diketahui / Disetujui --}}
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 {{ $report->department->name === 'PLASTIC INJECTION' ? 'md:grid-cols-2' : 'md:grid-cols-3' }}  gap-4">

            {{-- ========== DIBUAT ========== --}}
            <div class="flex flex-col items-center">
                <h2 class="text-sm font-semibold text-slate-900 mb-2">Dibuat</h2>

                <div id="autographBox1"
                    class="w-[200px] h-[100px] bg-contain bg-no-repeat bg-center border border-slate-300 rounded-md">
                </div>
                <div id="autographUser1" class="mt-2 text-xs font-medium text-slate-700 text-center">
                </div>

                @php
                    $showCreatedAutograph = false;
                    if (!$report->created_autograph) {
                        $showCreatedAutograph = true;
                    }
                @endphp

                @if ($showCreatedAutograph && $report->is_reject === 0)
                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        {{-- REJECT --}}
                        <button type="button" @click="openReject()"
                            class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700">
                            Reject
                        </button>

                        {{-- APPROVE FORM + BUTTON --}}
                        <form action="{{ route('monthly.budget.save.autograph', $report->id) }}" method="POST"
                            id="formCreatedAutograph" class="hidden">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="created_autograph" value="{{ ucwords($authUser->name) }}">
                        </form>

                        <button type="button" @click="openApprove('formCreatedAutograph')"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700">
                            Approve
                        </button>
                    </div>
                @endif
            </div>

            {{-- ========== DIKETAHUI ========== --}}
            <div
                class="flex flex-col items-center
                       {{ $report->department->name === 'PLASTIC INJECTION' ? 'hidden opacity-30 pointer-events-none' : '' }}">
                <h2 class="text-sm font-semibold text-slate-900 mb-2">Diketahui</h2>

                <div id="autographBox2"
                    class="w-[200px] h-[100px] bg-contain bg-no-repeat bg-center border border-slate-300 rounded-md">
                </div>
                <div id="autographUser2" class="mt-2 text-xs font-medium text-slate-700 text-center">
                </div>

                @php
                    $showIsKnownAutograph = false;
                    if (
                        !$report->is_known_autograph &&
                        $authUser->is_head === 1 &&
                        $report->department->name !== 'PLASTIC INJECTION'
                    ) {
                        if ($authUser->department->name === $report->department->name) {
                            if (
                                $report->department->name === 'MOULDING' &&
                                $authUser->specification->name === 'DESIGN'
                            ) {
                                $showIsKnownAutograph = true;
                            } elseif (!$report->department->is_office) {
                                $showIsKnownAutograph = true;
                            }
                        } elseif ($report->department->name === 'STORE') {
                            if ($authUser->department->name === 'LOGISTIC') {
                                $showIsKnownAutograph = true;
                            }
                        }
                    }
                    $showIsKnownAutograph = $showIsKnownAutograph && $report->is_reject === 0;
                @endphp

                @if ($showIsKnownAutograph)
                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        {{-- REJECT --}}
                        <button type="button" @click="openReject()"
                            class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700">
                            Reject
                        </button>

                        {{-- APPROVE FORM + BUTTON --}}
                        <form action="{{ route('monthly.budget.save.autograph', $report->id) }}" method="POST"
                            id="formIsKnownAutograph" class="hidden">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_known_autograph" value="{{ ucwords($authUser->name) }}">
                        </form>

                        <button type="button" @click="openApprove('formIsKnownAutograph')"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700">
                            Approve
                        </button>
                    </div>
                @endif
            </div>

            {{-- ========== DISETUJUI ========== --}}
            <div class="flex flex-col items-center">
                <h2 class="text-sm font-semibold text-slate-900 mb-2">Disetujui</h2>

                <div id="autographBox3"
                    class="w-[200px] h-[100px] bg-contain bg-no-repeat bg-center border border-slate-300 rounded-md">
                </div>
                <div id="autographUser3" class="mt-2 text-xs font-medium text-slate-700 text-center">
                </div>

                @php
                    $showApprovedAutograph = false;
                    if (!$report->approved_autograph) {
                        if ($report->department->name === 'MOULDING') {
                            if ($authUser->is_head && $authUser->specification->name !== 'DESIGN') {
                                $showApprovedAutograph = true;
                            }
                        } elseif ($report->department->name === 'QC' || $report->department->name === 'QA') {
                            if ($authUser->specification->name === 'DIRECTOR') {
                                $showApprovedAutograph = true;
                            }
                        } elseif (!$report->department->is_office) {
                            if ($authUser->is_gm) {
                                $showApprovedAutograph = true;
                            }
                        }
                    }
                    $showApprovedAutograph = $showApprovedAutograph && $report->is_reject === 0;
                @endphp

                @if ($showApprovedAutograph)
                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        {{-- REJECT --}}
                        <button type="button" @click="openReject()"
                            class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700">
                            Reject
                        </button>

                        {{-- APPROVE FORM + BUTTON --}}
                        <form action="{{ route('monthly.budget.save.autograph', $report->id) }}" method="POST"
                            id="formApprovedAutograph" class="hidden">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="approved_autograph" value="{{ ucwords($authUser->name) }}">
                        </form>

                        <button type="button" @click="openApprove('formApprovedAutograph')"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700">
                            Approve
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ========== REJECT MODAL (Alpine, 2 STEP) ========== --}}
    <div x-show="rejectOpen" x-transition.opacity
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/40" @click.self="closeReject()"
        @keydown.escape.window="closeReject()">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200" x-transition>
            <form action="{{ route('monthly-budget-reports.reject', $report->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900"
                        x-text="rejectStep === 'question' ? 'Reject Confirmation' : 'Reject'"></h2>
                    <button type="button" @click="closeReject()"
                        class="rounded-full p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        &times;
                    </button>
                </div>

                <div class="px-4 py-4 text-sm text-slate-700">
                    {{-- Step 1: question --}}
                    <template x-if="rejectStep === 'question'">
                        <div id="prompt" class="space-y-2">
                            <p>
                                Are you sure you want to reject
                                <span class="font-semibold">{{ $report->doc_num }}</span>?
                            </p>
                        </div>
                    </template>

                    {{-- Step 2: description form --}}
                    <template x-if="rejectStep === 'form'">
                        <div class="space-y-2">
                            <label for="description" class="block text-xs font-medium text-slate-700">
                                Description
                            </label>
                            <textarea id="description" name="description" rows="4" required
                                class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                                placeholder="Tell us why you are rejecting this report..."></textarea>
                        </div>
                    </template>
                </div>

                <div class="px-4 py-3 border-t border-slate-100 flex justify-end gap-2 text-xs">
                    <template x-if="rejectStep === 'question'">
                        <div class="flex gap-2">
                            <button type="button" @click="closeReject()"
                                class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">
                                No
                            </button>
                            <button type="button" @click="rejectStep = 'form'"
                                class="inline-flex items-center rounded-md bg-slate-900 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-slate-800">
                                Yes
                            </button>
                        </div>
                    </template>

                    <template x-if="rejectStep === 'form'">
                        <div class="flex gap-2">
                            <button type="button" @click="closeReject()"
                                class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">
                                Close
                            </button>
                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-rose-700">
                                Confirm
                            </button>
                        </div>
                    </template>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== APPROVE MODAL (Alpine) ========== --}}
    <div x-show="approveOpen" x-transition.opacity
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/40" @click.self="closeApprove()"
        @keydown.escape.window="closeApprove()">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200" x-transition>
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">
                    Approval Confirmation
                </h2>
                <button type="button" @click="closeApprove()"
                    class="rounded-full p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    &times;
                </button>
            </div>

            <div class="px-4 py-4 text-sm text-slate-700">
                Are you sure want to approve this report?
            </div>

            <div class="px-4 py-3 border-t border-slate-100 flex justify-end gap-2 text-xs">
                <button type="button" @click="closeApprove()"
                    class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">
                    Close
                </button>
                <button type="button" @click="submitApprove()"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script>
        // Masih pakai JS lama untuk load file autograph
        document.addEventListener('DOMContentLoaded', checkAutographStatus);

        function checkAutographStatus() {
            var autographs = {
                autograph_1: '{{ $report->created_autograph ?? null }}',
                autograph_2: '{{ $report->is_known_autograph ?? null }}',
                autograph_3: '{{ $report->approved_autograph ?? null }}',
            };

            for (var i = 1; i <= 3; i++) {
                var autographBox = document.getElementById('autographBox' + i);
                var autographNameBox = document.getElementById('autographUser' + i);

                if (!autographBox || !autographNameBox) continue;

                if (autographs['autograph_' + i]) {
                    var fileKey = autographs['autograph_' + i];
                    var url = '/autographs/' + fileKey;

                    autographBox.style.backgroundImage = "url('" + url ;
                    var autographName = fileKey.split('.')[0];
                    autographNameBox.textContent = autographName;
                    autographNameBox.style.display = 'block';
                }
            }
        }
    </script>
@endpush
