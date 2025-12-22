<div
    x-data="{ open: false }"
    x-on:open-ask-revision-{{ $report->id }}.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-cloak
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-black/40"
        @click="open = false"
    ></div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        role="dialog"
        aria-modal="true"
    >
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
            <form method="POST" action="{{ route('spk.revision', $report->id) }}" class="flex flex-col">
                @csrf
                @method('PUT')

                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
                    <h5 class="text-sm font-semibold text-slate-900">
                        Ask a Revision
                    </h5>
                    <button
                        type="button"
                        @click="open = false"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                    >
                        <span class="sr-only">Close</span>
                        &times;
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-5 py-4 space-y-2 text-sm">
                    <div>
                        <label for="revision_reason" class="block text-xs font-medium text-slate-700 mb-1">
                            Revision reason
                        </label>
                        <textarea
                            class="mt-1 block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            name="revision_reason"
                            id="revision_reason"
                            rows="5"
                            placeholder="Contoh: Layar laptop kembali bluescreen dan tidak bisa dinyalakan walaupun sudah dicoba dicabut colok."
                            required
                        ></textarea>
                        <p class="mt-1 text-[11px] text-slate-400">
                            Berikan alasan revisi yang jelas sehingga tim terkait bisa menindaklanjuti dengan tepat.
                        </p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 px-5 py-3 border-t border-slate-100">
                    <button
                        type="button"
                        @click="open = false"
                        class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                    >
                        Close
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                    >
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
