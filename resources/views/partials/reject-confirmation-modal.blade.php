{{-- resources/views/partials/reject-confirmation-modal.blade.php --}}
<div x-data="{ open: false, step: 'confirm' }" x-on:open-reject-confirmation.window="
        open = true;
        step = 'confirm';
    "
    x-on:keydown.escape.window="open = false" x-cloak>
    {{-- Backdrop --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/40" @click="open = false"></div>

    {{-- Modal --}}
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4" role="dialog"
        aria-modal="true">
        <form action="{{ $route }}" method="POST" class="w-full max-w-md">
            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <h1 class="text-sm font-semibold text-slate-900"
                        x-text="step === 'confirm' ? 'Reject Confirmation' : 'Reject'"></h1>

                    <button type="button" @click="open = false"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        <span class="sr-only">Close</span>
                        &times;
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-4 py-4 text-sm text-slate-700">
                    {{-- Step 1: konfirmasi --}}
                    <div x-show="step === 'confirm'">
                        <p class="text-center">
                            Are you sure you want to reject
                            <span class="font-semibold">
                                {{ $doc_num }}
                            </span>?
                        </p>
                    </div>

                    {{-- Step 2: isi alasan --}}
                    <div x-show="step === 'reason'" x-transition>
                        <label for="description" class="mb-1 block text-sm font-medium text-slate-800">
                            Description
                        </label>
                        <textarea id="description" name="description"
                            class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="4" placeholder="Tell us why you are rejecting this report..." required></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
                    {{-- Tombol kiri: No / Close --}}
                    <button type="button"
                        @click="
                            if (step === 'confirm') {
                                open = false;
                            } else {
                                step = 'confirm';
                            }
                        "
                        class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        x-text="step === 'confirm' ? 'No' : 'Close'"></button>

                    {{-- Step 1: Yes → lanjut ke reason --}}
                    <button type="button" x-show="step === 'confirm'" @click="step = 'reason'"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Yes
                    </button>

                    {{-- Step 2: Confirm → submit form --}}
                    <button type="submit" x-show="step === 'reason'"
                        class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1">
                        Confirm
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
