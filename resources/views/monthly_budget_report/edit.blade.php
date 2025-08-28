@extends('layouts.app')

@section('content')
  @include('partials.alert-success-error')
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('monthly.budget.report.index') }}">Monthly Budget
            Reports</a>
        </li>
        <li class="breadcrumb-item active">Edit</li>
      </ol>
    </nav>
    <div class="h2 fw-bold">Edit Monthly Budget Report</div>
    <div class="row justify-content-center mt-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body pb-4">
            <form action="{{ route('monthly.budget.report.update', $report->id) }}" method="post"
              class="row gx-3" id="form-monthly-budget-report">
              @csrf
              @method('PUT')
              {{-- <input type="hidden" name="created_autograph" value="{{ ucwords(auth()->user()->name) }}">
                            <input type="hidden" name="creator_id" value="{{ auth()->user()->id }}"> --}}
              <div class="form-group mt-1 col">
                <label class="form-label fs-5 fw-bold">Dept No</label>
                <input type="text" name="dept_no" value="{{ $report->dept_no }}" readonly
                  class="form-control bg-secondary-subtle">
                {{-- <select name="dept_no" id="dept_no" required>
                                    @foreach ($departments as $department)
                                        @if ($department->name !== 'MANAGEMENT')
                                            <option value="{{ $department->dept_no }}"
                                                {{ $report->department->id === $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}</option>
                                        @endif
                                    @endforeach
                                </select> --}}
              </div>
              <div class="form-group mt-1 col">
                <label class="form-label fs-5 fw-bold">Report Date</label>
                <input class="form-control" type="date" name="report_date"
                  value="{{ $report->report_date }}" required>
              </div>

              {{-- <div id="manualInputSection">
                                <div class="form-group mt-4">
                                    <label class="form-label fs-5 fw-bold">List of Items</label>
                                    <div id="items" class="border rounded-1 pt-2 pb-4 ps-3 pe-3 mb-1"></div>
                                </div>
                                <button class="btn btn-outline-secondary mt-3 btn-sm" type="button"
                                    onclick="addNewItem()">+ Add Item</button>
                            </div> --}}
            </form>

            <form action="{{ route('monthly.budget.download.excel.template') }}" method="post"
              id="formExcelTemplate">
              @csrf
              <input type="hidden" name="dept_no" id="deptNoFormExcelTemplate">
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="row justify-content-center mt-4">
      <div class="col-md-12">
        <div class="form-group mt-2">
          <div class="row justify-content-between">
            <div class="col">
              <label class="form-label fs-4 fw-bold">List of Items</label>
            </div>
            <div class="col-auto">
              @include('partials.create-monthly-budget-report-detail', [
                  'report' => $report,
              ])
              <button data-bs-toggle="modal" data-bs-target="#create-monthly-budget-report-detail"
                class="btn btn-outline-primary">Create</button>
            </div>
          </div>
          <div class="card mt-2">
            <div class="card-body pb-0">
              <table class="table table-borderless table-striped text-center">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    @if ($report->department->name === 'MOULDING')
                      <th>Spec</th>
                    @endif
                    <th>UoM</th>
                    @if ($report->department->name === 'MOULDING')
                      <th>Last Recorded Stock</th>
                      <th>Usage Per Month</th>
                    @endif
                    <th>Quantity Request</th>
                    <th>Remark</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($details as $detail)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $detail->name }}</td>
                      @if ($report->department->name === 'MOULDING')
                        <td>{{ $detail->spec }}</td>
                      @endif
                      <td>{{ $detail->uom }}</td>
                      @if ($report->department->name === 'MOULDING')
                        <td>{{ $detail->last_recorded_stock }}</td>
                        <td>{{ $detail->usage_per_month }}</td>
                      @endif
                      <td>{{ $detail->quantity }}</td>
                      <td>{{ $detail->remark }}</td>
                      <td>
                        @include('partials.edit-monthly-budget-report-detail')
                        <button
                          data-bs-target="#edit-monthly-budget-report-detail-{{ $detail->id }}"
                          data-bs-toggle="modal" class="btn btn-primary">Edit</button>
                        @include('partials.delete-confirmation-modal', [
                            'id' => $detail->id,
                            'title' => 'Are you sure want to delete this?',
                            'body' => 'Once it delete it cannot be undone!',
                            'route' => 'monthly.budget.report.detail.delete',
                        ])
                        <button data-bs-toggle="modal"
                          data-bs-target="#delete-confirmation-modal-{{ $detail->id }}"
                          class="btn btn-danger">Delete</button>

                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10">No Data</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-group mt-4">
      <button type="submit" class="btn btn-primary w-100"
        onclick="document.getElementById('form-monthly-budget-report').submit()">Submit</button>
    </div>
  </div>
@endsection

@push('extraJs')
  <script>
    let details = {!! $report->details !!};
    // console.log(details);
    const deptNoSelect = document.getElementById('dept_no');
    const deptNoFormExcelTemplate = document.getElementById('deptNoFormExcelTemplate');
    // Initial Value
    deptNoFormExcelTemplate.value = deptNoSelect.value;
    // Change deptNoFormExcelTemplate value based on the deptNoSelect value
    deptNoSelect.addEventListener('change', function() {
      deptNoFormExcelTemplate.value = deptNoSelect.value;
    });

    document.addEventListener('DOMContentLoaded', function() {
      // Initialize TomSelect for dropdown
      new TomSelect('#dept_no', {
        plugins: ['dropdown_input'],
        sortField: {
          field: "text",
          direction: "asc"
        }
      });

      // Populate existing details
      if (details.length > 0) {
        details.forEach(detail => {
          addNewItem(detail);
        });
      } else {
        addNewItem(); // Initialize with one item if no details
      }
    });

    let itemIdCounter = 0;
    let isFirstCall = true;

    function addNewItem(detail = {}) {
      const newItemContainer = document.createElement('div');
      newItemContainer.classList.add('added-item', 'row', 'gy-2', 'gx-2', 'align-items-center',
        'mt-1');

      const columnSizes = ['col-md-1', 'col-md-2', 'col-md-2', 'col-md-1', 'col-md-1', 'col-md-1',
        'col-md-1',
        'col-md-2', 'col-md-1'
      ];

      if (isFirstCall) {
        const headerLabels = ['#', 'Name', 'Spec', 'UoM', 'Last Recorded Stock', 'Usage Per Month',
          'Quantity Request', 'Remark', 'Action'
        ];
        const headerRow = document.createElement('div');
        headerRow.classList.add('row', 'gy-2', 'gx-2', 'align-items-center', 'header-row');

        headerLabels.forEach((label, index) => {
          const headerLabel = document.createElement('div');
          headerLabel.classList.add(columnSizes[index], 'text-center', 'header-label',
            'fw-semibold',
            `header-${index}`);
          headerLabel.textContent = label;
          headerRow.appendChild(headerLabel);
        });

        headerRow.appendChild(document.createElement('hr'));
        document.getElementById('items').appendChild(headerRow);
        isFirstCall = false;
      }

      const countGroup = document.createElement('div');
      countGroup.classList.add(columnSizes[0], 'text-center', 'count-group');
      countGroup.textContent = itemIdCounter + 1;

      const formGroups = [{
          size: columnSizes[1],
          name: `items[${itemIdCounter}][name]`,
          placeholder: 'Name',
          type: 'text',
          value: detail.name || ''
        },
        {
          size: columnSizes[2],
          name: `items[${itemIdCounter}][spec]`,
          placeholder: 'Spec',
          type: 'text',
          class: 'spec',
          value: detail.spec || ''
        },
        {
          size: columnSizes[3],
          name: `items[${itemIdCounter}][uom]`,
          placeholder: 'UoM',
          type: 'text',
          value: detail.uom || 'PCS'
        },
        {
          size: columnSizes[4],
          name: `items[${itemIdCounter}][last_recorded_stock]`,
          placeholder: 'Last Recorded Stock',
          type: 'number',
          class: 'stock',
          value: detail.last_recorded_stock || ''
        },
        {
          size: columnSizes[5],
          name: `items[${itemIdCounter}][usage_per_month]`,
          placeholder: 'Usage Per Month',
          type: 'text',
          class: 'usage',
          value: detail.usage_per_month || ''
        },
        {
          size: columnSizes[6],
          name: `items[${itemIdCounter}][quantity]`,
          placeholder: 'Qty',
          type: 'number',
          value: detail.quantity || ''
        },
        {
          size: columnSizes[7],
          name: `items[${itemIdCounter}][remark]`,
          placeholder: 'Remark',
          type: 'text',
          value: detail.remark || ''
        }
      ];

      newItemContainer.appendChild(countGroup);

      formGroups.forEach(group => {
        const formGroup = document.createElement('div');
        formGroup.classList.add(group.size);

        if (group.class) {
          formGroup.classList.add(group.class);
        }

        const input = document.createElement('input');
        input.classList.add('form-control');
        input.type = group.type;
        input.name = group.name;
        input.placeholder = group.placeholder;
        if (group.value) input.value = group.value;

        formGroup.appendChild(input);
        newItemContainer.appendChild(formGroup);
      });

      // Add hidden input for detail id
      if (detail.id) {
        const hiddenIdInput = document.createElement('input');
        hiddenIdInput.type = 'hidden';
        hiddenIdInput.name = `items[${itemIdCounter}][id]`;
        hiddenIdInput.value = detail.id;
        newItemContainer.appendChild(hiddenIdInput);
      }

      const actionGroup = document.createElement('div');
      actionGroup.classList.add(columnSizes[8], 'text-center');

      const removeButton = document.createElement('a');
      removeButton.classList.add('btn', 'btn-danger');
      removeButton.textContent = "Remove";
      removeButton.addEventListener('click', removeItem);

      actionGroup.appendChild(removeButton);
      newItemContainer.appendChild(actionGroup);

      document.getElementById('items').appendChild(newItemContainer);
      itemIdCounter++;

      updateItemCount();
      applyDepartmentRules(deptNoSelect.value);
    }

    function applyDepartmentRules(deptNo) {
      const items = document.querySelectorAll('.added-item');
      const headers = document.querySelectorAll('.header-row .header-label');

      if (deptNo == '363') {
        items.forEach(item => {
          showElement(item.children[2]);
          showElement(item.children[4]);
          showElement(item.children[5]);

          enableInput(item.children[2].querySelector('input'));
          enableInput(item.children[4].querySelector('input'));
          enableInput(item.children[5].querySelector('input'));
        });

        showElement(headers[2]);
        showElement(headers[4]);
        showElement(headers[5]);

        items.forEach(item => {
          resetColumnSizes(item.children[1], 'col-md-3', 'col-md-2');
          resetColumnSizes(item.children[6], 'col-md-2', 'col-md-1');
          resetColumnSizes(item.children[7], 'col-md-4', 'col-md-2');
        });

        resetColumnSizes(headers[1], 'col-md-3', 'col-md-2');
        resetColumnSizes(headers[6], 'col-md-2', 'col-md-1');
        resetColumnSizes(headers[7], 'col-md-4', 'col-md-2');
      } else {
        items.forEach(item => {
          hideElement(item.children[2]);
          hideElement(item.children[4]);
          hideElement(item.children[5]);

          disableInput(item.children[2].querySelector('input'));
          disableInput(item.children[4].querySelector('input'));
          disableInput(item.children[5].querySelector('input'));
        });

        hideElement(headers[2]);
        hideElement(headers[4]);
        hideElement(headers[5]);

        items.forEach(item => {
          resetColumnSizes(item.children[1], 'col-md-2', 'col-md-3');
          resetColumnSizes(item.children[6], 'col-md-1', 'col-md-2');
          resetColumnSizes(item.children[7], 'col-md-2', 'col-md-4');
        });

        resetColumnSizes(headers[1], 'col-md-2', 'col-md-3');
        resetColumnSizes(headers[6], 'col-md-1', 'col-md-2');
        resetColumnSizes(headers[7], 'col-md-2', 'col-md-4');
      }
    }

    function showElement(element) {
      element.style.display = 'block';
    }

    function hideElement(element) {
      element.style.display = 'none';
    }

    function enableInput(input) {
      input.removeAttribute('disabled');
    }

    function disableInput(input) {
      input.setAttribute('disabled', 'disabled');
    }

    function resetColumnSizes(element, oldClass, newClass) {
      element.classList.replace(oldClass, newClass);
    }

    function removeItem(event) {
      const itemContainer = event.target.closest('.added-item');
      itemContainer.remove();
      itemIdCounter--;
      updateItemCount();
    }

    function updateItemCount() {
      const addedItems = document.querySelectorAll('.added-item');
      addedItems.forEach((item, index) => {
        const countGroup = item.querySelector('.count-group');
        countGroup.textContent = index + 1;
      });
    }

    document.getElementById('dept_no').addEventListener('change', function() {
      applyDepartmentRules(this.value);
    });
  </script>
@endpush
