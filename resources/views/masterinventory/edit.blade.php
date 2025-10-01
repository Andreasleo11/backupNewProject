@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Inventory</h1>

        <form action="{{ route('masterinventory.update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Form fields for Master Inventory -->
            <div class="form-group">
                <label for="ip_address">IP Address</label>
                <input type="text" name="ip_address" id="ip_address" class="form-control" value="{{ $data->ip_address }}"
                    required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="{{ $data->username }}"
                    required>
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
                <select name="dept" id="dept" class="form-control" required>
                    @foreach ($depts as $dept)
                        <option value="{{ $dept->name }}" {{ $data->dept == $dept->name ? 'selected' : '' }}>
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
                <select name="type" id="type" class="form-control" required>
                    <option value="PC" {{ $data->type == 'PC' ? 'selected' : '' }}>PC</option>
                    <option value="Laptop" {{ $data->type == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                    <option value="Others" {{ $data->type == 'Others' ? 'selected' : '' }}>Others</option>
                </select>
                @error('type')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="purpose">Purpose</label>
                <input type="text" name="purpose" id="purpose" class="form-control" value="{{ $data->purpose }}"
                    required>
            </div>

            <div class="form-group">
                <label for="brand">Brand </label>
                <input type="text" name="brand" id="brand" class="form-control" value="{{ $data->brand }}"
                    required>
            </div>

            <div class="form-group">
                <label for="os">OS</label>
                <input type="text" name="os" id="os" class="form-control" value="{{ $data->os }}"
                    required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" class="form-control"
                    value="{{ $data->description }}" required>
            </div>

            <!-- Form fields for Hardwares -->
            <h4>Hardwares</h4>
            <table class="table" id="hardwares-table">
                <thead>
                    <tr>
                        <th>Hardware Type</th>
                        <th>Nomor Inventaris</th>
                        <th>Hardware Name</th>
                        <th>Tanggal Pembelian</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="hardwares-container">
                    @foreach ($data->hardwares as $index => $hardware)
                        <tr data-index="{{ $index }}">
                            <td>
                                <select name="hardwares[{{ $index }}][type]" class="form-control">
                                    @foreach ($hardwareTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ $hardware->hardware_id == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="hardwares[{{ $index }}][brand]" class="form-control"
                                    value="{{ $hardware->brand }}" required>
                            </td>
                            <td>
                                <input type="text" name="hardwares[{{ $index }}][hardware_name]"
                                    class="form-control" value="{{ $hardware->hardware_name }}" required>
                            </td>
                            <td>
                                <input type="text" name="hardwares[{{ $index }}][remark]" class="form-control"
                                    value="{{ $hardware->remark }}" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger remove-hardware">Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" class="btn btn-secondary" id="add-hardware">Add Hardware</button>

            <!-- Form fields for Softwares -->
            <h4>Softwares</h4>
            <table class="table" id="softwares-table">
                <thead>
                    <tr>
                        <th>Software Type</th>
                        <th>Software Brand</th>
                        <th>Software Name</th>
                        <th>License</th>
                        <th>Tanggal Pembelian</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="softwares-container">
                    @foreach ($data->softwares as $index => $software)
                        <tr data-index="{{ $index }}">
                            <td>
                                <select name="softwares[{{ $index }}][type]" class="form-control">
                                    @foreach ($softwareTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ $software->software_id == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="softwares[{{ $index }}][software_brand]"
                                    class="form-control" value="{{ $software->software_brand }}" required>
                            </td>
                            <td>
                                <input type="text" name="softwares[{{ $index }}][software_name]"
                                    class="form-control" value="{{ $software->software_name }}" required>
                            </td>
                            <td>
                                <input type="text" name="softwares[{{ $index }}][license]"
                                    class="form-control" value="{{ $software->license }}" required>
                            </td>
                            <td>
                                <input type="text" name="softwares[{{ $index }}][remark]" class="form-control"
                                    value="{{ $software->remark }}" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger remove-software">Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" class="btn btn-secondary" id="add-software">Add Software</button>

            <button type="submit" class="btn btn-primary">Update Inventory</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let hardwareIndex = {{ count($data->hardwares) }};
            let softwareIndex = {{ count($data->softwares) }};
            const hardwareTypes = @json($hardwareTypes);
            const softwareTypes = @json($softwareTypes);

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
                    <input type="text" name="hardwares[${hardwareIndex}][brand]" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="hardwares[${hardwareIndex}][hardware_name]" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="hardwares[${hardwareIndex}][remark]" class="form-control" required>
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
                    <input type="text" name="softwares[${softwareIndex}][software_brand]" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="softwares[${softwareIndex}][software_name]" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="softwares[${softwareIndex}][license]" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="softwares[${softwareIndex}][remark]" class="form-control" required>
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
