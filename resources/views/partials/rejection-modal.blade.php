@php
    $title = $title ?? 'Reject Request';
    $entityName = $entityName ?? 'Request';
    $buttonLabel = $buttonLabel ?? 'Reject Request';
    $openEvent = $openEvent ?? 'open-reject-modal';
    $method = $method ?? 'PUT';
@endphp

<div x-data="{ open: false }" 
     {{ '@' . $openEvent }}.window="open = true"
     x-effect="document.body.style.overflow = open ? 'hidden' : ''"
>
    <template x-teleport="body">
        <div x-show="open" x-cloak
             class="fixed inset-0 z-[100] overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"
                 @click="open = false"></div>
            
            {{-- Modal Panel --}}
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all"
                     @click.stop>
                    
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-rose-500 to-rose-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white bg-opacity-20">
                                    <i class='bx bx-x-circle text-2xl text-white'></i>
                                </div>
                                <h3 class="text-lg font-semibold text-white" id="modal-title">
                                    {{ $title }}
                                </h3>
                            </div>
                            <button type="button" 
                                    @click="open = false"
                                    class="rounded-lg p-1 text-white hover:bg-white hover:bg-opacity-20">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Body --}}
                    <form method="POST" action="{{ route($route, $id) }}" class="p-6">
                        @csrf
                        @if($method && strtoupper($method) !== 'POST')
                            @method($method)
                        @endif
                        
                        <div class="mb-6">
                            <div class="mb-4 flex items-start gap-3 rounded-lg bg-rose-50 p-4 border border-rose-100">
                                <i class='bx bx-info-circle text-xl text-rose-600'></i>
                                <div class="text-sm text-rose-800">
                                    <p class="font-medium">This action cannot be undone.</p>
                                    <p class="mt-1 text-rose-700">
                                        Please provide a clear reason for rejecting this {{ strtolower($entityName) }}. This will be visible to the requester.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <label for="reject-remarks" class="block text-sm font-semibold text-slate-700">
                                    Rejection Reason <span class="text-rose-500">*</span>
                                </label>
                            </div>
                            <textarea 
                                id="reject-remarks"
                                name="remarks" 
                                rows="4"
                                required
                                placeholder="e.g., Budget constraints, incorrect specifications, duplicate request..."
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm placeholder-slate-400 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 transition-all"></textarea>
                            <p class="mt-1 text-xs text-slate-500 font-medium">Please provide at least 10 characters.</p>
                        </div>
                        
                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button"
                                    @click="open = false"
                                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 hover:text-slate-800">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-200 transition-all hover:bg-rose-700 hover:-translate-y-0.5 hover:shadow-rose-300">
                                <i class='bx bx-x-circle'></i>
                                <span>{{ $buttonLabel }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
