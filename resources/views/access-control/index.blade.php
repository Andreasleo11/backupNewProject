@extends('layouts.app')
@pushOnce('extraCss')
    <script src="https://cdn.tailwindcss.com"></script>
@endPushOnce
@section('content')
    <div class="max-w-6xl mx-auto px-4 py-6">
        {{-- Page header --}}
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Access Control</h1>
                <p class="mt-1 text-xs text-slate-500">
                    Manage roles, permissions, and user assignments in one place.
                </p>
            </div>

            {{-- Optional: quick stats / badge --}}
            <div class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-medium text-slate-600">
                <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Access control module active
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ tab: '{{ request('tab', 'roles') }}' }">
            <div class="mb-4 border-b border-slate-200">
                <nav class="-mb-px flex space-x-4 text-sm">
                    <button type="button" @click="tab = 'roles'"
                        :class="tab === 'roles'
                            ?
                            'border-indigo-500 text-indigo-600' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1 font-medium">
                        Roles
                    </button>

                    <button type="button" @click="tab = 'users'"
                        :class="tab === 'users'
                            ?
                            'border-indigo-500 text-indigo-600' :
                            'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1 font-medium">
                        Users
                    </button>
                </nav>
            </div>

            {{-- Tab contents --}}
            <div x-show="tab === 'roles'" x-cloak>
                <livewire:admin.roles.role-index />
            </div>

            <div x-show="tab === 'users'" x-cloak>
                <livewire:admin.users.user-index />
            </div>
        </div>
    </div>
@endsection
