@extends('layouts.app')

@section('content')
    <div class="container py-4">
        @livewire('master-data-part.import-parts') {{-- left: upload + progress --}}
        @livewire('master-data-part.import-jobs-list') {{-- right: history table --}} </div>
@endsection
