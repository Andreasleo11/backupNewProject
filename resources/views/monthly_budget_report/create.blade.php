@extends('layouts.app')

@section('content')
    @include('partials.alert-success-error')
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('monthly.budget.report.index') }}">Monthly Budget Reports</a>
                </li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
        <div class="h2 fw-bold">Create Monthly Budget Report</div>
        <div class="row justify-content-center mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('monthly.budget.report.store') }}" method="post"
                            enctype="multipart/form-data" class="row gx-3">
                            @csrf
                            <input type="hidden" name="created_autograph" value="{{ ucwords(auth()->user()->name) }}">
                            <input type="hidden" name="creator_id" value="{{ auth()->user()->id }}">
                            <div class="form-group mt-1 col">
                                <label class="form-label fs-5 fw-bold">Dept No</label>
                                <select name="dept_no" id="dept_no" required>
                                    @foreach ($departments as $department)
                                        @if ($department->name !== 'DIRECTOR')
                                            <option value="{{ $department->dept_no }}"
                                                {{ auth()->user()->department->id === $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mt-1 col">
                                <label class="form-label fs-5 fw-bold">Report Date</label>
                                <input class="form-control" type="date" name="report_date" required>
                            </div>
                            <div class="form-group mt-4">
                                <label class="form-label fs-5 fw-bold">Input Method</label><br>
                                <input type="checkbox" id="inputToggle" name="input_method" value="excel">
                                <label for="inputToggle"> Use Excel Input</label>
                                <button type="button" class="btn btn-sm btn-outline-secondary" style="display: none;"
                                    id="downloadExcelTemplateButton"
                                    onclick="document.getElementById('formExcelTemplate').submit()">Download Excel
                                    Template</button>
                            </div>
                            <div id="manualInputSection">
                                <div class="form-group mt-4">
                                    <label class="form-label fs-5 fw-bold">List of Items</label>
                                    <div id="items" class="border rounded-1 pt-2 pb-4 ps-3 pe-3 mb-1"></div>
                                </div>
                                <button class="btn btn-outline-secondary mt-3 btn-sm" type="button"
                                    onclick="addNewItem()">+ Add Item</button>
                            </div>
                            <div id="fileInputSection" style="display: none;">
                                <div class="form-group mt-4">
                                    <label class="form-label fs-5 fw-bold">Upload Excel File</label>
                                    <input class="form-control" type="file" name="excel_file">
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary w-100">Submit</button>
                            </div>
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
    </div>
@endsection

@push('extraJs')
    <script>
        const deptNoSelect = document.getElementById('dept_no');
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

            // Add event listener for toggle input method
            document.getElementById('inputToggle').addEventListener('change', toggleInputMethod);
        });

        let itemIdCounter = 0;
        let isFirstCall = true;

        function toggleInputMethod() {
            const manualInputSection = document.getElementById('manualInputSection');
            const fileInputSection = document.getElementById('fileInputSection');
            const inputToggle = document.getElementById('inputToggle');
            const downloadExcelTemplateButton = document.getElementById('downloadExcelTemplateButton');

            if (inputToggle.checked) {
                manualInputSection.style.display = 'none';
                fileInputSection.style.display = 'block';
                downloadExcelTemplateButton.style.display = 'inline';
                disableManualInputs(); // Disable manual input fields
            } else {
                manualInputSection.style.display = 'block';
                fileInputSection.style.display = 'none';
                downloadExcelTemplateButton.style.display = 'none';
                enableManualInputs(); // Enable manual input fields
            }
        }

        function enableManualInputs() {
            const manualInputs = document.querySelectorAll('#items input');
            manualInputs.forEach(input => {
                input.removeAttribute('disabled');
                input.setAttribute('required', 'required');
            });
        }

        function disableManualInputs() {
            const manualInputs = document.querySelectorAll('#items input');
            manualInputs.forEach(input => {
                input.setAttribute('disabled', 'disabled');
                input.removeAttribute('required');
            });
        }

        function addNewItem() {
            const newItemContainer = document.createElement('div');
            newItemContainer.classList.add('added-item', 'row', 'gy-2', 'gx-2', 'align-items-center', 'mt-1');

            const columnSizes = ['col-md-1', 'col-md-2', 'col-md-2', 'col-md-1', 'col-md-1', 'col-md-1', 'col-md-1',
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
                    headerLabel.classList.add(columnSizes[index], 'text-center', 'header-label', 'fw-semibold',
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
                    type: 'text'
                },
                {
                    size: columnSizes[2],
                    name: `items[${itemIdCounter}][spec]`,
                    placeholder: 'Spec',
                    type: 'text',
                    class: 'spec'
                },
                {
                    size: columnSizes[3],
                    name: `items[${itemIdCounter}][uom]`,
                    placeholder: 'UoM',
                    type: 'text',
                    value: 'PCS'
                },
                {
                    size: columnSizes[4],
                    name: `items[${itemIdCounter}][last_recorded_stock]`,
                    placeholder: 'Last Recorded Stock',
                    type: 'number',
                    class: 'stock'
                },
                {
                    size: columnSizes[5],
                    name: `items[${itemIdCounter}][usage_per_month]`,
                    placeholder: 'Usage Per Month',
                    type: 'text',
                    class: 'usage'
                },
                {
                    size: columnSizes[6],
                    name: `items[${itemIdCounter}][quantity]`,
                    placeholder: 'Qty',
                    type: 'number'
                },
                {
                    size: columnSizes[7],
                    name: `items[${itemIdCounter}][remark]`,
                    placeholder: 'Remark',
                    type: 'text'
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

        addNewItem(); // Initialize with one item
    </script>
@endpush
