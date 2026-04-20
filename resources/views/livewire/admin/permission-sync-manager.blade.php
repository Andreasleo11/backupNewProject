<div class="p-6 bg-white rounded-lg shadow-xl">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Permission Synchronization Manager</h2>
            <p class="text-sm text-gray-600">Align your database roles and permissions with the PermissionRegistry.</p>
        </div>
        <div class="flex space-x-2">
            <button wire:click="$set('activeTab', 'compare')" 
                class="px-4 py-2 text-sm font-medium rounded-md {{ $activeTab === 'compare' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Sync Preview
            </button>
            <button wire:click="$set('activeTab', 'history')" 
                class="px-4 py-2 text-sm font-medium rounded-md {{ $activeTab === 'history' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                History & Reversion
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 mb-6 text-green-700 bg-green-100 border-l-4 border-green-500 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($activeTab === 'compare')
        <div class="space-y-6">
            <div class="flex items-center justify-between p-4 bg-blue-50 border border-blue-100 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-blue-800">Pending Changes</h3>
                        <p class="text-sm text-blue-600">Showing differences between the registry definition and current database state.</p>
                    </div>
                </div>
                <button 
                    wire:click="syncPermissions"
                    wire:loading.attr="disabled"
                    class="relative inline-flex items-center px-8 py-3 overflow-hidden text-white font-bold bg-[#14385e] rounded-xl group active:bg-blue-800 focus:outline-none transition-all duration-300 shadow-md hover:shadow-xl hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="absolute left-0 transition-all duration-300 -translate-x-full group-hover:translate-x-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </span>
                    <span class="transition-all duration-300 group-hover:translate-x-4" wire:loading.remove wire:target="syncPermissions">Apply Sync Now</span>
                    <span wire:loading wire:target="syncPermissions" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Working...
                    </span>
                </button>
            </div>

            @if(empty($managedChanges) && empty($unmanagedRoles))
                <div class="flex flex-col items-center justify-center py-12 text-center text-gray-500 border-2 border-dashed border-gray-200 rounded-xl">
                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-lg font-medium">Nothing to report!</p>
                    <p class="text-sm">Database is empty or perfectly synced with the registry.</p>
                </div>
            @else
                @if(!empty($managedChanges))
                    <div class="mb-4">
                        <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Managed Roles (From Registry)</h4>
                        <div class="grid gap-4">
                            @foreach($managedChanges as $role => $diff)
                                <div class="overflow-hidden border border-gray-200 rounded-lg bg-white shadow-sm">
                                    <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                                        <span class="font-bold text-gray-700">Role: {{ $role }}</span>
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 rounded-full uppercase">Managed</span>
                                    </div>
                                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <h5 class="mb-2 text-[10px] font-bold text-gray-400 uppercase">To be Added</h5>
                                            @forelse($diff['added'] as $perm)
                                                <div class="flex items-center mb-1 text-sm text-green-600 font-medium">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    {{ $perm }}
                                                </div>
                                            @empty
                                                <span class="text-xs text-gray-300 italic">No additions</span>
                                            @endforelse
                                        </div>
                                        <div>
                                            <h5 class="mb-2 text-[10px] font-bold text-gray-400 uppercase">To be Removed</h5>
                                            @forelse($diff['removed'] as $perm)
                                                <div class="flex items-center mb-1 text-sm text-red-600 font-medium line-through">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                    {{ $perm }}
                                                </div>
                                            @empty
                                                <span class="text-xs text-gray-300 italic">No removals</span>
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
                        <div class="flex items-center mb-3">
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Unmanaged Roles (Database Only)</h4>
                            <div class="ml-3 flex-grow border-t border-gray-200"></div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($unmanagedRoles as $role => $perms)
                                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg group hover:bg-white hover:shadow-md transition-all">
                                    <div class="flex items-center justify-between mb-2">
                                        <h5 class="font-bold text-gray-800">{{ $role }}</h5>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-[10px] text-gray-500 mb-2">These roles exist in the database but are not managed by the PermissionRegistry. They will not be affected by the sync.</p>
                                    <div class="flex flex-wrap gap-1">
                                        <span class="px-2 py-0.5 bg-gray-200 text-gray-600 rounded text-[10px]">{{ count($perms) }} Permissions</span>
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
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Triggered By</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Changes</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $log->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $log->user->name ?? 'System (CLI)' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="max-h-24 overflow-y-auto text-xs space-y-1">
                                    @foreach($log->changes as $role => $diff)
                                        <div>
                                            <span class="font-bold">{{ $role }}:</span> 
                                            <span class="text-green-600">+{{ count($diff['added']) }}</span>, 
                                            <span class="text-red-600">-{{ count($diff['removed']) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button 
                                        onclick="confirm('Are you sure you want to revert permissions to this snapshot? Current mapping will be replaced.') || event.stopImmediatePropagation()"
                                        wire:click="revert({{ $log->id }})" 
                                        class="text-amber-600 hover:text-amber-900 bg-amber-50 px-3 py-1 rounded border border-amber-200">
                                        Revert
                                    </button>
                                    <button 
                                        onclick="confirm('Delete this log entry?') || event.stopImmediatePropagation()"
                                        wire:click="deleteLog({{ $log->id }})" 
                                        class="text-red-400 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                No synchronization history found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
