<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30 p-6">

    {{-- Page Header --}}
    <div class="max-w-7xl mx-auto mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Nav Visibility Manager</h1>
            <p class="text-sm text-slate-500 mt-0.5">Control which users can see each menu item.</p>
        </div>
        {{-- Tab switcher --}}
        <div class="flex gap-1 bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
            <button wire:click="$set('tab', 'menu')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-all
                           {{ $tab === 'menu' ? 'bg-blue-600 text-white shadow' : 'text-slate-500 hover:text-slate-800' }}">
                Menu Items
            </button>
            <button wire:click="$set('tab', 'groups')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-all
                           {{ $tab === 'groups' ? 'bg-blue-600 text-white shadow' : 'text-slate-500 hover:text-slate-800' }}">
                User Groups
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto">

        {{-- ══ TAB: Menu Items ══════════════════════════════════════════ --}}
        @if($tab === 'menu')
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- Left: Menu item list --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Menu Items</p>
                </div>
                <div class="overflow-y-auto max-h-[70vh] divide-y divide-slate-100">
                    @php $currentGroup = null; @endphp
                    @foreach($menuItems as $mi)
                        @if($mi['group'] !== $currentGroup)
                            @php $currentGroup = $mi['group']; @endphp
                            @if($currentGroup)
                            <div class="px-4 py-2 bg-slate-50 sticky top-0 z-10">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $currentGroup }}</span>
                            </div>
                            @endif
                        @endif
                        <button wire:click="selectRoute('{{ $mi['route'] }}', '{{ addslashes($mi['label']) }}')"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm text-left transition-colors
                                       {{ $selectedRoute === $mi['route'] ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                            <span class="truncate">{{ $mi['label'] }}</span>
                            <div class="flex items-center gap-2 shrink-0 ml-2">
                                @if(in_array($mi['route'], $managedRoutes))
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 bg-blue-100 text-blue-600 rounded">DB</span>
                                @else
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 bg-slate-100 text-slate-400 rounded">default</span>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Right: Assignment panel --}}
            <div class="lg:col-span-3 space-y-4">
                @if($selectedRoute)
                    {{-- Current assignments --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Assignments for</p>
                                <p class="text-sm font-black text-slate-900 mt-0.5">{{ $selectedLabel }}</p>
                                <code class="text-[10px] text-slate-400">{{ $selectedRoute }}</code>
                            </div>
                            @if(in_array($selectedRoute, $managedRoutes))
                                <span class="text-xs bg-blue-100 text-blue-700 font-bold px-2 py-1 rounded-lg">DB-managed</span>
                            @else
                                <span class="text-xs bg-amber-100 text-amber-700 font-bold px-2 py-1 rounded-lg">Hardcoded roles</span>
                            @endif
                        </div>

                        @if($assignments->isEmpty())
                            <p class="px-4 py-5 text-sm text-slate-400 italic">
                                No DB assignments — menu item uses its hardcoded <code>roles[]</code>.<br>
                                Add an assignment below to switch it to DB-managed.
                            </p>
                        @else
                            <ul class="divide-y divide-slate-100">
                                @foreach($assignments as $a)
                                    <li class="flex items-center justify-between px-4 py-3">
                                        <span class="text-sm text-slate-700">{{ $a['label'] }}</span>
                                        <button wire:click="removeAssignment({{ $a['id'] }})"
                                                wire:confirm="Remove this assignment?"
                                                class="text-rose-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Add assignment form --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Add Assignment</p>

                        {{-- Subject type selector --}}
                        <div class="flex gap-2 mb-4">
                            @foreach(['user' => 'User', 'role' => 'Role', 'permission' => 'Permission', 'group' => 'User Group'] as $val => $label)
                                <button wire:click="$set('subjectType', '{{ $val }}')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all
                                               {{ $subjectType === $val ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-200 text-slate-500 hover:border-blue-300' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Search input --}}
                        <div class="relative mb-3">
                            <input type="text"
                                   wire:model.live.debounce.300ms="subjectSearch"
                                   placeholder="Search {{ $subjectType }}..."
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                        </div>

                        {{-- Suggestions --}}
                        @if($subjectSuggestions->isNotEmpty())
                            <ul class="border border-slate-200 rounded-xl overflow-hidden divide-y divide-slate-100 mb-3">
                                @foreach($subjectSuggestions as $s)
                                    <li>
                                        <button wire:click="$set('subjectId', {{ $s['id'] }}); $set('subjectSearch', '{{ addslashes($s['label']) }}')"
                                                class="w-full text-left px-4 py-2.5 text-sm transition-colors
                                                       {{ $subjectId === $s['id'] ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-50 text-slate-700' }}">
                                            {{ $s['label'] }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <button wire:click="addAssignment"
                                @disabled(!$subjectId)
                                class="w-full py-2.5 rounded-xl font-bold text-sm transition-all
                                       {{ $subjectId ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                            Add Assignment
                        </button>

                        @error('subjectId') <p class="text-xs text-rose-500 mt-2">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div class="flex items-center justify-center h-48 bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <p class="text-slate-400 text-sm">← Select a menu item to manage its visibility</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ══ TAB: User Groups ═════════════════════════════════════════ --}}
        @elseif($tab === 'groups')
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- Left: Group list + create --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Create group --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">New Group</p>
                    <input type="text" wire:model="newGroupName" placeholder="Group name"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 mb-2">
                    <input type="text" wire:model="newGroupDesc" placeholder="Description (optional)"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 mb-3">
                    <button wire:click="createGroup"
                            class="w-full py-2.5 rounded-xl font-bold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        Create Group
                    </button>
                    @error('newGroupName') <p class="text-xs text-rose-500 mt-2">{{ $message }}</p> @enderror
                </div>

                {{-- Group list --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Groups</p>
                    </div>
                    @forelse($groups as $group)
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 last:border-0">
                            <button wire:click="editGroup({{ $group->id }})"
                                    class="text-left flex-1 {{ $editingGroupId === $group->id ? 'text-blue-700 font-bold' : 'text-slate-700' }}">
                                <span class="text-sm">{{ $group->name }}</span>
                                <span class="text-xs text-slate-400 ml-2">{{ $group->users_count }} member{{ $group->users_count !== 1 ? 's' : '' }}</span>
                            </button>
                            <button wire:click="deleteGroup({{ $group->id }})"
                                    wire:confirm="Delete group '{{ $group->name }}'?"
                                    class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="px-4 py-5 text-sm text-slate-400 italic">No groups yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Right: Member management --}}
            <div class="lg:col-span-3">
                @if($editingGroup)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Members of</p>
                            <p class="text-sm font-black text-slate-900 mt-0.5">{{ $editingGroup->name }}</p>
                        </div>

                        {{-- Current members --}}
                        <ul class="divide-y divide-slate-100 max-h-48 overflow-y-auto">
                            @forelse($editingGroup->users as $member)
                                <li class="flex items-center justify-between px-4 py-3">
                                    <div>
                                        <span class="text-sm font-semibold text-slate-800">{{ $member->name }}</span>
                                        <span class="text-xs text-slate-400 ml-2">{{ $member->email }}</span>
                                    </div>
                                    <button wire:click="removeMember({{ $editingGroup->id }}, {{ $member->id }})"
                                            class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </li>
                            @empty
                                <li class="px-4 py-5 text-sm text-slate-400 italic">No members yet.</li>
                            @endforelse
                        </ul>

                        {{-- Add member --}}
                        <div class="p-4 border-t border-slate-100 bg-slate-50/40">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Add Member</p>
                            <input type="text" wire:model.live.debounce.300ms="memberSearch"
                                   placeholder="Search users by name or email..."
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 mb-2">
                            @if($memberCandidates->isNotEmpty())
                                <ul class="border border-slate-200 rounded-xl overflow-hidden divide-y divide-slate-100">
                                    @foreach($memberCandidates as $candidate)
                                        <li>
                                            <button wire:click="addMember({{ $candidate->id }})"
                                                    class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                                {{ $candidate->name }}
                                                <span class="text-slate-400 text-xs ml-1">{{ $candidate->email }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-center h-48 bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <p class="text-slate-400 text-sm">← Select a group to manage its members</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
