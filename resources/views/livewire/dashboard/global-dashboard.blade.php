<div class="space-y-8 pb-12">
    {{-- Hero Section: Personalized Greeting --}}
    <div class="relative overflow-hidden rounded-3xl bg-white/40 border border-white/60 p-8 shadow-xl backdrop-blur-md">
        <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute -left-24 -bottom-24 h-64 w-64 rounded-full bg-violet-500/10 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                @php
                    $hour = date('H');
                    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                @endphp
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $greeting }}, <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-violet-600">{{ auth()->user()->name }}</span>!
                </h1>
                <p class="mt-2 text-slate-500 font-medium max-w-lg">
                    Welcome back to the system. You have <span class="text-blue-600 font-bold underline decoration-blue-200 underline-offset-4">{{ $kpis['pending_approvals'] }}</span> pending approvals and <span class="text-violet-600 font-bold underline decoration-violet-200 underline-offset-4">{{ $kpis['unread_notifications'] }}</span> unread notifications.
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="px-5 py-3 rounded-2xl bg-white/80 border border-slate-100 shadow-sm transition-all hover:shadow-md hover:-translate-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Company Status</p>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-sm font-bold text-slate-700">Operational</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bento Grid Layout --}}
    <div class="grid grid-cols-12 gap-6">
        
        {{-- Approval Queue (Large Widget) --}}
        <div class="col-span-12 lg:col-span-8">
            @livewire('dashboard.widgets.approval-queue')
        </div>

        {{-- Quick Stats (Siderbar style widgets) --}}
        <div class="col-span-12 lg:col-span-4 space-y-6">
            @livewire('dashboard.widgets.kpi-card', [
                'label' => 'Total Submissions',
                'value' => '1.2k',
                'trend' => '+12%',
                'icon' => 'file',
                'color' => 'blue'
            ])
            
            @livewire('dashboard.widgets.kpi-card', [
                'label' => 'Active Contracts',
                'value' => '42',
                'trend' => '-3%',
                'icon' => 'shield',
                'color' => 'emerald'
            ])
        </div>

        {{-- Activity Timeline (Medium Widget) --}}
        <div class="col-span-12 lg:col-span-7">
            @livewire('dashboard.widgets.activity-timeline')
        </div>

        {{-- Department Spotlight (Small Widget) --}}
        <div class="col-span-12 lg:col-span-5">
            <div class="h-full rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 p-8 text-white shadow-2xl relative overflow-hidden group">
                <div class="absolute right-0 bottom-0 opacity-10 group-hover:scale-110 transition-transform duration-700">
                    <svg class="h-64 w-64 text-slate-400/20 fill-current" viewBox="0 0 24 24">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div>
                        <h3 class="text-xl font-bold mb-4">Departmental Insights</h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6">
                            Explore specific metrics and documentation for the **{{ auth()->user()->department->name ?? 'Global' }}** department.
                        </p>
                    </div>
                    <div>
                        <button class="px-6 py-3 rounded-xl bg-white text-slate-900 font-bold text-sm shadow-lg hover:bg-slate-50 transition-all active:scale-95">
                            View Department Portal
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
