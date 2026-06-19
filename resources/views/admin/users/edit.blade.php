@extends('new.layouts.admin-settings')

@section('title', 'Edit User')

@section('page-title', 'Edit User')
@section('page-subtitle', 'Modify user details and roles.')

@section('settings-content')
    <livewire:admin.users.user-edit :userId="$id" />
@endsection
