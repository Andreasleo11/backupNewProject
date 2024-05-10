@extends('layouts.app')

@section('content')
    <style>
        /* Style for displaying added items */
        .added-item {
            margin-bottom: 10px;
        }

        #itemDropdown {
            max-height: 200px;
            /* Set maximum height for the dropdown */
            overflow-y: auto;
            /* Enable vertical scrolling */
            border: 1px solid #ccc;
            /* Optional: Add border for visual clarity */
            position: absolute;
            /* Position the dropdown absolutely */
            z-index: 999;
            /* Ensure dropdown is above other elements */
            background-color: #fff;
            /* Set background color to white */
            opacity: 1;
            /* Adjust opacity to ensure dropdown is not transparent */

        }

        .dropdown-item {
            padding: 5px;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: #f0f0f0;
        }
    </style>



    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card">
                    <div class="container p-5">
                        <div class="h2 text-center fw-semibold">Create Purchase Request</div>
                        <form action="{{ route('purchaserequest.insert') }}" method="POST" class="row ">
                            @csrf

                            <div class="form-group mt-5 col-md-4">
                                <label class="form-label fs-5 fw-bold" for="from_department">From Department</label>
                                <select class="form-select" name="from_department" id="fromDepartmentDropdown" required>
                                    <option value="" selected disabled>Select from department..</option>
                                    @foreach ($departments as $department)
                                        @if ($department->id === Auth::user()->department->id)
                                            <option value="{{ $department->name }}" selected>{{ $department->name }}
                                            </option>
                                        @elseif ($department->name === 'HRD' || $department->name === 'DIRECTOR')
                                        @else
                                            <option value="{{ $department->name }}">{{ $department->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih departmen tujuan (HANYA JIKA DIPERLUKAN)</div>
                            </div>

                            <div class="form-group mt-5 col-md-4">
                                <label class="form-label fs-5 fw-bold" for="to_department">To Department</label>
                                <select class="form-select" name="to_department" id="toDepartmentDropdown" required
                                    onchange="updateTypeDropdown()">
                                    <option value="" selected disabled>Select to department..</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Purchasing">Purchasing</option>
                                    <option value="Personnel">Personnel</option>
                                    <option value="Computer">Computer</option>
                                </select>
                                <div class="form-text">Pilih departemen yang dituju. Eg. Computer</div>
                            </div>

                            <div class="form-group mt-5 col-md-4">
                                <label class="form-label fs-5 fw-bold" for="type">Type</label>
                                <select class="form-select" name="type" id="typeDropdown" required>
                                    <option value="" selected disabled>Select Type..</option>
                                </select>
                                <div class="form-text">Pilih Tipe dari PR</div>
                            </div>

                            <div class="form-group mt-3">
                                <div id="itemsContainer">
                                    <label class="form-label fs-5 fw-bold">List of Items</label>
                                    <div id="items" class="border rounded-1 py-2 my-2 px-1 pe-2 mb-3"></div>
                                    <button class="btn btn-secondary btn-sm" type="button" onclick="addNewItem()">Add
                                        Item</button>
                                </div>
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label fs-5 fw-bold" for="date_of_pr">Date of PR</label>
                                <input class="form-control" type="date" id="date_of_pr" name="date_of_pr" required>
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label fs-5 fw-bold" for="date_of_required">Date of Required</label>
                                <input class="form-control" type="date" name="date_of_required" required>
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label fs-5 fw-bold col-sm-2" for="supplier">Supplier</label>
                                <input class="form-control" type="text" name="supplier" required>
                            </div>

                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label fs-5 fw-bold col-sm-2" for="pic">PIC</label>
                                <input class="form-control" type="text" name="pic" required>
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label fs-5 fw-bold" for="remark">Remark</label>
                                <textarea class="form-control" name="remark" rows="4" cols="50" required></textarea>
                            </div>

                            <button class="btn btn-primary mt-3" type="submit">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>



    <script>
        function updateTypeDropdown() {
            // Get Selected value of the type dropdown
            var selectedValue = document.getElementById('toDepartmentDropdown').value;

            var typeDropdown = document.getElementById('typeDropdown');

            typeDropdown.innerHTML = "";

            if (selectedValue === "Maintenance") {
                typeDropdown.innerHTML += "<option value=\"factory\">Factory</option>";
            } else if (selectedValue === "Purchasing") {
                typeDropdown.innerHTML += "<option value=\"factory\">Factory</option>";
            } else if (selectedValue === "Personnel") {
                typeDropdown.innerHTML += "<option value=\"factory\">Factory</option>";
                typeDropdown.innerHTML += "<option value=\"office\">Office</option>";
            } else {
                typeDropdown.innerHTML += "<option value=\"factory\">Factory</option>";
                typeDropdown.innerHTML += "<option value=\"office\">Office</option>";
            }
        }

        // Counter for creating unique IDs for items
        let itemIdCounter = 0;
        let isFirstCall = true; // Flag to track the first call

        function addNewItem() {
            // Create a new item container
            const newItemContainer = document.createElement('div');
            newItemContainer.classList.add('added-item', 'row', 'gy-2', 'gx-2', 'align-items-center');

            if (isFirstCall) {
                // Define header labels and their corresponding column sizes
                const headerLabels = ['Count', 'Item Name', 'Qty', 'UoM', 'Unit Price', 'Subtotal', 'Purpose',
                    'Action'
                ];
                const columnSizes = ['col-md-1', 'col-md-2', 'col-md-1', 'col-md-1', 'col-md-2', 'col-md-2', 'col-md-2',
                    'col-md-1'
                ];

                // Create header row and add header labels with specified column sizes
                const headerRow = document.createElement('div');
                headerRow.classList.add('row', 'gy-2', 'gx-2', 'align-items-center', 'header-row');

                headerLabels.forEach((label, index) => {
                    const headerLabel = document.createElement('div');
                    headerLabel.classList.add(columnSizes[index], 'text-center', 'header-label', 'fw-semibold');
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
            itemNameInput.placeholder = 'Item Name';

            const itemNameDropdown = document.createElement('div');
            itemNameDropdown.id = 'itemDropdown';
            itemNameDropdown.classList.add('dropdown-content');

            //ajax for dropdown item
            itemNameInput.addEventListener('keyup', function() {
                const inputValue = itemNameInput.value.trim();
                // Fetch item names from server based on user input
                fetch(`/get-item-names?itemName=${inputValue}`)
                    .then(response => response.json())
                    .then(data => {
                        // Clear previous dropdown options
                        itemNameDropdown.innerHTML = '';

                        // Populate dropdown with fetched item names
                        if (data.length > 0) {
                            // console.log(data);
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.classList.add('dropdown-item')
                                option.value = item.id;
                                option.textContent = item.name;
                                option.addEventListener('click', function() {
                                    itemNameInput.value = item.name;
                                    if (item.latest_price === null) {
                                        unitPriceInput.value = item.price;
                                    } else {
                                        unitPriceInput.value = item.latest_price;
                                    }
                                    formatPrice(unitPriceInput);
                                    itemDropdown.innerHTML = '';
                                    itemNameDropdown.style.display = 'none';
                                });
                                itemNameDropdown.appendChild(option);
                            });
                            itemNameDropdown.style.display = 'block';
                        } else {
                            itemNameDropdown.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
            //ajax for dropdown item

            document.addEventListener('click', function(event) {
                if (!itemNameInput.contains(event.target) && !itemDropdown.contains(event.target)) {
                    itemDropdown.style.display = 'none';
                    // console.log(itemNameInput.value);
                }
            });

            formGroupName.appendChild(itemNameInput);
            formGroupName.appendChild(itemNameDropdown);

            const formGroupQuantityInput = document.createElement('div')
            formGroupQuantityInput.classList.add('col-md-1');

            const quantityInput = document.createElement('input');
            quantityInput.classList.add('form-control');
            quantityInput.setAttribute('required', 'required');
            quantityInput.type = 'number';
            quantityInput.name = `items[${itemIdCounter}][quantity]`;
            quantityInput.placeholder = 'Qty';

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

            formGroupUomInput.appendChild(uomInput);

            const formGroupUnitPriceInput = document.createElement('div')
            formGroupUnitPriceInput.classList.add('col-md-2');

            const unitPriceInput = document.createElement('input');
            unitPriceInput.classList.add('form-control');
            unitPriceInput.setAttribute('required', 'required');
            unitPriceInput.type = 'text';
            unitPriceInput.name = `items[${itemIdCounter}][price]`;
            unitPriceInput.placeholder = 'Unit Price';

            formGroupUnitPriceInput.appendChild(unitPriceInput);

            const formGroupSubtotalInput = document.createElement('div');
            formGroupSubtotalInput.classList.add('col-md-2');

            const subtotalInput = document.createElement('input');
            subtotalInput.classList.add('form-control');
            subtotalInput.type = 'text';
            subtotalInput.disabled = true;
            subtotalInput.id = `subtotal-${itemIdCounter}`;
            subtotalInput.value = 0;

            formGroupSubtotalInput.appendChild(subtotalInput);

            const formGroupPurposeInput = document.createElement('div')
            formGroupPurposeInput.classList.add('col-md-2');

            const purposeInput = document.createElement('input');
            purposeInput.classList.add('form-control');
            purposeInput.setAttribute('required', 'required');
            purposeInput.type = 'text';
            purposeInput.name = `items[${itemIdCounter}][purpose]`;
            purposeInput.placeholder = 'Purpose';

            formGroupPurposeInput.appendChild(purposeInput);

            const actionGroup = document.createElement('div');
            actionGroup.classList.add('col-md-1');

            const removeButton = document.createElement('a');
            removeButton.classList.add('btn', 'btn-danger', 'btn-sm');
            removeButton.textContent = "remove";
            removeButton.addEventListener('click', removeItem);

            actionGroup.appendChild(removeButton);

            // Append input fields to the item container
            newItemContainer.appendChild(countGroup);
            newItemContainer.appendChild(formGroupName);
            newItemContainer.appendChild(formGroupQuantityInput);
            newItemContainer.appendChild(formGroupUomInput);
            newItemContainer.appendChild(formGroupUnitPriceInput);
            newItemContainer.appendChild(formGroupSubtotalInput);
            newItemContainer.appendChild(formGroupPurposeInput);
            newItemContainer.appendChild(actionGroup);

            // Append the new item container to the items container
            document.getElementById('items').appendChild(newItemContainer);

            quantityInput.addEventListener('input', function() {
                const unitPrice = unitPriceInput.value.replace(/[^\d]/g, '');
                subtotalInput.value = parseFloat(quantityInput.value) * unitPrice;
                formatPrice(subtotalInput);
            });
            unitPriceInput.addEventListener('input', function() {
                const unitPrice = unitPriceInput.value.replace(/[^\d]/g, '');
                subtotalInput.value = parseFloat(quantityInput.value) * unitPrice;
                formatPrice(unitPriceInput);
                formatPrice(subtotalInput);
            });

            // Increment the item ID counter
            itemIdCounter++;

            updateItemCount();
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
            console.log(addedItems);

            // Loop through each added item and update the count
            addedItems.forEach((item, index) => {
                // Find the countGroup element in the current added item
                const countGroup = item.querySelector('.count-group');
                console.log(countGroup);

                // Update the text content of the countGroup element
                countGroup.textContent = index + 1; // Add 1 because item ID starts from 0

                const subtotalInput = item.querySelector('.subtotal-input');
                const unitPriceInput = item.querySelector('.unit-price-input');
                const quantityInput = item.querySelector('.quantity-input');
                formatPrice(subtotalInput);
                formatPrice(unitPriceInput);

                unitPriceInput.addEventListener('input', function(event) {
                    let price = event.target.value.replace(/\D/g, ''); // Remove non-digit characters
                    price = parseInt(price); // Convert string to integer
                    if (!isNaN(price)) {
                        // Format the price with thousand separators and add currency symbol
                        const formattedPrice = 'Rp. ' + price.toLocaleString('id-ID');
                        event.target.value = formattedPrice;
                    } else {
                        event.target.value = ''; // Clear the input if it's not a valid number
                    }
                });
            });
        }

        addNewItem();

        function formatPrice(input) {
            let price = input.value.replace(/\D/g, ''); // Remove non-digit characters
            price = parseInt(price); // Convert string to integer
            if (!isNaN(price)) {
                // Format the price with thousand separators and add currency symbol
                const formattedPrice = 'Rp. ' + price.toLocaleString('id-ID');
                input.value = formattedPrice;
            } else {
                input.value = ''; // Clear the input if it's not a valid number
            }
        }

        // Add event listener for DOMContentLoaded event
        document.addEventListener('DOMContentLoaded', function() {
            // Call addNewItem function when the DOM content is loaded

        });
    </script>
@endsection
