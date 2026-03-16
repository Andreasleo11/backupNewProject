@php
    $title = $title ?? 'Cancel Confirmation';
    $buttonLabel = $buttonLabel ?? 'Cancel';
    $push = $push ?? false;
@endphp

<div x-data="{ open: false }" class="inline-block">
    {{-- Trigger button --}}
    @if(isset($iconOnly) && $iconOnly)
        <button type="button" @click="open = true"
            class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50 transition-all active:scale-95"
            title="{{ $buttonLabel }}">
            <i class="bx bx-x-circle text-lg"></i>
        </button>
    @else
        <button type="button" @click="open = true"
            class="inline-flex items-center rounded-md bg-amber-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-amber-700">
            <i class='bx bx-x-circle mr-1 text-[0.9rem]'></i>
            <span class="hidden sm:inline">{{ $buttonLabel }}</span>
        </button>
    @endif

    {{-- Teleport to body for full-screen overlay --}}
    <template x-teleport="body">
        <div>
            {{-- Backdrop --}}
            <div x-show="open" x-transition.opacity class="fixed inset-0 z-[100] bg-black/30 backdrop-blur-sm" @click="open = false"
                @keydown.escape.window="open = false" x-cloak></div>

            {{-- Modal --}}
            <div x-show="open" x-transition class="fixed inset-0 z-[110] flex items-center justify-center px-4" role="dialog"
                aria-modal="true" x-cloak>
                <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 overflow-hidden transform transition-all">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                        <h5 class="text-sm font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <i class="bx bx-info-circle text-amber-500"></i>
                            {{ $title }}
                        </h5>
                        <button type="button" @click="open = false"
                            class="rounded-full p-2 text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                            <i class="bx bx-x text-xl"></i>
                        </button>
                    </div>

                    <form action="{{ $route }}" method="post">
                        @csrf
                        @method('put')

                        <div class="px-6 py-6 space-y-4">
                            <div class="space-y-1.5">
                                <label for="cancel_description_{{ $id }}" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    Reason for Cancellation <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="description" id="cancel_description_{{ $id }}" rows="4"
                                    class="block w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 py-3 text-sm text-slate-900 shadow-sm transition-all focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10"
                                    placeholder="Please provide a reason for cancelling this report..." required>{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50/30">
                            <button type="button" @click="open = false"
                                class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-all hover:border-slate-400">
                                Close
                            </button>
                            <button type="submit"
                                class="inline-flex items-center rounded-xl bg-amber-600 px-6 py-2 text-xs font-bold text-white hover:bg-amber-700 shadow-lg shadow-amber-200 transition-all hover:scale-105 active:scale-95">
                                <i class="bx bx-check-circle mr-1.5 text-[0.9rem]"></i>
                                Confirm Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
