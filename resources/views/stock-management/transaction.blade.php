@extends('layouts.app')

@push('extraCss')
  <style>
    #dropdown-container {
      position: absolute;
      background-color: white;
      border: 1px solid #ccc;
      width: 50%;
      display: none;
      max-height: 150px;
      overflow-y: auto;
      z-index: 1000;
    }

    #dropdown-container .dropdown-item {
      padding: 8px 12px;
      cursor: pointer;
    }

    #dropdown-container .dropdown-item:hover {
      background-color: #f1f1f1;
    }
  </style>
@endpush

@section('content')
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('mastertinta.index') }}">Management Stock</a>
        </li>
        <li class="breadcrumb-item active">Edit</li>
      </ol>
    </nav>

    <div class="row d-flex">
      <div class="col">
        <h2 class="fw-bold">Edit Management Stock</h2>
      </div>
    </div>

    <form id="stock-form" action="{{ route('mastertinta.process') }}" method="post">
      @csrf
      <div class="row mt-4">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col">
                  <div class="form-group ">
                    <label for="stock_id" class="fw-semibold form-label">Stock Type<span
                        class="text-danger">*</span></label>
                    <select name="stock_id" id="stock_id" class="form-select" required
                      name="stock_id">
                      <option value="" selected disabled>--Select Master Stock--</option>
                      @foreach ($masterStocks as $stock)
                        <option value="{{ $stock->id }}">{{ $stock->stock_code }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col">
                  <div class="form-group mt-3">
                    <label for="type" class="fw-semibold form-label">Transaction Type <span
                        class="text-danger">*</span></label>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="transaction_type"
                        id="in" value="in">
                      <label class="form-check-label" for="in">
                        In
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="transaction_type"
                        id="out" value="out" checked>
                      <label class="form-check-label" for="out">
                        Out
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row align-items-center">
                <div class="col">
                  <div class="form-group mt-3">
                    <label for="department" class="fw-semibold form-label">Department <span
                        class="text-danger">*</span></label>
                    <select name="department" id="department" class="form-select" required>
                      <option value="" selected disabled>--Select Department--</option>
                      @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col">
                  <div class="form-group mt-3">
                    <label for="pic" class="fw-semibold form-label">PIC <span
                        class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="pic" name="pic" required>
                  </div>
                </div>
              </div>

              <div class="form-group mt-3">
                <label for="remark" class="fw-semibold form-label">Remark <span
                    class="text-danger">*</span></label>
                <textarea name="remark" id="remark" cols="30" rows="5" placeholder="Your remark here"
                  class="form-control" required></textarea>
              </div>

              <div class="row align-items-center mt-3">
                <div class="col">
                  <div class="form-group">
                    <label for="available_quantity" class="fw-semibold form-label">Available
                      Quantity</label>
                    <input type="text" class="form-control" id="available_quantity"
                      name="available_quantity" readonly>
                  </div>
                </div>
              </div>

            </div>
          </div>
          <div class="card mt-4">
            <div class="card-body">
              <label for="type" class="fw-semibold form-label">List Items<span
                  class="text-danger">*</span></label>
              <div id="item-container">
                <div class="row align-items-center my-2 mx-3 item-row">
                  <div class="col-auto">
                    <label for="staticEmail2" class="visually-hidden">No</label>
                    <input type="text" readonly class="form-control-plaintext item-no"
                      value="1">
                  </div>
                  <div class="col">
                    <label for="item_name_1" class="visually-hidden">Item Name</label>
                    <input type="text" class="form-control item-name" id="item_name_1"
                      name="item_name_1" placeholder="Item Name">
                    <div class="dropdown-menu" style="display: none;"></div>
                    <!-- Dropdown container for item_name_1 -->
                  </div>
                  <div class="col-auto">
                    <button type="button"
                      class="btn btn-danger btn-sm remove-item-btn">Remove</button>
                  </div>
                </div>
              </div>
              <div>
                <button type="button" id="add-item-btn"
                  class="btn btn-sm btn-outline-secondary mt-3">Add Item</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
@endsection

@push('extraJs')
  <script type="module">
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize TomSelect for dropdown
      new TomSelect('#department', {
        plugins: ['dropdown_input'],
        sortField: {
          field: "text",
          direction: "asc"
        }
      });
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Add event listener for Add Item button
      document.getElementById('add-item-btn').addEventListener('click', addItem);

      // Handle input events for all item-name inputs
      document.addEventListener('input', function(event) {
        if (event.target.classList.contains('item-name')) {
          handleInput(event.target);
        }
      });


      function fetchAvailableQuantity() {
        const stockId = document.getElementById('stock_id').value;
        const departmentId = document.getElementById('department').value;

        if (stockId && departmentId) {
          fetch(`/stock/get-available-quantity/${stockId}/${departmentId}`)
            .then(response => response.json())
            .then(data => {
              document.getElementById('available_quantity').value = data.available_quantity;
            })
            .catch(error => console.error('Error fetching available quantity:', error));
          document.getElementById('available_quantity').value = '0';
        }
      }


      // Function to add a new item row
      function addItem() {
        const container = document.getElementById('item-container');
        const itemRows = document.querySelectorAll('.item-row');
        const newItemRow = itemRows[0].cloneNode(true); // Clone the first row
        const newItemIndex = itemRows.length + 1;

        // Update the item number
        newItemRow.querySelector('.item-no').value = newItemIndex;

        // Update the input field properties
        const itemNameInput = newItemRow.querySelector('.item-name');
        itemNameInput.id = 'item_name_' + newItemIndex;
        itemNameInput.name = 'item_name_' + newItemIndex;
        itemNameInput.value = '';

        // Add remove button functionality
        const removeButton = newItemRow.querySelector('.remove-item-btn');
        removeButton.addEventListener('click', function() {
          removeItem(newItemRow);
        });

        // Append the new item row to the container
        container.appendChild(newItemRow);

        // Focus on the new item input
        itemNameInput.focus();
      }

      // Function to remove an item row
      function removeItem(row) {
        row.remove();

        // Update item numbers after removal
        const itemRows = document.querySelectorAll('.item-row');
        itemRows.forEach((row, index) => {
          row.querySelector('.item-no').value = index + 1;
        });
      }

      // Function to handle input events in the item-name field
      function handleInput(input) {
        const inputValue = input.value.trim();
        if (inputValue) {
          const masterStockId = document.getElementById('stock_id').value;
          const transactionType = document.querySelector('input[name="transaction_type"]:checked')
            .value;



          if (transactionType === 'out') {
            fetch(`/masterstock/get-items/${masterStockId}?name=${inputValue}`)
              .then(response => response.json())
              .then(data => {
                const dropdownContainer = input.nextElementSibling; // Dropdown container
                dropdownContainer.innerHTML = ''; // Clear existing options
                dropdownContainer.style.maxHeight = '100px'; // Adjust this value as needed
                dropdownContainer.style.overflowY = 'auto';
                data.forEach(item => {
                  const dropdownItem = document.createElement('div');
                  dropdownItem.className = 'dropdown-item';
                  dropdownItem.textContent = item.unique_code;
                  dropdownItem.addEventListener('click', function() {
                    input.value = item.unique_code;
                    dropdownContainer.style.display = 'none';
                  });
                  dropdownContainer.appendChild(dropdownItem);
                });

                dropdownContainer.style.display = 'block';
              })
              .catch(error => console.error('Error fetching items:', error));
          }
        }
      }

      // Handle stock_id change event
      document.getElementById('stock_id').addEventListener('change', function() {
        const itemInputs = document.querySelectorAll('.item-name');
        itemInputs.forEach(input => {
          input.value = ''; // Clear existing values
          handleInput(input); // Trigger handleInput for each input
        });
      });


      // Toggle fields based on transaction type
      const transactionTypeInputs = document.querySelectorAll('input[name="transaction_type"]');
      const departmentField = document.getElementById('department');
      const picField = document.getElementById('pic');
      const remarkField = document.getElementById('remark');

      const toggleFields = () => {
        const isOut = document.getElementById('out').checked;
        const displayStyle = isOut ? 'block' : 'none';

        departmentField.closest('.form-group').style.display = displayStyle;
        picField.closest('.form-group').style.display = displayStyle;
        remarkField.closest('.form-group').style.display = displayStyle;

        departmentField.disabled = !isOut;
        picField.disabled = !isOut;
        remarkField.disabled = !isOut;
      };

      transactionTypeInputs.forEach(input => input.addEventListener('change', toggleFields));
      toggleFields(); // Initial call to set initial state
      // Handle stock_id and department change events
      document.getElementById('stock_id').addEventListener('change', fetchAvailableQuantity);
      document.getElementById('department').addEventListener('change', fetchAvailableQuantity);
    });
  </script>
@endpush
