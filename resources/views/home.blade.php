@extends('new.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
            <div class="text-sm text-slate-500">
                Welcome back, {{ auth()->user()->name }}!
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Recent Activities Widget -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900">Recent Activities</h2>
                </div>
                <div class="p-6">
                    <p class="text-slate-600">No recent activities.</p>
                    <!-- TODO: Integrate with activity log -->
                </div>
            </div>

            <!-- Quick Stats Widget -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900">Quick Stats</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Department:</span>
                            <span class="font-medium">{{ auth()->user()->department->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Role:</span>
                            <span class="font-medium">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Widget -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900">Notifications</h2>
                </div>
                <div class="p-6">
                    <div x-data="notificationsWidget()" class="space-y-2">
                        <div x-show="unreadCount > 0" class="text-sm text-blue-600">
                            You have <span x-text="unreadCount"></span> unread notifications.
                        </div>
                        <button @click="markAllRead" class="text-sm text-blue-500 hover:text-blue-700">
                            Mark all as read
                        </button>
                    </div>
                    <script>
                        function notificationsWidget() {
                            return {
                                unreadCount: 0,
                                init() {
                                    this.fetchUnreadCount();
                                },
                                fetchUnreadCount() {
                                    fetch('/notifications/unread-count')
                                        .then(response => response.json())
                                        .then(data => this.unreadCount = data.unread);
                                },
                                markAllRead() {
                                    fetch('/notifications/mark-read', { method: 'POST' })
                                        .then(() => this.unreadCount = 0);
                                }
                            }
                        }
                    </script>
                </div>
            </div>
        </div>

        <!-- Department-Specific Content -->
        @if(auth()->user()->department)
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900">{{ auth()->user()->department->name }} Overview</h2>
                </div>
                <div class="p-6">
                    <p class="text-slate-600">Department-specific dashboard content can be added here.</p>
                    <!-- TODO: Add department-specific widgets -->
                </div>
            </div>
        @endif
    </div>
@endsection
