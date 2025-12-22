@section('title', 'Account Security')

@section('page-title', $pageTitle)
@section('page-subtitle', $pageSubtitle)

<div class="max-w-xl mx-auto">
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">
                Change password
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                For security, please enter your current password and a new one.
            </p>
        </div>

        <form wire:submit.prevent="changePassword" class="px-5 py-4 space-y-4">
            {{-- Current password --}}
            <div>
                <label class="block text-xs font-medium text-slate-700">
                    Current password <span class="text-red-500">*</span>
                </label>
                <input type="password" wire:model.defer="current_password"
                       class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- New password --}}
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-slate-700">
                        New password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" wire:model.defer="password"
                           class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700">
                        Confirm password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" wire:model.defer="password_confirmation"
                           class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-end border-t border-slate-100 pt-3">
                <button type="submit"
                        class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-black focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-1">
                    Update password
                </button>
            </div>
        </form>
    </div>
</div>
