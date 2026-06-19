@extends('new.layouts.app')

@section('title', $title ?? 'System Settings')
@section('page-title', $pageTitle ?? 'System Settings')
@section('page-subtitle', $pageSubtitle ?? 'Manage your system configurations.')

@section('content')
<div class="flex flex-col md:flex-row gap-6 items-start">
    <!-- Inner Sidebar (Tabs) -->
    <div class="w-full md:w-[260px] flex-shrink-0 bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200/60 p-3 flex flex-col gap-1 md:sticky md:top-6 relative z-10">
        
        <div class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1 mb-1 flex items-center gap-2">
            <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div> Access Control
        </div>
        
        @can('user.view-any')
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-200 font-bold' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <div class="flex items-center justify-center w-7 h-7 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-blue-100' }} transition-colors">
                <i class="bx bx-user text-lg"></i>
            </div>
            <span class="text-sm tracking-tight pt-0.5">Users</span>
        </a>
        @endcan
        
        @can('role.view-any')
        <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.roles.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-200 font-bold' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <div class="flex items-center justify-center w-7 h-7 rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-blue-100' }} transition-colors">
                <i class="bx bx-key text-lg"></i>
            </div>
            <span class="text-sm tracking-tight pt-0.5">Roles</span>
        </a>
        @endcan

        @can('system.admin')
        <a href="{{ route('admin.permission-sync.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.permission-sync.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-200 font-bold' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <div class="flex items-center justify-center w-7 h-7 rounded-lg {{ request()->routeIs('admin.permission-sync.*') ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-blue-100' }} transition-colors">
                <i class="bx bx-refresh text-lg"></i>
            </div>
            <span class="text-sm tracking-tight pt-0.5">Permission Sync</span>
        </a>
        @endcan

        <div class="h-px bg-slate-100 my-2 mx-2"></div>

        <div class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1 mb-1 flex items-center gap-2">
            <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div> Organization
        </div>

        @can('department.view-any')
        <a href="{{ route('admin.departments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.departments.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-200 font-bold' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <div class="flex items-center justify-center w-7 h-7 rounded-lg {{ request()->routeIs('admin.departments.*') ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-blue-100' }} transition-colors">
                <i class="bx bx-building text-lg"></i>
            </div>
            <span class="text-sm tracking-tight pt-0.5">Departments</span>
        </a>
        @endcan

        @can('employee.view-any')
        <a href="{{ route('admin.employees.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.employees.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-200 font-bold' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <div class="flex items-center justify-center w-7 h-7 rounded-lg {{ request()->routeIs('admin.employees.*') ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-blue-100' }} transition-colors">
                <i class="bx bx-group text-lg"></i>
            </div>
            <span class="text-sm tracking-tight pt-0.5">Employees</span>
        </a>
        @endcan

        <div class="h-px bg-slate-100 my-2 mx-2"></div>

        <div class="px-3 py-2 text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1 mb-1 flex items-center gap-2">
            <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div> Workflows
        </div>

        @can('approval.manage-rules')
        <a href="{{ route('admin.approval-rules.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.approval-rules.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-200 font-bold' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <div class="flex items-center justify-center w-7 h-7 rounded-lg {{ request()->routeIs('admin.approval-rules.*') ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-blue-100' }} transition-colors">
                <i class="bx bx-check-shield text-lg"></i>
            </div>
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
