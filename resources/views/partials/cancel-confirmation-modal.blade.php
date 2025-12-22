{{-- resources/views/partials/cancel-confirmation-modal.blade.php --}}
@php
    /**
     * Expected vars:
     * - $id          : primary key (int/string)
     * - $route       : full URL (ex: route('monthly.budget.report.cancel', $report->id))
     * - $title       : modal title (optional)
     * - $buttonLabel : trigger label (optional)
     */
    $title = $title ?? 'Cancel Confirmation';
    $buttonLabel = $buttonLabel ?? 'Cancel';
@endphp

<div x-data="{ open: false }" class="inline-block">
    {{-- Trigger button --}}
    <button
        type="button"
        @click="open = true"
        class="inline-flex items-center rounded-md bg-amber-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-amber-700"
    >
        <i class='bx bx-x-circle mr-1 text-[0.9rem]'></i>
        <span class="hidden sm:inline">{{ $buttonLabel }}</span>
    </button>

    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-black/30"
        @click="open = false"
        @keydown.escape.window="open = false"
    ></div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        role="dialog"
        aria-modal="true"
    >
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h5 class="text-sm font-semibold text-slate-900">
                    {{ $title }}
                </h5>
                <button
                    type="button"
                    @click="open = false"
                    class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                >
                    <span class="sr-only">Close</span>
                    &times;
                </button>
            </div>

            <form action="{{ $route }}" method="post">
                @csrf
                @method('put')

                <div class="px-4 py-3 space-y-2">
                    <label
                        for="cancel_description_{{ $id }}"
                        class="block text-xs font-semibold text-slate-700"
                    >
                        Reason (Description)
                    </label>
                    <textarea
                        name="description"
                        id="cancel_description_{{ $id }}"
                        rows="5"
                        class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Tell us why you cancel this report..."
                        required
                    >{{ old('description') }}</textarea>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
                    <button
                        type="button"
                        @click="open = false"
                        class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Close
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700"
                    >
                        Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
