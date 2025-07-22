@extends('layouts.app')
@section('title', 'Destination Suggestions')

@section('content')
    <div class="container mt-4">
        <h2>Manage Destination Suggestions</h2>

        @include('partials.alert-success-error')

        <a href="{{ route('destinations.create') }}" class="btn btn-primary mb-3">+ Add Destination</a>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($destinations as $destination)
                    <tr>
                        <td>{{ $destination->code }}</td>
                        <td>{{ $destination->name }}</td>
                        <td>{{ $destination->city }}</td>
                        <td>{{ $destination->description }}</td>
                        <td>
                            <a href="{{ route('destinations.edit', $destination->id) }}"
                                class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('destinations.destroy', $destination->id) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Delete this destination?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
