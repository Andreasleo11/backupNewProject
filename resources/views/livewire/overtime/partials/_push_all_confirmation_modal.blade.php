{{-- ===== PUSH ALL CONFIRMATION MODAL ===== --}}
<template x-teleport="body">
    <div x-cloak
         x-show="$wire.pushAllConfirmationOpen"
         x-data="{ showModal: $wire.pushAllConfirmationOpen }"
         x-init="$watch('$wire.pushAllConfirmationOpen', value => showModal = value)"
         @keydown.escape.window="$wire.pushAllConfirmationOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">

        {{-- Backdrop --}}
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
             x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$wire.pushAllConfirmationOpen = false"></div>

            {{-- Modal panel --}}
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <x-bx-cloud-upload class="text-indigo-600 w-5 h-5" />
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Push All Overtime Data to JPayroll
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">
                                    This will push all eligible overtime details from approved forms to JPayroll. This operation runs in the background and may take several minutes to complete.
                                </p>

                                {{-- Summary --}}
                                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">Eligible Forms:</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $pushAllSummary['total_forms'] }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">Total Details:</span>
                                        <span class="text-sm font-bold text-gray-900">{{ number_format($pushAllSummary['total_details']) }}</span>
                                    </div>
                                    @if($pushAllSummary['estimated_time'] > 0)
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">Estimated Time:</span>
                                            <span class="text-sm font-bold text-gray-900">
                                                {{ $pushAllSummary['estimated_time'] < 60
                                                    ? $pushAllSummary['estimated_time'] . ' seconds'
                                                    : round($pushAllSummary['estimated_time'] / 60, 1) . ' minutes' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                @if($pushAllSummary['total_forms'] === 0)
                                    <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                        <div class="flex">
                                            <x-bx-info-circle class="text-yellow-400" />
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-800">
                                                    No eligible forms found. Forms must be approved and contain pending details to be pushed.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    @if($pushAllSummary['total_forms'] > 0)
                        <button type="button"
                                wire:click="pushAllToJPayroll"
                                wire:loading.attr="disabled"
                                @click="$wire.pushAllConfirmationOpen = false"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading.remove>Start Push Operation</span>
                            <span wire:loading>
                                <x-bx-loader-alt class="animate-spin mr-2" />
                                Starting...
                            </span>
                        </button>
                    @endif
                    <button type="button"
                            @click="$wire.pushAllConfirmationOpen = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>