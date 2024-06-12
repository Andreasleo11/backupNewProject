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


<div class="modal fade" id="edit-form-overtime-modal-{{ $header->id }}" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-body">
                <div class="text-end">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="  px-2 py-5">
                    <div class="h2 text-center fw-semibold">Edit Form Overtime</div>
                    <form action="{{ route('formovertime.update', $header->id) }}" method="POST" class="row "
                        id="form-overtime-edit">
                        @method('PUT')
                        @csrf

                        <div class="form-group mt-3 col">
                            <label class="form-label fs-5 fw-bold" for="from_department">From Department</label>
                            <select class="form-select" name="from_department" id="fromDepartmentDropdown" required>
                                <option value="" selected>Select from department..</option>
                                @foreach ($departements as $department)
                                    @if ($department->id === $header->dept_id)
                                        <option value="{{ $department->id }}" selected>{{ $department->name }}
                                        </option>
                                    @else
                                        <option value="{{ $department->id }}">{{ $department->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>




                        <div id="designFieldContainer"></div>

                        <div class="form-group mt-3 col-md-6">
                            <label class="form-label fs-5 fw-bold" for="date_form_overtime">Date of Form Overtime
                                Create</label>
                            <input class="form-control" type="date" id="date_form_overtime" name="date_form_overtime"
                                required value="{{ $header->create_date }}">
                        </div>


                        <div class="form-group mt-3">
                            <div id="itemsContainer">
                                <label class="form-label fs-5 fw-bold">List of Items</label>
                                <div id="items" class="border rounded-1 py-2 my-2 px-1 pe-2 mb-3"></div>
                                <button class="btn btn-secondary btn-sm" type="button" onclick="addNewItem()">Add
                                    Employee</button>
                            </div>
                        </div>


                    </form>
                </div>
                </body>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary"
                    onclick="document.getElementById('form-overtime-edit').submit()">Save changes</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const departmentDropdown = document.getElementById('fromDepartmentDropdown');
            const designFieldContainer = document.getElementById('designFieldContainer');


            let header = {!! json_encode($header) !!};
            let datas = {!! json_encode($datas) !!};

            console.log(header.is_design);

            const selectedDepartment = departmentDropdown.options[departmentDropdown.selectedIndex].text;
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
                designSelect.value = header?.is_design ?? "";
                designSelect.required = true;

                const yesOption = document.createElement('option');
                yesOption.value = '1';
                yesOption.textContent = 'Yes';

                const noOption = document.createElement('option');
                noOption.value = '0';
                noOption.textContent = 'No';


                // Set the selected value based on header.is_design
                if (header?.is_design !== undefined) {
                    designSelect.value = header.is_design;

                    if (header.is_design === 1) {
                        yesOption.selected = true;
                    } else if (header.is_design === 0) {
                        noOption.selected = true;
                    }
                }

                designSelect.appendChild(yesOption);
                designSelect.appendChild(noOption);

                designFormGroup.appendChild(designLabel);
                designFormGroup.appendChild(designSelect);

                // Append the Design field to the container
                designFieldContainer.appendChild(designFormGroup);
            }

            departmentDropdown.addEventListener('change', function() {
                // Clear the design field container
                designFieldContainer.innerHTML = '';



                // Check if the selected department is Moulding
                const selectedDepartment = departmentDropdown.options[departmentDropdown.selectedIndex]
                    .text;
                console.log("test : ", selectedDepartment);
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


                    designSelect.value = header?.is_design ?? "";

                    if (header?.is_design !== undefined) {
                        designSelect.value = header.is_design;

                        if (header.is_design === 1) {
                            yesOption.selected = true;
                        } else if (header.is_design === 0) {
                            noOption.selected = true;
                        }
                    }
                    designSelect.appendChild(yesOption);
                    designSelect.appendChild(noOption);

                    designFormGroup.appendChild(designLabel);
                    designFormGroup.appendChild(designSelect);

                    // Append the Design field to the container
                    designFieldContainer.appendChild(designFormGroup);
                }
            });
        });

        let header = {!! json_encode($header) !!};
        let datas = {!! json_encode($datas) !!};


        console.log("ini data :", datas);

        // Counter for creating unique IDs for items
        let itemIdCounter = 0;
        let isFirstCall = true; // Flag to track the first call

        datas.forEach(datas => {
            addNewItem(datas)
        });

        function addNewItem($datas = null) {
            // Create a new item container
            const newItemContainer = document.createElement('div');
            newItemContainer.classList.add('added-item', 'row', 'gy-2', 'gx-2', 'align-items-center');

            if (isFirstCall) {
                // Define header labels and their corresponding column sizes
                const headerLabels = ['Count', 'NIK ', 'Name ', 'Job desc','Start Date', 'Start Time', 'End Date',
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
            itemNameInput.value = $datas?.NIK ?? "";

            itemNameInput.name = `items[${itemIdCounter}][NIK]`;

            itemNameInput.placeholder = 'Item Name';

            const itemNameDropdown = document.createElement('div');
            itemNameDropdown.id = `itemDropdown`;
            itemNameDropdown.classList.add('dropdown-content');

            // Add event listener for keyup event
            itemNameInput.addEventListener('keyup', function() {
                const departmentDropdown = document.getElementById('fromDepartmentDropdown');
                const inputValue = itemNameInput.value.trim();

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
            namaInput.value = $datas?.nama ?? "";
            namaInput.placeholder = 'Nama';

            formGroupNamaInput.appendChild(namaInput);

            // Add event listener for keyup event
            namaInput.addEventListener('keyup', function() {

                const departmentDropdown = document.getElementById('fromDepartmentDropdown');
                const inputValue = namaInput.value.trim();

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
            jobdescInput.value = $datas?.job_desc ?? "";
            jobdescInput.placeholder = 'jobdesc';

            formGroupJobdescInput.appendChild(jobdescInput);

            const formGroupStartDateInput = document.createElement('div')
            formGroupStartDateInput.classList.add('col-md-1');

            const startdateInput = document.createElement('input');
            startdateInput.classList.add('form-control');
            startdateInput.setAttribute('required', 'required');
            startdateInput.type = 'date';
            startdateInput.name = `items[${itemIdCounter}][startdate]`;
            startdateInput.value = $datas?.start_date ?? "";

            formGroupStartDateInput.appendChild(startdateInput);

            const formGroupStartTimeInput = document.createElement('div')
            formGroupStartTimeInput.classList.add('col-md-1');

            const starttimeInput = document.createElement('input');
            starttimeInput.classList.add('form-control');
            starttimeInput.setAttribute('required', 'required');
            starttimeInput.type = 'time';
            starttimeInput.name = `items[${itemIdCounter}][starttime]`;
            starttimeInput.placeholder = 'Unit Price';
            starttimeInput.value = $datas?.start_time ?? "";

            formGroupStartTimeInput.appendChild(starttimeInput);

            const formGroupEndDateInput = document.createElement('div')
            formGroupEndDateInput.classList.add('col-md-1');

            const enddateInput = document.createElement('input');
            enddateInput.classList.add('form-control');
            enddateInput.setAttribute('required', 'required');
            enddateInput.type = 'date';
            enddateInput.name = `items[${itemIdCounter}][enddate]`;
            enddateInput.value = $datas?.end_date ?? "";

            formGroupEndDateInput.appendChild(enddateInput);

            const formGroupEndTimeInput = document.createElement('div')
            formGroupEndTimeInput.classList.add('col-md-1');

            const endtimeInput = document.createElement('input');
            endtimeInput.classList.add('form-control');
            endtimeInput.setAttribute('required', 'required');
            endtimeInput.type = 'time';
            endtimeInput.name = `items[${itemIdCounter}][endtime]`;
            endtimeInput.placeholder = 'Unit Price';
            endtimeInput.value = $datas?.end_time ?? "";

            formGroupEndTimeInput.appendChild(endtimeInput);


            const formGroupBreakInput = document.createElement('div')
            formGroupBreakInput.classList.add('col-md-1');

            const breakInput = document.createElement('input');
            breakInput.classList.add('form-control');
            breakInput.setAttribute('required', 'required');
            breakInput.type = 'text';
            breakInput.name = `items[${itemIdCounter}][break]`;
            breakInput.placeholder = '45';
            breakInput.value = $datas?.break ?? "";

            formGroupBreakInput.appendChild(breakInput);


            const formGroupRemarkInput = document.createElement('div')
            formGroupRemarkInput.classList.add('col-md-1');

            const remarkInput = document.createElement('input');
            remarkInput.classList.add('form-control');
            remarkInput.setAttribute('required', 'required');
            remarkInput.type = 'text';
            remarkInput.name = `items[${itemIdCounter}][remark]`;
            remarkInput.value = $datas?.remark ?? "";
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
    </script>

</div>
