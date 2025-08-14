@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Upload Excel Actual Overtime</h2>

    @if(session('success'))
        <div class="p-2 bg-green-200 text-green-800 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-2 bg-red-200 text-red-800 rounded mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('actual.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".xlsx,.xls" required class="mb-4 block w-full">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Upload</button>
    </form>
</div>
@endsection
