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
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h2>Create Purchase Request</h2></div>
                    <form action="{{route('purchaserequest.insert')}}" method="POST">
                        @csrf

                        <!-- First: Department Selection -->
                        <label for="to_department">Select Department:</label>
                        <select name="to_department" id="to_department">
                            <option value="maintenance">Maintenance</option>
                            <option value="purchasing">Purchasing</option>
                            <option value="personnel">Personnel</option>
                            <option value="computer">Computer</option>
                        </select>
                        <br>

                        <!-- Second: List of Items (provide details on how you want to implement this) -->
                        <div id="itemsContainer">
                            <label>List of Items:</label>
                            <div id="items">
                                <!-- Initially, no items are added -->
                            </div>
                            <button type="button" onclick="addNewItem()">Add Item</button>
                        </div>

                        <br>


                        <div class="form-group">
                            <label for="date_of_pr">Date of PR</label>
                            <input type="date" id="date_of_pr" name="date_of_pr">
                        </div>

                          <!-- Fourth: Date of Required -->
                          <label for="date_of_required">Date of Required:</label>
                            <input type="date" name="date_of_required" required>
                            <br>


                        <!-- Last: Remark -->
                        <label for="supplier">Supplier:</label>
                        <textarea name="supplier" rows="4" cols="50"></textarea>
                        <br>

                        <!-- Last: Remark -->
                        <label for="remark">Remark:</label>
                        <textarea name="remark" rows="4" cols="50"></textarea>
                        <br>

                        <input type="submit" value="Submit">
                    </form>
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
        newItemContainer.classList.add('added-item');

        // Create input fields for item details
        const itemNameInput = document.createElement('input');
        itemNameInput.type = 'text';
        itemNameInput.name = `items[${itemIdCounter}][item_name]`;
        itemNameInput.placeholder = 'Item Name';

        const quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.name = `items[${itemIdCounter}][quantity]`;
        quantityInput.placeholder = 'Quantity';

        const purposeInput = document.createElement('input');
        purposeInput.type = 'text';
        purposeInput.name = `items[${itemIdCounter}][purpose]`;
        purposeInput.placeholder = 'Purpose';

        const unitPriceInput = document.createElement('input');
        unitPriceInput.type = 'number';
        unitPriceInput.name = `items[${itemIdCounter}][unit_price]`;
        unitPriceInput.placeholder = 'Unit Price';

        // Append input fields to the item container
        newItemContainer.appendChild(itemNameInput);
        newItemContainer.appendChild(quantityInput);
        newItemContainer.appendChild(purposeInput);
        newItemContainer.appendChild(unitPriceInput);

        // Append the new item container to the items container
        document.getElementById('items').appendChild(newItemContainer);

        // Increment the item ID counter
        itemIdCounter++;
    }
</script>

@endsection