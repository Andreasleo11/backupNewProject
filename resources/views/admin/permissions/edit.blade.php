@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <h1>Edit Permission</h1>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <form action="{{ route('superadmin.permissions.updatePermission', $permission->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="permission-name" class="form-label">Permission Name</label>
                        <input type="text" class="form-control" id="permission-name" name="name"
                            value="{{ $permission->name }}" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
@endsection
