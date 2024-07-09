@extends('layouts.app')

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
                                        <select name="stock_id" id="stock_id" class="form-select" required>
                                            <option value="" selected disabled>--Select Stock Type--</option>
                                            @foreach ($datas as $data)
                                                <option value="{{ $data->id }}">{{ $data->stock_code }}</option>
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
                                            name="item_name_1" placeholder="Item Name" required>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-danger btn-sm remove-item-btn">Remove</button>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button type="button" id="add-item-btn" class="btn btn-sm btn-outline-secondary mt-3">Add Item</button>
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

            // Add event listener for toggle input method
            document.getElementById('inputToggle').addEventListener('change', toggleInputMethod);
        });
    </script>
    <script>
       document.getElementById('add-item-btn').addEventListener('click', addItem);

let inputTimeout;

function addItem() {
    const container = document.getElementById('item-container');
    const itemRows = document.querySelectorAll('.item-row');
    const newItemRow = itemRows[0].cloneNode(true);
    const newItemIndex = itemRows.length + 1;

    newItemRow.querySelector('.item-no').value = newItemIndex;

    const itemNameInput = newItemRow.querySelector('.item-name');
    itemNameInput.id = 'item_name_' + newItemIndex;
    itemNameInput.name = 'item_name_' + newItemIndex;
    itemNameInput.value = '';

    // Add required attribute
    itemNameInput.required = true;

    // Add remove button
    const removeButton = newItemRow.querySelector('.remove-item-btn');
    removeButton.addEventListener('click', function() {
        removeItem(newItemRow);
    });

    container.appendChild(newItemRow);

    // Add event listener to the new input field
    itemNameInput.addEventListener('input', handleInput);
}

function removeItem(row) {
    row.remove();

    // Update item numbers
    const itemRows = document.querySelectorAll('.item-row');
    itemRows.forEach((row, index) => {
        row.querySelector('.item-no').value = index + 1;
    });
}

        function handleInput(event) {
            clearTimeout(inputTimeout);
            inputTimeout = setTimeout(() => {
                if (event.target.value) {
                    addItem();
                    const itemRows = document.querySelectorAll('.item-row');
                    const nextInput = itemRows[itemRows.length - 1].querySelector('.item-name');
                    nextInput.focus();
                }
            }, 1000); // Adjust the delay as needed (e.g., 300ms)
        }

// Initial setup for the first input field
document.getElementById('item_name_1').addEventListener('input', handleInput);

// Add initial event listener
document.querySelector('.item-name').addEventListener('input', handleInput);

const transactionTypeInputs = document.querySelectorAll('input[name="transaction_type"]');
const departmentField = document.getElementById('department');
const picField = document.getElementById('pic');
const remarkField = document.getElementById('remark');

function toggleFields() {
    if (document.getElementById('in').checked) {
        departmentField.disabled = true;
        picField.disabled = true;
        remarkField.disabled = true;

        departmentField.closest('.form-group').style.display = 'none';
        picField.closest('.form-group').style.display = 'none';
        remarkField.closest('.form-group').style.display = 'none';
    } else {
        departmentField.disabled = false;
        picField.disabled = false;
        remarkField.disabled = false;

        departmentField.closest('.form-group').style.display = 'block';
        picField.closest('.form-group').style.display = 'block';
        remarkField.closest('.form-group').style.display = 'block';
    }
}

// Add event listeners to radio buttons
transactionTypeInputs.forEach(input => {
    input.addEventListener('change', toggleFields);
});

// Initial toggle based on the default selection
toggleFields();

// Add event listener to form submit event to remove the last item row
document.getElementById('stock-form').addEventListener('submit', function(event) {
    const itemRows = document.querySelectorAll('.item-row');
    if (itemRows.length > 0) {
        itemRows[itemRows.length - 1].remove();
    }
});
    </script>
@endpush
