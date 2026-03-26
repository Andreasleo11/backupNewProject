<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ }">
    {{-- Onboarding Banner --}}
    @if (session('onboarding_signature'))
        <div class="mb-8 overflow-hidden rounded-3xl bg-indigo-600 shadow-lg shadow-indigo-200" x-data="{ show: true }" x-show="show" x-transition>
            <div class="relative px-6 py-8 sm:px-12 sm:py-10">
                {{-- Decorative background elements --}}
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-indigo-500/50 blur-3xl"></div>
                <div class="absolute -bottom-12 left-1/4 h-48 w-48 rounded-full bg-indigo-400/30 blur-3xl"></div>
                
                <div class="relative flex flex-col items-center gap-6 sm:flex-row sm:gap-10">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20 backdrop-blur-md">
                        {{-- pen icon --}}
                        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                        </svg>
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <h2 class="text-2xl font-bold text-white sm:text-3xl">Ready to lead the way? ✍️</h2>
                        <p class="mt-2 text-lg text-indigo-100/90 leading-relaxed">
                            Your role is vital for official approvals. To make it official, we need your <span class="font-semibold text-white">digital signature</span> on file. 
                            It's your "digital badge" that ensures every approval is secure, verified, and professional.
                        </p>
                    </div>

                    <div class="shrink-0">
                        <button type="button" x-on:click="show = false" class="rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20 focus:outline-none">
                            Got it!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
            <a href="{{ route('signatures.capture', $return_to ? ['return_to' => $return_to] : []) }}"
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

    {{-- Revoke Modal (Pushed to layout stack and teleported to body) --}}
    @push('modals')
        <div x-data="{ 
                revokeOpen: false, 
                revokeTarget: null, 
                revokeLabel: '', 
                revokeId: null 
            }"
            x-on:open-revoke.window="
                revokeOpen = true;
                revokeTarget = $event.detail?.id ?? null;
                revokeLabel = $event.detail?.label ?? '—';
                revokeId = $event.detail?.id ?? null;
            "
            x-on:keydown.escape.window="revokeOpen = false"
            x-cloak>
            
            <template x-teleport="body">
                <div x-show="revokeOpen" class="fixed inset-0 z-[100] flex items-center justify-center"
                    aria-labelledby="revoke-title" role="dialog" aria-modal="true">

                    {{-- Backdrop --}}
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="revokeOpen = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

                    {{-- Panel --}}
                    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl ring-1 ring-black/5" 
                        x-show="revokeOpen"
                        x-transition:enter="transition ease-out duration-300" 
                        x-transition:enter-start="opacity-0 translate-y-4 scale-95" 
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
                        x-transition:leave="transition ease-in duration-200" 
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                        x-transition:leave-end="opacity-0 translate-y-4 scale-95">
                        
                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                            <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Security Action</h2>
                            <button type="button" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-all duration-200"
                                x-on:click="revokeOpen = false" aria-label="Close">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M6 6l12 12M18 6L6 18" stroke-width="2.5" stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-6 py-10 text-center">
                            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 ring-4 ring-rose-50/50 transition-transform duration-500 hover:scale-110">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>

                            <h3 id="revoke-title" class="text-xl font-extrabold text-slate-900 tracking-tight">Revoke this signature?</h3>
                            <p class="mt-3 text-sm text-slate-500 leading-relaxed max-w-[280px] mx-auto">
                                This action is permanent. Once revoked, this signature will be <span class="text-rose-600 font-bold underline decoration-rose-200 underline-offset-4">deactivated forever</span>.
                            </p>

                            <div class="mt-8 rounded-2xl border border-slate-100 bg-slate-50/30 p-5 text-left backdrop-blur-sm">
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Target Label</p>
                                        <p class="text-[13px] font-bold text-slate-800" x-text="revokeLabel"></p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Internal ID</p>
                                        <p class="text-[13px] font-mono font-bold text-slate-500" x-text="revokeId"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-stretch gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-6 rounded-b-2xl">
                            <button type="button"
                                class="w-full sm:flex-1 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 focus:outline-none"
                                x-on:click="revokeOpen = false">
                                Nevermind
                            </button>

                            <button type="button"
                                class="w-full sm:flex-1 rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 px-5 py-3 text-sm font-bold text-white shadow-xl shadow-rose-200/50 hover:shadow-rose-300/60 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                                x-on:click="
                                        @this.revoke(revokeTarget);
                                        revokeOpen = false;
                                    ">
                                Yes, Revoke it
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    @endpush


    @if (session('onboarding_signature'))
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Welcome to the New Approval System! ✍️',
                        html: '<p class="text-slate-600 leading-relaxed">To ensure your approvals are secure and verified, we need you to set up a <b>digital signature</b>. It only takes a minute!</p>',
                        icon: 'info',
                        iconColor: '#6366f1',
                        confirmButtonText: 'Let\'s get started',
                        confirmButtonColor: '#4f46e5',
                        allowOutsideClick: false,
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        },
                        customClass: {
                            popup: 'rounded-[2rem]',
                            confirmButton: 'rounded-xl px-10 py-3 font-bold text-sm tracking-wide uppercase',
                        }
                    });
                });
            </script>
        @endpush
    @endif
</div>
