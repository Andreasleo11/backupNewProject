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

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }

    th {
        background-color: #f2f2f2;
    }

    .add-data-btn {
        text-align: right;
        margin-bottom: 10px;
    }

    .add-data-btn button {
        padding: 8px 16px;
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

     .itemNameInput {
        width: 100%;
        box-sizing: border-box;
    }
</style>
<div class="add-data-btn">
    <a class="btn btn-success" id="addDataBtn">Add Data</a>
</div>

<form action="{{route('qaqc.report.postdetail')}}"  method="post">
    @csrf

    <div class="table-responsive">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Rec Quantity</th>
                    <th>Verify Quantity</th>
                    <th>Prod Date</th>
                    <th>Shift</th>
                    <th>Can Use</th>
                    <th>Can't Use</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <a href="{{ route('qaqc.report.create') }}" class="btn btn-primary">back </a>
    <button type="submit" class="btn btn-primary mt-3">Submit</button>
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
            newRow.classList.add('added-row');
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
