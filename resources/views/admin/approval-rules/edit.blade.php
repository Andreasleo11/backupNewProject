@extends('new.layouts.admin-settings')

@section('title', 'Rule Workflow Builder')

@section('page-title', 'Rule Workflow Builder')
@section('page-subtitle', 'Design and sequence approval steps.')

@section('settings-content')
    <livewire:admin.approvals.rule-builder :ruleId="$id" />
@endsection
