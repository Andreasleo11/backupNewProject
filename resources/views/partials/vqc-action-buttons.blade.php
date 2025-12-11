{{-- Detail button --}}
<a href="{{ route('qaqc.report.detail', $report->id) }}"
    class="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white 
          px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm 
          hover:bg-gray-50 mr-2 my-1">
    <i class='bx bx-info-circle text-gray-500'></i>
    <span class="hidden sm:inline">Detail</span>
</a>

{{-- DEV ONLY --}}
{{-- 
<a href="{{ route('qaqc.report.preview', $report->id) }}"
   class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 
          text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 mr-2 my-1">
    Preview
</a>
--}}

@php
    // Hindari error kalau rejected_at null
    $hoursDifference = $report->rejected_at ? Date::now()->diffInHours($report->rejected_at) : 0;

    $autoReject = $hoursDifference > 24 && $report->is_approve === 2 && !$report->is_locked;

    $canEdit =
        $report->created_by === Auth::user()->name &&
        $hoursDifference <= 24 &&
        $report->is_approve != 1 &&
        !$report->is_locked;
@endphp

{{-- Auto reject setelah 24 jam (tetap tanpa Bootstrap) --}}
@if ($autoReject)
    <form class="hidden" action="{{ route('qaqc.report.rejectAuto', $report->id) }}" method="get"
        id="form-reject-report-{{ $report->id }}">
        <input type="hidden" name="description" value="Automatically rejected after 24 hours">
    </form>

    <script>
        document.getElementById('form-reject-report-{{ $report->id }}')?.submit();
    </script>
@endif

{{-- Edit button (hanya muncul kalau $canEdit = true) --}}
@if ($canEdit)
    <a href="{{ route('qaqc.report.edit', $report->id) }}"
        class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 
              text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 mr-2 my-1">
        <i class='bx bx-edit text-white'></i>
        <span class="hidden sm:inline">Edit</span>
    </a>
@endif


{{-- Wrapper modal: ditampilkan kalau openDeleteId === report id --}}
<div x-show="openDeleteId === {{ $report->id }}" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    @keydown.escape.window="openDeleteId = null">
    {{-- Click overlay to close --}}
    <div class="absolute inset-0" @click="openDeleteId = null"></div>

    {{-- Modal card --}}
    <div
        class="relative z-10 w-full max-w-md mx-4 rounded-2xl bg-white shadow-xl border border-gray-100
            transform transition-all">
        {{-- Header --}}
        <div class="px-5 pt-5 pb-2 flex items-start justify-between gap-3">
            <h5 class="text-sm font-semibold text-gray-900">
                Delete Confirmation
            </h5>
            <button type="button"
                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-gray-400
                    hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2
                    focus:ring-indigo-500 focus:ring-offset-1"
                @click="openDeleteId = null">
                <span class="sr-only">Close</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-5 pt-1 pb-4">
            <div class="flex items-start gap-3">
                <div class="mt-1 flex h-9 w-9 items-center justify-center rounded-full bg-red-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        class="h-5 w-5 text-red-600">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 9v4m0 4h.01M4.5 19.5h15l-7.5-15-7.5 15z" />
                    </svg>
                </div>
                <div class="text-sm text-gray-600">
                    <p class="mb-1">
                        Are you sure you want to delete
                        <span class="font-semibold text-gray-900">
                            {{ $report->doc_num }}
                        </span>?
                    </p>
                    <p class="text-xs text-gray-400">
                        This action cannot be undone.
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 pb-5 pt-2 flex items-center justify-end gap-2 border-t border-gray-100">
            <button type="button"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5
                    text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50
                    focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-1"
                @click="openDeleteId = null">
                Cancel
            </button>

            <form action="{{ route('qaqc.report.delete', $report->id) }}" method="POST" class="inline-flex"
                @submit="openDeleteId = null">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5
                        text-xs font-semibold text-white shadow-sm hover:bg-red-700
                        focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<button type="button"
    class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-100 border border-red-200 
    @if (
        $report->created_by !== Auth::user()->name ||
            $hoursDifference > 24 ||
            $report->autograph_3 ||
            $report->is_approve == 1 ||
            $report->is_locked) d-none @endif"
    @click="openDeleteId = {{ $report->id }}">
    Delete
</button>


{{-- Modal Lock Report --}}
<div x-show="openLockId === {{ $report->id }}" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @keydown.escape.window="openLockId = null">

    {{-- overlay click to close --}}
    <div class="absolute inset-0" @click="openLockId = null"></div>

    {{-- card --}}
    <div class="relative z-10 w-full max-w-md mx-4 rounded-2xl bg-white shadow-xl border border-gray-100">
        <form action="{{ route('qaqc.report.lock', $report->id) }}" method="get" @submit="openLockId = null">
            {{-- Header --}}
            <div class="px-5 pt-5 pb-2 flex items-start justify-between gap-3">
                <h5 class="text-sm font-semibold text-gray-900">
                    Lock Verification Report {{ $report->invoice_no }}
                </h5>
                <button type="button"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full text-gray-400
                               hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-1"
                    @click="openLockId = null">
                    <span class="sr-only">Close</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                            d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 pt-1 pb-4 text-sm text-gray-600">
                <p class="mb-2">
                    Once the report is locked, it cannot be modified or edited.
                </p>
                <p>
                    Are you sure you want to lock
                    <strong class="font-semibold text-gray-900">{{ $report->doc_num }}</strong>?
                </p>
            </div>

            {{-- Footer --}}
            <div class="px-5 pb-5 pt-2 flex items-center justify-end gap-2 border-t border-gray-100">
                <button type="button"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5
                               text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50
                               focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-1"
                    @click="openLockId = null">
                    Close
                </button>

                <button type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5
                               text-xs font-semibold text-white shadow-sm hover:bg-indigo-700
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

@php
    $lockDisabled = $report->is_locked || $report->is_approve;
@endphp

<div class="relative inline-block text-left" x-data="{ open: false }">
    {{-- Trigger button --}}
    <button type="button"
        class="inline-flex items-center gap-1 rounded-md border border-emerald-500 bg-emerald-50
                   px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm
                   hover:bg-emerald-100 focus:outline-none focus:ring-2
                   focus:ring-emerald-500 focus:ring-offset-1"
        @click="open = !open" @click.outside="open = false">
        More
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
            <path fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                clip-rule="evenodd" />
        </svg>
    </button>

    {{-- Dropdown panel --}}
    <div x-show="open" x-cloak
        class="absolute right-0 mt-1 w-44 origin-top-right rounded-md bg-white shadow-lg border border-gray-100
                z-40"
        x-transition.opacity x-transition.scale.origin.top>
        <div class="py-1 text-sm text-gray-700">
            {{-- Export PDF --}}
            <a href="{{ route('qaqc.report.download', $report->id) }}"
                class="flex w-full items-center gap-2 px-3 py-1.5 hover:bg-gray-50">
                <i class='bx bxs-file-pdf text-red-500'></i>
                <span>Export PDF</span>
            </a>

            {{-- Lock --}}
            <button type="button"
                @if (!$lockDisabled) @click="open = false; openLockId = {{ $report->id }}" @endif
                class="flex w-full items-center gap-2 px-3 py-1.5 text-left
                       @if ($lockDisabled) opacity-40 cursor-not-allowed pointer-events-none
                       @else
                           hover:bg-gray-50 @endif">
                <i class='bx bxs-lock text-amber-500'></i>
                <span>Lock</span>
            </button>
        </div>
    </div>
</div>
