@php
    $title = $title ?? 'Cancel Report';
    $entityName = $entityName ?? 'Report';
    $buttonLabel = $buttonLabel ?? 'Cancel';
    $confirmLabel = $confirmLabel ?? 'Confirm Cancellation';
    $triggerClass =
        $triggerClass ??
        'w-full flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50/50 py-3 text-sm font-bold text-rose-600 transition-all hover:bg-rose-50 hover:border-rose-300 active:scale-95';
@endphp

<div x-data="{ open: false, description: '' }" @open-cancel-modal.window="if($event.detail.id == {{ $id }}) open = true"
    x-effect="document.body.style.overflow = open ? 'hidden' : ''" class="inline-block">
    {{-- Trigger button --}}
    @if (isset($iconOnly) && $iconOnly)
        <button type="button" @click="open = true"
            class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all active:scale-95"
            title="{{ $buttonLabel }}">
            <i class="bx bx-x-circle text-lg"></i>
        </button>
    @else
        <button type="button" @click="open = true" class="{{ $triggerClass }}">
            <i class="bx bx-x-circle text-lg"></i>
            <span>{{ $buttonLabel }}</span>
        </button>
    @endif

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="cancel-modal-title"
            role="dialog" aria-modal="true">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" x-show="open" x-transition.opacity
                @click="open = false"></div>

            {{-- Modal Panel --}}
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all"
                    x-show="open" x-transition @click.stop>

                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-rose-600 to-rose-400 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white bg-opacity-20">
                                    <i class="bx bx-x-circle text-2xl text-white"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-white" id="cancel-modal-title">
                                    {!! $title !!}
                                </h3>
                            </div>
                            <button type="button" @click="open = false"
                                class="rounded-lg p-1 text-white hover:bg-white hover:bg-opacity-20">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <form method="POST" action="{{ route($route, $id) }}" class="p-6">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <div class="mb-4 flex items-start gap-3 rounded-lg bg-rose-50 p-4 border border-rose-100">
                                <i class='bx bx-error-circle text-xl text-rose-600'></i>
                                <div class="text-sm text-rose-800">
                                    <p class="font-medium">Are you sure you want to cancel this
                                        {{ strtolower($entityName) }}?</p>
                                    <p class="mt-1 text-rose-700 font-medium">
                                        This action will permanently terminate the approval workflow for this document.
                                    </p>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="cancel-description" class="block text-sm font-semibold text-slate-700">
                                    Reason for Cancellation <span class="text-rose-500">*</span>
                                </label>
                            </div>
                            <textarea id="cancel-description" name="description" rows="4" required x-model="description"
                                placeholder="Please provide a reason for cancelling this report..."
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm placeholder-slate-400 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 transition-all"></textarea>
                            <p class="mt-1 text-xs text-slate-500 font-medium">Please provide a brief explanation for
                                this action.</p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="open = false"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 hover:text-slate-800">
                                Abort
                            </button>
                            <button type="submit" :disabled="description.trim().length === 0"
                                class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-200 transition-all hover:bg-rose-700 hover:-translate-y-0.5 hover:shadow-rose-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="bx bx-check-circle"></i>
                                <span>{{ $confirmLabel }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
