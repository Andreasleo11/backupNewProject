@extends('new.layouts.admin-settings')

@section('title', 'Permission Sync')

@section('page-title', 'Permission Synchronization')
@section('page-subtitle', 'Align your database roles and permissions with the PermissionRegistry.')

@section('settings-content')
    <livewire:admin.permission-sync-manager />
@endsection
