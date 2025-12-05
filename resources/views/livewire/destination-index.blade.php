<div class="max-w-5xl mx-auto px-4 py-6">
    {{-- Header --}}
    <section class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">
                Destination Suggestions
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Kelola list tujuan yang akan digunakan pada Delivery Note.
            </p>
        </div>

        <div class="flex justify-start sm:justify-end">
            <a href="{{ route('destination.create') }}"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold
                    text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2
                    focus:ring-indigo-500 focus:ring-offset-1">
                <span class="mr-1.5 text-sm">＋</span>
                Add new destination
            </a>
        </div>
    </section>

    {{-- Card --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Destination list
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Cari berdasarkan nama atau kota untuk mempercepat pencarian.
                    </p>
                </div>

                {{-- Search --}}
                <div class="w-full sm:w-72">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 text-slate-400">
                                <path fill="currentColor"
                                    d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l4.25 4.25a1 1 0 0 0 1.41-1.41L15.5 14zm-6 0A4.5 4.5 0 1 1 14 9.5 4.505 4.505 0 0 1 9.5 14z" />
                            </svg>
                        </div>
                        <input wire:model.live="search" type="text" placeholder="Search name or city..."
                            class="block w-full rounded-md border border-slate-300 bg-white py-2 pl-8 pr-3 text-sm
                                text-slate-900 shadow-sm placeholder:text-slate-400
                                focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div wire:loading.delay.short wire:target="search" class="mt-1 text-xs text-slate-400">
                        Searching...
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 py-3">
            {{-- Table --}}
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Name
                            </th>
                            <th
                                class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                                City
                            </th>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Description
                            </th>
                            <th
                                class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($destinations as $destination)
                            <tr class="hover:bg-slate-50">
                                {{-- Name + Code --}}
                                <td class="px-3 py-2 align-top">
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ $destination->name }}
                                    </div>
                                    @if ($destination->code)
                                        <div class="mt-0.5">
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                                {{ $destination->code }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- City --}}
                                <td class="px-3 py-2 text-center align-top text-sm text-slate-700">
                                    {{ $destination->city ?? '-' }}
                                </td>

                                {{-- Description --}}
                                <td class="px-3 py-2 align-top text-sm text-slate-600">
                                    {{ $destination->description ?? '-' }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-3 py-2 text-center align-top">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('destination.edit', $destination->id) }}"
                                            class="inline-flex items-center rounded-md border border-slate-200
                                                bg-white px-2 py-1 text-[11px] font-medium text-slate-700
                                                shadow-sm hover:bg-slate-50">
                                            Edit
                                        </a>

                                        <button wire:click="delete({{ $destination->id }})"
                                            onclick="confirm('Are you sure you want to delete this destination?')"
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-red-200
                                                bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700
                                                hover:bg-red-100">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-sm text-slate-500">
                                    No destination found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($destinations, 'links'))
                <div class="mt-3">
                    {{ $destinations->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
