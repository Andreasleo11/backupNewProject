<div class="container">
    <h1 class="my-4 text-center">Inspection Form</h1>

    @php
        $stepLabels = [
            1 => 'Header',
            2 => 'Detail',
            3 => 'Problems',
            4 => 'Final Submit',
        ];
    @endphp

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        @for ($i = 1; $i <= count($stepLabels); $i++)
                            <div class="col text-center">
                                <button type="button"
                                    class="btn mb-2
                                        {{ $currentStep === $i ? 'btn-primary' : ($filledSteps[$i] ?? false ? 'btn-outline-primary' : 'btn-outline-secondary') }}"
                                    wire:click="$set('currentStep', {{ $i }})">
                                    {{ "$i" }}
                                </button>
                                <span class="d-block">{{ $stepLabels[$i] }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="mt-4">
                @if ($currentStep === 1)
                    @livewire('inspection-form.step-header', key('step-header'))
                @elseif ($currentStep === 2)
                    @livewire('inspection-form.step-detail', ['inspection_report_document_number' => $inspection_report_document_number], key('step-detail'))
                @elseif ($currentStep === 3)
                    @livewire('inspection-form.step-problem', ['inspection_report_document_number' => $inspection_report_document_number], key('step-problem'))
                @elseif ($currentStep === 4)
                    @livewire('inspection-form.final-submit', key('final-submit'))
                @endif
            </div>
        </div>
    </div>

    {{-- ⬇ Toast stack – fade + slide animation ------------------------------- --}}
    <div x-data="toastStack()" x-on:toast.window="add($event.detail)"
        class="toast-container position-fixed bottom-0 end-0 p-3 d-flex flex-column gap-2" style="z-index:1055">

        <template x-for="t in toasts" :key="t.id">
            <div x-show="t.visible" x-transition:enter="fade-slide-enter"
                x-transition:enter-end="fade-slide-enter-active" x-transition:leave="fade-slide-leave"
                x-transition:leave-end="fade-slide-leave-active" class="toast align-items-center border-0 show"
                :class="bgClass(t.type)">

                <div class="d-flex">
                    <div class="toast-body" x-text="t.message"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="close(t)">
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- fade + slide utility classes ----------------------------------------- --}}
    <style>
        .fade-slide-enter {
            opacity: 0;
            transform: translateY(10px);
        }

        .fade-slide-enter-active {
            opacity: 1;
            transform: translateY(0);
            transition: opacity .35s ease-out, transform .35s ease-out;
        }

        .fade-slide-leave {
            opacity: 1;
            transform: translateY(0);
        }

        .fade-slide-leave-active {
            opacity: 0;
            transform: translateY(10px);
            transition: opacity .35s ease-in, transform .35s ease-in;
        }
    </style>

    <script>
        function toastStack() {
            return {
                toasts: [],
                uid: 0,

                add({
                    message,
                    type = 'success',
                    timeout = 3000
                }) {
                    const id = this.uid++
                    const item = {
                        id,
                        message,
                        type,
                        visible: true
                    }
                    this.toasts.push(item)

                    setTimeout(() => item.visible = false, timeout) // fade
                    setTimeout(() => { // DOM-remove
                        this.toasts = this.toasts.filter(t => t.id !== id)
                    }, timeout + 400) // 400 ≈ fade + buffer
                },

                close(item) {
                    item.visible = false
                },

                bgClass(type) {
                    return {
                        'text-bg-success': type === 'success',
                        'text-bg-danger': type === 'error',
                        'text-bg-warning': type === 'warning',
                    }
                },
            }
        }
    </script>


</div>
