@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    {{-- GLOBAL VARIABLE --}}
    @php
        $authUser = auth()->user();
    @endphp
    {{-- END GLOBAL VARIABLE --}}
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('maintenance.inventory.index') }}">Maintenance Inventory
                        Reports</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col">
                <h2 class="fw-bold">Create Maintenance Inventory Reports</h2>
            </div>
        </div>
        <form action="{{ route('maintenance.inventory.store') }}" method="POST">
            @csrf
            <div class="row mt-3">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h4>Header</h4>
                            <hr>
                            <div class="row">
                                <div class="col">
                                    <label for="masterSelect" class="form-label">Select Master Inventory <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('master_id') is-invalid @enderror" id="masterSelect"
                                        name="master_id" required>
                                        <option value="" disabled {{ old('master_id', $id) ? '' : 'selected' }}>
                                            --Select a
                                            master inventory--</option>
                                        @foreach ($masters as $master)
                                            <option value="{{ $master->id }}"
                                                {{ old('master_id', $id) == $master->id ? 'selected' : '' }}>
                                                {{ $master->username }} - {{ $master->ip_address }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('master_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col">
                                    <label for="revisionDate" class="form-label">Revision Date</label>
                                    <input type="date" name="revision_date" id="revisionDate"
                                        class="form-control @error('revision_date') is-invalid @enderror"
                                        value="{{ old('revision_date') }}">
                                    @error('revision_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h4>Details</h4>
                            <hr>
                            <div class="mb-3">
                                <button type="button" class="btn btn-primary" onclick="checkAll()">Check All</button>
                                <button type="button" class="btn btn-success" onclick="setAllGood()">All Good
                                    Condition</button>
                                <button type="button" class="btn btn-info" onclick="setCheckedByMe()">Checked by
                                    Me</button>
                            </div>
                            @foreach ($groups as $group)
                                @foreach ($group as $groupName => $items)
                                    <div class="form-group mt-3">
                                        <h5 class="mb-3">{{ $groupName }}</h5>
                                        <ul class="list-group" id="list-group-{{ Str::slug($groupName) }}">
                                            @foreach ($items as $item)
                                                <li class="list-group-item" id="item-{{ $item['id'] }}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input @error('items') is-invalid @enderror"
                                                                type="checkbox" name="items[]" value="{{ $item['id'] }}"
                                                                id="item{{ $item['id'] }}"
                                                                {{ in_array($item['id'], old('items', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="item{{ $item['id'] }}">{{ $item['name'] }}</label>
                                                        </div>
                                                        <div class="d-flex">
                                                            <div class="form-group me-3">
                                                                <select name="conditions[{{ $item['id'] }}]"
                                                                    id="condition{{ $item['id'] }}"
                                                                    class="form-select @error('conditions.' . $item['id']) is-invalid @enderror">
                                                                    <option value="" disabled
                                                                        {{ old('conditions.' . $item['id']) ? '' : 'selected' }}>
                                                                        --Select Condition--</option>
                                                                    <option value="good"
                                                                        {{ old('conditions.' . $item['id']) == 'good' ? 'selected' : '' }}>
                                                                        Good</option>
                                                                    <option value="bad"
                                                                        {{ old('conditions.' . $item['id']) == 'bad' ? 'selected' : '' }}>
                                                                        Bad</option>
                                                                </select>
                                                                @error('conditions.' . $item['id'])
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="form-group me-3">
                                                                <textarea name="remarks[{{ $item['id'] }}]" id="remark{{ $item['id'] }}"
                                                                    class="form-control @error('remarks.' . $item['id']) is-invalid @enderror" rows="1" placeholder="Remark">{{ old('remarks.' . $item['id']) }}</textarea>
                                                                @error('remarks.' . $item['id'])
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="form-group me-3">
                                                                <select name="checked_by[{{ $item['id'] }}]"
                                                                    id="checkedBy{{ $item['id'] }}"
                                                                    class="form-select @error('checked_by.' . $item['id']) is-invalid @enderror">
                                                                    <option value="" disabled
                                                                        {{ old('checked_by.' . $item['id']) ? '' : 'selected' }}>
                                                                        --Select Checker--</option>
                                                                    @foreach ($users as $user)
                                                                        <option value="{{ $user->name }}"
                                                                            {{ old('checked_by.' . $item['id']) == $user->name ? 'selected' : '' }}>
                                                                            {{ $user->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('checked_by.' . $item['id'])
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <button type="button" class="btn btn-danger ms-2"
                                                                onclick="removeItem({{ $item['id'] }}, true)">Remove</button>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn btn-secondary mt-2"
                                            onclick="addItem('{{ Str::slug($groupName) }}')">Add Item</button>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" name="action" value="create" class="btn btn-primary mt-3 me-2">Create</button>
            <button type="submit" name="action" value="create_another" class="btn btn-outline-primary mt-3">Create and
                create another</button>
        </form>
    </div>

    <script>
        function addItem(groupSlug) {
            const listGroup = document.getElementById('list-group-' + groupSlug);
            const itemId = Date.now(); // Unique ID based on timestamp

            const newItem = document.createElement('li');
            newItem.className = 'list-group-item';
            newItem.id = `newItem${itemId}`;
            newItem.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="new_items[]" value="${itemId}" id="newItem${itemId}">
                        <label class="form-check-label" for="newItem${itemId}">
                            <input type="text" name="new_items_names[${itemId}]" class="form-control" placeholder="Item name">
                        </label>
                    </div>
                    <div class="d-flex">
                        <div class="form-group me-3">
                            <select name="new_conditions[${itemId}]" id="condition${itemId}" class="form-select">
                                <option value="" disabled selected>--Select Condition--</option>
                                <option value="good">Good</option>
                                <option value="bad">Bad</option>
                            </select>
                        </div>
                        <div class="form-group me-3">
                            <textarea name="new_remarks[${itemId}]" id="remark${itemId}" class="form-control" rows="1" placeholder="Remark"></textarea>
                        </div>
                        <div class="form-group me-3">
                            <select name="new_checked_by[${itemId}]" id="checkedBy${itemId}" class="form-select">
                                <option value="" disabled selected>--Select Checker--</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-danger ms-2" onclick="removeItem(${itemId}, false)">Remove</button>
                    </div>
                </div>
            `;
            listGroup.appendChild(newItem);
        }

        function removeItem(itemId, isPredefined) {
            const item = document.getElementById(isPredefined ? `item-${itemId}` : `newItem${itemId}`);
            item.remove();
        }

        function checkAll() {
            document.querySelectorAll('.form-check-input').forEach(checkbox => checkbox.checked = true);
        }

        function setAllGood() {
            document.querySelectorAll('select').forEach(select => {
                if (select.name.includes('conditions') || select.name.includes('new_conditions')) {
                    select.value = 'good';
                }
            });
        }

        function setCheckedByMe() {
            const userName = '{{ $authUser->name }}';
            document.querySelectorAll('select').forEach(select => {
                if (select.name.includes('checked_by') || select.name.includes('new_checked_by')) {
                    const userOption = Array.from(select.options).find(option => option.value == userName);
                    if (userOption) {
                        select.value = userName;
                    }
                }
            });
        }
    </script>
@endsection
