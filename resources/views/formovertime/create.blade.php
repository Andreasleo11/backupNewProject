@extends('layouts.app')
@section('title', 'Create Overtime Form - ' . env('APP_NAME'))

@section('content')
    @livewire('overtime.create')
@endsection
