<div x-data="{ isOpen: @entangle('isOpen') }" x-show="isOpen" @keydown.escape.window="isOpen = false" class="relative z-[100]"
    aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
    <!-- Background backdrop -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal panel -->
            <div x-show="isOpen" @click.away="isOpen = false" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="mt-3 text-left w-full">
                        <h3 class="text-xl font-black text-slate-900 mb-1" id="modal-title">
                            Update Ticket Status
                        </h3>
                        <p class="text-sm font-medium text-slate-500 mb-6">
                            Changing the status may affect SLA timers.
                        </p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">New
                                    Status</label>
                                <select wire:model.live="newStatus"
                                    class="block w-full rounded-xl border-slate-200 py-2.5 pl-3 pr-10 text-sm font-bold text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}">{{ $status->value }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="$wire.newStatus === 'On Hold' || $wire.newStatus === 'Resolved'" x-collapse>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">
                                    <span
                                        x-text="$wire.newStatus === 'On Hold' ? 'Reason for Hold' : 'Resolution Notes'"></span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                <textarea wire:model="reason" rows="3"
                                    class="block w-full rounded-xl border-slate-200 py-2.5 px-3 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50"></textarea>
                                @error('reason')
                                    <span class="text-xs font-bold text-rose-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 border-t border-slate-100 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" wire:click="save"
                        class="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-xl shadow-indigo-200 hover:bg-indigo-700 sm:ml-3 sm:w-auto transition-colors">
                        Confirm Update
                    </button>
                    <button type="button" @click="isOpen = false"
                        class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
