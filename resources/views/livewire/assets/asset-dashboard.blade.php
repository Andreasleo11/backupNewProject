<div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Asset Dashboard</h1>
        <p class="text-gray-600">Overview of your assets and consumables.</p>
    </div>

    <!-- Main Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Assets Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-indigo-500 transform transition hover:scale-[1.02] hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold text-gray-500">Total Assets</p>
                    <p class="text-4xl font-bold mt-2 text-gray-800">{{ $totalAssets }}</p>
                </div>
                <div class="p-3 bg-indigo-50 rounded-full text-indigo-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
        </div>

        <!-- Assigned Assets Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-blue-500 transform transition hover:scale-[1.02] hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold text-gray-500">Assigned Assets</p>
                    <p class="text-4xl font-bold mt-2 text-gray-800">{{ $assignedAssets }}</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Total Consumables Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-teal-500 transform transition hover:scale-[1.02] hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold text-gray-500">Consumables in Stock</p>
                    <p class="text-4xl font-bold mt-2 text-gray-800">{{ $totalConsumablesInStock }}</p>
                </div>
                <div class="p-3 bg-teal-50 rounded-full text-teal-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6M8 21h8a2 2 0 002-2v-5H6v5a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Low Stock Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-red-500 transform transition hover:scale-[1.02] hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold text-gray-500">Low Stock Items</p>
                    <p class="text-4xl font-bold mt-2 text-gray-800">{{ $lowStockConsumables }}</p>
                </div>
                <div class="p-3 bg-red-50 rounded-full text-red-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Warranty 30 Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-amber-500 transform transition hover:scale-[1.02] hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold text-gray-500">Warranty Expiring (30 Days)</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $warrantyExpiring30 }}</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-full text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Warranty 60 Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-yellow-500 transform transition hover:scale-[1.02] hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold text-gray-500">Warranty Expiring (60 Days)</p>
                    <p class="text-3xl font-bold mt-2 text-gray-800">{{ $warrantyExpiring60 }}</p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-full text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recently Added Assets -->
        <div class="col-span-1 lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recently Added Assets</h3>
                <a href="{{ route('assets.manage') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-gray-500 uppercase text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3">Tag</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Warranty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentAssets as $asset)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $asset->asset_tag ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $asset->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $asset->category->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusMap = [
                                            'in_stock'    => ['label' => 'In Stock',    'class' => 'bg-green-100 text-green-700'],
                                            'assigned'    => ['label' => 'Assigned',    'class' => 'bg-blue-100 text-blue-700'],
                                            'maintenance' => ['label' => 'Maintenance', 'class' => 'bg-yellow-100 text-yellow-700'],
                                            'retired'     => ['label' => 'Retired',     'class' => 'bg-gray-100 text-gray-600'],
                                        ];
                                        $s = $statusMap[$asset->status] ?? ['label' => $asset->status, 'class' => 'bg-gray-100 text-gray-600'];
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $s['class'] }}">
                                        {{ $s['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ optional($asset->warranty_expiry)->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recently Issued Consumables -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recently Issued</h3>
                <a href="{{ route('consumables.manage') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-gray-500 uppercase text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Consumable</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">To</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentConsumableIssues as $tx)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-gray-600">{{ $tx->created_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $tx->consumable->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-bold text-indigo-600">{{ $tx->quantity }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $tx->targetUser->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
