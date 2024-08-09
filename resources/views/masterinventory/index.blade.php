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
    }

    .filter-input {
        margin-bottom: 1rem;
        width: calc(50% - 10px);
        display: inline-block;
    }
</style>

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
                <th>IP Address</th>
                <th>Username</th>
                <th>Department</th>
                <th>Type</th>
                <th>Purpose</th>
                <th>Brand</th>
                <th>OS</th>
                <th>Description</th>
                <th>Hardwares</th>
                <th>Softwares</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="inventory-table">
            @foreach($datas as $data)
                <tr>
                    <td class="ip-address">{{ $data->ip_address }}</td>
                    <td class="username">{{ $data->username }}</td>
                    <td>{{ $data->dept }}</td>
                    <td>{{ $data->type }}</td>
                    <td>{{ $data->purpose }}</td>
                    <td>{{ $data->brand }}</td>
                    <td>{{ $data->os }}</td>
                    <td>{{ $data->description }}</td>
                    <td>
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
                    </td>
                    <td>
                        <a href="{{ route('masterinventory.editpage', $data->id) }}" class="btn btn-warning">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal" id="dataModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="dataModalLabel">Details</h5>
            <!-- <button type="button" class="close" onclick="closeModal()">
                <span aria-hidden="true">&times;</span>
            </button> -->
        </div>
        <div class="modal-body" id="dataModalBody">
            <!-- Dynamic content will be loaded here -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<!-- Include necessary scripts -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script>
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
</script>

@endsection
