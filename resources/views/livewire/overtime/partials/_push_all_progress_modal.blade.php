{{-- ===== PUSH ALL PROGRESS MODAL ===== --}}
<template x-teleport="body">
    <div x-cloak
         x-show="$wire.pushAllProgressOpen"
         x-data="{
             showModal: $wire.pushAllProgressOpen,
             jobProgress: null,
             pollInterval: null,
             startPolling(jobProgressId) {
                 this.stopPolling();
                 this.pollInterval = setInterval(() => {
                     fetch(`/job-progress/${jobProgressId}`)
                         .then(response => {
                             if (!response.ok) {
                                 throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                             }
                             return response.json();
                         })
                         .then(data => {
                             this.jobProgress = data;
                             if (data.status === 'completed' || data.status === 'failed' || data.status === 'cancelled') {
                                 this.stopPolling();
                                 $wire.call('refreshData');
                             }
                         })
                         .catch(error => {
                             console.error('Error polling job progress:', error);
                             // Show error in UI instead of just stopping polling
                             this.jobProgress = {
                                 ...this.jobProgress,
                                 error: 'Failed to update progress. Please refresh the page.',
                                 status: 'error'
                             };
                             this.stopPolling();
                         });
                 }, 2000); // Poll every 2 seconds
             },
             stopPolling() {
                 if (this.pollInterval) {
                     clearInterval(this.pollInterval);
                     this.pollInterval = null;
                 }
             }
         }"
         x-init="
             $watch('$wire.pushAllProgressOpen', value => {
                 showModal = value;
                 if (value && $wire.currentJobProgressId) {
                     startPolling($wire.currentJobProgressId);
                 } else {
                     stopPolling();
                 }
             });
             $watch('$wire.currentJobProgressId', value => {
                 if (value && showModal) {
                     startPolling(value);
                 }
             });
         "
         @keydown.escape.window="stopPolling(); $wire.pushAllProgressOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="progress-modal-title"
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

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="stopPolling(); $wire.pushAllProgressOpen = false"></div>

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
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10"
                             :class="jobProgress?.status === 'completed' ? 'bg-green-100' : (jobProgress?.status === 'failed' ? 'bg-red-100' : 'bg-indigo-100')">
                            <x-bx-check class="w-5 h-5 text-green-600" x-show="jobProgress?.status === 'completed'" x-cloak />
                            <x-bx-x class="w-5 h-5 text-red-600" x-show="jobProgress?.status === 'failed'" x-cloak />
                            <x-bx-cloud-upload class="w-5 h-5 text-indigo-600" x-show="jobProgress?.status !== 'processing' && jobProgress?.status !== 'completed' && jobProgress?.status !== 'failed'" x-cloak />
                            <x-bx-loader-alt class="animate-spin text-indigo-600 w-5 h-5" x-show="jobProgress?.status === 'processing'" />
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="progress-modal-title">
                                Push All to JPayroll
                            </h3>
                            <div class="mt-2">
                                {{-- Error State --}}
                                <div x-show="jobProgress?.status === 'error'" class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="flex">
                                        <x-bx-error-circle class="text-red-400 w-5 h-5" />
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-red-800">Progress Tracking Error</h4>
                                            <p class="text-sm text-red-700 mt-1" x-text="jobProgress?.error || 'Unable to track progress. The operation may still be running.'"></p>
                                            <button @click="window.location.reload()" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
                                                Refresh Page
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Progress Bar --}}
                                <div x-show="jobProgress?.status !== 'error'" class="mb-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span x-text="jobProgress?.current_task || 'Initializing...'"></span>
                                        <span x-text="jobProgress?.progress_percentage ? jobProgress.progress_percentage + '%' : ''"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                                             :style="`width: ${jobProgress?.progress_percentage || 0}%`">
                                        </div>
                                    </div>
                                </div>

                                {{-- Status Details --}}
                                <div x-show="jobProgress" class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="font-medium capitalize"
                                              :class="jobProgress.status === 'completed' ? 'text-green-600' : (jobProgress.status === 'failed' ? 'text-red-600' : 'text-indigo-600')"
                                              x-text="jobProgress.status">
                                        </span>
                                    </div>

                                    <div x-show="jobProgress.results" class="border-t pt-2 mt-3 space-y-1">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Total Forms:</span>
                                            <span class="font-medium" x-text="jobProgress.results?.total_forms || 0"></span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Processed:</span>
                                            <span class="font-medium" x-text="jobProgress.results?.processed_forms || 0"></span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Successful:</span>
                                            <span class="font-medium text-green-600" x-text="jobProgress.results?.successful_forms || 0"></span>
                                        </div>
                                        <div class="flex justify-between text-xs" x-show="jobProgress.results?.failed_forms > 0">
                                            <span class="text-gray-500">Failed:</span>
                                            <span class="font-medium text-red-600" x-text="jobProgress.results?.failed_forms || 0"></span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Total Details:</span>
                                            <span class="font-medium" x-text="jobProgress.results?.total_details || 0"></span>
                                        </div>
                                    </div>

                                    {{-- Error Message --}}
                                    <div x-show="jobProgress.error_message" class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                                        <div class="flex">
                                            <x-bx-error-circle class="text-red-400" />
                                            <div class="ml-3">
                                                <p class="text-sm text-red-800" x-text="jobProgress.error_message"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            x-show="jobProgress?.status === 'processing'"
                            wire:click="cancelPushAllJob($wire.currentJobProgressId)"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">
                        <span wire:loading.remove>Cancel Operation</span>
                        <span wire:loading>
                            <x-bx-loader-alt class="animate-spin mr-2" />
                            Cancelling...
                        </span>
                    </button>
                    <button type="button"
                            @click="stopPolling(); $wire.pushAllProgressOpen = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>