@extends('new.layouts.admin-settings')

@section('title', 'Approval Rules')

@section('page-title', 'Approval Rules Configuration')
@section('page-subtitle', 'Manage system-wide workflow approval rules.')

@section('settings-content')
    <livewire:admin.approvals.rule-manager />
@endsection
