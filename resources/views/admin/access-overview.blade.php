@extends('new.layouts.app')

@section('title', 'Access Overview')

@section('page-title', 'Access Overview')
@section('page-subtitle', 'Overview of users, roles, and permissions.')

@section('content')
    <div class="max-w-6xl mx-auto space-y-4">
        {{-- Top summary / description --}}
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">
                Access Overview
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                Manage how users can access the system. Use the navigation on the left to edit users, roles,
                and permissions. This page can be your “control center” for access-related tools.
            </p>
        </div>

        {{-- Slot for your existing content / widgets --}}
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            {{-- Example: replace this block with your old access-control content --}}
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-900">
                    Quick links
                </h3>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 text-xs">
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:bg-slate-100">
                    <div>
                        <div class="font-semibold text-slate-900">Users</div>
                        <div class="text-[11px] text-slate-500">Manage user accounts and status.</div>
                    </div>
                    <span class="text-slate-400 text-lg">→</span>
                </a>

                <a href="{{ route('admin.roles.index') }}" {{-- adjust if needed --}}
                   class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:bg-slate-100">
                    <div>
                        <div class="font-semibold text-slate-900">Roles</div>
                        <div class="text-[11px] text-slate-500">Define role-based access.</div>
                    </div>
                    <span class="text-slate-400 text-lg">→</span>
                </a>

                <a href="#" class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 hover:bg-slate-100">
                    <div>
                        <div class="font-semibold text-slate-900">Permissions</div>
                        <div class="text-[11px] text-slate-500">(Future) granular permission management.</div>
                    </div>
                    <span class="text-slate-400 text-lg">→</span>
                </a>
            </div>
        </div>
    </div>
@endsection
