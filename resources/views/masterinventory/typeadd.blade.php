<!-- resources/views/masterinventory/typeadd.blade.php -->

@extends('layouts.app')

@section('content')
  <div class="container">
    <h1 class="mb-4">Type List</h1>

    <!-- Hardware Types Section -->
    <div class="d-flex justify-content-between mb-3">
      <h2>Hardware Types</h2>
      <button class="btn btn-primary" data-toggle="modal" data-target="#addHardwareTypeModal">Add
        Hardware Type</button>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="thead-dark">
          <tr>
            <th>Type Name</th>
          </tr>
        </thead>
        <tbody>
          @forelse($hardwareTypes as $type)
            <tr>
              <td>{{ $type->name }}</td>
              <td>
                <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $type->id }}"
                  data-type="hardware">Delete</button>
              </td>
            </tr>
          @empty
            <tr>
              <td>No hardware types available.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Software Types Section -->
    <div class="d-flex justify-content-between mb-3">
      <h2>Software Types</h2>
      <button class="btn btn-primary" data-toggle="modal" data-target="#addSoftwareTypeModal">Add
        Software Type</button>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="thead-dark">
          <tr>
            <th>Type Name</th>
          </tr>
        </thead>
        <tbody>
          @forelse($softwareTypes as $type)
            <tr>
              <td>{{ $type->name }}</td>
              <td>
                <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $type->id }}"
                  data-type="software">Delete</button>
              </td>
            </tr>
          @empty
            <tr>
              <td>No software types available.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add Hardware Type Modal -->
  <div class="modal fade" id="addHardwareTypeModal" tabindex="-1" role="dialog"
    aria-labelledby="addHardwareTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addHardwareTypeModalLabel">Add New Hardware Type</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addHardwareTypeForm">
            <div class="form-group">
              <label for="hardwareTypeName">Type Name</label>
              <input type="text" class="form-control" id="hardwareTypeName" name="name"
                required>
            </div>
            <button type="submit" class="btn btn-primary">Add Type</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Software Type Modal -->
  <div class="modal fade" id="addSoftwareTypeModal" tabindex="-1" role="dialog"
    aria-labelledby="addSoftwareTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addSoftwareTypeModalLabel">Add New Software Type</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <form id="addSoftwareTypeForm">
            <div class="form-group">
              <label for="softwareTypeName">Type Name</label>
              <input type="text" class="form-control" id="softwareTypeName" name="name"
                required>
            </div>
            <button type="submit" class="btn btn-primary">Add Type</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog"
    aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this type?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" id="confirmDelete" class="btn btn-danger">Delete</button>
        </div>
      </div>
    </div>
  </div>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#addHardwareTypeForm').on('submit', function(e) {
        e.preventDefault();
        var name = $('#hardwareTypeName').val();
        $.ajax({
          url: '{{ route('add.hardware.type') }}',
          method: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            name: name
          },
          success: function(response) {
            if (response.success) {
              location.reload(); // Refresh the page to update the table
            } else {
              alert('Error adding type');
            }
          },
          error: function(xhr) {
            console.log(xhr.responseText);
          }
        });
      });

      $('#addSoftwareTypeForm').on('submit', function(e) {
        e.preventDefault();
        var name = $('#softwareTypeName').val();
        $.ajax({
          url: '{{ route('add.software.type') }}',
          method: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            name: name
          },
          success: function(response) {
            if (response.success) {
              location.reload(); // Refresh the page to update the table
            } else {
              alert('Error adding type');
            }
          },
          error: function(xhr) {
            console.log(xhr.responseText);
          }
        });
      });
    });

    document.addEventListener('DOMContentLoaded', function() {
      var deleteId, deleteType;

      // Handle delete button click
      document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function() {
          deleteId = this.getAttribute('data-id');
          deleteType = this.getAttribute('data-type');
          var deleteModal = document.getElementById('deleteModal');
          var bsModal = new bootstrap.Modal(deleteModal);
          bsModal.show();
        });
      });

      // Confirm delete
      document.getElementById('confirmDelete').addEventListener('click', function() {
        fetch('{{ route('delete.type') }}', {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              id: deleteId,
              type: deleteType
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              location.reload(); // Refresh the page to update the table
            } else {
              alert('Error deleting type');
            }
          })
          .catch(error => console.error('Error:', error));
      });
    });
  </script>
@endsection
