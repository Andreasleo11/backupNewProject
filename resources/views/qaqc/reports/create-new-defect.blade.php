@extends('layouts.app')

@section('content')


<body>
    <h1>Add New Category</h1>
    <form action="{{ route('qaqc.add.newdefect') }}" method="POST">
        @csrf
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name">
        <button type="submit">Add Category</button>
    </form>
</body>




@endsection