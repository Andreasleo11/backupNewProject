<div class="modal fade" id="edit-purchase-request-modal-{{ $pr->id }}" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-body">
                <div class="text-end">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="row justify-content-center">
                    <div class="container p-5 pb-0">
                        <div class="h2 text-center fw-bold mb-4">Edit Purchase Request</div>
                        <form action="{{ route('purchaserequest.update', $pr->id) }}" method="POST" class="row"
                            id="form-pr-edit">
                            @method('PUT')
                            @csrf

                            <div class="form-group mt-3 col">
                                <label class="form-label fs-5 fw-bold" for="from_department">From Department</label>
                                <select class="form-select" name="from_department" id="fromDepartmentDropdown" required
                                    disabled>
                                    <option value="{{ $pr->from_department }}" selected>{{ $pr->from_department }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group mt-3 col d-none" id="localImportFormGroup">
                                <label class="form-label fs-5 fw-bold">Local/Import</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_import" id="localRadio"
                                        value="false" @if (!$pr->is_import) checked @endif
                                        @if ($pr->is_import !== null) disabled @endif>
                                    <label class="form-check-label" for="localRadio">
                                        Local
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_import" id="importRadio"
                                        value="true" @if ($pr->is_import) checked @endif
                                        @if ($pr->is_import !== null) disabled @endif>
                                    <label class="form-check-label" for="importRadio">
                                        Import
                                    </label>
                                </div>

                                <div class="form-text">Jenis PR termasuk Local atau Import (Khusus MOULDING)</div>
                            </div>

                            <div class="form-group mt-3 col">
                                <label class="form-label fs-5 fw-bold" for="to_department">To Department</label>
                                <select class="form-select" name="to_department" id="to_department" required disabled>
                                    <option value="{{ $pr->to_department }}" selected>
                                        {{ $pr->to_department }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group mt-3 col">
                                <label class="form-label fs-5 fw-bold" for="type">Type</label>
                                <select class="form-select" name="type" id="typeDropdown" required disabled>
                                    <option value="{{ $pr->type }}" selected>
                                        {{ ucwords($pr->type) }}</option>
                                </select>
                            </div>

                            <div class="form-group mt-3">
                                <div id="itemsContainer">
                                    <label class="form-label fs-5 fw-bold">List of Items</label>
                                    <div id="items" class="border rounded-1 py-2 my-2 px-1 pe-2 mb-3">
                                    </div>
                                    @if (Auth::user()->specification->name === 'PURCHASER')
                                        <button class="btn btn-secondary btn-sm" type="button"
                                            onclick="addNewItem()">Add
                                            Item</button>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label fs-5 fw-bold" for="date_pr">Date of PR</label>
                                <input class="form-control" type="date" id="date_pr" name="date_pr" required
                                    value="{{ $pr->date_pr }}">
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label fs-5 fw-bold" for="date_required">Date of Required</label>
                                <input class="form-control" type="date" name="date_required" required
                                    value="{{ $pr->date_required }}">
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label fs-5 fw-bold col-sm-2" for="supplier">Supplier</label>
                                <input class="form-control" type="text" name="supplier" required
                                    value="{{ $pr->supplier }}">
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label fs-5 fw-bold fs-5 fw-bold col-sm-2" for="pic">PIC</label>
                                <input class="form-control" type="text" name="pic" required
                                    value="{{ $pr->pic }}">
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label fs-5 fw-bold" for="remark">Remark</label>
                                <textarea class="form-control" name="remark" rows="4" cols="50" required>{{ $pr->remark }}</textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary"
                    onclick="document.getElementById('form-pr-edit').submit()">Save changes</button>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var fromDepartmentDropdown = document.getElementById("fromDepartmentDropdown");
            var localImportFormGroup = document.getElementById("localImportFormGroup");

            fromDepartmentDropdown.addEventListener("change", function() {
                if (fromDepartmentDropdown.value === "MOULDING") {
                    localImportFormGroup.classList.remove("d-none");
                    if (document.querySelector('input[name="is_import"]:checked')) {
                        document.querySelectorAll('input[name="is_import"]').forEach(function(input) {
                            input.disabled = true;
                        });
                    } else {
                        document.querySelectorAll('input[name="is_import"]').forEach(function(input) {
                            input.disabled = false;
                        });
                    }
                } else {
                    localImportFormGroup.classList.add("d-none");
                    document.querySelectorAll('input[name="is_import"]').forEach(function(input) {
                        input.disabled = true;
                    });
                }
            });

            // Trigger change event on page load if initial value is "MOULDING"
            if (fromDepartmentDropdown.value === "MOULDING") {
                fromDepartmentDropdown.dispatchEvent(new Event("change"));
            }
        });

        function updateTypeDropdown() {
            var selectedValue = document.getElementById('toDepartmentDropdown').value;
            var typeDropdown = document.getElementById('typeDropdown');
            typeDropdown.innerHTML = "";

            if (selectedValue === "Maintenance" || selectedValue === "Purchasing") {
                typeDropdown.innerHTML += "<option value=\"factory\">Factory</option>";
            } else if (selectedValue === "Personnel") {
                typeDropdown.innerHTML += "<option value=\"factory\">Factory</option>";
                typeDropdown.innerHTML += "<option value=\"office\">Office</option>";
            } else {
                typeDropdown.innerHTML += "<option value=\"factory\">Factory</option>";
                typeDropdown.innerHTML += "<option value=\"office\">Office</option>";
            }
        }

        let itemIdCounter = 0;
        let isFirstCall = true;

        let details = {!! json_encode($details) !!};
        // console.log(details);

        let filteredDetails = details.filter(detail => {
            return detail.is_approve_by_head === 1 || detail.is_approve_by_head === null;
        });

        filteredDetails.forEach(detail => {
            addNewItem(detail);
        });

        function addNewItem($detail = null) {
            const newItemContainer = document.createElement('div');
            newItemContainer.classList.add('added-item', 'row', 'gy-2', 'gx-2', 'align-items-center');

            if (isFirstCall) {
                const headerLabels = ['Count', 'Item Name', 'Qty', 'UoM', 'Currency', 'Unit Price',
                    'Subtotal', 'Purpose',
                    'Action'
                ];
                const columnSizes = ['col-md-1', 'col-md-2', 'col-md-1', 'col-md-1', 'col-md-1', 'col-md-2',
                    'col-md-2',
                    'col-md-1', 'col-md-1'
                ];
                const headerRow = document.createElement('div');
                headerRow.classList.add('row', 'gy-2', 'gx-2', 'my-1', 'align-items-center', 'header-row');

                headerLabels.forEach((label, index) => {
                    const headerLabel = document.createElement('div');
                    headerLabel.classList.add(columnSizes[index], 'text-center', 'header-label',
                        'fw-semibold');
                    headerLabel.textContent = label;
                    headerRow.appendChild(headerLabel);
                });

                document.getElementById('items').appendChild(headerRow);
                isFirstCall = false;
            }

            const countGroup = document.createElement('div');
            countGroup.classList.add('count-group', 'col-md-1', 'text-center');
            countGroup.textContent = itemIdCounter + 1;

            const formGroupName = document.createElement('div');
            formGroupName.classList.add('col-md-2');

            const itemNameInput = document.createElement('input');
            itemNameInput.classList.add('form-control');
            itemNameInput.setAttribute('required', 'required');
            itemNameInput.type = 'text';
            itemNameInput.name = `items[${itemIdCounter}][item_name]`;
            itemNameInput.id = `itemNameInput_${itemIdCounter}`;
            itemNameInput.placeholder = 'Item Name';
            itemNameInput.value = $detail?.item_name ?? "";
            itemNameInput.required = true;

            formGroupName.appendChild(itemNameInput);
            // formGroupName.appendChild(itemNameDropdown);

            const formGroupQuantityInput = document.createElement('div');
            formGroupQuantityInput.classList.add('col-md-1');

            const quantityInput = document.createElement('input');
            quantityInput.classList.add('form-control', 'quantity-input');
            quantityInput.setAttribute('required', 'required');
            quantityInput.type = 'text';
            quantityInput.name = `items[${itemIdCounter}][quantity]`;
            quantityInput.placeholder = 'Qty';
            quantityInput.required = true;
            quantityInput.value = $detail?.quantity ?? "";
            quantityInput.addEventListener('input', function() {
                validateNumber(quantityInput);
            });

            formGroupQuantityInput.appendChild(quantityInput);

            const formGroupUomInput = document.createElement('div');
            formGroupUomInput.classList.add('col-md-1');

            const uomInput = document.createElement('input');
            uomInput.classList.add('form-control');
            uomInput.value = $detail?.uom ?? "";
            uomInput.setAttribute('required', 'required');
            uomInput.type = 'text';
            uomInput.name = `items[${itemIdCounter}][uom]`;
            uomInput.placeholder = 'UoM';
            uomInput.required = true;

            formGroupUomInput.appendChild(uomInput);

            const formGroupCurrencyInput = document.createElement('div');
            formGroupCurrencyInput.classList.add('col-md-1');

            const currencyInput = document.createElement('select');
            currencyInput.classList.add('form-select');
            currencyInput.name = `items[${itemIdCounter}][currency]`;
            currencyInput.required = true;

            var options = [{
                    value: 'IDR',
                    text: 'IDR',
                    selected: false
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
                if (option.value === $detail?.currency) {
                    option.selected = true;
                }

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

            const formGroupUnitPriceInput = document.createElement('div');
            formGroupUnitPriceInput.classList.add('col-md-2');

            const unitPriceInput = document.createElement('input');
            unitPriceInput.classList.add('form-control', 'unit-price-input');
            unitPriceInput.setAttribute('required', 'required');
            unitPriceInput.type = 'text';
            unitPriceInput.name = `items[${itemIdCounter}][price]`;
            unitPriceInput.placeholder = 'Unit Price';
            unitPriceInput.required = true;
            unitPriceInput.value = $detail?.price ?? "";

            formGroupUnitPriceInput.appendChild(unitPriceInput);

            const formGroupSubtotalInput = document.createElement('div');
            formGroupSubtotalInput.classList.add('col-md-2');

            const subtotalInput = document.createElement('input');
            subtotalInput.classList.add('form-control', 'subtotal-input');
            subtotalInput.type = 'text';
            subtotalInput.disabled = true;
            subtotalInput.required = true;
            subtotalInput.id = `subtotal-${itemIdCounter}`;
            subtotalInput.value = parseFloat(quantityInput.value) * parseFloat(unitPriceInput.value);

            formGroupSubtotalInput.appendChild(subtotalInput);

            const formGroupPurposeInput = document.createElement('div');
            formGroupPurposeInput.classList.add('col-md-1');

            const purposeInput = document.createElement('input');
            purposeInput.classList.add('form-control');
            purposeInput.setAttribute('required', 'required');
            purposeInput.type = 'text';
            purposeInput.name = `items[${itemIdCounter}][purpose]`;
            purposeInput.placeholder = 'Purpose';
            purposeInput.required = true;
            purposeInput.value = $detail?.purpose ?? "";

            formGroupPurposeInput.appendChild(purposeInput);

            const actionGroup = document.createElement('div');
            actionGroup.classList.add('col-md-1', 'text-center');

            const removeButton = document.createElement('a');
            removeButton.classList.add('btn', 'btn-danger');
            removeButton.innerHTML = `<i class='bx bx-trash-alt'></i>`;
            removeButton.addEventListener('click', removeItem);

            actionGroup.appendChild(removeButton);

            newItemContainer.appendChild(countGroup);
            newItemContainer.appendChild(formGroupName);
            newItemContainer.appendChild(formGroupQuantityInput);
            newItemContainer.appendChild(formGroupUomInput);
            newItemContainer.appendChild(formGroupCurrencyInput);
            newItemContainer.appendChild(formGroupUnitPriceInput);
            newItemContainer.appendChild(formGroupSubtotalInput);
            newItemContainer.appendChild(formGroupPurposeInput);
            newItemContainer.appendChild(actionGroup);

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

            itemIdCounter++;
            updateItemCount();

            document.addEventListener("DOMContentLoaded", function() {
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
            });
        }

        // Function to validate the input
        function validateNumber(input) {
            const value = input.value;
            const regex = /^[+-]?(\d*\.)?\d*$/;
            if (!regex.test(value)) {
                input.value = value.slice(0, -1);
            }
        }

        function removeItem(event) {
            const itemContainer = event.target.closest('.added-item');
            itemContainer.remove();
            itemIdCounter--;
            updateItemCount();
        }

        function updateItemCount() {
            const addedItems = document.querySelectorAll('.added-item');
            console.log(addedItems);

            addedItems.forEach((item, index) => {
                const countGroup = item.querySelector('.count-group');
                countGroup.textContent = index + 1;
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

        // Event listener for modal shown event
        document.getElementById('edit-purchase-request-modal-' + {{ $pr->id }}).addEventListener(
            'shown.bs.modal',
            function() {
                document.querySelectorAll('.unit-price-input').forEach(function(input) {
                    const currency = input.closest('.added-item').querySelector('.form-select').value;
                    formatPrice(input, currency);
                });

                document.querySelectorAll('.subtotal-input').forEach(function(input) {
                    const currency = input.closest('.added-item').querySelector('.form-select').value;
                    formatPrice(input, currency);
                });
            });
    </script>
</div>
