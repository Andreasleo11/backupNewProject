@extends('new.layouts.admin-settings')

@section('title', 'Create Role')

@section('page-title', 'Create Role')
@section('page-subtitle', 'Define a new role and assign its system permissions.')

@section('settings-content')
    <livewire:admin.roles.role-create />
@endsection
