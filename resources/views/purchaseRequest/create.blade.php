@extends('layouts.app')

@section('content')

<style>
        /* Style for displaying added items */
        .added-item {
            margin-bottom: 10px;
        }
    </style>



<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="container p-5">
                    <span class="h2 ">Create Purchase Request</span>
                    <form action="{{route('purchaserequest.insert')}}" method="POST" class="row ">
                        @csrf

                        <div class="form-group mt-5">
                            <label class="form-label" for="to_department">To Department</label>
                            <select class="form-select" name="to_department" id="to_department">
                                <option value="Maintenance">Maintenance</option>
                                <option value="Purchasing">Purchasing</option>
                                <option value="Personnel">Personnel</option>
                                <option value="Computer">Computer</option>
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <div id="itemsContainer">
                                <label class="form-label">List of Items</label>
                                <div id="items"></div>
                                <button class="btn btn-secondary btn-sm" type="button" onclick="addNewItem()">Add Item</button>
                            </div>
                        </div>

                        <div class="form-group mt-3 col-md-6">
                            <label class="form-label" for="date_of_pr">Date of PR</label>
                            <input class="form-control" type="date" id="date_of_pr" name="date_of_pr">
                        </div>

                        <div class="form-group mt-3 col-md-6">
                            <label class="form-label" for="date_of_required">Date of Required</label>
                            <input class="form-control" type="date" name="date_of_required" required>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label" for="supplier">Supplier</label>
                            <textarea class="form-control" name="supplier" rows="4" cols="50"></textarea>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label" for="remark">Remark</label>
                            <textarea class="form-control" name="remark" rows="4" cols="50"></textarea>
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
    // Counter for creating unique IDs for items
    let itemIdCounter = 0;

    function addNewItem() {
        // Create a new item container
        const newItemContainer = document.createElement('div');
        newItemContainer.classList.add('added-item', 'row', 'gy-2', 'gx-2', 'align-items-center');

        const countGroup = document.createElement('div')
        countGroup.classList.add('count-group', 'col-md-1' ,'text-center');
        countGroup.textContent = itemIdCounter + 1;

        // Create input fields for item details
        const formGroupName = document.createElement('div')
        formGroupName.classList.add('col-md-3');

        const itemNameInput = document.createElement('input');
        itemNameInput.classList.add('form-control')
        itemNameInput.type = 'text';
        itemNameInput.name = `items[${itemIdCounter}][item_name]`;
        itemNameInput.placeholder = 'Item Name';

        formGroupName.appendChild(itemNameInput);

        const formGroupQuantityInput = document.createElement('div')
        formGroupQuantityInput.classList.add('col-md-1');

        const quantityInput = document.createElement('input');
        quantityInput.classList.add('form-control')
        quantityInput.type = 'number';
        quantityInput.name = `items[${itemIdCounter}][quantity]`;
        quantityInput.placeholder = 'Qty';

        formGroupQuantityInput.appendChild(quantityInput);

        const formGroupUnitPriceInput = document.createElement('div')
        formGroupUnitPriceInput.classList.add('col-md-2');

        const unitPriceInput = document.createElement('input');
        unitPriceInput.classList.add('form-control')
        unitPriceInput.type = 'number';
        unitPriceInput.name = `items[${itemIdCounter}][unit_price]`;
        unitPriceInput.placeholder = 'Unit Price';

        formGroupUnitPriceInput.appendChild(unitPriceInput);

        const formGroupPurposeInput = document.createElement('div')
        formGroupPurposeInput.classList.add('col-md-4');

        const purposeInput = document.createElement('input');
        purposeInput.classList.add('form-control')
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
        newItemContainer.appendChild(formGroupUnitPriceInput);
        newItemContainer.appendChild(formGroupPurposeInput);
        newItemContainer.appendChild(actionGroup);

        // Append the new item container to the items container
        document.getElementById('items').appendChild(newItemContainer);

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

        // Loop through each added item and update the count
        addedItems.forEach((item, index) => {
            // Find the countGroup element in the current added item
            const countGroup = item.querySelector('.count-group');

            // Update the text content of the countGroup element
            countGroup.textContent = index + 1; // Add 1 because item ID starts from 0
        });
    }
</script>

@endsection
