<div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Asset Dashboard</h1>
        <p class="text-gray-600">Overview of your assets and consumables.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Assets Card -->
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white transform transition hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold opacity-75">Total Assets</p>
                    <p class="text-4xl font-bold mt-2">{{ $totalAssets }}</p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
        </div>

        <!-- Low Stock Card -->
        <div class="bg-gradient-to-br from-pink-500 to-red-600 rounded-2xl shadow-xl p-6 text-white transform transition hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold opacity-75">Low Stock Items</p>
                    <p class="text-4xl font-bold mt-2">{{ $lowStockConsumables }}</p>
                </div>
                <div class="p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Placeholder for Assigned Assets -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold text-gray-500">Assigned Assets</p>
                    <p class="text-4xl font-bold mt-2 text-gray-800">{{ $assignedAssets }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Placeholder for Available Assets -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase font-semibold text-gray-500">Available Assets</p>
                    <p class="text-4xl font-bold mt-2 text-gray-800">{{ $availableAssets }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>
</div>
