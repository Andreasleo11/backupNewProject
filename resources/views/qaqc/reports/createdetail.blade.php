@extends('layouts.app')

@section('content')

<style>
    .dropdown-content {
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

    .circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #007bff;
        border: 2px solid #007bff; /* This creates the #007bff outline */
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
    }

    .outline {
        background-color: transparent;
        color: #007bff; /* Hide the text inside the circles */
    }

</style>

<form action="{{route('qaqc.report.postdetail')}}"  method="post" class="align-middle">
    @csrf

    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card pt-2 py-2">
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <div class="circle">1</div>
                            </div>
                            <div class="col">
                                <div class="progress" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="height: 12px">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                                </div>
                            </div>

                            <!-- Circle 2 -->
                            <div class="col-auto">
                                <div class="circle">2</div>
                            </div>
                            <div class="col">
                                <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="height: 12px">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- Circle 3 -->
                            <div class="col-auto">
                                <div class="circle outline">3</div>
                            </div>
                        </div>

                            <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <div class="col-auto">
                                <span class="h3">Add Part Details</span>
                                <p class="text-secondary mt-2">You need to add part details for the report header that you have <br>
                                    been made before. Everytime you add, it will stored in the table <br> below.</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <a class="btn btn-outline-primary" id="addDataBtn">+ Add Data</a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <div class="card">
                                <div class="card-body">
                                    <table id="dataTable" class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th class="text-center">Name</th>
                                                <th class="text-center">Rec Quantity</th>
                                                <th class="text-center">Verify Quantity</th>
                                                <th class="text-center">Prod Date</th>
                                                <th class="text-center">Shift</th>
                                                <th class="text-center">Can Use</th>
                                                <th class="text-center">Can't Use</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="">
                                <a href="{{ route('qaqc.report.create') }}" class="btn btn-primary">Back</a>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>


    <script>
        let rowNumber = 0;

        @if(Session::get('details'))
            @foreach (Session::get('details') as $detail)
                addDataRow({!! json_encode($detail) !!});
            @endforeach
        @else
            addDataRow();
        @endif


        function addDataRow(detail = {}) {
            rowNumber++;
            updateRowNumber();
            const tableBody = document.querySelector('#dataTable tbody');
            const details = @json($details ?? []);
            console.log(details);
            const rowCount = tableBody.children.length + 1; // Get the current row count
            const newRow = document.createElement('tr');
            newRow.classList.add('added-row', 'text-center', 'align-middle');
            newRow.innerHTML = `
            <input required type="number" value="${rowCount}" name="rowCount" class="d-none"></input>
                <td class="rowNum">${rowNumber}</td>
                <td><input class="form-control" required type="text" id="itemNameInput${rowCount}" value="${detail.Part_Name ?? ''}" name="itemName${rowCount}" placeholder="Enter item name" autocomplete="off">
                            <div id="itemDropdown${rowCount}" class="dropdown-content"></div></td>

                <td><input required type="number" value="${detail.Rec_Quantity ?? ''}" name="rec_quantity${rowCount}" class="form-control rec-input"></td>
                <td><input required type="number" value="${detail.Verify_Quantity ?? ''}" name="verify_quantity${rowCount}" class="form-control verify-input"></td>
                <td><input required type="date" value="${detail.Prod_Date ?? ''}" name="prod_date${rowCount}" class="form-control prod-input"></td>
                <td><input required type="number" value="${detail.Shift ?? ''}" name="shift${rowCount}" class="form-control shift-input"></td>
                <td><input required type="number" value="${detail.Can_Use ?? ''}" name="can_use${rowCount}" class="form-control canuse-input"></td>
                <td><input required type="number" value="${detail.Cant_Use ?? ''}" name="cant_use${rowCount}" class="form-control cantuse-input"></td>
                <td><a class="btn btn-danger btn-sm" onclick="removeItem()">Remove </a></td>
            `;
            tableBody.appendChild(newRow);


            const itemNameInput = document.getElementById('itemNameInput'+ rowCount);
            const itemDropdown = document.getElementById('itemDropdown' + rowCount);



            itemNameInput.addEventListener('keyup', function() {
                const inputValue = itemNameInput.value.trim();

                // Make an AJAX request to fetch relevant items
                fetch(`/items?item_name=${inputValue}`)
                    .then(response => response.json())
                    .then(data => {
                        // Clear previous dropdown options
                        itemDropdown.innerHTML = '';

                        // Display dropdown options
                        if (data.length > 0) {
                            data.forEach(item => {
                                const option = document.createElement('div');
                                option.classList.add('dropdown-item');
                                option.textContent = item;
                                option.addEventListener('click', function() {
                                    itemNameInput.value = item;
                                    itemDropdown.innerHTML = ''; // Hide dropdown after selection
                                });
                                itemDropdown.appendChild(option);
                            });
                            itemDropdown.style.display = 'block'; // Show dropdown
                        } else {
                            itemDropdown.style.display = 'none'; // Hide dropdown if no options
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            // Close dropdown when clicking outside the dropdown or input field
            document.addEventListener('click', function(event) {
                if (!itemNameInput.contains(event.target) && !itemDropdown.contains(event.target)) {
                    itemDropdown.style.display = 'none';
                    console.log(itemNameInput.value);
                }
            });


        }

        // Add event listener to the Add Data button
        document.getElementById('addDataBtn').addEventListener('click', addDataRow);

        function removeItem() {
            // Get the parent container of the remove button (which is the item container)
            const itemContainer = event.target.closest('.added-row');

            // Remove the item container from the DOM
            itemContainer.remove();

            // Decrement the item ID counter
            rowNumber--;

            updateRowNumber();
        }

        function updateRowNumber() {
            // Get all elements with the added-item class
            const addedRows = document.querySelectorAll('.added-row');

            // Loop through each added item and update the count
            addedRows.forEach((row, index) => {
                // Get the <td> element containing the row count for this row
                const rowCountCell = row.querySelector('.rowNum');

                // Update the content of the <td> element
                rowCountCell.textContent = index + 1;
            });
        }


    </script>

@endsection
