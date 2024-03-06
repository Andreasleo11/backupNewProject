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
                    <span class="h2 ">Generate Employee Training Report</span>
                    <form action="{{route('training.post')}}" method="POST" class="row ">
                        @csrf

                        <div class="form-group mt-3">
                            <label class="form-label" for="name">Nama : </label>
                            <textarea class="form-control" name="name" rows="1" cols="0"></textarea>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label" for="nik">Nik : </label>
                            <textarea class="form-control" name="nik" rows="1" cols="0"></textarea>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label" for="department">Department : </label>
                            <textarea class="form-control" name="department" rows="1" cols="0"></textarea>
                        </div>

                        
                        <div class="form-group mt-3 col-md-6">
                            <label class="form-label" for="mulai_bekerja">Mulai Bekerja : </label>
                            <input class="form-control" type="date" id="mulai_bekerja" name="mulai_bekerja">
                        </div>

                        <div class="form-group mt-3">
                            <div id="itemsContainer">
                                <label class="form-label">List of Training</label>
                                <div id="trainings"></div>
                                <button class="btn btn-secondary btn-sm" type="button" onclick="addNewItem()">Add Training</button>
                            </div>
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
        itemNameInput.name = `trainings[${itemIdCounter}][training_name]`;
        itemNameInput.placeholder = 'Training Name';


        formGroupName.appendChild(itemNameInput);

        const formGroupQuantityInput = document.createElement('div')
        formGroupQuantityInput.classList.add('col-md-2');

        const quantityInput = document.createElement('input');
        quantityInput.classList.add('form-control')
        quantityInput.type = 'date';
        quantityInput.name = `trainings[${itemIdCounter}][training_date]`;
        quantityInput.placeholder = 'Training Date';

      
        formGroupQuantityInput.appendChild(quantityInput);

        /////////
        const formGroupUnitPriceInput = document.createElement('div')
        formGroupUnitPriceInput.classList.add('col-md-4');

        const internalCheckbox = document.createElement('input');
        internalCheckbox.classList.add('form-check-input');
        internalCheckbox.type = 'checkbox';
        internalCheckbox.name = `trainings[${itemIdCounter}][types][]`;
        internalCheckbox.value = 'internal'; // Value for internal option

        // Create label for the internal checkbox
        const internalLabel = document.createElement('label');
        internalLabel.classList.add('form-check-label');
        internalLabel.textContent = 'Internal';

        // Create the second checkbox for external
        const externalCheckbox = document.createElement('input');
        externalCheckbox.classList.add('form-check-input');
        externalCheckbox.type = 'checkbox';
        externalCheckbox.name = `trainings[${itemIdCounter}][types][]`;
        externalCheckbox.value = 'external'; // Value for external option

        // Create label for the external checkbox
        const externalLabel = document.createElement('label');
        externalLabel.classList.add('form-check-label');
        externalLabel.textContent = 'External';

        // Append the internal checkbox and label to the formGroupUnitPriceInput
        formGroupUnitPriceInput.appendChild(internalCheckbox);
        formGroupUnitPriceInput.appendChild(internalLabel);

        // Append the external checkbox and label to the formGroupUnitPriceInput
        formGroupUnitPriceInput.appendChild(externalCheckbox);
        formGroupUnitPriceInput.appendChild(externalLabel);
        //////////////////////

       // Create a div for the form group
        const formHasilPelatihanInput = document.createElement('div');
        formHasilPelatihanInput.classList.add('col-md-4');

        // Create the dropdown menu
        const hasilPelatihanSelect = document.createElement('select');
        hasilPelatihanSelect.classList.add('form-control');
        hasilPelatihanSelect.name = `trainings[${itemIdCounter}][hasil_pelatihan]`;

        // Create options for the dropdown
        const efektifOption = document.createElement('option');
        efektifOption.value = 'efektif';
        efektifOption.textContent = 'Efektif';
        const tidakEfektifOption = document.createElement('option');
        tidakEfektifOption.value = 'tidak_efektif';
        tidakEfektifOption.textContent = 'Tidak Efektif';

        // Append options to the dropdown
        hasilPelatihanSelect.appendChild(efektifOption);
        hasilPelatihanSelect.appendChild(tidakEfektifOption);

        // Create the text input for the purpose field
        const purposeInput = document.createElement('input');
        purposeInput.classList.add('form-control');
        purposeInput.type = 'text';
        purposeInput.name = `trainings[${itemIdCounter}][purpose]`;
        purposeInput.placeholder = 'Purpose';

        // Append dropdown and text input to the form group div
        formHasilPelatihanInput.appendChild(hasilPelatihanSelect);
        
        // Create a div for the form group
        const formKeteranganInput = document.createElement('div');
        formKeteranganInput.classList.add('col-md-4');

        // Create the text input for the keterangan field
        const keteranganInput = document.createElement('input');
        keteranganInput.classList.add('form-control');
        keteranganInput.type = 'text';
        keteranganInput.name = `trainings[${itemIdCounter}][keterangan]`;
        keteranganInput.placeholder = 'Keterangan';

        // Append the text input to the form group div
        formKeteranganInput.appendChild(keteranganInput);

        ///////////////////////////////////////////////////////////////////////////////

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
        newItemContainer.appendChild(formHasilPelatihanInput);
        newItemContainer.appendChild(formKeteranganInput);
        newItemContainer.appendChild(actionGroup);

        // Append the new item container to the items container
        document.getElementById('trainings').appendChild(newItemContainer);

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
