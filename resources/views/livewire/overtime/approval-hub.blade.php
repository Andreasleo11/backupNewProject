@section('title', 'Overtime Approval Hub')
@section('page-title', 'Approval Command Center')
@section('page-subtitle', 'Batch review and high-speed multi-approvals')

<div class="space-y-6 pb-20 font-sans">
    {{-- MINIMAL HERO + SUMMARY (Title + Metrics only) --}}
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">

        <!-- Top: Title + Summary Metrics -->
        <div class="px-8 py-7">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                <!-- Title -->
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-2xl bg-indigo-600 flex items-center justify-center flex-shrink-0">
                        <i class='bx bxs-zap text-3xl text-white'></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">Approval Hub</h1>
                        <p class="text-sm text-slate-500">Review and approve overtime requests</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex gap-3">
                    <button type="button" 
                            wire:click="expandAll" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium flex items-center gap-2 text-sm transition-all">
                        <i class='bx bx-expand-alt'></i>
                        Review All
                    </button>
                    <a href="{{ route('overtime.index') }}"
                    class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium flex items-center gap-2 text-sm transition-all">
                        <i class='bx bx-arrow-back'></i>
                        Back
                    </a>
                </div>
            </div>

            <!-- Minimal Summary Metrics -->
            <div class="mt-6 flex flex-wrap gap-x-10 gap-y-5 text-sm">
                <div class="flex items-center gap-3">
                    <span class="text-blue-600"><i class='bx bx-file'></i></span>
                    <div>
                        <span class="font-semibold text-slate-900">{{ $totalForms }}</span>
                        <span class="text-slate-500 ml-1">forms</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-green-600"><i class='bx bx-user'></i></span>
                    <div>
                        <span class="font-semibold text-slate-900">{{ $totalEmployees }}</span>
                        <span class="text-slate-500 ml-1">employees</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-amber-600"><i class='bx bx-time'></i></span>
                    <div>
                        <span class="font-semibold text-slate-900">{{ round($totalHours, 1) }}h</span>
                        <span class="text-slate-500 ml-1">total hours</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-purple-600"><i class='bx bx-bar-chart'></i></span>
                    <div>
                        <span class="font-semibold text-slate-900">{{ $avgHours }}h</span>
                        <span class="text-slate-500 ml-1">avg/person</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MINIMAL GROUPING TABS + SEARCH ==================== -->
    <div class="mt-6 flex flex-col sm:flex-row gap-4 items-center">

        <!-- Minimal Grouping Tabs -->
        <div class="flex bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
            <button wire:click="setGroupingMode('department')"
                    :class="{ 'bg-slate-900 text-white shadow': $wire.groupingMode === 'department' }"
                    class="px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2 transition-all">
                <i class='bx bx-building'></i>
                <span>Department</span>
            </button>
            <button wire:click="setGroupingMode('creator')"
                    :class="{ 'bg-slate-900 text-white shadow': $wire.groupingMode === 'creator' }"
                    class="px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2 transition-all">
                <i class='bx bx-user'></i>
                <span>Creator</span>
            </button>
            <button wire:click="setGroupingMode('branch')"
                    :class="{ 'bg-slate-900 text-white shadow': $wire.groupingMode === 'branch' }"
                    class="px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2 transition-all">
                <i class='bx bx-map-pin'></i>
                <span>Branch</span>
            </button>
        </div>

        <!-- Minimal Search -->
        <div class="flex-1 relative w-full">
            <i class='bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400'></i>
            <input 
                wire:model.live="search" 
                placeholder="Search name or NIK..." 
                class="w-full pl-11 py-3 bg-white border border-slate-200 rounded-2xl focus:border-indigo-300 text-sm placeholder:text-slate-400">
        </div>

    </div>

    {{-- GROUPING MODE SELECTION MODAL --}}
    @if (!$hasSelectedGroupingMode)
        <div x-data="{ open: true }"
            x-effect="document.body.style.overflow = open ? 'hidden' : ''">
            <template x-teleport="body">
                <div x-show="open" x-cloak class="fixed inset-0 z-[9999] overflow-y-auto"
                    aria-labelledby="grouping-modal-title" role="dialog" aria-modal="true">

                    {{-- Backdrop --}}
                    <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>

                    {{-- Modal Panel --}}
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all"
                            @click.stop>
                            <div class="p-8">
                                <div class="text-center mb-6">
                                    <div class="h-16 w-16 bg-indigo-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                        <i class='bx bxs-layer text-3xl text-indigo-600'></i>
                                    </div>
                                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight" id="grouping-modal-title">Choose Your View</h2>
                                    <p class="text-slate-600 mt-2">Select how you want to organize overtime requests for review</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <button wire:click="setGroupingMode('department')"
                                        class="group p-6 bg-white border-2 border-slate-200 hover:border-blue-300 rounded-2xl transition-all hover:shadow-lg text-center">
                                        <div class="h-12 w-12 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 transition-colors">
                                            <i class='bx bx-building text-2xl text-blue-600'></i>
                                        </div>
                                        <h3 class="font-bold text-slate-900 mb-2">By Department</h3>
                                        <p class="text-sm text-slate-600">Group requests by department first, then by creator and date</p>
                                    </button>
                                    <button wire:click="setGroupingMode('creator')"
                                        class="group p-6 bg-white border-2 border-slate-200 hover:border-gray-300 rounded-2xl transition-all hover:shadow-lg text-center">
                                        <div class="h-12 w-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-gray-200 transition-colors">
                                            <i class='bx bx-user text-2xl text-gray-600'></i>
                                        </div>
                                        <h3 class="font-bold text-slate-900 mb-2">By Creator</h3>
                                        <p class="text-sm text-slate-600">Group requests by who created them first, then by department and date</p>
                                    </button>
                                    <button wire:click="setGroupingMode('branch')"
                                        class="group p-6 bg-white border-2 border-slate-200 hover:border-violet-300 rounded-2xl transition-all hover:shadow-lg text-center">
                                        <div class="h-12 w-12 bg-violet-100 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:bg-violet-200 transition-colors">
                                            <i class='bx bx-map-pin text-2xl text-violet-600'></i>
                                        </div>
                                        <h3 class="font-bold text-slate-900 mb-2">By Branch</h3>
                                        <p class="text-sm text-slate-600">Group requests by branch first, then by department, creator, and date</p>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    @endif

    {{-- ONBOARDING MODAL --}}
    @if ($showOnboarding)
        <div x-data="{ open: true }"
            x-effect="document.body.style.overflow = open ? 'hidden' : ''">
            <template x-teleport="body">
                <div x-show="open" x-cloak class="fixed inset-0 z-[9999] overflow-y-auto"
                    aria-labelledby="onboarding-modal-title" role="dialog" aria-modal="true">

                    {{-- Backdrop --}}
                    <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>

                    {{-- Modal Panel --}}
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all"
                            @click.stop>
                            <div class="p-8">
                                <div class="text-center mb-6">
                                    <div class="h-16 w-16 bg-indigo-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                        <i class='bx bxs-zap text-3xl text-indigo-600'></i>
                                    </div>
                                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight" id="onboarding-modal-title">Welcome to Approval Hub</h2>
                                    <p class="text-slate-600 mt-2">Your streamlined tool for managing overtime approvals</p>
                                </div>
                                <div class="space-y-4 mb-6">
                                    <div class="flex items-start gap-4">
                                        <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class='bx bx-group text-blue-600'></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900">Group & Review</h3>
                                            <p class="text-sm text-slate-600">Items are grouped by department, creator, or branch.
                                                Expand sections to review details.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-4">
                                        <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class='bx bx-check-circle text-green-600'></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900">Quick Approve</h3>
                                            <p class="text-sm text-slate-600">Approve entire groups or select individual items for bulk
                                                approval.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-4">
                                        <div class="h-8 w-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class='bx bx-search text-purple-600'></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900">Search & Filter</h3>
                                            <p class="text-sm text-slate-600">Use the search bar to find specific employees or NIKs.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-center">
                                    <button type="button" wire:click="dismissOnboarding"
                                        class="px-8 py-3 bg-indigo-600 text-white font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all">
                                        Get Started
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    @endif

    {{-- REJECTION REASON MODAL --}}
    <div x-data="{ open: @entangle('showRejectModal').live }"
        x-effect="document.body.style.overflow = open ? 'hidden' : ''">
        <template x-teleport="body">
            <div x-show="open" x-cloak class="fixed inset-0 z-[9999] overflow-y-auto"
                aria-labelledby="reject-modal-title" role="dialog" aria-modal="true">

                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" @click="$wire.set('showRejectModal', false)"></div>

                {{-- Modal Panel --}}
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all"
                        @click.stop>
                        <div class="p-6">
                            <div class="text-center mb-4">
                                <div class="h-12 w-12 bg-rose-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                    <i class='bx bx-x text-2xl text-rose-600'></i>
                                </div>
                                <h2 class="text-lg font-black text-slate-900" id="reject-modal-title">Rejection Reason</h2>
                                <p class="text-sm text-slate-600">Please provide a reason for rejecting the selected requests.</p>
                            </div>
                            <div class="mb-4">
                                <textarea wire:model="rejectReason" rows="4" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500" placeholder="Enter rejection reason..."></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" @click="$wire.set('showRejectModal', false)" class="flex-1 px-4 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition-all">
                                    Cancel
                                </button>
                                <button type="button" wire:click="confirmReject" wire:loading.attr="disabled" class="flex-1 px-4 py-3 rounded-xl bg-rose-500 text-white font-medium hover:bg-rose-600 transition-all disabled:opacity-50">
                                    <span wire:loading.remove>Reject</span>
                                    <span wire:loading>Rejecting...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @if ($groups->isEmpty())
        <div class="bg-white rounded-[3rem] border-4 border-dashed border-slate-100 p-20 text-center">
            <div class="h-24 w-24 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-200">
                <i class='bx bx-check-double text-6xl'></i>
            </div>
            <h3 class="text-xl font-black text-slate-400 uppercase tracking-tight">Zero Pending Tasks</h3>
            <p class="text-[10px] text-slate-300 font-bold uppercase tracking-widest mt-2">All your assigned flows are
                cleared</p>
        </div>
    @else
        <div class="space-y-6">
            {{-- GROUP LIST --}}
            <div class="space-y-6">

                {{-- ==================== DEPARTMENT MODE ==================== --}}
                @if ($groupingMode === 'department')
                    @foreach ($groups as $deptId => $deptData)
                        <div class="bg-white rounded-3xl border border-blue-200/60 shadow-sm overflow-hidden">

                            {{-- DEPT HEADER --}}
                            <div class="px-8 py-6 flex flex-wrap items-center justify-between gap-4 cursor-pointer hover:bg-blue-50/50 transition-colors"
                                wire:click="toggleGroup('dept-{{ $deptId }}')">
                                <div class="flex items-center gap-6">
                                    <i class='bx bx-building text-2xl text-blue-600'></i>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] leading-none mb-1">Department</span>
                                        <h3 class="text-base font-black text-slate-900 leading-none">
                                            {{ $deptData['department']->name }}</h3>
                                    </div>
                                    <div class="h-8 w-px bg-slate-100 hidden sm:block"></div>
                                    <div class="hidden sm:flex items-center gap-4">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black">
                                                {{ $deptData['total_employees'] }}</div>
                                            <span
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">People</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                                                {{ round($deptData['total_hours'], 1) }}</div>
                                            <span
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hours</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center text-sm font-black">
                                                {{ $deptData['total_forms'] }}</div>
                                            <span
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Forms</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" @click.stop
                                        wire:click="selectAllDept('{{ $deptId }}')"
                                        class="h-5 w-5 rounded-lg border-2 border-slate-200 text-indigo-600">
                                    <i
                                        class='bx bx-chevron-down text-xl text-slate-300 transition-transform duration-300 {{ isset($expandedGroups["dept-{$deptId}"]) ? 'rotate-180' : '' }}'></i>
                                </div>
                            </div>

                            {{-- EXPANDED: USERS --}}
                            @if (isset($expandedGroups["dept-{$deptId}"]))
                                <div class="px-8 pb-6 pt-4 border-t border-slate-50 bg-slate-50/30 space-y-4">
                                    @foreach ($deptData['users'] as $userId => $userData)
                                        <div
                                            class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden ml-8">

                                            {{-- USER HEADER --}}
                                            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 cursor-pointer hover:bg-gray-50/50 transition-colors"
                                                wire:click="toggleGroup('user-{{ $deptId }}-{{ $userId }}')">
                                                <div class="flex items-center gap-6">
                                                    <i class='bx bx-user text-xl text-gray-600'></i>
                                                    <div class="flex flex-col">
                                                        <span
                                                            class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] leading-none mb-1">Creator</span>
                                                        <h4 class="text-sm font-black text-slate-900 leading-none">
                                                            {{ $userData['user']->name }}</h4>
                                                    </div>
                                                    <div class="h-8 w-px bg-slate-100 hidden sm:block"></div>
                                                    <div class="hidden sm:flex items-center gap-4">
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black">
                                                                {{ $userData['total_employees'] }}</div>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">People</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                                                                {{ round($userData['total_hours'], 1) }}</div>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hours</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="h-8 w-8 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center text-sm font-black">
                                                                {{ $userData['total_forms'] }}</div>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Forms</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <i
                                                    class='bx bx-chevron-down text-xl text-slate-300 transition-transform duration-300 {{ isset($expandedGroups["user-{$deptId}-{$userId}"]) ? 'rotate-180' : '' }}'></i>
                                            </div>

                                            {{-- EXPANDED: DATES --}}
                                            @if (isset($expandedGroups["user-{$deptId}-{$userId}"]))
                                                <div
                                                    class="px-6 pb-4 pt-4 border-t border-slate-50 bg-slate-50/30 space-y-4">
                                                    @foreach ($userData['dates'] as $dateKey => $groupData)
                                                        @php
                                                            $items = $groupData['forms'];
                                                            $first = $items->first();
                                                            $groupKey = "date|department|{$deptId}|{$userId}|{$dateKey}";
                                                            $isExpanded = isset($expandedGroups[$groupKey]);
                                                        @endphp

                                                        @include(
                                                            'livewire.overtime.partials._approval-pack',
                                                            [
                                                                'groupKey' => $groupKey,
                                                                'isExpanded' => $isExpanded,
                                                                'items' => $items,
                                                                'first' => $first,
                                                                'totalEmployees' => $groupData['total_employees'],
                                                                'totalHours' => $groupData['total_hours'],
                                                                'totalForms' => $groupData['total_forms'],
                                                                'insights' => $insights,
                                                                'indent' => 'ml-16',
                                                            ]
                                                        )
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- ==================== CREATOR MODE ==================== --}}
                @elseif ($groupingMode === 'creator')
                    @foreach ($groups as $userId => $userData)
                        <div class="bg-white rounded-3xl border border-gray-200/60 shadow-sm overflow-hidden">

                            {{-- USER HEADER --}}
                            <div class="px-8 py-6 flex flex-wrap items-center justify-between gap-4 cursor-pointer hover:bg-gray-50/50 transition-colors"
                                wire:click="toggleGroup('user-{{ $userId }}')">
                                <div class="flex items-center gap-6">
                                    <i class='bx bx-user text-2xl text-gray-600'></i>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] leading-none mb-1">Creator</span>
                                        <h3 class="text-base font-black text-slate-900 leading-none">
                                            {{ $userData['user']->name }}</h3>
                                    </div>
                                    <div class="h-8 w-px bg-slate-100 hidden sm:block"></div>
                                    <div class="hidden sm:flex items-center gap-4">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black">
                                                {{ $userData['total_employees'] }}</div>
                                            <span
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">People</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                                                {{ round($userData['total_hours'], 1) }}</div>
                                            <span
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hours</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center text-sm font-black">
                                                {{ $userData['total_forms'] }}</div>
                                            <span
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Forms</span>
                                        </div>
                                    </div>
                                </div>
                                <i
                                    class='bx bx-chevron-down text-xl text-slate-300 transition-transform duration-300 {{ isset($expandedGroups["user-{$userId}"]) ? 'rotate-180' : '' }}'></i>
                            </div>

                            {{-- EXPANDED: DEPARTMENTS --}}
                            @if (isset($expandedGroups["user-{$userId}"]))
                                <div class="px-8 pb-6 pt-4 border-t border-slate-50 bg-slate-50/30 space-y-4">
                                    @foreach ($userData['departments'] as $deptId => $deptData)
                                        <div
                                            class="bg-white rounded-2xl border border-blue-200/60 shadow-sm overflow-hidden ml-8">

                                            {{-- DEPT HEADER --}}
                                            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 cursor-pointer hover:bg-blue-50/50 transition-colors"
                                                wire:click="toggleGroup('dept-{{ $userId }}-{{ $deptId }}')">
                                                <div class="flex items-center gap-6">
                                                    <i class='bx bx-building text-xl text-blue-600'></i>
                                                    <div class="flex flex-col">
                                                        <span
                                                            class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] leading-none mb-1">Department</span>
                                                        <h4 class="text-sm font-black text-slate-900 leading-none">
                                                            {{ $deptData['department']->name }}</h4>
                                                    </div>
                                                    <div class="h-8 w-px bg-slate-100 hidden sm:block"></div>
                                                    <div class="hidden sm:flex items-center gap-4">
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black">
                                                                {{ $deptData['total_employees'] }}</div>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">People</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                                                                {{ round($deptData['total_hours'], 1) }}</div>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hours</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="h-8 w-8 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center text-sm font-black">
                                                                {{ $deptData['total_forms'] }}</div>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Forms</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <i
                                                    class='bx bx-chevron-down text-xl text-slate-300 transition-transform duration-300 {{ isset($expandedGroups["dept-{$userId}-{$deptId}"]) ? 'rotate-180' : '' }}'></i>
                                            </div>

                                            {{-- EXPANDED: DATES --}}
                                            @if (isset($expandedGroups["dept-{$userId}-{$deptId}"]))
                                                <div
                                                    class="px-6 pb-4 pt-4 border-t border-slate-50 bg-slate-50/30 space-y-4">
                                                    @foreach ($deptData['dates'] as $dateKey => $groupData)
                                                        @php
                                                            $items = $groupData['forms'];
                                                            $first = $items->first();
                                                            $groupKey = "date|creator|{$userId}|{$deptId}|{$dateKey}";
                                                            $isExpanded = isset($expandedGroups[$groupKey]);
                                                        @endphp

                                                        @include(
                                                            'livewire.overtime.partials._approval-pack',
                                                            [
                                                                'groupKey' => $groupKey,
                                                                'isExpanded' => $isExpanded,
                                                                'items' => $items,
                                                                'first' => $first,
                                                                'totalEmployees' => $groupData['total_employees'],
                                                                'totalHours' => $groupData['total_hours'],
                                                                'totalForms' => $groupData['total_forms'],
                                                                'insights' => $insights,
                                                                'indent' => 'ml-16',
                                                            ]
                                                        )
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- ==================== BRANCH MODE ==================== --}}
                @elseif ($groupingMode === 'branch')
                    @foreach ($groups as $branch => $branchData)
                        <div class="bg-white rounded-3xl border border-violet-200/60 shadow-sm overflow-hidden">

                            {{-- BRANCH HEADER --}}
                            <div class="px-8 py-6 flex flex-wrap items-center justify-between gap-4 cursor-pointer hover:bg-violet-50/50 transition-colors"
                                wire:click="toggleGroup('branch-{{ $branch }}')">
                                <div class="flex items-center gap-6">
                                    <i class='bx bx-map-pin text-2xl text-violet-600'></i>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[10px] font-black text-violet-400 uppercase tracking-[0.2em] leading-none mb-1">Branch</span>
                                        <h3 class="text-base font-black text-slate-900 leading-none">
                                            {{ $branchData['branch'] }}</h3>
                                    </div>
                                    <div class="h-8 w-px bg-slate-100 hidden sm:block"></div>
                                    <div class="hidden sm:flex items-center gap-4">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black">
                                                {{ $branchData['total_employees'] }}</div>
                                            <span
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">People</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                                                {{ round($branchData['total_hours'], 1) }}</div>
                                            <span
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hours</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-8 w-8 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center text-sm font-black">
                                                {{ $branchData['total_forms'] }}</div>
                                            <span
                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Forms</span>
                                        </div>
                                    </div>
                                </div>
                                <i
                                    class='bx bx-chevron-down text-xl text-slate-300 transition-transform duration-300 {{ isset($expandedGroups["branch-{$branch}"]) ? 'rotate-180' : '' }}'></i>
                            </div>

                            {{-- EXPANDED: DEPARTMENTS --}}
                            @if (isset($expandedGroups["branch-{$branch}"]))
                                <div class="px-8 pb-6 pt-4 border-t border-slate-50 bg-slate-50/30 space-y-4">
                                    @foreach ($branchData['departments'] as $deptId => $deptData)
                                        <div
                                            class="bg-white rounded-2xl border border-blue-200/60 shadow-sm overflow-hidden ml-8">

                                            {{-- DEPT HEADER --}}
                                            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 cursor-pointer hover:bg-blue-50/50 transition-colors"
                                                wire:click="toggleGroup('dept-{{ $branch }}-{{ $deptId }}')">
                                                <div class="flex items-center gap-6">
                                                    <i class='bx bx-building text-xl text-blue-600'></i>
                                                    <div class="flex flex-col">
                                                        <span
                                                            class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] leading-none mb-1">Department</span>
                                                        <h4 class="text-sm font-black text-slate-900 leading-none">
                                                            {{ $deptData['department']->name }}</h4>
                                                    </div>
                                                    <div class="h-8 w-px bg-slate-100 hidden sm:block"></div>
                                                    <div class="hidden sm:flex items-center gap-4">
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black">
                                                                {{ $deptData['total_employees'] }}</div>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">People</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="h-8 w-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                                                                {{ round($deptData['total_hours'], 1) }}</div>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hours</span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="h-8 w-8 rounded-lg bg-slate-50 text-slate-600 flex items-center justify-center text-sm font-black">
                                                                {{ $deptData['total_forms'] }}</div>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Forms</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <i
                                                    class='bx bx-chevron-down text-xl text-slate-300 transition-transform duration-300 {{ isset($expandedGroups["dept-{$branch}-{$deptId}"]) ? 'rotate-180' : '' }}'></i>
                                            </div>

                                            {{-- EXPANDED: USERS --}}
                                            @if (isset($expandedGroups["dept-{$branch}-{$deptId}"]))
                                                <div
                                                    class="px-6 pb-4 pt-4 border-t border-slate-50 bg-slate-50/30 space-y-4">
                                                    @foreach ($deptData['users'] as $userId => $userData)
                                                        <div
                                                            class="bg-white rounded-xl border border-gray-200/60 shadow-sm overflow-hidden ml-8">

                                                            {{-- USER HEADER --}}
                                                            <div class="px-5 py-3 flex flex-wrap items-center justify-between gap-3 cursor-pointer hover:bg-gray-50/50 transition-colors"
                                                                wire:click="toggleGroup('user-{{ $branch }}-{{ $deptId }}-{{ $userId }}')">
                                                                <div class="flex items-center gap-4">
                                                                    <i class='bx bx-user text-lg text-gray-600'></i>
                                                                    <div class="flex flex-col">
                                                                        <span
                                                                            class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] leading-none mb-1">Creator</span>
                                                                        <h5
                                                                            class="text-sm font-black text-slate-900 leading-none">
                                                                            {{ $userData['user']->name }}</h5>
                                                                    </div>
                                                                    <div
                                                                        class="hidden sm:flex items-center gap-3 ml-4">
                                                                        <span
                                                                            class="text-xs font-bold text-slate-400">{{ $userData['total_employees'] }}
                                                                            people ·
                                                                            {{ round($userData['total_hours'], 1) }}h ·
                                                                            {{ $userData['total_forms'] }} forms</span>
                                                                    </div>
                                                                </div>
                                                                <i
                                                                    class='bx bx-chevron-down text-lg text-slate-300 transition-transform duration-300 {{ isset($expandedGroups["user-{$branch}-{$deptId}-{$userId}"]) ? 'rotate-180' : '' }}'></i>
                                                            </div>

                                                            {{-- EXPANDED: DATES --}}
                                                            @if (isset($expandedGroups["user-{$branch}-{$deptId}-{$userId}"]))
                                                                <div
                                                                    class="px-5 pb-4 pt-3 border-t border-slate-50 bg-slate-50/30 space-y-4">
                                                                    @foreach ($userData['dates'] as $dateKey => $groupData)
                                                                        @php
                                                                            $items = $groupData['forms'];
                                                                            $first = $items->first();
                                                                            $groupKey = "date|branch|{$dateKey}|{$branch}|{$deptId}|{$userId}";
                                                                            $isExpanded = isset(
                                                                                $expandedGroups[$groupKey],
                                                                            );
                                                                        @endphp

                                                                        @include(
                                                                            'livewire.overtime.partials._approval-pack',
                                                                            [
                                                                                'groupKey' => $groupKey,
                                                                                'isExpanded' => $isExpanded,
                                                                                'items' => $items,
                                                                                'first' => $first,
                                                                                'totalEmployees' =>
                                                                                    $groupData['total_employees'],
                                                                                'totalHours' =>
                                                                                    $groupData['total_hours'],
                                                                                'totalForms' => $groupData['total_forms'],
                                                                                'insights' => $insights,
                                                                                'indent' => 'ml-8',
                                                                            ]
                                                                        )
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif

            </div>

            {{-- PAGINATION --}}
            <div class="mt-8 flex justify-center">
                {{ $groups->links() }}
            </div>

        </div>
    @endif

    {{-- FLOATING ACTION BUTTONS --}}
    @if ($groups->isNotEmpty() && count($selectedPackKeys) > 0)
        <template x-teleport="body">
            <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[100] flex items-center gap-3" x-cloak>
                <!-- Approve FAB -->
                <button 
                    type="button" 
                    wire:click="approveSelected"
                    wire:loading.attr="disabled"
                    wire:confirm="Approve all selected overtime requests?"
                    class="group flex items-center gap-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-8 py-4 rounded-2xl shadow-xl shadow-emerald-500/30 transition-all active:scale-95">
                    <i class='bx bx-check text-2xl'></i>
                    <span>Approve Selected</span>
                    <span class="bg-emerald-500/30 text-xs px-2.5 py-0.5 rounded-full font-mono">
                        {{ count($selectedPackKeys) }}
                    </span>
                </button>

                <!-- Reject FAB -->
                <button 
                    type="button" 
                    wire:click="$set('showRejectModal', true)"
                    class="group flex items-center gap-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold px-8 py-4 rounded-2xl shadow-xl shadow-rose-500/30 transition-all active:scale-95">
                    <i class='bx bx-x text-2xl'></i>
                    <span>Reject Selected</span>
                </button>
            </div>
        </template>
    @endif
</div>