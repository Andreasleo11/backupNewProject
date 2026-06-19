@extends('new.layouts.admin-settings')

@section('title', 'Create Approval Rule')

@section('page-title', 'Create Approval Rule')
@section('page-subtitle', 'Define a new system-wide workflow approval rule.')

@section('settings-content')
    <livewire:admin.approvals.rule-create />
@endsection
