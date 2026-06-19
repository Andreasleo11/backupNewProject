@extends('new.layouts.app')

@section('title', $title ?? 'System Settings')
@section('page-title', $pageTitle ?? 'System Settings')
@section('page-subtitle', $pageSubtitle ?? 'Manage your system configurations.')

@section('content')
<div class="flex flex-col md:flex-row gap-6 items-start">
    <!-- Inner Sidebar (Tabs) -->
    <div class="w-full md:w-[260px] flex-shrink-0 bg-white rounded-md border border-slate-200 p-3 flex flex-col gap-1 md:sticky md:top-6 relative z-10">
        
        <div class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1 mb-1">
            Access Control
        </div>
        
        @can('system.admin')
        <a href="{{ route('admin.access-overview.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors group {{ request()->routeIs('admin.access-overview.*') ? 'bg-slate-900 text-slate-50 font-medium' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i class="bx bx-shield-quarter text-lg"></i>
            <span class="text-sm tracking-tight pt-0.5">Access Overview</span>
        </a>
        @endcan
        
        @can('user.view-any')
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors group {{ request()->routeIs('admin.users.*') ? 'bg-slate-900 text-slate-50 font-medium' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i class="bx bx-user text-lg"></i>
            <span class="text-sm tracking-tight pt-0.5">Users</span>
        </a>
        @endcan
        
        @can('role.view-any')
        <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors group {{ request()->routeIs('admin.roles.*') ? 'bg-slate-900 text-slate-50 font-medium' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i class="bx bx-key text-lg"></i>
            <span class="text-sm tracking-tight pt-0.5">Roles</span>
        </a>
        @endcan

        @can('system.admin')
        <a href="{{ route('admin.permission-sync.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors group {{ request()->routeIs('admin.permission-sync.*') ? 'bg-slate-900 text-slate-50 font-medium' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i class="bx bx-refresh text-lg"></i>
            <span class="text-sm tracking-tight pt-0.5">Permission Sync</span>
        </a>
        @endcan

        <div class="h-px bg-slate-100 my-2 mx-2"></div>

        <div class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1 mb-1">
            Organization
        </div>

        @can('department.view-any')
        <a href="{{ route('admin.departments.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors group {{ request()->routeIs('admin.departments.*') ? 'bg-slate-900 text-slate-50 font-medium' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i class="bx bx-building text-lg"></i>
            <span class="text-sm tracking-tight pt-0.5">Departments</span>
        </a>
        @endcan

        @can('employee.view-any')
        <a href="{{ route('admin.employees.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors group {{ request()->routeIs('admin.employees.*') ? 'bg-slate-900 text-slate-50 font-medium' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i class="bx bx-group text-lg"></i>
            <span class="text-sm tracking-tight pt-0.5">Employees</span>
        </a>
        @endcan

        <div class="h-px bg-slate-100 my-2 mx-2"></div>

        <div class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1 mb-1">
            Workflows
        </div>

        @can('approval.manage-rules')
        <a href="{{ route('admin.approval-rules.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors group {{ request()->routeIs('admin.approval-rules.*') ? 'bg-slate-900 text-slate-50 font-medium' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i class="bx bx-check-shield text-lg"></i>
            <span class="text-sm tracking-tight pt-0.5">Approval Rules</span>
        </a>
        @endcan
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 min-w-0">
        {{ $slot ?? '' }}
        @yield('settings-content')
    </div>
</div>
@endsection
