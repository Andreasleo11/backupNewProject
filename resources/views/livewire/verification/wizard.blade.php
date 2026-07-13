<div class="w-full bg-slate-50/50 min-h-screen" x-data="{ openDiscard: false }">
    <main class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="{ step: @entangle('step') }" x-init="$root.addEventListener('input', () => $wire.set('isDirty', true), { capture: true });
    $root.addEventListener('change', () => $wire.set('isDirty', true), { capture: true });"
        x-on:livewire:navigated.window="$wire.set('isDirty', true)">

        {{-- STEPPER CONSTANTS --}}
        @php
            $labels = [1 => 'Header', 2 => 'Items & Defects', 3 => 'Preview'];
            $statuses = [
                1 => $errors->hasAny(['form.*']) ? 'error' : (filled($form['customer']) ? 'done' : 'todo'),
                2 => ($errors->hasAny(['items', 'items.*'])) ? 'error' : (count($items) ? 'done' : 'todo'),
                3 => 'todo',
            ];
        @endphp

        {{-- Top toolbar --}}
        <div class="flex flex-wrap justify-between items-center mb-6 gap-4 border-b border-slate-200 pb-4">
            <div>
                <div class="flex items-center flex-wrap gap-2">
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ $report?->id ? "Edit Verification Report" : 'New Verification Report' }}
                    </h1>
                    @if ($lastAutosaveAt)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                            <i class="bi bi-cloud-check"></i> Autosaved at {{ $lastAutosaveAt }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-50 text-slate-500 border border-slate-200">
                            <i class="bi bi-cloud"></i> Autosave active
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 hidden" 
                          wire:dirty.class.remove="hidden"
                          wire:target="form,items,defaultCurrency">
                        <i class="bi bi-pencil"></i> Unsaved changes
                    </span>
                </div>
                @if ($report?->document_number)
                    <div class="text-sm text-slate-500 mt-1">
                        Document Number: <span class="font-bold text-slate-800">{{ $report->document_number }}</span>
                    </div>
                @else
                    <div class="text-sm text-slate-500 mt-1">This report is automatically saved as a draft.</div>
                @endif

                @if ($autosaveEnabled)
                    <div wire:poll.keep-alive.{{ (int) $autosaveMs }}ms="autosaveDraft" class="hidden"></div>
                @endif
            </div>

            <div>
                <button class="inline-flex items-center justify-center font-medium rounded-lg text-xs px-3 py-1.5 border border-red-200 text-red-600 hover:bg-red-50 transition-colors" @click="openDiscard = true">
                    <i class="bi bi-trash mr-1"></i> Discard draft
                </button>

                {{-- Tailwind Discard Modal --}}
                <div x-show="openDiscard" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
                    <div class="fixed inset-0 bg-slate-950/45 backdrop-blur-sm" @click="openDiscard = false"></div>
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl border border-slate-100" @click.outside="openDiscard = false">
                            <h3 class="text-lg font-bold text-slate-900">Discard draft?</h3>
                            <p class="mt-2 text-sm text-slate-500">This will permanently remove the auto-saved draft for this report.</p>
                            <div class="mt-2 text-xs font-semibold text-red-600 flex items-center gap-1">
                                <i class="bi bi-exclamation-triangle-fill"></i> You cannot undo this action.
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" class="px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50" @click="openDiscard = false">Cancel</button>
                                <button type="button" class="px-3 py-2 rounded-lg bg-red-600 text-sm font-medium text-white hover:bg-red-700" wire:click="clearDraft" @click="openDiscard = false">Discard draft</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Horizontal Stepper Card --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 mb-6 shadow-sm">
            <nav aria-label="Progress">
                <ol role="list" class="flex flex-col md:flex-row items-stretch md:items-center justify-between w-full divide-y md:divide-y-0 divide-slate-100 gap-4 md:gap-2">
                    @foreach ($labels as $s => $label)
                        @php
                            $state = $statuses[$s]; // 'todo' | 'done' | 'error'
                            $isActive = $step === $s;
                        @endphp
                        <li class="flex-1 flex items-center gap-3 py-2.5 md:py-0">
                            <button type="button" 
                                    class="flex items-center text-left focus:outline-none transition-all duration-150 w-full"
                                    wire:click="goToStep({{ $s }})"
                                    aria-current="{{ $isActive ? 'step' : 'false' }}">
                                
                                {{-- Step Bullet --}}
                                <span class="flex items-center justify-center rounded-full w-8 h-8 text-xs font-bold shrink-0 transition-colors
                                             @if($state === 'done') bg-green-50 text-green-700 border border-green-200
                                             @elseif($state === 'error') bg-red-50 text-red-700 border border-red-200
                                             @elseif($isActive) bg-blue-600 text-white shadow-sm border border-blue-600
                                             @else bg-slate-50 text-slate-400 border border-slate-200 @endif">
                                    @if ($state === 'done')
                                        <i class="bi bi-check-lg text-sm"></i>
                                    @elseif($state === 'error')
                                        <i class="bi bi-exclamation-lg text-sm"></i>
                                    @else
                                        {{ $s }}
                                    @endif
                                </span>

                                {{-- Text details --}}
                                <span class="ml-3 flex-1 min-w-0">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none">Step {{ $s }}</span>
                                    <span class="block text-sm font-semibold mt-1 leading-none @if($isActive) text-blue-700 @else text-slate-700 @endif">
                                        {{ $label }}
                                    </span>
                                </span>

                                @if($state === 'error')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 ml-1.5 shrink-0">Fix</span>
                                @endif
                            </button>

                            {{-- Divider Line (except last item) --}}
                            @if($s < 3)
                                <div class="hidden md:block flex-1 h-0.5 bg-slate-200 mx-4 max-w-[60px]" aria-hidden="true"></div>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        {{-- Validation Errors summary --}}
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 mb-6 text-sm">
                <div class="font-bold mb-1">Please fix the following validation errors:</div>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->toArray() as $field => $messages)
                        @php $anchor = 'fld-' . preg_replace('/[^a-z0-9\-]+/i', '-', $field); @endphp
                        <li>
                            <a href="#{{ $anchor }}" class="underline font-semibold hover:text-red-900"
                                onclick="const el=document.getElementById('{{ $anchor }}'); if(el){ el.scrollIntoView({behavior:'smooth', block:'center'}); el.focus(); } return false;">
                                {{ $messages[0] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Sections --}}
        <section x-show="step === 1" x-cloak>
            <div class="bg-transparent">
                <livewire:verification.steps.header wire:model="form" wire:key="step-header" />
            </div>
        </section>

        <section x-show="step === 2" x-cloak>
            <div class="bg-transparent">
                <livewire:verification.steps.items wire:model="items" :customer="$form['customer']" :default-currency="$defaultCurrency"
                    :active-item="$activeItem" wire:key="step-items" />
            </div>
        </section>

        <section x-show="step === 3" x-cloak>
            <livewire:verification.steps.preview :form="$form" :items="$items"
                wire:key="preview-{{ $previewVersion }}" />
        </section>

        {{-- Sticky action bar --}}
        <div class="sticky bottom-0 bg-slate-50/90 backdrop-blur-sm border-t border-slate-200 py-3 mt-6 z-10 flex justify-end items-center gap-3">
            @if ($step > 1)
                <button class="inline-flex items-center justify-center font-medium rounded-lg text-sm px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors" wire:click="prevStep">
                    <i class="bi bi-arrow-left mr-1.5"></i> Back
                </button>
            @else
                <a href="{{ route('verification.index') }}" class="inline-flex items-center justify-center font-medium rounded-lg text-sm px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition-colors">
                    <i class="bi bi-list mr-1.5"></i> List
                </a>
            @endif

            @if ($step < 3)
                <button class="inline-flex items-center justify-center font-medium rounded-lg text-sm px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-colors" wire:click="nextStep">
                    Save & Next <i class="bi bi-arrow-right ml-1.5"></i>
                </button>
            @else
                <button class="inline-flex items-center justify-center font-bold rounded-lg text-base px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white shadow-md transition-colors"
                    onclick="if (window.__stepInvalid) { if (!confirm('There are validation errors. Are you sure you want to save anyway?')) return false; }"
                    wire:click="save">
                    <i class="bi bi-check2-circle mr-1.5"></i> Submit Report
                </button>
            @endif
        </div>
    </main>

    @pushOnce('extraJs')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('draft-cleared', () => {
                    window.location.href = "{{ route('verification.index') }}";
                })
            });

            function reinitTooltips() {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el =>
                    bootstrap.Tooltip.getOrCreateInstance(el)
                );
            }

            document.addEventListener('livewire:initialized', () => {
                reinitTooltips();
                Livewire.hook('morph.updated', () => reinitTooltips());
                Livewire.hook('morph.added', () => reinitTooltips());
                Livewire.hook('morph.removed', () => reinitTooltips());
            });

            window.scrollToFirstError = () => {
                const first = document.querySelector('.is-invalid, [aria-invalid="true"]');
                if (first) {
                    first.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    first.focus({
                        preventScroll: true
                    });
                }
            };

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('step-invalid', () => {
                    window.__stepInvalid = true;
                    setTimeout(window.scrollToFirstError, 0);
                });
                Livewire.on('step-valid', () => {
                    window.__stepInvalid = false;
                });
            });

            let _unsaved = false;

            document.addEventListener('input', () => {
                _unsaved = true;
            }, {
                capture: true
            });
            document.addEventListener('change', () => {
                _unsaved = true;
            }, {
                capture: true
            });

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('saved-clean', () => {
                    _unsaved = false;
                });
            });

            window.addEventListener('beforeunload', (e) => {
                if (!_unsaved) return;
                e.preventDefault();
                e.returnValue = '';
            });
        </script>
    @endPushOnce
</div>
