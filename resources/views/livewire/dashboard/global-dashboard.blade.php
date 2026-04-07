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
                    Welcome back to the system. You have <span class="text-blue-600 font-bold underline decoration-blue-200 underline-offset-4">{{ $kpis['pending_approvals'] }}</span> pending tasks in your inbox.
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                {{-- Quick contextual hint without fake data --}}
                <div class="px-5 py-3 rounded-2xl bg-white/50 border border-slate-100/50 shadow-sm">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ \Carbon\Carbon::now()->format('l, j F Y') }}</p>
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

        {{-- Quick Actions (Sidebar style) --}}
        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="h-full rounded-3xl bg-white border border-slate-200 shadow-sm p-6 overflow-hidden relative group hover:border-indigo-100 transition-all">
                <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-indigo-50 blur-3xl group-hover:bg-indigo-100 transition-colors duration-700"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                            <i class="bi bi-lightning-charge-fill text-lg"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 tracking-tight">Quick Actions</h3>
                    </div>
                    
                    <div class="space-y-4 flex-1">
                        <a href="{{ route('purchase-requests.create') }}" class="flex items-center justify-between p-4 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all group/btn">
                            <div class="flex text-left items-center gap-4">
                                <div class="h-10 w-10 bg-white/20 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-cart-plus text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold">New Purchase</p>
                                    <p class="text-[10px] text-indigo-100 uppercase tracking-widest font-bold mt-0.5">Request Items</p>
                                </div>
                            </div>
                            <i class="bi bi-arrow-right text-xl opacity-50 group-hover/btn:opacity-100 group-hover/btn:translate-x-1 transition-all"></i>
                        </a>

                        <a href="{{ route('overtime.create') }}" class="flex items-center justify-between p-4 rounded-2xl bg-white border border-slate-200 text-slate-700 shadow-sm hover:border-amber-200 hover:bg-amber-50 hover:-translate-y-1 transition-all group/btn">
                            <div class="flex text-left items-center gap-4">
                                <div class="h-10 w-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-clock-history text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">File Overtime</p>
                                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mt-0.5">Request hours</p>
                                </div>
                            </div>
                            <i class="bi bi-arrow-right text-xl text-slate-400 group-hover/btn:text-amber-600 group-hover/btn:translate-x-1 transition-all"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="col-span-12">
            @livewire('dashboard.widgets.activity-timeline')
        </div>

    </div>
</div>
