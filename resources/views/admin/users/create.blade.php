@extends('new.layouts.admin-settings')

@section('title', 'Create User')

@section('page-title', 'Create User')
@section('page-subtitle', 'Add a new user to the system and assign roles.')

@section('settings-content')
    <livewire:admin.users.user-create />
@endsection
