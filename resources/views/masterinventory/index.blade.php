@extends('layouts.app')

@section('content')

<style>
    .card {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        background-color: #f8f9fa;
    }

    .card-header {
        background-color: #e9ecef;
        border-bottom: 1px solid #dee2e6;
    }

    .card-title {
        margin: 0;
        font-size: 1.25rem;
    }

    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1050; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Could be more or less, depending on screen size */
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    table {
        width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
    }

    th, td {
        padding: 0.75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }

    th {
        text-align: inherit;
        cursor: pointer;
    }
    
    th.sortable {
    cursor: pointer;
    }

    th.sortable.asc::after {
        content: " ↑"; /* Up arrow for ascending */
    }

    th.sortable.desc::after {
        content: " ↓"; /* Down arrow for descending */
    }

    .filter-input {
        margin-bottom: 1rem;
        width: calc(50% - 10px);
        display: inline-block;
    }

</style>

<!-- Lightbox2 CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">



<div class="container">
    <h1>Master Inventory List</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('masterinventory.createpage') }}" class="btn btn-primary">Add New Inventory</a>
    </div>

    <input type="text" id="filter-ip" class="form-control filter-input" placeholder="Filter by IP Address">
    <input type="text" id="filter-username" class="form-control filter-input" placeholder="Filter by Username">

    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="sortable" data-column="ip_address">IP Address</th>
                <th class="sortable" data-column="username">Username</th>
                <!-- <th>Position Image</th> -->
                <th class="sortable" data-column="dept">Department</th>
                <th class="sortable" data-column="type">Type</th>
                <th class="sortable" data-column="purpose">Purpose</th>
                <th class="sortable" data-column="brand">Brand</th>
                <th class="sortable" data-column="os">OS</th>
                <th class="sortable" data-column="description">Description</th>
                <!-- <th>Hardwares</th>
                <th>Softwares</th> -->
                <th colspan="2" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody id="inventory-table">
            @foreach($datas as $data)
                <tr>
                    <td class="ip-address">{{ $data->ip_address }}</td>
                    <td class="username">{{ $data->username }}</td>
                    <!-- <td>
                        <a data-fancybox="gallery" href="{{ asset('storage/' . $data->position_image) }}" data-caption="Position Image">
                            <img src="{{ asset('storage/' . $data->position_image) }}" alt="Position Image" style="max-width: 1000px; max-height: 100px;">
                        </a>
                    </td> -->
                    <td>{{ $data->dept }}</td>
                    <td>{{ $data->type }}</td>
                    <td>{{ $data->purpose }}</td>
                    <td>{{ $data->brand }}</td>
                    <td>{{ $data->os }}</td>
                    <td>{{ $data->description }}</td>
                    <!-- <td>
                        @if($data->hardwares->isEmpty())
                            No hardwares
                        @else
                            <button type="button" class="btn btn-primary showDetails" data-type="hardware" data-details='@json($data->hardwares)'>
                                Show Hardware Details
                            </button>
                        @endif
                    </td>
                    <td>
                        @if($data->softwares->isEmpty())
                            No softwares
                        @else
                            <button type="button" class="btn btn-primary showDetails" data-type="software" data-details='@json($data->softwares)'>
                                Show Software Details
                            </button>
                        @endif
                    </td> -->
                    <!-- <td>
                        <a href="{{ route('masterinventory.editpage', $data->id) }}" class="btn btn-warning">Edit</a>
                    </td> -->
                    <td>
                    <a href="{{ route('masterinventory.detail', $data->id) }}" class="btn btn-success">Detail</a>
                    <a href="{{ route('maintenance.inventory.create', ['id' => $data->id]) }}" class="btn btn-success">Create Maintenance</a>
                    <form action="{{ route('masterinventory.delete', $data->id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Include necessary scripts -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>


<script>

function confirmDelete() {
        return confirm('Are you sure you want to delete this item? This action cannot be undone.');
    }

$(document).ready(function() {
        $('[data-fancybox="gallery"]').fancybox({
            // Custom options
            loop: true,
            buttons: [
                'slideShow',
                'fullScreen',
                'thumbs',
                'close'
            ],
            caption: function(instance, item) {
                return $(this).data('caption') || '';
            },
            transitionEffect: "fade", // Example of custom option
            transitionDuration: 500
        });
    });

    document.addEventListener('DOMContentLoaded', function() {

        const dataModal = document.getElementById('dataModal');
        const dataModalBody = document.getElementById('dataModalBody');

        document.querySelectorAll('.showDetails').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const details = JSON.parse(this.getAttribute('data-details'));
                let modalContent = '';

                 // Function to format the date
                 const formatDate = (dateString) => {
                    const date = new Date(dateString);
                    const year = date.getFullYear();
                    const month = ('0' + (date.getMonth() + 1)).slice(-2);
                    const day = ('0' + date.getDate()).slice(-2);
                    return `${year}-${month}-${day}`;
                };

                if (type === 'hardware') {
                    modalContent += `
                        <h4>Hardwares</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Brand</th>
                                    <th>Hardware Name</th>
                                    <th>Remark</th>
                                    <th>Last Update</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    details.forEach(hardware => {
                        modalContent += `
                            <tr>
                                <td>${hardware.hardware_type.name ?? 'Unknown Type'}</td>
                                <td>${hardware.brand}</td>
                                <td>${hardware.hardware_name}</td>
                                <td>${hardware.remark}</td>
                                <td>${formatDate(hardware.updated_at)}</td>
                            </tr>
                        `;
                    });
                    modalContent += `
                            </tbody>
                        </table>
                    `;
                } else if (type === 'software') {
                    modalContent += `
                        <h4>Softwares</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Software Name</th>
                                    <th>License</th>
                                    <th>Remark</th>
                                    <th>Last Update</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    details.forEach(software => {
                        modalContent += `
                            <tr>
                                <td>${software.software_type.name ?? 'Unknown Type'}</td>
                                <td>${software.software_name}</td>
                                <td>${software.license}</td>
                                <td>${software.remark}</td>
                                <td>${formatDate(software.updated_at)}</td>
                            </tr>
                        `;
                    });
                    modalContent += `
                            </tbody>
                        </table>
                    `;
                }

                dataModalBody.innerHTML = modalContent;
                dataModal.style.display = 'block';
            });
        });

        // Function to close the modal
        window.closeModal = function() {
            dataModal.style.display = 'none';
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target === dataModal) {
                closeModal();
            }
        }

        // Filter functionality
        document.getElementById('filter-ip').addEventListener('keyup', function() {
            const filterValue = this.value.toLowerCase();
            document.querySelectorAll('#inventory-table tr').forEach(row => {
                const ip = row.querySelector('.ip-address').textContent.toLowerCase();
                if (ip.includes(filterValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        document.getElementById('filter-username').addEventListener('keyup', function() {
            const filterValue = this.value.toLowerCase();
            document.querySelectorAll('#inventory-table tr').forEach(row => {
                const username = row.querySelector('.username').textContent.toLowerCase();
                if (username.includes(filterValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('table');
    const headers = table.querySelectorAll('.sortable');
    let sortDirection = {};

    headers.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.getAttribute('data-column');
            const order = sortDirection[column] === 'asc' ? 'desc' : 'asc';
            sortDirection[column] = order;
            sortTable(column, order);
        });
    });

    function sortTable(column, order) {
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const index = Array.from(headers).findIndex(header => header.getAttribute('data-column') === column);

        rows.sort((a, b) => {
            const aText = a.children[index].textContent.trim();
            const bText = b.children[index].textContent.trim();
            
            // Determine if column data is numeric or text
            const aValue = isNaN(aText) ? aText.toLowerCase() : parseFloat(aText);
            const bValue = isNaN(bText) ? bText.toLowerCase() : parseFloat(bText);

            if (aValue < bValue) return order === 'asc' ? -1 : 1;
            if (aValue > bValue) return order === 'asc' ? 1 : -1;
            return 0;
        });

        rows.forEach(row => table.querySelector('tbody').appendChild(row));

        // Remove previous sort indicators
        headers.forEach(header => header.classList.remove('asc', 'desc'));

        // Add sort indicator to the current header
        const sortedHeader = Array.from(headers).find(header => header.getAttribute('data-column') === column);
        sortedHeader.classList.add(order);
    }
});
  


</script>

@endsection
