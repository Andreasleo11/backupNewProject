@extends('new.layouts.admin-settings')

@section('title', 'Users')

@section('page-title', 'Users')
@section('page-subtitle', 'Manage application users, roles, and status.')

@section('settings-content')
    <livewire:admin.users.user-index />
@endsection
