@extends('layouts.app')

@section('content')

<style>
        /* Style for displaying added items */
        .added-item {
            margin-bottom: 10px;
        }

        #itemDropdown {
        max-height: 200px; /* Set maximum height for the dropdown */
        overflow-y: auto; /* Enable vertical scrolling */
        border: 1px solid #ccc; /* Optional: Add border for visual clarity */
        position: absolute; /* Position the dropdown absolutely */
        z-index: 999; /* Ensure dropdown is above other elements */
        background-color: #fff; /* Set background color to white */
        opacity: 1; /* Adjust opacity to ensure dropdown is not transparent */

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
        <div class="col-md-10">
            <div class="card">
                <div class="container p-5">
                    <span class="h2 ">Create Purchase Request</span>
                    <form action="{{route('purchaserequest.insert')}}" method="POST" class="row ">
                        @csrf

                        <div class="form-group mt-5">
                            <label class="form-label" for="to_department">To Department</label>
                            <select class="form-select" name="to_department" id="to_department" required>
                                <option value="" selected disabled>Select department..</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Purchasing">Purchasing</option>
                                <option value="Personnel">Personnel</option>
                                <option value="Computer">Computer</option>
                            </select>
                            <div class="form-text">Pilih departemen yang dituju. Eg. Computer</div>
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
                            <input class="form-control" type="date" id="date_of_pr" name="date_of_pr" required>
                        </div>

                        <div class="form-group mt-3 col-md-6">
                            <label class="form-label" for="date_of_required">Date of Required</label>
                            <input class="form-control" type="date" name="date_of_required" required>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label" for="supplier">Supplier</label>
                            <textarea class="form-control" name="supplier" rows="4" cols="50" required></textarea>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label" for="remark">Remark</label>
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
                    if(data.length > 0){
                        console.log(data);
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.classList.add('dropdown-item')
                            option.value = item.id;
                            option.textContent = item.name;
                            option.addEventListener('click', function(){
                                itemNameInput.value = item.name;
                                if (item.latest_price === null) {
                                    unitPriceInput.value = item.price;
                                } else {
                                    unitPriceInput.value = item.latest_price;
                                }
                                itemDropdown.innerHTML = '';
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

        document.addEventListener('click', function(event){
            if(!itemNameInput.contains(event.traget) && !itemDropdown.contains(event.target)){
                itemDropdown.style.display = 'none';
                console.log(itemNameInput.value);
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

        const formGroupUnitPriceInput = document.createElement('div')
        formGroupUnitPriceInput.classList.add('col-md-2');

        const unitPriceInput = document.createElement('input');
        unitPriceInput.classList.add('form-control');
        unitPriceInput.setAttribute('required', 'required');
        unitPriceInput.type = 'number';
        unitPriceInput.name = `items[${itemIdCounter}][price]`;
        unitPriceInput.placeholder = 'Unit Price';

        formGroupUnitPriceInput.appendChild(unitPriceInput);

        const formGroupPurposeInput = document.createElement('div')
        formGroupPurposeInput.classList.add('col-md-4');

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

    // Add event listener for DOMContentLoaded event
    document.addEventListener('DOMContentLoaded', function() {
            // Call addNewItem function when the DOM content is loaded
            addNewItem();
        });
</script>

@endsection
