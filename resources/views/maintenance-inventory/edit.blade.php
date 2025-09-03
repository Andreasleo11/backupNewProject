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
        <li class="breadcrumb-item"><a href="{{ route('maintenance.inventory.index') }}">Maintenance
            Inventory
            Reports</a></li>
        <li class="breadcrumb-item active">Edit</li>
      </ol>
    </nav>
    <div class="row">
      <div class="col">
        <h2 class="fw-bold">Edit Maintenance Inventory Report</h2>
      </div>
    </div>
    <form action="{{ route('maintenance.inventory.update', $report->id) }}" method="POST">
      @csrf
      @method('PUT')
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
                  <select class="form-select @error('master_id') is-invalid @enderror"
                    id="masterSelect" name="master_id" required>
                    <option value="" disabled
                      {{ old('master_id', $report->master_id) ? '' : 'selected' }}>--Select a master
                      inventory--</option>
                    @foreach ($masters as $master)
                      <option value="{{ $master->id }}"
                        {{ old('master_id', $report->master_id) == $master->id ? 'selected' : '' }}>
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
                    value="{{ old('revision_date', $report->revision_date) }}">
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
              @foreach ($groupedDetails as $groupName => $items)
                <div class="form-group mt-3">
                  <h5 class="mb-3">{{ $groupName }}</h5>
                  <ul class="list-group" id="list-group-{{ Str::slug($groupName) }}">
                    @foreach ($items as $item)
                      <li class="list-group-item" id="item-{{ $item->id }}">
                        <div class="d-flex justify-content-between align-items-center">
                          <div class="form-check">
                            <input class="form-check-input @error('items') is-invalid @enderror"
                              type="checkbox" name="items[]" value="{{ $item->id }}"
                              id="item{{ $item->id }}">
                            <label class="form-check-label"
                              for="item{{ $item->id }}">{{ $item->name ?? $item->typecategory->name }}</label>
                          </div>
                          <div class="d-flex">
                            <div class="form-group me-3">
                              <select name="conditions[{{ $item->id }}]"
                                id="condition{{ $item->id }}"
                                class="form-select @error('conditions.' . $item->id) is-invalid @enderror">
                                <option value="" disabled
                                  {{ old('conditions.' . $item->id, $item->condition ?? '') ? '' : 'selected' }}>
                                  --Select Condition--</option>
                                <option value="good"
                                  {{ old('conditions.' . $item->id, $item->condition ?? '') == 'good' ? 'selected' : '' }}>
                                  Good</option>
                                <option value="bad"
                                  {{ old('conditions.' . $item->id, $item->condition ?? '') == 'bad' ? 'selected' : '' }}>
                                  Bad</option>
                              </select>
                              @error('conditions.' . $item->id)
                                <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                            </div>
                            <div class="form-group me-3">
                              <textarea name="remarks[{{ $item->id }}]" id="remark{{ $item->id }}"
                                class="form-control @error('remarks.' . $item->id) is-invalid @enderror" rows="1"
                                placeholder="Remark">{{ old('remarks.' . $item->id, $item->remark ?? '') }}</textarea>
                              @error('remarks.' . $item->id)
                                <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                            </div>
                            <div class="form-group me-3">
                              <select name="checked_by[{{ $item->id }}]"
                                id="checkedBy{{ $item->id }}"
                                class="form-select @error('checked_by.' . $item->id) is-invalid @enderror">
                                <option value="" disabled
                                  {{ old('checked_by.' . $item->id, $item->checked_by ?? '') ? '' : 'selected' }}>
                                  --Select Checker--</option>
                                @foreach ($users as $user)
                                  <option value="{{ $user->name }}"
                                    {{ old('checked_by.' . $item->id, $item->checked_by ?? '') == $user->name ? 'selected' : '' }}>
                                    {{ $user->name }}</option>
                                @endforeach
                              </select>
                              @error('checked_by.' . $item->id)
                                <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                            </div>
                            <button type="button" class="btn btn-danger ms-2"
                              onclick="removeItem({{ $item->id }}, true)">Remove</button>
                          </div>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                  <button type="button" class="btn btn-secondary mt-2"
                    onclick="addItem('{{ Str::slug($groupName) }}')">Add Item</button>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      <button type="submit" name="action" value="update"
        class="btn btn-primary mt-3 me-2">Update</button>
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
                    <input type="hidden" name="new_group_ids[${itemId}]" value="${groupSlug}">
                    <div class="d-flex">
                        <div class="form-group me-3">
                            <select name="new_conditions[${itemId}]" class="form-select">
                                <option value="" disabled selected>--Select Condition--</option>
                                <option value="good">Good</option>
                                <option value="bad">Bad</option>
                            </select>
                        </div>
                        <div class="form-group me-3">
                            <textarea name="new_remarks[${itemId}]" class="form-control" rows="1" placeholder="Remark"></textarea>
                        </div>
                        <div class="form-group me-3">
                            <select name="new_checked_by[${itemId}]" class="form-select">
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

    function removeItem(itemId, isExistingItem) {
      const item = isExistingItem ? document.getElementById(`item-${itemId}`) : document
        .getElementById(
          `newItem${itemId}`);
      if (item) {
        item.remove();
      }
    }

    function checkAll() {
      const checkboxes = document.querySelectorAll('.form-check-input');
      checkboxes.forEach(checkbox => checkbox.checked = true);
    }

    function setAllGood() {
      const conditionSelects = document.querySelectorAll(
        'select[name^="conditions"], select[name^="new_conditions"]');
      conditionSelects.forEach(select => select.value = 'good');
    }

    function setCheckedByMe() {
      const checkedBySelects = document.querySelectorAll(
        'select[name^="checked_by"], select[name^="new_checked_by"]');
      const authUserName = "{{ $authUser->name }}";
      checkedBySelects.forEach(select => select.value = authUserName);
    }
  </script>
@endsection
