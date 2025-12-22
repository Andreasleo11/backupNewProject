@php
    $currentColumn = request()->get('filterColumn');
    $currentAction = request()->get('filterAction');
    $currentValue  = request()->get('filterValue');
@endphp

<div class="mb-4 border-b border-gray-100 pb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-2">
        {{-- Toggle button --}}
        <button
            id="toggleFilters"
            type="button"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
            <i class='bx bx-filter-alt mr-1 text-base'></i>
            <span class="filter-toggle-text">
                {{ $filtersApplied ? 'Hide Filters' : 'Show Filters' }}
            </span>
        </button>

        {{-- Small info about active filter --}}
        @if ($filtersApplied && $currentColumn && $currentAction && $currentValue)
            <div class="hidden sm:inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                Active filter:
                <span class="ml-1 font-semibold">
                    {{ $filterColumns[$currentColumn] ?? $currentColumn }}
                </span>
                <span class="mx-1">/</span>
                <span class="capitalize">{{ $currentAction }}</span>
                <span class="mx-1">/</span>
                <span>"{{ $currentValue }}"</span>
            </div>
        @endif
    </div>

    @if ($filtersApplied)
        <div class="text-xs text-gray-500 sm:text-right">
            Filter applied — showing {{ request()->get('itemsPerPage', 'some') }} per page.
        </div>
    @endif
</div>

<div id="filterSection" class="{{ $filtersApplied ? '' : 'hidden' }}">
    <form id="filterForm" method="GET" action="{{ $filterRoute }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">

            {{-- Column --}}
            <div>
                <label for="columnSelect" class="block text-sm font-medium text-gray-700">
                    Column
                </label>
                <select id="columnSelect"
                        name="filterColumn"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                               px-3 py-2
                               focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="" disabled {{ $currentColumn ? '' : 'selected' }}>Select Column</option>
                    @foreach ($filterColumns as $value => $label)
                        <option value="{{ $value }}"
                            {{ $currentColumn == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Action --}}
            <div>
                <label for="actionSelect" class="block text-sm font-medium text-gray-700">
                    Action
                </label>
                <select id="actionSelect"
                        name="filterAction"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                               px-3 py-2
                               focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="" disabled {{ $currentAction ? '' : 'selected' }}>Select Action</option>
                    <option value="contains"   {{ $currentAction == 'contains'   ? 'selected' : '' }}>Contains</option>
                    <option value="equals"     {{ $currentAction == 'equals'     ? 'selected' : '' }}>Equals</option>
                    <option value="startswith" {{ $currentAction == 'startswith' ? 'selected' : '' }}>Starts With</option>
                    <option value="endswith"   {{ $currentAction == 'endswith'   ? 'selected' : '' }}>Ends With</option>
                </select>
            </div>

            {{-- Value --}}
            <div>
                <label for="filterValue" class="block text-sm font-medium text-gray-700">
                    Value
                </label>
                <input type="text"
                       id="filterValue"
                       name="filterValue"
                       value="{{ $currentValue }}"
                       placeholder="Value"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                              px-3 py-2
                              focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            {{-- Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Apply Filter
                </button>

                @if ($filtersApplied)
                    <button type="button"
                            id="resetFilters"
                            class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-red-600 hover:text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                        Reset
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Script kecil khusus filter --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn   = document.getElementById('toggleFilters');
        const section     = document.getElementById('filterSection');
        const toggleText  = toggleBtn?.querySelector('.filter-toggle-text');
        const resetBtn    = document.getElementById('resetFilters');

        if (toggleBtn && section) {
            toggleBtn.addEventListener('click', () => {
                section.classList.toggle('hidden');
                if (toggleText) {
                    const isHidden = section.classList.contains('hidden');
                    toggleText.textContent = isHidden ? 'Show Filters' : 'Hide Filters';
                }
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                // Redirect ke route tanpa query string
                window.location.href = "{{ $filterRoute }}";
            });
        }
    });
</script>
@endpush
