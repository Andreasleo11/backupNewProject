<div x-data="{ open: false }" x-on:open-confirmation-{{ $id }}.window="open = true"
    x-on:keydown.escape.window="open = false" x-cloak>
    {{-- Backdrop --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/40" @click="open = false"></div>

    {{-- Modal --}}
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4" role="dialog"
        aria-modal="true">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <h5 class="text-sm font-semibold text-slate-900">
                    {{ $title }}
                </h5>
                <button type="button" @click="open = false"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <span class="sr-only">Close</span>
                    &times;
                </button>
            </div>

            {{-- Body --}}
            <div class="px-4 py-3 text-sm text-slate-600">
                {!! $body !!}
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-2 px-4 py-3 border-t border-slate-100">
                <button type="button" @click="open = false"
                    class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Close
                </button>

                {{-- Submit button dikirim dari Blade parent (boleh ada onclick JS, dll) --}}
                {!! $submitButton !!}
            </div>
        </div>
    </div>
</div>
