<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Nav Visibility</h1>
            <p class="text-slate-500 font-medium">Manage menu access by User, Role, or Permission.</p>
        </div>
        
        {{-- Premium Tab Switcher --}}
        <div class="flex p-1 bg-white/60 backdrop-blur-md border border-white/40 rounded-xl shadow-sm">
            <button wire:click="$set('tab', 'menu')"
                    class="relative px-6 py-2.5 text-sm font-bold rounded-lg transition-all duration-300
                           {{ $tab === 'menu' ? 'text-blue-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700' }}">
                @if($tab === 'menu')
                    <div class="absolute inset-0 bg-white rounded-lg shadow-sm" style="z-index: -1;"></div>
                @endif
                Menu Items
            </button>
            <button wire:click="$set('tab', 'groups')"
                    class="relative px-6 py-2.5 text-sm font-bold rounded-lg transition-all duration-300
                           {{ $tab === 'groups' ? 'text-blue-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700' }}">
                @if($tab === 'groups')
                    <div class="absolute inset-0 bg-white rounded-lg shadow-sm" style="z-index: -1;"></div>
                @endif
                User Groups
            </button>
        </div>
    </div>

    {{-- TAB: Menu Items --}}
    @if($tab === 'menu')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- Left: Menu List --}}
        <div class="lg:col-span-4 space-y-4 sticky top-6">
            {{-- Search --}}
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="menuSearch"
                       placeholder="Search menu items..."
                       class="block w-full pl-10 pr-3 py-3 border-0 bg-white/60 backdrop-blur-xl rounded-2xl text-slate-900 shadow-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all placeholder:text-slate-400 font-medium">
            </div>

            {{-- List --}}
            <div class="glass-card overflow-hidden flex flex-col max-h-[calc(100vh-12rem)]">
                <div class="overflow-y-auto custom-scrollbar p-2 space-y-0.5">
                    @php $currentGroup = null; @endphp
                    @foreach($menuItems as $mi)
                        @if($mi['group'] !== $currentGroup && $mi['group'] !== null)
                            @php $currentGroup = $mi['group']; @endphp
                            <div class="sticky top-0 z-10 px-3 py-2 mt-2 first:mt-0 bg-white/95 backdrop-blur-md border-b border-slate-100/50 shadow-sm mb-1 rounded-lg">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $currentGroup }}</span>
                            </div>
                        @endif

                        <button wire:click="selectRoute('{{ $mi['route'] }}', '{{ addslashes($mi['label']) }}')"
                                class="w-full group flex items-start text-left p-3 rounded-xl transition-all duration-200 border border-transparent
                                       {{ $selectedRoute === $mi['route'] 
                                            ? 'bg-blue-50/80 border-blue-100 shadow-sm ring-1 ring-blue-200' 
                                            : 'hover:bg-white/60 hover:border-slate-100 hover:shadow-sm' }}">
                            
                            {{-- Icon / Label --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold truncate transition-colors {{ $selectedRoute === $mi['route'] ? 'text-blue-700' : 'text-slate-700 group-hover:text-slate-900' }}">
                                    {{ $mi['label'] }}
                                </p>
                                <p class="text-[10px] font-medium truncate {{ $selectedRoute === $mi['route'] ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-500' }}">
                                    {{ $mi['route'] }}
                                </p>
                            </div>

                            {{-- Badges --}}
                            <div class="flex flex-col items-end gap-1 shrink-0 ml-2">
                                @if(in_array($mi['route'], $managedRoutes))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-700">
                                        Managed
                                    </span>
                                @endif
                                @if(isset($mi['assignment_count']) && $mi['assignment_count'] > 0)
                                    <span class="inline-flex items-center justify-center h-4 min-w-[1rem] px-1 rounded-full text-[9px] font-bold bg-slate-200 text-slate-600">
                                        {{ $mi['assignment_count'] }}
                                    </span>
                                @endif
                            </div>
                        </button>
                    @endforeach
                    
                    @if(count($menuItems) === 0)
                        <div class="text-center py-8 px-4">
                            <div class="inline-flex p-3 rounded-full bg-slate-50 mb-3 text-slate-300">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-slate-500">No menu items found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Assignments --}}
        <div class="lg:col-span-8">
            @if($selectedRoute)
                <div class="space-y-6" x-data="{ adding: true }">
                    
                    {{-- Selected Item Header --}}
                    <div class="glass-panel p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30 text-white shrink-0">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 leading-tight">{{ $selectedLabel }}</h2>
                                <code class="text-xs font-mono text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded mt-1 inline-block">{{ $selectedRoute }}</code>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Visibility</p>
                                <p class="text-sm font-bold text-slate-700">
                                    {{ in_array($selectedRoute, $managedRoutes) ? 'Restricted (DB)' : 'Default (Code)' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Assignments List --}}
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-1 border border-slate-200/60 shadow-sm min-h-[300px] flex flex-col">
                            <div class="px-4 py-3 border-b border-slate-100/50 flex items-center justify-between">
                                <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest">Visible To</h3>
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $assignments->count() }}</span>
                            </div>
                            
                            @if($assignments->isEmpty())
                                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <svg class="h-8 w-8 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600">No explicit overrides</p>
                                    <p class="text-xs mt-1 max-w-[200px] mx-auto">This menu item is visible to users based on the hardcoded `roles` array in `NavigationService`.</p>
                                </div>
                            @else
                                <ul class="divide-y divide-slate-100">
                                    @foreach($assignments as $a)
                                        <li class="flex items-center justify-between px-4 py-3 hover:bg-slate-50/50 transition-colors group">
                                            <div class="flex items-center gap-3">
                                                @php
                                                    $isUser = str_contains($a['label'], '(user)');
                                                    $isRole = str_contains($a['label'], '(role)');
                                                    $isGrp  = str_contains($a['label'], '(group)');
                                                    $icon   = match(true) {
                                                        $isUser => 'user',
                                                        $isRole => 'key',
                                                        $isGrp  => 'users',
                                                        default => 'shield-check'
                                                    };
                                                    $color = match(true) {
                                                        $isUser => 'bg-emerald-100 text-emerald-600',
                                                        $isRole => 'bg-purple-100 text-purple-600',
                                                        $isGrp  => 'bg-blue-100 text-blue-600',
                                                        default => 'bg-amber-100 text-amber-600'
                                                    };
                                                @endphp
                                                <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ $color }}">
                                                    {{-- Heroicons mini --}}
                                                    @if($isUser) <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                    @elseif($isRole) <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11.536 17 10 18.465V20l-2 2-2-2v-2.5l5.536-5.536A6 6 0 0119 9z" /></svg>
                                                    @elseif($isGrp) <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                    @else <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    @endif
                                                </div>
                                                <span class="text-sm font-semibold text-slate-700">{{ $a['label'] }}</span>
                                            </div>
                                            
                                            <button wire:click="removeAssignment({{ $a['id'] }})"
                                                    wire:confirm="Remove this assignment?"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 opacity-0 group-hover:opacity-100 transition-all">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- Add Assignment Form --}}
                        <div class="glass-card p-5">
                            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Grant Access</h3>
                            
                            {{-- Type Selector --}}
                            <div class="grid grid-cols-4 gap-2 mb-4">
                                @foreach(['role' => 'Role', 'user' => 'User', 'group' => 'Group', 'permission' => 'Perm'] as $val => $label)
                                    <button wire:click="$set('subjectType', '{{ $val }}')"
                                            class="text-xs font-bold py-2 rounded-lg border transition-all
                                                   {{ $subjectType === $val ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white border-slate-200 text-slate-500 hover:border-blue-300 hover:text-blue-600' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>

                            {{-- Search --}}
                            <div class="relative mb-3">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </span>
                                <input type="text"
                                       wire:model.live.debounce.300ms="subjectSearch"
                                       placeholder="Find {{ $subjectType }}..."
                                       class="w-full pl-9 px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 font-medium">
                            </div>

                            {{-- Results --}}
                            @if($subjectSuggestions->isNotEmpty())
                                <div class="max-h-40 overflow-y-auto custom-scrollbar border border-slate-200 rounded-xl mb-3 divide-y divide-slate-100 bg-white shadow-sm">
                                    @foreach($subjectSuggestions as $s)
                                        <button wire:click="$set('subjectId', {{ $s['id'] }}); $set('subjectSearch', '{{ addslashes($s['label']) }}')"
                                                class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between
                                                       {{ $subjectId === $s['id'] ? 'bg-blue-50 text-blue-700 font-bold' : 'hover:bg-slate-50 text-slate-700' }}">
                                            <span>{{ $s['label'] }}</span>
                                            @if($subjectId === $s['id'])
                                                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @elseif($subjectSearch && !$subjectId)
                                <p class="text-xs text-slate-400 mb-3 ml-1 italic">No matches found.</p>
                            @endif

                            <button wire:click="addAssignment"
                                    @disabled(!$subjectId)
                                    class="w-full py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm
                                           {{ $subjectId ? 'bg-slate-900 text-white hover:bg-black hover:shadow-md' : 'bg-slate-100 text-slate-300 cursor-not-allowed' }}">
                                Add Assignment
                            </button>
                            @error('subjectId') <p class="text-xs text-rose-500 mt-2 font-bold">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="h-full min-h-[400px] flex flex-col items-center justify-center p-8 text-center text-slate-400 bg-white/40 border border-dashed border-slate-300 rounded-3xl">
                    <div class="h-24 w-24 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                        <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-600">Select a Menu Item</h3>
                    <p class="text-sm max-w-xs mx-auto mt-1 text-slate-500">Choose an item from the sidebar to manage explicit visibility rules for users, roles, or groups.</p>
                </div>
            @endif
        </div>
    </div>


    {{-- TAB: User Groups --}}
    @elseif($tab === 'groups')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Left: Groups Grid --}}
        <div class="lg:col-span-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800">All Groups</h3>
                
                {{-- New Group Form (Inline for speed) --}}
                <div class="flex gap-2" x-data="{ expanded: false }">
                    <div x-show="expanded" x-transition class="flex gap-2">
                        <input type="text" wire:model="newGroupName" placeholder="Group Name" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <input type="text" wire:model="newGroupDesc" placeholder="Description" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <button wire:click="createGroup" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700">Save</button>
                    </div>
                    <button @click="expanded = !expanded" class="px-4 py-1.5 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-black shadow-sm flex items-center gap-2">
                        <span x-text="expanded ? 'Cancel' : '+ New Group'"></span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($groups as $group)
                    <div class="glass-card p-5 group relative transition-all hover:shadow-md border border-slate-200/60
                                {{ $editingGroupId === $group->id ? 'ring-2 ring-blue-500 border-transparent shadow-md' : 'hover:border-blue-200' }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-slate-800">{{ $group->name }}</h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $group->description ?: 'No description' }}</p>
                            </div>
                            <div class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 text-xs font-bold">
                                {{ $group->users_count }}
                            </div>
                        </div>
                        
                        <div class="mt-4 flex items-center gap-2">
                            <button wire:click="editGroup({{ $group->id }})" 
                                    class="text-xs font-bold px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                Manage Members
                            </button>
                            <button wire:click="deleteGroup({{ $group->id }})" 
                                    wire:confirm="Delete group '{{ $group->name }}'?"
                                    class="text-xs font-bold px-3 py-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors opacity-0 group-hover:opacity-100">
                                Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-400 italic bg-white/40 rounded-2xl border border-dashed border-slate-300">
                        No groups created yet.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Right: Member Manager --}}
        <div class="lg:col-span-4">
            @if($editingGroup)
                <div class="glass-panel p-5 sticky top-6">
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Managing</p>
                            <h3 class="text-lg font-black text-slate-900">{{ $editingGroup->name }}</h3>
                        </div>
                        <button wire:click="$set('editingGroupId', null)" class="text-slate-400 hover:text-slate-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Add Member --}}
                    <div class="mb-4">
                        <label class="text-xs font-bold text-slate-500 mb-1 block">Add User</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="memberSearch" placeholder="Search by name..."
                                   class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            @if($memberCandidates->isNotEmpty())
                                <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-20 max-h-48 overflow-y-auto">
                                    @foreach($memberCandidates as $candidate)
                                        <button wire:click="addMember({{ $candidate->id }})"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50 hover:text-blue-700 flex justify-between items-center group">
                                            <span>{{ $candidate->name }}</span>
                                            <span class="text-xs text-slate-400 group-hover:text-blue-400">Add +</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- List --}}
                    <div class="space-y-1 max-h-[60vh] overflow-y-auto custom-scrollbar pr-1">
                        @forelse($editingGroup->users as $member)
                            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-white/60 group border border-transparent hover:border-slate-100 transition-all">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold shadow-sm shrink-0">
                                        {{ substr($member->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-700 truncate">{{ $member->name }}</p>
                                        <p class="text-[10px] text-slate-400 truncate">{{ $member->email }}</p>
                                    </div>
                                </div>
                                <button wire:click="removeMember({{ $editingGroup->id }}, {{ $member->id }})"
                                        class="p-1.5 text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        @empty
                            <div class="text-center py-6 text-slate-400 text-sm italic">
                                No members in this group.
                            </div>
                        @endforelse
                    </div>

                </div>
            @else
                <div class="h-full flex items-center justify-center text-center p-8 text-slate-400 border border-dashed border-slate-200 rounded-3xl bg-white/40">
                    <div>
                        <svg class="h-10 w-10 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <p class="text-sm">Select a group to manage members</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
