<div class="max-w-2xl mx-auto px-4 py-6">
    {{-- Header --}}
    <header class="mb-4">
        <h1 class="text-lg font-semibold text-slate-900">
            {{ $destinationId ? 'Edit Destination' : 'Add Destination' }}
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Lengkapi data tujuan untuk digunakan di Delivery Note.
        </p>
    </header>

    {{-- Card --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-sm font-semibold text-slate-800">
                Destination Form
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                Field bertanda <span class="text-red-500">*</span> wajib diisi.
            </p>
        </div>

        <div class="px-4 py-2">
            {{-- Flash success --}}
            @if (session()->has('success'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit.prevent="save" class="space-y-4">
                {{-- Name + City --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Name --}}
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.defer="name" placeholder="e.g. Surabaya Port"
                            class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                       text-slate-900 shadow-sm placeholder:text-slate-400
                                       focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500
                                       @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('name')
                            <p class="text-xs text-red-600 mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- City --}}
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700">
                            City
                        </label>
                        <input type="text" wire:model.defer="city" placeholder="e.g. Surabaya"
                            class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                       text-slate-900 shadow-sm placeholder:text-slate-400
                                       focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Description --}}
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-slate-700">
                        Description
                    </label>
                    <textarea wire:model.defer="description" rows="3" placeholder="Optional notes, location, access info, etc."
                        class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm
                                   text-slate-900 shadow-sm placeholder:text-slate-400
                                   focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description', $description ?? '') }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="mt-4 flex items-center justify-end gap-2">
                    <a href="{{ route('destination.index') }}"
                        class="inline-flex items-center rounded-md border border-slate-300 bg-white
                                  px-3 py-2 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50
                                  focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2
                                       text-xs font-semibold text-white shadow-sm hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        Save destination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
