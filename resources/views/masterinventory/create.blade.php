@extends('layouts.app')

@section('content')
  <div class="container">
    <h1>Create Master Inventory</h1>

    @if (session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif

    <form method="POST" action="{{ route('masterinventory.store') }}" enctype="multipart/form-data">
      @csrf

      <!-- Form fields for Master Inventory -->
      <div class="form-group">
        <label for="ip_address">IP Address</label>
        <input type="text" name="ip_address" id="ip_address" class="form-control"
          value="{{ old('ip_address') }}" required>
        @error('ip_address')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" class="form-control"
          value="{{ old('username') }}" required>
        @error('username')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="position_image">Position Image</label>
        <input type="file" name="position_image" id="position_image" class="form-control">
        @error('position_image')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="dept">Department</label>
        <select name="dept" id="dept" class="form-control">
          @foreach ($depts as $dept)
            <option value="{{ $dept->name }}" {{ old('dept') == $dept->name ? 'selected' : '' }}>
              {{ $dept->name }}
            </option>
          @endforeach
        </select>
        @error('dept')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="type">Type</label>
        <select name="type" id="type" class="form-control">
          <option value="PC" {{ old('type') == 'PC' ? 'selected' : '' }}>PC</option>
          <option value="Laptop" {{ old('type') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
          <option value="Others" {{ old('type') == 'Others' ? 'selected' : '' }}>Others</option>
        </select>
        @error('type')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="purpose">Purpose</label>
        <input type="text" name="purpose" id="purpose" class="form-control"
          value="{{ old('purpose') }}" required>
        @error('purpose')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="brand">Brand</label>
        <input type="text" name="brand" id="brand" class="form-control"
          value="{{ old('brand') }}" required>
        @error('brand')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="os">OS</label>
        <input type="text" name="os" id="os" class="form-control"
          value="{{ old('os') }}" required>
        @error('os')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <input type="text" name="description" id="description" class="form-control"
          value="{{ old('description') }}" required>
        @error('description')
          <span class="text-danger">{{ $message }}</span>
        @enderror
      </div>

      <!-- Form fields for Hardwares -->
      <h4>Hardwares</h4>
      <table class="table" id="hardwares-table">
        <thead>
          <tr>
            <th>Hardware Type</th>
            <th>Nomor Inventaris</th> <!-- header only -->
            <th>Hardware Name</th>
            <th>Tanggal Pembelian</th> <!-- header only -->
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="hardwares-container">
          <!-- Dynamic hardware rows will be added here -->
        </tbody>
      </table>
      <button type="button" class="btn btn-secondary" id="add-hardware">Add Hardware</button>

      <!-- Form fields for Softwares -->
      <h4>Softwares</h4>
      <table class="table" id="softwares-table">
        <thead>
          <tr>
            <th>Software Type</th>
            <th>software Brand</th>
            <th>Software Name</th>
            <th>License</th>
            <th>Tanggal Pembelian</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="softwares-container">
          <!-- Dynamic software rows will be added here -->
        </tbody>
      </table>
      <button type="button" class="btn btn-secondary" id="add-software">Add Software</button>

      <button type="submit" class="btn btn-primary">Create Inventory</button>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      let hardwareIndex = 0;
      let softwareIndex = 0;
      const hardwareTypes = @json($hardwares);
      const softwareTypes = @json($softwares);

      document.getElementById('add-hardware').addEventListener('click', function() {
        const container = document.getElementById('hardwares-container');
        const row = document.createElement('tr');
        row.dataset.index = hardwareIndex;
        let options = hardwareTypes.map(type =>
          `<option value="${type.id}">${type.name}</option>`).join('');
        row.innerHTML = `
                <td>
                    <select name="hardwares[${hardwareIndex}][type]" class="form-control">
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="text" name="hardwares[${hardwareIndex}][brand]" class="form-control">
                </td>
                <td>
                    <input type="text" name="hardwares[${hardwareIndex}][hardware_name]" class="form-control">
                </td>
                <td>
                    <input type="text" name="hardwares[${hardwareIndex}][remark]" class="form-control">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-hardware">Remove</button>
                </td>
            `;
        container.appendChild(row);
        hardwareIndex++;
      });

      document.getElementById('add-software').addEventListener('click', function() {
        const container = document.getElementById('softwares-container');
        const row = document.createElement('tr');
        row.dataset.index = softwareIndex;
        let options = softwareTypes.map(type =>
          `<option value="${type.id}">${type.name}</option>`).join('');
        row.innerHTML = `
                <td>
                    <select name="softwares[${softwareIndex}][type]" class="form-control">
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="text" name="softwares[${softwareIndex}][software_brand]" class="form-control">
                </td>
                <td>
                    <input type="text" name="softwares[${softwareIndex}][software_name]" class="form-control">
                </td>
                <td>
                    <input type="text" name="softwares[${softwareIndex}][license]" class="form-control">
                </td>
                <td>
                    <input type="text" name="softwares[${softwareIndex}][remark]" class="form-control">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-software">Remove</button>
                </td>
            `;
        container.appendChild(row);
        softwareIndex++;
      });

      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-hardware')) {
          event.target.closest('tr').remove();
        }
        if (event.target.classList.contains('remove-software')) {
          event.target.closest('tr').remove();
        }
      });
    });
  </script>
@endsection
