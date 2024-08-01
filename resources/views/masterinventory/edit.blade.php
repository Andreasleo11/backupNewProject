@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Inventory</h1>

    <form action="{{ route('masterinventory.update', $data->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Form fields for Master Inventory -->
        <div class="form-group">
            <label for="ip_address">IP Address</label>
            <input type="text" name="ip_address" id="ip_address" class="form-control" value="{{ $data->ip_address }}" required>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="{{ $data->username }}" required>
        </div>

        <div class="form-group">
            <label for="dept">Department </label>
            <input type="text" name="dept" id="dept" class="form-control" value="{{ $data->dept }}" required>
        </div>

        <div class="form-group">
            <label for="type">Type </label>
            <input type="text" name="type" id="type" class="form-control" value="{{ $data->type }}" required>
        </div>

        <div class="form-group">
            <label for="purpose">Purpose</label>
            <input type="text" name="purpose" id="purpose" class="form-control" value="{{ $data->purpose }}" required>
        </div>

        <div class="form-group">
            <label for="brand">Brand </label>
            <input type="text" name="brand" id="brand" class="form-control" value="{{ $data->brand }}" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ $data->description }}" required>
        </div>
        <!-- Repeat for other fields like username, dept, etc. -->

        <!-- Form fields for Hardwares -->
        <div id="hardwares-container">
            <h4>Hardwares</h4>
            @foreach($data->hardwares as $index => $hardware)
                <div class="form-group" data-index="{{ $index }}">
                    <label for="hardware_type_{{ $index }}">Hardware Type</label>
                    <select name="hardwares[{{ $index }}][type]" id="hardware_type_{{ $index }}" class="form-control">
                        @foreach($hardwareTypes as $type)
                            <option value="{{ $type->id }}" {{ $hardware->hardware_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <label for="hardware_brand_{{ $index }}">Hardware Brand</label>
                    <input type="text" name="hardwares[{{ $index }}][brand]" id="hardware_brand_{{ $index }}" class="form-control" value="{{ $hardware->brand }}" required>
                    <!-- Repeat for other hardware fields like hardware_name, remark, etc. -->
                    <label for="hardware_name_{{ $index }}">Hardware Name</label>
                    <input type="text" name="hardwares[{{ $index }}][hardware_name]" id="hardware_name_{{ $index }}" class="form-control" value="{{ $hardware->hardware_name }}" required>

                    <label for="hardware_remark_{{ $index }}">Remark</label>
                    <input type="text" name="hardwares[{{ $index }}][remark]" id="hardware_remark_{{ $index }}" class="form-control" value="{{ $hardware->remark }}" required>

                    
                    <button type="button" class="btn btn-danger remove-hardware">Remove</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-secondary" id="add-hardware">Add Hardware</button>

        <div id="softwares-container">
            <h4>Softwares</h4>
            @foreach($data->softwares as $index => $software)
                <div class="form-group" data-index="{{ $index }}">
                    <label for="software_type_{{ $index }}">Software Type</label>
                    <select name="softwares[{{ $index }}][type]" id="software_type_{{ $index }}" class="form-control">
                        @foreach($softwareTypes as $type)
                            <option value="{{ $type->id }}" {{ $software->software_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <label for="software_name_{{ $index }}">Software Name</label>
                    <input type="text" name="softwares[{{ $index }}][software_name]" id="software_name_{{ $index }}" class="form-control" value="{{ $software->software_name }}" required>
                    
                    <label for="software_license_{{ $index }}">License</label>
                    <input type="text" name="softwares[{{ $index }}][license]" id="software_license_{{ $index }}" class="form-control" value="{{ $software->license }}" required>

                    <label for="software_remark_{{ $index }}">Remark</label>
                    <input type="text" name="softwares[{{ $index }}][remark]" id="software_remark_{{ $index }}" class="form-control" value="{{ $software->remark }}" required>
                    <!-- Repeat for other software fields like license, remark, etc. -->
                    <button type="button" class="btn btn-danger remove-software">Remove</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-secondary" id="add-software">Add Software</button>

        <button type="submit" class="btn btn-primary">Update Inventory</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let hardwareIndex = {{ count($data->hardwares) }};
        let softwareIndex = {{ count($data->softwares) }};
        const hardwareTypes = @json($hardwareTypes);
        const softwareTypes = @json($softwareTypes);

        document.getElementById('add-hardware').addEventListener('click', function () {
            const container = document.getElementById('hardwares-container');
            const div = document.createElement('div');
            div.className = 'form-group';
            div.dataset.index = hardwareIndex;
            let options = hardwareTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('');
            div.innerHTML = `
                <label for="hardware_type_${hardwareIndex}">Hardware Type</label>
                <select name="hardwares[${hardwareIndex}][type]" id="hardware_type_${hardwareIndex}" class="form-control">
                    ${options}
                </select>
                <label for="hardware_brand_${hardwareIndex}">Hardware Brand</label>
                <input type="text" name="hardwares[${hardwareIndex}][brand]" id="hardware_brand_${hardwareIndex}" class="form-control" required>
                 <label for="hardware_name_${hardwareIndex}">Hardware Name</label>
                    <input type="text" name="hardwares[${hardwareIndex}][hardware_name]" id="hardware_name_${hardwareIndex}" class="form-control" required>

                    <label for="hardware_remark_${hardwareIndex}">Remark</label>
                    <input type="text" name="hardwares[${hardwareIndex}][remark]" id="hardware_remark_${hardwareIndex}" class="form-control" required>

                <!-- Repeat for other hardware fields like hardware_name, remark, etc. -->
                <button type="button" class="btn btn-danger remove-hardware">Remove</button>
            `;
            container.appendChild(div);
            hardwareIndex++;
        });

        document.getElementById('add-software').addEventListener('click', function () {
            const container = document.getElementById('softwares-container');
            const div = document.createElement('div');
            div.className = 'form-group';
            div.dataset.index = softwareIndex;
            let options = softwareTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('');
            div.innerHTML = `
                <label for="software_type_${softwareIndex}">Software Type</label>
                <select name="softwares[${softwareIndex}][type]" id="software_type_${softwareIndex}" class="form-control">
                    ${options}
                </select>
                <label for="software_name_${softwareIndex}">Software Name</label>
                <input type="text" name="softwares[${softwareIndex}][software_name]" id="software_name_${softwareIndex}" class="form-control" required>
                <!-- Repeat for other software fields like license, remark, etc. -->

                <label for="software_license_${softwareIndex}">License</label>
                    <input type="text" name="softwares[${softwareIndex}][license]" id="software_license_${softwareIndex}" class="form-control" required>

                    <label for="software_remark_${softwareIndex}">Remark</label>
                    <input type="text" name="softwares[${softwareIndex}][remark]" id="software_remark_${softwareIndex}" class="form-control" required>
                <button type="button" class="btn btn-danger remove-software">Remove</button>
            `;
            container.appendChild(div);
            softwareIndex++;
        });

        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-hardware')) {
                event.target.parentElement.remove();
            }
            if (event.target.classList.contains('remove-software')) {
                event.target.parentElement.remove();
            }
        });
    });
</script>
@endsection