{{-- Upload Requirement Modal — Sub-component view --}}
{{-- Tailwind & Alpine.js --}}
<div x-data="{ open: false }"
     @show-upload-modal.window="open = true"
     @hide-upload-modal.window="open = false"
     class="relative z-50"
     style="display: none;"
     x-show="open">

    {{-- Backdrop --}}
    <div x-show="open"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="open = false"></div>

    {{-- Modal Panel --}}
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                {{-- Header --}}
                <div class="bg-indigo-600 px-6 py-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-white shadow-sm flex items-center gap-2">
                            <i class="bx bx-upload text-xl"></i> Document Upload
                        </h3>
                        <p class="text-[11px] text-indigo-100 mt-0.5">Submitting requirement #{{ $requirementId }} for {{ $department?->name }}</p>
                    </div>
                    <button type="button" @click="open = false" class="text-indigo-200 hover:text-white transition-colors">
                        <span class="sr-only">Close</span>
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-6 border-b border-slate-100">
                    @if ($requirementId)
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">File <span class="text-rose-500">*</span></label>
                                <div class="mt-1 flex justify-center rounded-xl border border-dashed border-slate-300 px-6 py-8 hover:bg-slate-50 hover:border-indigo-300 transition-colors group relative cursor-pointer">
                                    <div class="text-center">
                                        <i class="bx bx-cloud-upload text-4xl text-slate-300 group-hover:text-indigo-400 mb-2 transition-colors"></i>
                                        <div class="flex text-sm text-slate-600 justify-center">
                                            <div class="relative cursor-pointer rounded-md font-medium text-indigo-600 focus-within:outline-none hover:text-indigo-500">
                                                <span>Upload a file</span>
                                                <input type="file" wire:model="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                            </div>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <div wire:loading wire:target="file" class="mt-2 text-xs font-semibold text-indigo-500 flex items-center justify-center gap-1.5">
                                            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            Uploading to staging…
                                        </div>
                                        @if($file && !$errors->has('file'))
                                            <div wire:loading.remove wire:target="file" class="mt-2 text-xs font-semibold text-emerald-600 flex justify-center items-center gap-1">
                                                <i class="bx bx-check-circle text-sm"></i> File staged: {{ $file->getClientOriginalName() }}
                                            </div>
                                        @endif
                                        <p class="text-[10px] text-slate-400 mt-2">Max limit 20MB. Note: Final mime-type checks follow requirement rules.</p>
                                    </div>
                                </div>
                                @error('file')<p class="text-rose-500 text-xs mt-2 font-medium">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Validity Start Date</label>
                                <div class="flex rounded-xl overflow-hidden border border-slate-200 focus-within:ring-2 focus-within:ring-indigo-400">
                                    <span class="flex items-center px-3 bg-slate-50 border-r border-slate-200 text-slate-400">
                                        <i class="bx bx-calendar"></i>
                                    </span>
                                    <input type="date" wire:model="valid_from" class="w-full py-2 px-3 text-sm outline-none text-slate-700">
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1">If the certificate has an explicit issue date, set it here. Expiration is calculated automatically.</p>
                            </div>
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <i class="bx bx-folder-open text-4xl text-slate-300 mb-2"></i>
                            <p class="text-sm font-medium text-slate-500">Please select a requirement from the table first.</p>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="save" wire:loading.attr="disabled"
                        @disabled(!$file || !$requirementId)
                        class="px-5 py-2 rounded-xl text-sm font-bold bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all flex items-center gap-1.5">
                        <i class="bx bx-upload"></i> Save Document
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
