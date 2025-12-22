<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" wire:ignore.self x-data="{ revokeOpen: false, revokeTarget: null, revokeLabel: '', revokeId: null }"
    x-on:open-revoke.window="
        revokeOpen = true;
        revokeTarget = $event.detail?.id ?? null;
        revokeLabel = $event.detail?.label ?? '—';
        revokeId = $event.detail?.id ?? null;
     "
    x-on:keydown.escape.window="revokeOpen = false">
    {{-- Header --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <h1 class="text-lg font-semibold text-slate-900">My Signatures</h1>
            <span
                class="inline-flex items-center rounded-full bg-slate-900 px-2.5 py-0.5 text-xs font-semibold text-white">
                {{ count($items) }} total
            </span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('signatures.capture') }}"
                class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{-- pencil icon --}}
                <svg class="-ml-0.5 mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z" stroke="currentColor" stroke-width="2"
                        stroke-linejoin="round" />
                </svg>
                Capture New
            </a>
        </div>
    </div>

    {{-- Empty state --}}
    @if (empty($items))
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-900 text-white">
                    {{-- info icon --}}
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 22a10 10 0 1 0-10-10 10 10 0 0 0 10 10Z" stroke="currentColor" stroke-width="2" />
                        <path d="M12 16v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M12 8h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="text-sm text-slate-700">
                    <div class="font-semibold text-slate-900">No signatures yet</div>
                    <div>Click <span class="font-semibold">Capture New</span> to add one.</div>
                </div>
            </div>
        </div>
    @else
        {{-- Grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($items as $sig)
                <div
                    class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
                    {{-- Top row --}}
                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Label</div>

                        @if ($sig['is_default'])
                            <span
                                class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                Default
                            </span>
                        @endif
                    </div>

                    {{-- Label --}}
                    <div class="mb-3 text-sm font-semibold text-slate-900">
                        {{ $sig['label'] ?? '—' }}
                    </div>

                    {{-- Signature preview --}}
                    <div
                        class="mb-4 flex h-[150px] items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <img src="{{ $sig['url'] }}" alt="Signature"
                            class="max-h-[130px] max-w-full object-contain" />
                    </div>

                    {{-- Actions --}}
                    <div class="mt-auto flex flex-wrap gap-2">
                        @unless ($sig['is_default'])
                            <button wire:click="setDefault({{ $sig['id'] }})" type="button"
                                class="inline-flex items-center rounded-xl border border-indigo-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{-- star icon --}}
                                <svg class="mr-1.5 h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 17.3l-5.4 3 1-6-4.6-4.5 6.2-.9L12 3l2.8 5.9 6.2.9-4.6 4.5 1 6L12 17.3Z"
                                        stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                </svg>
                                Set Default
                            </button>
                        @endunless

                        <button type="button"
                            x-on:click="$dispatch('open-revoke', { id: {{ $sig['id'] }}, label: @js($sig['label'] ?? '—') })"
                            class="inline-flex items-center rounded-xl border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                            {{-- x-circle icon --}}
                            <svg class="mr-1.5 h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 22a10 10 0 1 0-10-10 10 10 0 0 0 10 10Z" stroke="currentColor"
                                    stroke-width="2" />
                                <path d="M15 9l-6 6M9 9l6 6" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" />
                            </svg>
                            Revoke
                        </button>

                        <a href="{{ $sig['url'] }}" target="_blank"
                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                            {{-- external icon --}}
                            <svg class="mr-1.5 h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M14 3h7v7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                <path d="M21 3l-9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                <path d="M10 7H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-4" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" />
                            </svg>
                            Open
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Revoke Modal (single modal, reused for all items) --}}
    <div x-show="revokeOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center"
        aria-labelledby="revoke-title" role="dialog" aria-modal="true">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/50" x-on:click="revokeOpen = false"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-black/5" x-transition>
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 id="revoke-title" class="text-sm font-semibold text-slate-900">Revoke signature</h2>
                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                    x-on:click="revokeOpen = false" aria-label="Close">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <div class="px-5 py-4">
                <p class="text-sm text-slate-700">You are about to revoke this signature:</p>

                <div class="mt-3 space-y-1 text-sm text-slate-600">
                    <div>Label: <span class="font-semibold text-slate-900" x-text="revokeLabel"></span></div>
                    <div>ID: <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs" x-text="revokeId"></code></div>
                </div>

                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    <div class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 9v4m0 4h.01" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" />
                            <path d="M10.3 4.2 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z"
                                stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                        </svg>
                        <div>Revoked signatures cannot be used anymore.</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    x-on:click="revokeOpen = false">
                    Cancel
                </button>

                <button type="button"
                    class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                    x-on:click="
                            $wire.revoke(revokeTarget);
                            revokeOpen = false;
                        ">
                    Revoke
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-data="{ show: false, msg: '', timer: null }"
        x-on:toast.window="
            msg = $event.detail?.message ?? 'Done';
            show = true;
            clearTimeout(timer);
            timer = setTimeout(() => show = false, 2500);
         "
        class="pointer-events-none fixed bottom-4 right-4 z-[60]">
        <div x-show="show" x-transition x-cloak
            class="pointer-events-auto flex items-center gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg">
            <span x-text="msg"></span>
            <button type="button" class="rounded-lg p-1 text-white/80 hover:bg-white/10 hover:text-white"
                x-on:click="show = false" aria-label="Close toast">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
        </div>
    </div>
</div>
