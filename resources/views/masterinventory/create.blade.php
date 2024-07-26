@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Master Inventory</h1>
    
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('masterinventory.store') }}">
        @csrf

        <div class="form-group">
            <label for="ip_address">IP Address</label>
            <input type="text" name="ip_address" id="ip_address" class="form-control" value="{{ old('ip_address') }}">
            @error('ip_address')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}">
            @error('username')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="dept">Department</label>
            <select name="dept" id="dept" class="form-control">
                @foreach($depts as $dept)
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
            <input type="text" name="purpose" id="purpose" class="form-control" value="{{ old('purpose') }}">
            @error('purpose')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="brand">Brand</label>
            <input type="text" name="brand" id="brand" class="form-control" value="{{ old('brand') }}">
            @error('brand')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <input type="text" name="description" id="description" class="form-control" value="{{ old('description') }}">
            @error('brand')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div id="hardware-section">
            <h3>Hardware</h3>
            <button type="button" class="btn btn-secondary" onclick="addHardwareRow()">Add Hardware</button>
            
            <div id="hardware-rows"></div>
        </div>

        <div id="software-section">
            <h3>Software</h3>
            <button type="button" class="btn btn-secondary" onclick="addSoftwareRow()">Add Software</button>
            
            <div id="software-rows"></div>
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>

<script>
    let hardwareCount = 0;
    let softwareCount = 0;

    function addHardwareRow() {
        hardwareCount++;

        const hardwareRow = document.createElement('div');
        hardwareRow.className = 'hardware-row';
        hardwareRow.innerHTML = `
            <div class="form-group">
                <label for="hardware_type_${hardwareCount}">Type</label>
                <select name="hardwares[${hardwareCount}][type]" id="hardwares_type_${hardwareCount}" class="form-control">
                    @foreach($hardwares as $hardware)
                        <option value="{{ $hardware->id }}">{{ $hardware->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="hardwares_brand_${hardwareCount}">Brand</label>
                <input type="text" name="hardwares[${hardwareCount}][brand]" id="hardwares_brand_${hardwareCount}" class="form-control">
            </div>
            <div class="form-group">
                <label for="hardwares_name_${hardwareCount}">Hardware Name</label>
                <input type="text" name="hardwares[${hardwareCount}][hardware_name]" id="hardwares_name_${hardwareCount}" class="form-control">
            </div>
            <div class="form-group">
                <label for="hardwares_remark_${hardwareCount}">Remark</label>
                <input type="text" name="hardwares[${hardwareCount}][remark]" id="hardwares_remark_${hardwareCount}" class="form-control">
            </div>
            <button type="button" class="btn btn-danger" onclick="removeHardwareRow(this)">Remove</button>
            <hr>
        `;

        document.getElementById('hardware-rows').appendChild(hardwareRow);
    }

    function removeHardwareRow(button) {
        button.parentElement.remove();
    }

    function addSoftwareRow() {
        softwareCount++;

        const softwareRow = document.createElement('div');
        softwareRow.className = 'software-row';
        softwareRow.innerHTML = `
            <div class="form-group">
                <label for="software_type_${softwareCount}">Type</label>
                <select name="softwares[${softwareCount}][type]" id="software_type_${softwareCount}" class="form-control">
                    @foreach($softwares as $software)
                        <option value="{{ $software->id }}">{{ $software->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="software_license_${softwareCount}">License</label>
                <input type="text" name="softwares[${softwareCount}][license]" id="software_license_${softwareCount}" class="form-control">
            </div>
             <div class="form-group">
                <label for="softwares_name_${softwareCount}">Software Name</label>
                <input type="text" name="softwares[${softwareCount}][software_name]" id="softwares_name_${softwareCount}" class="form-control">
            </div>
            <div class="form-group">
                <label for="software_remark_${softwareCount}">Remark</label>
                <input type="text" name="softwares[${softwareCount}][remark]" id="software_remark_${softwareCount}" class="form-control">
            </div>
            <button type="button" class="btn btn-danger" onclick="removeSoftwareRow(this)">Remove</button>
            <hr>
        `;

        document.getElementById('software-rows').appendChild(softwareRow);
    }

    function removeSoftwareRow(button) {
        button.parentElement.remove();
    }
</script>
@endsection
