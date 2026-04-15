{{--
    Sign & Submit Confirmation Modal
    Shown when the creator clicks "Sign & Submit" on the PR form or show page.

    Props:
    - $hasDefaultSignature (bool)
    - $signaturePreviewUrl (string|null) — served via private storage route
    - $formId (string) — ID of the form to submit on confirm (for form modal)
    - $submitUrl (string|null) — POST URL for show page action (null = submit form)
--}}
<div x-data="{ open: false }" @open-sign-submit-modal.window="open = true" x-init="$watch('open', v => document.body.style.overflow = v ? 'hidden' : '')">

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900/75" x-show="open" x-transition.opacity @click="open = false"></div>

            {{-- Panel --}}
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-2xl"
                    x-show="open" x-transition.scale.origin.bottom @click.stop>

                    {{-- Header --}}
                    <div
                        class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                                <i class="bi bi-pen text-xl text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Sign & Submit</h3>
                        </div>
                        <button type="button" @click="open = false"
                            class="rounded-lg p-1 text-white hover:bg-white/20">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6">
                        @if ($hasDefaultSignature && $signaturePreviewUrl)
                            {{-- Has signature: show preview --}}
                            <p class="text-sm text-slate-600 mb-4">
                                Your saved signature will be applied as the <strong>requester's signature</strong> on
                                this request, and it will be submitted for approval immediately.
                            </p>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-center min-h-[100px]">
                                <img src="{{ $signaturePreviewUrl }}" alt="Your signature"
                                    class="max-h-20 object-contain">
                            </div>

                            <p class="mt-3 text-xs text-slate-400 text-center">
                                Not your signature? <a href="{{ route('signatures.manage') }}"
                                    class="text-indigo-600 hover:underline">Manage signatures</a>
                            </p>

                            {{-- Confirm action --}}
                            @if (isset($submitUrl))
                                {{-- Show page: POST to signAndSubmit route --}}
                                <form method="POST" action="{{ $submitUrl }}" class="mt-5">
                                    @csrf
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-all">
                                        <i class="bi bi-pen"></i>
                                        Confirm & Submit
                                    </button>
                                </form>
                            @else
                                {{-- Form page: set hidden field and submit the main form --}}
                                <button type="button"
                                    @click="
                                    document.getElementById('submit_action_input').value = 'sign_and_submit';
                                    document.getElementById('{{ $formId }}').submit();
                                "
                                    class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-all">
                                    <i class="bi bi-pen"></i>
                                    Confirm & Submit
                                </button>
                            @endif
                        @else
                            {{-- No signature: warn and redirect --}}
                            <div class="flex flex-col items-center text-center gap-4 py-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                                    <i class="bi bi-exclamation-triangle text-3xl text-amber-500"></i>
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-slate-800">No Signature Set Up</h4>
                                    <p class="mt-1 text-sm text-slate-500">
                                        You need to save a digital signature before you can sign and submit a request.
                                    </p>
                                </div>
                                @if (isset($submitUrl))
                                    <a href="{{ route('signatures.manage', ['return_to' => request()->url()]) }}"
                                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition-all">
                                        <i class="bi bi-pen"></i>
                                        Set Up Signature
                                    </a>
                                @else
                                    <button type="button"
                                        @click="
                                document.getElementById('submit_action_input').value = 'save_and_setup_signature';
                                document.getElementById('{{ $formId }}').submit();
                            "
                                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition-all">
                                        <i class="bi bi-pen"></i>
                                        Save & Set Up Signature
                                    </button>
                                @endif
                                <p class="text-xs text-slate-400">You can still <button type="button"
                                        @click="open = false" class="text-indigo-600 hover:underline">save your
                                        progress</button> without a signature.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
    </template>
</div>
