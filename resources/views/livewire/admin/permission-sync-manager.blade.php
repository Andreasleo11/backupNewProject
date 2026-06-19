<div class="w-full space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Permission Synchronization</h1>
            <p class="mt-1 text-sm text-slate-500">Align your database roles and permissions with the PermissionRegistry.</p>
        </div>
        <div class="flex space-x-1 rounded-md bg-slate-100 p-1">
            <button wire:click="$set('activeTab', 'compare')" 
                class="px-3 py-1.5 text-sm font-medium rounded-sm transition-all {{ $activeTab === 'compare' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                Sync Preview
            </button>
            <button wire:click="$set('activeTab', 'history')" 
                class="px-3 py-1.5 text-sm font-medium rounded-sm transition-all {{ $activeTab === 'history' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                History
            </button>
        </div>
    </div>

    @if($activeTab === 'compare')
        <div class="space-y-6">
            <div class="flex items-center justify-between p-4 bg-white border border-slate-200 rounded-md">
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-slate-100 text-slate-600 mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-sm">Pending Changes</h3>
                        <p class="text-sm text-slate-500">Showing differences between the registry definition and current database state.</p>
                    </div>
                </div>
                <button 
                    wire:click="syncPermissions"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-50 bg-slate-900 rounded-md hover:bg-slate-900/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="syncPermissions" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Apply Sync Now
                    </span>
                    <span wire:loading wire:target="syncPermissions" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-slate-50" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Working...
                    </span>
                </button>
            </div>

            @if(empty($managedChanges) && empty($unmanagedRoles))
                <div class="flex flex-col items-center justify-center py-12 text-center text-slate-500 border border-slate-200 bg-slate-50/50 rounded-md">
                    <svg class="w-12 h-12 mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm font-semibold text-slate-900">Nothing to report!</p>
                    <p class="text-sm">Database is perfectly synced with the registry.</p>
                </div>
            @else
                @if(!empty($managedChanges))
                    <div class="mb-4">
                    <div class="mb-4">
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Managed Roles (From Registry)</h4>
                        <div class="grid gap-4">
                            @foreach($managedChanges as $role => $diff)
                                <div class="overflow-hidden border border-slate-200 rounded-md bg-white">
                                    <div class="px-4 py-2 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                                        <span class="text-sm font-semibold text-slate-900">Role: {{ $role }}</span>
                                        <span class="px-2 py-0.5 text-xs font-medium bg-blue-50 text-blue-700 rounded-md">Managed</span>
                                    </div>
                                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <h5 class="mb-2 text-xs font-medium text-slate-500 uppercase">To be Added</h5>
                                            @forelse($diff['added'] as $perm)
                                                <div class="flex items-center mb-1 text-sm text-emerald-600 font-medium">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    {{ $perm }}
                                                </div>
                                            @empty
                                                <span class="text-sm text-slate-400">No additions</span>
                                            @endforelse
                                        </div>
                                        <div>
                                            <h5 class="mb-2 text-xs font-medium text-slate-500 uppercase">To be Removed</h5>
                                            @forelse($diff['removed'] as $perm)
                                                <div class="flex items-center mb-1 text-sm text-red-600 font-medium line-through">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                    {{ $perm }}
                                                </div>
                                            @empty
                                                <span class="text-sm text-slate-400">No removals</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($unmanagedRoles))
                    <div class="mt-8">
                    <div class="mt-8">
                        <div class="flex items-center mb-4">
                            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Unmanaged Roles (Database Only)</h4>
                            <div class="ml-3 flex-grow border-t border-slate-200"></div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($unmanagedRoles as $role => $perms)
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-md">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="font-semibold text-slate-900 text-sm">{{ $role }}</h5>
                                        <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-3">These roles exist in the database but are not managed by the PermissionRegistry. They will not be affected by the sync.</p>
                                    <div class="flex flex-wrap gap-1">
                                        <span class="px-2 py-0.5 bg-slate-200 text-slate-700 font-medium rounded-sm text-xs">{{ count($perms) }} Permissions</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif

    @if($activeTab === 'history')
        <div class="rounded-md border border-slate-200 bg-white" x-data="{
            revertModalOpen: false,
            deleteModalOpen: false,
            selectedLogId: null,
            openRevert(id) {
                this.selectedLogId = id;
                this.revertModalOpen = true;
            },
            openDelete(id) {
                this.selectedLogId = id;
                this.deleteModalOpen = true;
            }
        }">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Triggered By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Changes</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $log->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $log->user->name ?? 'System (CLI)' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $log->description }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="max-h-24 overflow-y-auto text-xs space-y-1 text-slate-600">
                                        @foreach($log->changes as $role => $diff)
                                            <div>
                                                <span class="font-medium">{{ $role }}:</span> 
                                                <span class="text-emerald-600">+{{ count($diff['added']) }}</span>, 
                                                <span class="text-red-600">-{{ count($diff['removed']) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-3">
                                        <button @click="openRevert({{ $log->id }})" class="text-sm font-medium text-amber-600 hover:text-amber-700 transition-colors">
                                            Revert
                                        </button>
                                        <button @click="openDelete({{ $log->id }})" class="text-sm font-medium text-red-600 hover:text-red-700 transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                    No synchronization history found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- UX Guardrail: Revert Modal --}}
            <div x-show="revertModalOpen" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div x-show="revertModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="revertModalOpen" @click.away="revertModalOpen = false" x-transition
                            class="relative transform overflow-hidden rounded-md bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                        <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Revert Permissions</h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-slate-500">Are you sure you want to revert permissions to this snapshot? The current mapping will be replaced. This action creates a new log entry.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="button" 
                                    @click="$wire.revert(selectedLogId); revertModalOpen = false"
                                    class="inline-flex w-full justify-center rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 sm:ml-3 sm:w-auto transition-colors">
                                    Yes, Revert
                                </button>
                                <button type="button" @click="revertModalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- UX Guardrail: Delete Modal --}}
            <div x-show="deleteModalOpen" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div x-show="deleteModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="deleteModalOpen" @click.away="deleteModalOpen = false" x-transition
                            class="relative transform overflow-hidden rounded-md bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                        <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Delete Sync Log</h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-slate-500">Are you sure you want to delete this log entry? This action cannot be undone.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="button" 
                                    @click="$wire.deleteLog(selectedLogId); deleteModalOpen = false"
                                    class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-colors">
                                    Yes, Delete
                                </button>
                                <button type="button" @click="deleteModalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
