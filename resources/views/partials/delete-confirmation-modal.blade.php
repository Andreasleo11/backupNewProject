@php
    /**
     * Expected vars:
     * - $id         : primary key (int/string)
     * - $route      : route name (string), ex: 'monthly.budget.report.delete'
     * - $title      : modal title (optional)
     * - $body       : modal body (optional, HTML allowed)
     * - $buttonLabel: trigger button label (optional)
     */
    $title = $title ?? 'Delete confirmation';
    $body = $body ?? 'Are you sure you want to delete this item? This action cannot be undone.';
    $buttonLabel = $buttonLabel ?? 'Delete';
@endphp

<div x-data="{ open: false }" class="inline-block">
    {{-- Trigger button --}}
    <button type="button" @click="open = true"
        class="inline-flex items-center rounded-md bg-rose-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-rose-700">
        <i class="bx bx-trash-alt mr-1 text-[0.9rem]"></i>
        <span class="hidden sm:inline">{{ $buttonLabel }}</span>
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/30" @click="open = false"
        @keydown.escape.window="open = false"></div>

    {{-- Modal --}}
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4" role="dialog"
        aria-modal="true">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-900">
                    {!! $title !!}
                </h2>
                <button type="button" @click="open = false"
                    class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <span class="sr-only">Close</span>
                    &times;
                </button>
            </div>

            <div class="px-4 py-3 text-sm text-slate-600">
                {!! $body !!}
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
                <button type="button" @click="open = false"
                    class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>

                <form method="POST" action="{{ route($route, $id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                        <i class="bx bx-trash-alt mr-1 text-[0.9rem]"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
