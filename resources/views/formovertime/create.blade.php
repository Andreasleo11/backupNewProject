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

        /* Custom toggle switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>

    <div class="  px-2 py-5">
        <form action="{{ route('formovertime.insert') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row justify-content-center">
                <div class="col-md-11">
                    <div class="card">
                        <div class="h2 text-center fw-semibold mt-3">Create Form Overtime</div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label fs-5 fw-bold" for="from_department">From Department</label>
                                <select class="form-select" name="from_department" id="fromDepartmentDropdown" required>
                                    <option value="" selected disabled>Select from department..</option>
                                    @foreach ($departements as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="designFieldContainer"></div>

                            <div class="form-group mt-3">
                                <label class="form-label fs-5 fw-bold" for="date_form_overtime">Date of Form Overtime
                                    Create</label>
                                <input class="form-control" type="date" id="date_form_overtime" name="date_form_overtime"
                                    required>
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label fs-5 fw-bold">Input Method</label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="inputMethodToggle">
                                    <span class="slider round"></span>
                                </label>
                                <span id="inputMethodLabel" class="ms-2">Import from Excel</span>
                            </div>

                            <div class="form-group mt-3" id="fileUploadSection">
                                <label class="form-label fs-5 fw-bold" for="excel_file">Import Employees from
                                    Excel</label>
                                <input class="form-control" type="file" id="excel_file" name="excel_file"
                                    accept=".xlsx,.xls">
                            </div>


                            <div class="form-group mt-3" id="manualInputSection">
                                <div id="itemsContainer">
                                    <label class="form-label fs-5 fw-bold">List of Employee</label>
                                    <div id="items" class="border rounded-1 py-2 my-2 px-1 pe-2 mb-3"></div>
                                    <button class="btn btn-secondary btn-sm" type="button" onclick="addNewItem()">Add
                                        Employee</button>
                                </div>
                            </div>

                            <button class="btn btn-primary mt-3" type="submit">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    </body>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputMethodToggle = document.getElementById('inputMethodToggle');
            const fileUploadSection = document.getElementById('fileUploadSection');
            const manualInputSection = document.getElementById('manualInputSection');
            const inputMethodLabel = document.getElementById('inputMethodLabel');
            const manualInputFields = manualInputSection.querySelectorAll('input, select, textarea');

            fileUploadSection.style.display = 'none';
            document.getElementById('excel_file').disabled = true;
            // Add event listener to the toggle switch
            inputMethodToggle.addEventListener('change', function() {
                if (inputMethodToggle.checked) {
                    // If the toggle switch is checked (import from Excel), hide the manual input section
                    manualInputSection.style.display = 'none';
                    // Show the file upload section
                    fileUploadSection.style.display = 'block';

                    document.getElementById('excel_file').disabled = false;
                    // Disable manual input fields
                    manualInputFields.forEach(function(field) {
                        field.disabled = true;
                    });
                    // Update the input method label
                    inputMethodLabel.textContent = 'Import from Excel';
                } else {
                    // If the toggle switch is not checked (manual input), hide the file upload section
                    fileUploadSection.style.display = 'none';
                    // Show the manual input section
                    manualInputSection.style.display = 'block';
                    // Update the input method label
                    manualInputFields.forEach(function(field) {
                        field.disabled = false;
                    });
                    // Disable file upload field
                    document.getElementById('excel_file').disabled = true;
                    inputMethodLabel.textContent = 'Manual Input';
                }
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const departmentDropdown = document.getElementById('fromDepartmentDropdown');
            const designFieldContainer = document.getElementById('designFieldContainer');

            departmentDropdown.addEventListener('change', function() {
                // Clear the design field container
                designFieldContainer.innerHTML = '';

                // Check if the selected department is Moulding
                const selectedDepartment = departmentDropdown.options[departmentDropdown.selectedIndex]
                    .text;
                if (selectedDepartment === 'MOULDING') {
                    // Create the Design field
                    const designFormGroup = document.createElement('div');
                    designFormGroup.classList.add('form-group', 'mt-3', 'col');

                    const designLabel = document.createElement('label');
                    designLabel.classList.add('form-label', 'fs-5', 'fw-bold');
                    designLabel.setAttribute('for', 'design');
                    designLabel.textContent = 'Design';

                    const designSelect = document.createElement('select');
                    designSelect.classList.add('form-select');
                    designSelect.setAttribute('name', 'design');
                    designSelect.setAttribute('id', 'design');
                    designSelect.required = true;

                    const yesOption = document.createElement('option');
                    yesOption.value = '1';
                    yesOption.textContent = 'Yes';

                    const noOption = document.createElement('option');
                    noOption.value = '0';
                    noOption.textContent = 'No';

                    designSelect.appendChild(yesOption);
                    designSelect.appendChild(noOption);

                    designFormGroup.appendChild(designLabel);
                    designFormGroup.appendChild(designSelect);

                    // Append the Design field to the container
                    designFieldContainer.appendChild(designFormGroup);
                }
            });
        });

        // Counter for creating unique IDs for items
        let itemIdCounter = 0;
        let isFirstCall = true; // Flag to track the first call

        function addNewItem() {
            // Create a new item container
            const newItemContainer = document.createElement('div');
            newItemContainer.classList.add('added-item', 'row', 'gy-2', 'gx-2', 'align-items-center');

            if (isFirstCall) {
                // Define header labels and their corresponding column sizes
                const headerLabels = ['Count', 'NIK ', 'Name ', 'Job desc', 'Start Date', 'Start Time', 'End Date',
                    'End Time', 'Break (Minute)', 'Remarks',
                    'Action'
                ];
                const columnSizes = ['col-md-1', 'col-md-1', 'col-md-1', 'col-md-2', 'col-md-1', 'col-md-1',
                    'col-md-1',
                    'col-md-1', 'col-md-1', 'col-md-1', 'col-md-1'
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
            const formGroupName = document.createElement('div');
            formGroupName.classList.add('col-md-1');

            const itemNameInput = document.createElement('input');
            itemNameInput.classList.add('form-control');
            itemNameInput.setAttribute('required', 'required');
            itemNameInput.type = 'text';
            itemNameInput.name = `items[${itemIdCounter}][NIK]`;
            itemNameInput.placeholder = 'Item Name';

            const itemNameDropdown = document.createElement('div');
            itemNameDropdown.id = `itemDropdown`;
            itemNameDropdown.classList.add('dropdown-content');

            // Add event listener for keyup event
            itemNameInput.addEventListener('keyup', function() {
                const departmentDropdown = document.getElementById('fromDepartmentDropdown');
                const inputValue = itemNameInput.value.trim();
                if (departmentDropdown.value == "") {
                    alert('Please select the from department name first!');
                }

                if (inputValue.length > 0) {
                    // Fetch item names from server based on user input
                    fetch(`/get-employees?nik=${inputValue}&deptid=${departmentDropdown.value}`)
                        .then(response => response.json())
                        .then(data => {
                            // Clear previous dropdown options
                            itemNameDropdown.innerHTML = '';
                            // Populate dropdown with fetched item names
                            if (data.length > 0) {
                                data.forEach(pegawai => {
                                    const option = document.createElement('div');
                                    option.classList.add('dropdown-item');
                                    option.textContent = `${pegawai.NIK} - ${pegawai.nama}`;
                                    option.addEventListener('click', function() {
                                        itemNameInput.value = pegawai.NIK;
                                        namaInput.value = pegawai.nama;

                                        itemNameDropdown.innerHTML = '';
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
                } else {
                    itemNameDropdown.innerHTML = '';
                    itemNameDropdown.style.display = 'none';
                }
            });
            //ajax for dropdown item

            formGroupName.appendChild(itemNameInput);
            formGroupName.appendChild(itemNameDropdown);

            const formGroupNamaInput = document.createElement('div')
            formGroupNamaInput.classList.add('col-md-1');

            const namaInput = document.createElement('input');
            namaInput.classList.add('form-control');
            namaInput.setAttribute('required', 'required');
            namaInput.type = 'text';
            namaInput.name = `items[${itemIdCounter}][nama]`;
            namaInput.placeholder = 'Nama';

            formGroupNamaInput.appendChild(namaInput);

            // Add event listener for keyup event
            namaInput.addEventListener('keyup', function() {
                const departmentDropdown = document.getElementById('fromDepartmentDropdown');
                const inputValue = namaInput.value.trim();

                if (departmentDropdown.value == "") {
                    alert('Please select the from department name first!');
                }

                if (inputValue.length > 0) {
                    // Fetch item names from server based on user input
                    fetch(`/get-employees?name=${inputValue}&deptid=${departmentDropdown.value}`)
                        .then(response => response.json())
                        .then(data => {
                            // Clear previous dropdown options
                            itemNameDropdown.innerHTML = '';
                            // Populate dropdown with fetched item names
                            if (data.length > 0) {
                                data.forEach(pegawai => {
                                    const option = document.createElement('div');
                                    option.classList.add('dropdown-item');
                                    option.textContent = `${pegawai.NIK} - ${pegawai.nama}`;
                                    option.addEventListener('click', function() {
                                        itemNameInput.value = pegawai.NIK;
                                        namaInput.value = pegawai.nama;

                                        itemNameDropdown.innerHTML = '';
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
                } else {
                    itemNameDropdown.innerHTML = '';
                    itemNameDropdown.style.display = 'none';
                }
            });
            //ajax for dropdown item

            document.addEventListener('click', function(event) {
                if (!itemNameInput.contains(event.target) && !itemNameDropdown.contains(event.target)) {
                    itemNameDropdown.style.display = 'none';
                    // console.log(itemNameInput.value);
                }
            });

            const formGroupJobdescInput = document.createElement('div')
            formGroupJobdescInput.classList.add('col-md-2');

            const jobdescInput = document.createElement('input');
            jobdescInput.classList.add('form-control');
            jobdescInput.setAttribute('required', 'required');
            jobdescInput.type = 'text';
            jobdescInput.name = `items[${itemIdCounter}][jobdesc]`;
            jobdescInput.placeholder = 'jobdesc';

            formGroupJobdescInput.appendChild(jobdescInput);

            const formGroupStartDateInput = document.createElement('div')
            formGroupStartDateInput.classList.add('col-md-1');

            const startdateInput = document.createElement('input');
            startdateInput.classList.add('form-control');
            startdateInput.setAttribute('required', 'required');
            startdateInput.type = 'date';
            startdateInput.name = `items[${itemIdCounter}][startdate]`;

            formGroupStartDateInput.appendChild(startdateInput);

            const formGroupStartTimeInput = document.createElement('div')
            formGroupStartTimeInput.classList.add('col-md-1');

            const starttimeInput = document.createElement('input');
            starttimeInput.classList.add('form-control');
            starttimeInput.setAttribute('required', 'required');
            starttimeInput.type = 'time';
            starttimeInput.name = `items[${itemIdCounter}][starttime]`;
            starttimeInput.placeholder = 'Unit Price';

            formGroupStartTimeInput.appendChild(starttimeInput);

            const formGroupEndDateInput = document.createElement('div')
            formGroupEndDateInput.classList.add('col-md-1');

            const enddateInput = document.createElement('input');
            enddateInput.classList.add('form-control');
            enddateInput.setAttribute('required', 'required');
            enddateInput.type = 'date';
            enddateInput.name = `items[${itemIdCounter}][enddate]`;

            formGroupEndDateInput.appendChild(enddateInput);

            const formGroupEndTimeInput = document.createElement('div')
            formGroupEndTimeInput.classList.add('col-md-1');

            const endtimeInput = document.createElement('input');
            endtimeInput.classList.add('form-control');
            endtimeInput.setAttribute('required', 'required');
            endtimeInput.type = 'time';
            endtimeInput.name = `items[${itemIdCounter}][endtime]`;
            endtimeInput.placeholder = 'Unit Price';

            formGroupEndTimeInput.appendChild(endtimeInput);


            const formGroupBreakInput = document.createElement('div')
            formGroupBreakInput.classList.add('col-md-1');

            const breakInput = document.createElement('input');
            breakInput.classList.add('form-control');
            breakInput.setAttribute('required', 'required');
            breakInput.type = 'text';
            breakInput.name = `items[${itemIdCounter}][break]`;
            breakInput.placeholder = '45';

            formGroupBreakInput.appendChild(breakInput);


            const formGroupRemarkInput = document.createElement('div')
            formGroupRemarkInput.classList.add('col-md-1');

            const remarkInput = document.createElement('input');
            remarkInput.classList.add('form-control');
            remarkInput.setAttribute('required', 'required');
            remarkInput.type = 'text';
            remarkInput.name = `items[${itemIdCounter}][remark]`;
            remarkInput.placeholder = 'Keterangan';

            formGroupRemarkInput.appendChild(remarkInput);


            const actionGroup = document.createElement('div');
            actionGroup.classList.add('col-md-1');

            const removeButton = document.createElement('a');
            removeButton.classList.add('btn', 'btn-danger', 'btn-sm');
            removeButton.textContent = "Remove";
            removeButton.addEventListener('click', removeItem);

            actionGroup.appendChild(removeButton);

            // Append input fields to the item container
            newItemContainer.appendChild(countGroup);
            newItemContainer.appendChild(formGroupName);
            newItemContainer.appendChild(formGroupNamaInput);
            newItemContainer.appendChild(formGroupJobdescInput);
            newItemContainer.appendChild(formGroupStartDateInput);
            newItemContainer.appendChild(formGroupStartTimeInput);
            newItemContainer.appendChild(formGroupEndDateInput);
            newItemContainer.appendChild(formGroupEndTimeInput);
            newItemContainer.appendChild(formGroupBreakInput);
            newItemContainer.appendChild(formGroupRemarkInput);
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
            console.log(addedItems);

            // Loop through each added item and update the count
            addedItems.forEach((item, index) => {
                // Find the countGroup element in the current added item
                const countGroup = item.querySelector('.count-group');
                console.log(countGroup);

                // Update the text content of the countGroup element
                countGroup.textContent = index + 1; // Add 1 because item ID starts from 0
            });
        }


        addNewItem();
    </script>
@endsection
