@extends('layouts.app')

@section('content')


<body>
    <div class="row justify-content-center">
        <div class="col-auto">
            <h1>Add New Category</h1>
            <form action="{{ route('qaqc.add.newdefect') }}" method="POST">
                @csrf
                <label for="category_name">Category Name:</label>
                <input type="text" id="category_name" name="category_name">
                <button type="submit">Add Category</button>
            </form>
        </div>
        <div class="col">
             <h1>All Defects</h1>
            <table class="table">   
            <thead>
                <tr>
                    <th>#</th>
                    <th>Defect Name</th>
                            <!-- Add more columns as needed -->
                </tr>
            </thead>
            <tbody>
                @foreach($defectcat as $defect)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $defect->name }}</td>
                                <!-- Add more columns as needed -->
                    </tr>
                @endforeach
            </tbody>
            </table>   
        </div>
    </div>
    

    

</body>

  



@endsection