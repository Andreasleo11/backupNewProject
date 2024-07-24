@extends('layouts.app')

@section('content')
    @php
        $authUser = auth()->user();
    @endphp

    @include('partials.alert-success-error')

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body p-5">
                    <div class="h2 text-center fw-semibold">Create Purchase Request</div>
                    <form action="{{ route('purchaserequest.insert') }}" method="POST" class="row ">
                        @csrf

                        <div class="form-group mt-5 col-md-2">
                            <label class="form-label fs-5 fw-bold">Branch</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="branch" id="jakartaRadio"
                                    value="JAKARTA" checked>
                                <label class="form-check-label" for="jakartaRadio">
                                    Jakarta
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="branch" id="karawangRadio"
                                    value="KARAWANG" {{ old('branch') == 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="karawangRadio">
                                    Karawang
                                </label>
                            </div>
                        </div>

                        <div class="form-group mt-5 col">
                            <label class="form-label fs-5 fw-bold" for="from_department">From Department</label>
                            <select class="form-select" name="from_department" id="fromDepartmentDropdown" required>
                                <option value="" disabled>Select from department..</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->name }}"
                                        {{ old('from_department', $authUser->department->name) === $department->name ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Ubah hanya jika ingin membuat PR diluar dari departemen sendiri</div>
                        </div>

                        <div class="form-group mt-5 col">
                            <label class="form-label fs-5 fw-bold" for="to_department">To Department</label>
                            <select class="form-select" name="to_department" id="toDepartmentDropdown" required>
                                <option value="" disabled>Select to department..</option>
                                <option value="Maintenance" {{ old('to_department') == 'Maintenance' ? 'selected' : '' }}>
                                    Maintenance</option>
                                <option value="Purchasing" {{ old('to_department') == 'Purchasing' ? 'selected' : '' }}>
                                    Purchasing</option>
                                <option value="Personnel" {{ old('to_department') == 'Personnel' ? 'selected' : '' }}>
                                    Personnel</option>
                                <option value="Computer" {{ old('to_department') == 'Computer' ? 'selected' : '' }}>
                                    Computer</option>
                            </select>
                            <div class="form-text">Pilih departemen yang dituju. Eg. Computer</div>
                        </div>

                        <div class="form-group mt-5 col d-none" id="localImportFormGroup">
                            <label class="form-label fs-5 fw-bold">Local/Import</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_import" id="localRadio"
                                    value="false" {{ old('is_import') == 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="localRadio">
                                    Local
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_import" id="importRadio"
                                    value="true" {{ old('is_import') == 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="importRadio">
                                    Import
                                </label>
                            </div>

                            <div class="form-text">Jenis PR termasuk Local atau Import (Khusus MOULDING)</div>
                        </div>

                        <div class="form-group mt-3">
                            <div id="itemsContainer">
                                <label class="form-label fs-5 fw-bold">List of Items</label>
                                <div id="items" class="border rounded-1 py-2 my-2 px-1 pe-2 mb-3">
                                    <!-- Item rows will be dynamically added here -->
                                </div>
                                <button class="btn btn-secondary btn-sm mt-3" type="button" onclick="addNewItem()">Add
                                    Item</button>
                            </div>
                        </div>

                        <div class="form-group mt-3 col-md-6">
                            <label class="form-label fs-5 fw-bold" for="date_of_pr">Date of PR</label>
                            <input class="form-control" type="date" id="date_of_pr" name="date_of_pr"
                                value="{{ old('date_of_pr') }}" required>
                        </div>

                        <div class="form-group mt-3 col-md-6">
                            <label class="form-label fs-5 fw-bold" for="date_of_required">Date of Required</label>
                            <input class="form-control" type="date" name="date_of_required"
                                value="{{ old('date_of_required') }}" required>
                        </div>

                        <div class="form-group mt-3 col-md-6">
                            <label class="form-label fs-5 fw-bold col-sm-2" for="supplier">Supplier</label>
                            <input class="form-control" type="text" name="supplier" value="{{ old('supplier') }}"
                                required>
                        </div>

                        <div class="form-group mt-3 col-md-6">
                            <label class="form-label fs-5 fw-bold col-sm-2" for="pic">PIC</label>
                            <input class="form-control" type="text" name="pic" value="{{ old('pic') }}"
                                required>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label fs-5 fw-bold" for="remark">Remark</label>
                            <textarea class="form-control" name="remark" rows="4" required>{{ old('remark') }}</textarea>
                        </div>

                        <button class="btn btn-primary mt-3" type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var fromDepartmentDropdown = document.getElementById("fromDepartmentDropdown");
            var localImportFormGroup = document.getElementById("localImportFormGroup");
            var toDepartmentDropdown = document.getElementById("toDepartmentDropdown");

            toDepartmentDropdown.addEventListener("change", function() {
                if (fromDepartmentDropdown.value === "MOULDING" && toDepartmentDropdown.value ===
                    "Purchasing") {
                    localImportFormGroup.classList.remove("d-none");
                    localImportFormGroup.querySelectorAll("input").forEach(function(input) {
                        input.disabled = false;
                    });
                } else {
                    localImportFormGroup.classList.add("d-none");
                    localImportFormGroup.querySelectorAll("input").forEach(function(input) {
                        input.disabled = true;
                    });
                }
            });

            // Trigger change event on page load if initial value is "MOULDING"
            if (fromDepartmentDropdown.value === "MOULDING") {
                fromDepartmentDropdown.dispatchEvent(new Event("change"));
            }
        });

        // Counter for creating unique IDs for items
        let itemIdCounter = 0;
        let isFirstCall = true; // Flag to track the first call

        var oldItemsData = {!! json_encode(old('items')) !!};
        if (oldItemsData) {
            for (let index = 0; index <= oldItemsData.length; index++) {
                addNewItem();
            }
        }

        function addNewItem() {
            // Create a new item container
            const newItemContainer = document.createElement('div');
            newItemContainer.classList.add('added-item', 'row', 'gy-2', 'gx-2', 'align-items-center', 'my-1');

            if (isFirstCall) {
                // Define header labels and their corresponding column sizes
                const headerLabels = ['Count', 'Item Name', 'Qty', 'UoM', 'Currency', 'Unit Price', 'Subtotal',
                    'Purpose',
                    'Action'
                ];
                const columnSizes = ['col-md-1', 'col-md-2', 'col-md-1', 'col-md-1', 'col-md-1', 'col-md-2',
                    'col-md-2',
                    'col-md-1', 'col-md-1'
                ];

                // Create header row and add header labels with specified column sizes
                const headerRow = document.createElement('div');
                headerRow.classList.add('row', 'gy-2', 'gx-2', 'align-items-center', 'header-row');

                headerLabels.forEach((label, index) => {
                    const headerLabel = document.createElement('div');
                    headerLabel.classList.add(columnSizes[index], 'text-center', 'header-label',
                        'fw-semibold');
                    headerLabel.textContent = label;
                    headerRow.appendChild(headerLabel);
                });

                document.getElementById('items').appendChild(headerRow);

                isFirstCall = false; // Update the flag to indicate that headers are added
            }

            const countGroup = document.createElement('div')
            countGroup.classList.add('count-group', 'col-md-1', 'text-center');
            countGroup.textContent = itemIdCounter + 1;

            // Create input fields for item details
            const formGroupName = document.createElement('div')
            formGroupName.classList.add('col-md-2');

            const itemNameInput = document.createElement('input');
            itemNameInput.classList.add('form-control');
            itemNameInput.setAttribute('required', 'required');
            itemNameInput.type = 'text';
            itemNameInput.name = `items[${itemIdCounter}][item_name]`;
            itemNameInput.id = `itemNameInput_${itemIdCounter}`;
            itemNameInput.placeholder = 'Item Name';
            itemNameInput.required = true;

            formGroupName.appendChild(itemNameInput);
            // formGroupName.appendChild(itemNameDropdown);

            const formGroupQuantityInput = document.createElement('div')
            formGroupQuantityInput.classList.add('col-md-1');

            const quantityInput = document.createElement('input');
            quantityInput.classList.add('form-control');
            quantityInput.setAttribute('required', 'required');
            quantityInput.type = 'text';
            quantityInput.name = `items[${itemIdCounter}][quantity]`;
            quantityInput.placeholder = 'Qty';
            quantityInput.required = true;
            quantityInput.addEventListener('input', function() {
                validateNumber(quantityInput);
            });
            quantityInput.addEventListener('change', function() {
                formatPrice(unitPriceInput, currencyInput.value);
            });

            formGroupQuantityInput.appendChild(quantityInput);

            const formGroupUomInput = document.createElement('div')
            formGroupUomInput.classList.add('col-md-1')

            const uomInput = document.createElement('input');
            uomInput.classList.add('form-control');
            uomInput.value = 'PCS';
            uomInput.setAttribute('required', 'required');
            uomInput.type = 'text';
            uomInput.name = `items[${itemIdCounter}][uom]`;
            uomInput.placeholder = 'UoM';
            uomInput.required = true;

            formGroupUomInput.appendChild(uomInput);

            const formGroupCurrencyInput = document.createElement('div')
            formGroupCurrencyInput.classList.add('col-md-1');

            const currencyInput = document.createElement('select');
            currencyInput.classList.add('form-select');
            currencyInput.setAttribute('required', 'required');
            currencyInput.name = `items[${itemIdCounter}][currency]`;
            currencyInput.required = true;

            var options = [{
                    value: 'IDR',
                    text: 'IDR',
                    selected: true
                },
                {
                    value: 'CNY',
                    text: 'CNY',
                    selected: false
                },
                {
                    value: 'USD',
                    text: 'USD',
                    selected: false
                },
            ];

            options.forEach(function(option) {
                var optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                if (option.selected) {
                    optionElement.selected = true;
                }
                currencyInput.appendChild(optionElement);
            });

            currencyInput.addEventListener('change', function() {
                formatPrice(unitPriceInput, currencyInput.value);
                formatPrice(subtotalInput, currencyInput.value);
            });

            formGroupCurrencyInput.appendChild(currencyInput);

            const formGroupUnitPriceInput = document.createElement('div')
            formGroupUnitPriceInput.classList.add('col-md-2');

            const unitPriceInput = document.createElement('input');
            unitPriceInput.classList.add('form-control');
            unitPriceInput.setAttribute('required', 'required');
            unitPriceInput.type = 'text';
            unitPriceInput.name = `items[${itemIdCounter}][price]`;
            unitPriceInput.placeholder = 'Unit Price';
            unitPriceInput.required = true;

            formGroupUnitPriceInput.appendChild(unitPriceInput);

            const formGroupSubtotalInput = document.createElement('div');
            formGroupSubtotalInput.classList.add('col-md-2');

            const subtotalInput = document.createElement('input');
            subtotalInput.classList.add('form-control');
            subtotalInput.type = 'text';
            subtotalInput.disabled = true;
            subtotalInput.id = `subtotal-${itemIdCounter}`;
            subtotalInput.value = 0;
            subtotalInput.required = true;

            formGroupSubtotalInput.appendChild(subtotalInput);

            const formGroupPurposeInput = document.createElement('div')
            formGroupPurposeInput.classList.add('col-md-1');

            const purposeInput = document.createElement('input');
            purposeInput.classList.add('form-control');
            purposeInput.setAttribute('required', 'required');
            purposeInput.type = 'text';
            purposeInput.name = `items[${itemIdCounter}][purpose]`;
            purposeInput.placeholder = 'Purpose';
            purposeInput.required = true;

            formGroupPurposeInput.appendChild(purposeInput);

            const actionGroup = document.createElement('div');
            actionGroup.classList.add('col-md-1', 'text-center');

            const removeButton = document.createElement('a');
            removeButton.classList.add('btn', 'btn-danger');
            removeButton.innerHTML = `<i class='bx bx-trash-alt'></i>`;
            removeButton.addEventListener('click', removeItem);

            actionGroup.appendChild(removeButton);

            // Append input fields to the item container
            newItemContainer.appendChild(countGroup);
            newItemContainer.appendChild(formGroupName);
            newItemContainer.appendChild(formGroupQuantityInput);
            newItemContainer.appendChild(formGroupUomInput);
            newItemContainer.appendChild(formGroupCurrencyInput);
            newItemContainer.appendChild(formGroupUnitPriceInput);
            newItemContainer.appendChild(formGroupSubtotalInput);
            newItemContainer.appendChild(formGroupPurposeInput);
            newItemContainer.appendChild(actionGroup);

            // Append the new item container to the items container
            document.getElementById('items').appendChild(newItemContainer);

            quantityInput.addEventListener('input', function() {
                const unitPrice = parseFloat(unitPriceInput.value.replace(/[^0-9.]/g,
                    '')); // Convert to float for calculation
                const quantity = parseFloat(quantityInput.value);
                const subtotal = (quantity * unitPrice).toFixed(2);
                subtotalInput.value = subtotal;
                formatPrice(subtotalInput, currencyInput.value);
            });

            unitPriceInput.addEventListener('input', function() {
                const unitPrice = parseFloat(unitPriceInput.value.replace(/[^0-9.]/g,
                    '')); // Convert to float for calculation
                const quantity = parseFloat(quantityInput.value);
                const subtotal = (quantity * unitPrice).toFixed(2);
                subtotalInput.value = subtotal;
                formatPrice(unitPriceInput, currencyInput.value);
                formatPrice(subtotalInput, currencyInput.value);
            });

            // Increment the item ID counter
            itemIdCounter++;
            // Initialize TomSelect for the newly added item name input with AJAX
            new TomSelect(`#itemNameInput_${itemIdCounter - 1}`, {
                valueField: 'name',
                labelField: 'name',
                searchField: 'name',
                load: function(query, callback) {
                    if (!query.length) return callback();
                    fetch(`/get-item-names?itemName=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            callback(data);
                        }).catch(() => {
                            callback();
                        });
                },
                render: {
                    option: function(item, escape) {
                        return `<div class="dropdown-item" data-id="${item.id}">
                                <span>${escape(item.name)}</span>
                            </div>`;
                    }
                },
                onItemAdd: function(value, $item) {
                    const selectedItem = this.options[value];
                    currencyInput.value = selectedItem.currency;
                    unitPriceInput.value = selectedItem.latest_price === null ? selectedItem.price :
                        selectedItem.latest_price;
                    formatPrice(unitPriceInput, currencyInput.value);
                    const unitPrice = unitPriceInput.value.replace(/[^\d,]/g, '').replace(',', '.');
                    subtotalInput.value = parseFloat(quantityInput.value) * unitPrice;
                    formatPrice(subtotalInput, currencyInput.value);
                },
                maxItems: 1,
                closeAfterSelect: true,
                create: true,
            });

            updateItemCount();

            if (oldItemsData && oldItemsData.length > 0) {
                const itemData = oldItemsData[itemIdCounter - 1];

                const {
                    item_name,
                    quantity,
                    uom,
                    currency,
                    price,
                    purpose
                } = itemData;

                // DEBUG
                // console.log(`item_name : ${item_name}`);
                // console.log(`quantity : ${quantity}`);
                // console.log(`uom : ${uom}`);
                // console.log(`currency : ${currency}`);
                // console.log(`price : ${price}`);
                // console.log(`purpose : ${purpose}`);

                itemNameInput.value = item_name;
                quantityInput.value = quantity;
                uomInput.value = uom;
                currencyInput.value = currency;
                unitPriceInput.value = price;
                purposeInput.value = purpose;

                // Calculate subtotal if quantity and unit price are provided
                if (itemData.quantity && itemData.price) {
                    const subtotal = (parseFloat(itemData.quantity) * parseFloat(itemData.price)).toFixed(2);
                    subtotalInput.value = subtotal;
                }
            }
        }

        // Function to validate the input
        function validateNumber(input) {
            const value = input.value;
            const regex = /^[+-]?(\d*\.)?\d*$/;
            if (!regex.test(value)) {
                input.value = value.slice(0, -1);
            }
        }

        function removeItem() {
            // Get the parent container of the remove button (which is the item container)
            const itemContainer = event.target.closest('.added-item');

            // Remove the item container from the DOM
            itemContainer.remove();

            // Decrement the item ID counter
            itemIdCounter--;

            // Update the item count
            updateItemCount();
        }

        function updateItemCount() {
            // Get all elements with the added-item class
            const addedItems = document.querySelectorAll('.added-item');

            // Loop through each added item and update the count
            addedItems.forEach((item, index) => {
                // Find the countGroup element in the current added item
                const countGroup = item.querySelector('.count-group');

                // Update the text content of the countGroup element
                countGroup.textContent = index + 1; // Add 1 because item ID starts from 0
            });
        }

        function formatPrice(input, currency) {
            // Replace non-numeric characters except period
            let price = input.value.replace(/[^0-9.]/g, '');

            let currencySymbol = '';
            if (currency === 'IDR') {
                currencySymbol = 'Rp ';
            } else if (currency === 'CNY') {
                currencySymbol = '¥ ';
            } else if (currency === 'USD') {
                currencySymbol = '$ ';
            }

            if (price.includes('.')) {
                // Handle decimal values
                let parts = price.split('.');
                let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g,
                    ','); // Add thousand separators with comma
                let decimalPart = parts[1];
                if (decimalPart.length > 2) {
                    decimalPart = decimalPart.substring(0, 2); // Limit to 2 decimal places
                }
                input.value = currencySymbol + integerPart + '.' + decimalPart;
            } else {
                // Handle integer values
                let formattedPrice = price.replace(/\B(?=(\d{3})+(?!\d))/g,
                    ','); // Add thousand separators with comma
                input.value = currencySymbol + formattedPrice;
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Initialize TomSelect for dropdown
            new TomSelect('#fromDepartmentDropdown', {
                plugins: ['dropdown_input'],
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });

            // Initialize TomSelect for dropdown
            new TomSelect('#toDepartmentDropdown', {
                plugins: ['dropdown_input'],
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
            addNewItem();
        });
    </script>
@endsection
