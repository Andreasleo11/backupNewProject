@extends('new.layouts.app')

@section('content')
    @include('partials.alert-success-error')

    @php
        $authUser = auth()->user();
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1 text-sm text-gray-500">
                <li>
                    <a href="{{ route('maintenance.inventory.index') }}"
                       class="font-medium text-gray-600 hover:text-indigo-600">
                        Maintenance Inventory Reports
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 mx-1 text-gray-400" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li class="font-medium text-gray-900">
                    Edit
                </li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Edit Maintenance Inventory Report
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Perbarui header dan detail pengecekan untuk laporan ini.
            </p>
        </div>

        <form action="{{ route('maintenance.inventory.update', $report->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- HEADER CARD --}}
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                <div class="px-4 py-4 sm:px-6 sm:py-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-900">
                            Header
                        </h2>
                        <span class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                            ID: {{ $report->id }}
                        </span>
                    </div>

                    <div class="border-t border-gray-100 pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Select Master Inventory --}}
                        <div>
                            <label for="masterSelect" class="block text-sm font-medium text-gray-700">
                                Select Master Inventory <span class="text-red-500">*</span>
                            </label>
                            <select id="masterSelect"
                                    name="master_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm
                                           focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                           @error('master_id') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <option value="" disabled {{ old('master_id', $report->master_id) ? '' : 'selected' }}>
                                    -- Select a master inventory --
                                </option>
                                @foreach ($masters as $master)
                                    <option value="{{ $master->id }}"
                                        {{ old('master_id', $report->master_id) == $master->id ? 'selected' : '' }}>
                                        {{ $master->username }} — {{ $master->ip_address }}
                                    </option>
                                @endforeach
                            </select>
                            @error('master_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Revision Date --}}
                        <div>
                            <label for="revisionDate" class="block text-sm font-medium text-gray-700">
                                Revision Date
                            </label>
                            <input type="date"
                                   name="revision_date"
                                   id="revisionDate"
                                   value="{{ old('revision_date', $report->revision_date) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm
                                          focus:bg-white focus:border-indigo-500 focus:ring-indigo-500
                                          @error('revision_date') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('revision_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- DETAILS CARD --}}
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                <div class="px-4 py-4 sm:px-6 sm:py-5 space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">
                                Details
                            </h2>
                            <p class="mt-1 text-xs text-gray-500">
                                Centang item yang disertakan, pilih kondisi, remark, dan siapa yang melakukan pengecekan.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:justify-end">
                            <button type="button"
                                    onclick="checkAll()"
                                    class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                Check All
                            </button>
                            <button type="button"
                                    onclick="setAllGood()"
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                All Good Condition
                            </button>
                            <button type="button"
                                    onclick="setCheckedByMe()"
                                    class="inline-flex items-center rounded-md bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-1 focus:ring-sky-500">
                                Checked by Me
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4 space-y-6">
                        @foreach ($groupedDetails as $groupName => $items)
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        {{ $groupName }}
                                    </h3>
                                    <button type="button"
                                            onclick="addItem('{{ Str::slug($groupName) }}')"
                                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        Add Item
                                    </button>
                                </div>

                                <ul id="list-group-{{ Str::slug($groupName) }}" class="space-y-2">
                                    @foreach ($items as $item)
                                        <li id="item-{{ $item->id }}"
                                            class="rounded-md border border-gray-100 bg-gray-50 px-3 py-2">
                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                {{-- Checkbox + label --}}
                                                <div class="flex items-start gap-2">
                                                    <input
                                                        class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 item-checkbox @error('items') border-red-500 @enderror"
                                                        type="checkbox"
                                                        name="items[]"
                                                        value="{{ $item->id }}"
                                                        id="item{{ $item->id }}">
                                                    <label for="item{{ $item->id }}"
                                                           class="text-sm font-medium text-gray-800">
                                                        {{ $item->name ?? $item->typecategory->name }}
                                                    </label>
                                                </div>

                                                {{-- Right side fields --}}
                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-end sm:gap-3 mt-2 sm:mt-0">
                                                    {{-- Condition --}}
                                                    <div class="sm:w-40">
                                                        <select
                                                            name="conditions[{{ $item->id }}]"
                                                            id="condition{{ $item->id }}"
                                                            class="block w-full rounded-md border-gray-300 bg-white px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                                   focus:border-indigo-500 focus:ring-indigo-500
                                                                   @error('conditions.' . $item->id) border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                                            <option value="" disabled
                                                                {{ old('conditions.' . $item->id, $item->condition ?? '') ? '' : 'selected' }}>
                                                                -- Condition --
                                                            </option>
                                                            <option value="good"
                                                                {{ old('conditions.' . $item->id, $item->condition ?? '') == 'good' ? 'selected' : '' }}>
                                                                Good
                                                            </option>
                                                            <option value="bad"
                                                                {{ old('conditions.' . $item->id, $item->condition ?? '') == 'bad' ? 'selected' : '' }}>
                                                                Bad
                                                            </option>
                                                        </select>
                                                        @error('conditions.' . $item->id)
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    {{-- Remark --}}
                                                    <div class="sm:w-56">
                                                        <textarea
                                                            name="remarks[{{ $item->id }}]"
                                                            id="remark{{ $item->id }}"
                                                            rows="1"
                                                            placeholder="Remark"
                                                            class="block w-full rounded-md border-gray-300 bg-white px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                                   focus:border-indigo-500 focus:ring-indigo-500
                                                                   @error('remarks.' . $item->id) border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('remarks.' . $item->id, $item->remark ?? '') }}</textarea>
                                                        @error('remarks.' . $item->id)
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    {{-- Checked by --}}
                                                    <div class="sm:w-40">
                                                        <select
                                                            name="checked_by[{{ $item->id }}]"
                                                            id="checkedBy{{ $item->id }}"
                                                            class="block w-full rounded-md border-gray-300 bg-white px-2 py-1.5 text-xs sm:text-sm shadow-sm
                                                                   focus:border-indigo-500 focus:ring-indigo-500
                                                                   @error('checked_by.' . $item->id) border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                                            <option value="" disabled
                                                                {{ old('checked_by.' . $item->id, $item->checked_by ?? '') ? '' : 'selected' }}>
                                                                -- Checker --
                                                            </option>
                                                            @foreach ($users as $user)
                                                                <option value="{{ $user->name }}"
                                                                    {{ old('checked_by.' . $item->id, $item->checked_by ?? '') == $user->name ? 'selected' : '' }}>
                                                                    {{ $user->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('checked_by.' . $item->id)
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    {{-- Remove --}}
                                                    <div class="flex sm:self-center">
                                                        <button type="button"
                                                                onclick="removeItem({{ $item->id }}, true)"
                                                                class="inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex gap-2">
                <button type="submit"
                        name="action"
                        value="update"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Update
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function addItem(groupSlug) {
                const listGroup = document.getElementById('list-group-' + groupSlug);
                const itemId = Date.now(); // Unique ID

                const newItem = document.createElement('li');
                newItem.id = `newItem${itemId}`;
                newItem.className = 'rounded-md border border-gray-100 bg-gray-50 px-3 py-2 mt-2';

                newItem.innerHTML = `
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-2">
                            <input
                                class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 item-checkbox"
                                type="checkbox"
                                name="new_items[]"
                                value="${itemId}"
                                id="newItem${itemId}">
                            <label for="newItem${itemId}" class="flex-1">
                                <input
                                    type="text"
                                    name="new_items_names[${itemId}]"
                                    class="mt-0.5 block w-full rounded-md border-gray-300 bg-white px-2 py-1.5 text-xs sm:text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Item name">
                            </label>
                        </div>

                        <input type="hidden" name="new_group_ids[${itemId}]" value="${groupSlug}">

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-end sm:gap-3 mt-2 sm:mt-0">
                            <div class="sm:w-40">
                                <select
                                    name="new_conditions[${itemId}]"
                                    class="block w-full rounded-md border-gray-300 bg-white px-2 py-1.5 text-xs sm:text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="" disabled selected>-- Condition --</option>
                                    <option value="good">Good</option>
                                    <option value="bad">Bad</option>
                                </select>
                            </div>

                            <div class="sm:w-56">
                                <textarea
                                    name="new_remarks[${itemId}]"
                                    rows="1"
                                    placeholder="Remark"
                                    class="block w-full rounded-md border-gray-300 bg-white px-2 py-1.5 text-xs sm:text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>

                            <div class="sm:w-40">
                                <select
                                    name="new_checked_by[${itemId}]"
                                    class="block w-full rounded-md border-gray-300 bg-white px-2 py-1.5 text-xs sm:text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="" disabled selected>-- Checker --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex sm:self-center">
                                <button type="button"
                                        onclick="removeItem(${itemId}, false)"
                                        class="inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                listGroup.appendChild(newItem);
            }

            function removeItem(itemId, isExistingItem) {
                const item = isExistingItem
                    ? document.getElementById(`item-${itemId}`)
                    : document.getElementById(`newItem${itemId}`);

                if (item) {
                    item.remove();
                }
            }

            function checkAll() {
                document.querySelectorAll('.item-checkbox').forEach(cb => {
                    cb.checked = true;
                });
            }

            function setAllGood() {
                const conditionSelects = document.querySelectorAll(
                    'select[name^="conditions["], select[name^="new_conditions["]'
                );
                conditionSelects.forEach(select => {
                    select.value = 'good';
                });
            }

            function setCheckedByMe() {
                const checkedBySelects = document.querySelectorAll(
                    'select[name^="checked_by["], select[name^="new_checked_by["]'
                );
                const authUserName = @json($authUser->name);

                checkedBySelects.forEach(select => {
                    const option = Array.from(select.options).find(opt => opt.value === authUserName);
                    if (option) {
                        select.value = authUserName;
                    }
                });
            }
        </script>
    @endpush
@endsection
