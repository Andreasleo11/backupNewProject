<div class="max-w-7xl mx-auto px-4 py-6 space-y-4">

    {{-- Flash message --}}
    @if (session()->has('success'))
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">
                Delivery Notes
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Kelola daftar delivery note beserta status, cabang, ritasi, dan kendaraan.
            </p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Bisa ditambah filter cepat di sini kalau perlu --}}
            <a href="{{ route('delivery-notes.create') }}"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm
                      hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                Create Delivery Note
            </a>
        </div>
    </div>

    {{-- Layout: Filters + Table --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- Filter Sidebar --}}
        <aside class="lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-800">
                        Filters
                    </h2>
                    <button type="button" wire:click="resetFilters"
                        class="text-xs text-slate-400 hover:text-slate-600">
                        Reset
                    </button>
                </div>

                <div class="space-y-3 text-sm">

                    {{-- Status --}}
                    <div class="space-y-1">
                        <label for="filter-status" class="block text-xs font-medium text-slate-600">
                            Status
                        </label>
                        <select id="filter-status" wire:model.defer="inputStatus"
                            class="block w-full rounded-md border border-slate-300 bg-white
                               px-3 py-2 text-sm text-slate-700 shadow-sm
                               focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all">All</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                        </select>
                    </div>

                    {{-- Branch --}}
                    <div class="space-y-1">
                        <label for="filter-branch" class="block text-xs font-medium text-slate-600">
                            Branch
                        </label>
                        <select id="filter-branch" wire:model.defer="inputBranch"
                            class="block w-full rounded-md border border-slate-300 bg-white
                               px-3 py-2 text-sm text-slate-700 shadow-sm
                               focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all">All</option>
                            <option value="JAKARTA">JAKARTA</option>
                            <option value="KARAWANG">KARAWANG</option>
                        </select>
                    </div>

                    {{-- Ritasi --}}
                    <div class="space-y-1">
                        <label for="filter-ritasi" class="block text-xs font-medium text-slate-600">
                            Ritasi
                        </label>
                        <select id="filter-ritasi" wire:model.defer="inputRitasi"
                            class="block w-full rounded-md border border-slate-300 bg-white
                               px-3 py-2 text-sm text-slate-700 shadow-sm
                               focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all">All</option>
                            <option value="1">1 (Pagi)</option>
                            <option value="2">2 (Siang)</option>
                            <option value="3">3 (Sore)</option>
                            <option value="4">4 (Malam)</option>
                        </select>
                    </div>

                    {{-- From Date --}}
                    <div class="space-y-1">
                        <label for="from-date" class="block text-xs font-medium text-slate-600">
                            From date
                        </label>
                        <input type="date" id="from-date" wire:model.defer="inputFromDate"
                            class="block w-full rounded-md border border-slate-300 bg-white
                              px-3 py-2 text-sm text-slate-700 shadow-sm
                              focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- To Date --}}
                    <div class="space-y-1">
                        <label for="to-date" class="block text-xs font-medium text-slate-600">
                            To date
                        </label>
                        <input type="date" id="to-date" wire:model.defer="inputToDate"
                            class="block w-full rounded-md border border-slate-300 bg-white
                              px-3 py-2 text-sm text-slate-700 shadow-sm
                              focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Search --}}
                    <div class="space-y-1">
                        <label for="search-all" class="block text-xs font-medium text-slate-600">
                            Search
                        </label>
                        <input type="text" id="search-all" wire:model.debounce.400ms="searchAll"
                            placeholder="Search number, driver, vehicle..."
                            class="block w-full rounded-md border border-slate-300 bg-white
                              px-3 py-2 text-sm text-slate-700 shadow-sm
                              focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="pt-2">
                        <button type="button" wire:click="applyFilters"
                            class="inline-flex w-full items-center justify-center rounded-md bg-slate-900
                           px-3 py-2 text-sm font-medium text-white hover:bg-slate-800
                           focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-1">
                            Apply filters
                        </button>
                    </div>
                </div>
            </div>
        </aside>


        {{-- Table + Active Filters --}}
        <section class="lg:col-span-3 space-y-3">

            {{-- Active Filters Summary --}}
            @if ($filterStatus !== 'all' || $filterBranch !== 'all' || $filterRitasi !== 'all' || $fromDate || $toDate || $searchAll)
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                    <div class="mb-1 font-medium">
                        Active filters
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($filterStatus !== 'all')
                            <span
                                class="inline-flex items-center rounded-full bg-white px-2 py-0.5 border border-slate-200">
                                <span class="mr-1 text-[11px] text-slate-500">Status</span>
                                <span class="text-[11px] font-medium text-slate-800">{{ ucfirst($filterStatus) }}</span>
                            </span>
                        @endif

                        @if ($filterBranch !== 'all')
                            <span
                                class="inline-flex items-center rounded-full bg-white px-2 py-0.5 border border-slate-200">
                                <span class="mr-1 text-[11px] text-slate-500">Branch</span>
                                <span class="text-[11px] font-medium text-slate-800">{{ $filterBranch }}</span>
                            </span>
                        @endif

                        @if ($filterRitasi !== 'all')
                            <span
                                class="inline-flex items-center rounded-full bg-white px-2 py-0.5 border border-slate-200">
                                <span class="mr-1 text-[11px] text-slate-500">Ritasi</span>
                                <span class="text-[11px] font-medium text-slate-800">{{ $filterRitasi }}</span>
                            </span>
                        @endif

                        @if ($fromDate)
                            <span
                                class="inline-flex items-center rounded-full bg-white px-2 py-0.5 border border-slate-200">
                                <span class="mr-1 text-[11px] text-slate-500">From</span>
                                <span class="text-[11px] font-medium text-slate-800">{{ $fromDate }}</span>
                            </span>
                        @endif

                        @if ($toDate)
                            <span
                                class="inline-flex items-center rounded-full bg-white px-2 py-0.5 border border-slate-200">
                                <span class="mr-1 text-[11px] text-slate-500">To</span>
                                <span class="text-[11px] font-medium text-slate-800">{{ $toDate }}</span>
                            </span>
                        @endif

                        @if ($searchAll)
                            <span
                                class="inline-flex items-center rounded-full bg-white px-2 py-0.5 border border-slate-200">
                                <span class="mr-1 text-[11px] text-slate-500">Search</span>
                                <span class="text-[11px] font-medium text-slate-800">{{ $searchAll }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Table card --}}
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-2.5 flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-800">
                        Delivery note list
                    </span>
                    <span class="text-xs text-slate-400">
                        {{ $deliveryNotes->total() }} records
                    </span>
                </div>

                <div class="overflow-x-auto">
                    @include('livewire.delivery-note._table')
                </div>

                <div class="border-t border-slate-100 px-4 py-2.5">
                    {{ $deliveryNotes->onEachSide(1)->links() }}
                </div>
            </div>
        </section>
    </div>
</div>
