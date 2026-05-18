<div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Assets</h1>
            <p class="text-gray-600">Manage your company assets.</p>
        </div>
        <button wire:click="resetFields" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            Add Asset
        </button>
    </div>

    <!-- Message -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Table Section -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-md overflow-hidden">
            <!-- Search & Filter -->
            <div class="p-4 border-b border-gray-100 flex flex-wrap gap-4">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search name or tag..." class="px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                
                <select wire:model="selectedCategory" class="px-4 py-2 border rounded-lg">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <select wire:model="selectedStatus" class="px-4 py-2 border rounded-lg">
                    <option value="">All Status</option>
                    <option value="Available">Available</option>
                    <option value="Assigned">Assigned</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Retired">Retired</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-sm">
                        <tr>
                            <th class="px-6 py-3">Tag</th>
                            <th class="px-6 py-3">Serial</th>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Category</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($assets as $asset)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-mono text-sm">{{ $asset->asset_tag }}</td>
                                <td class="px-6 py-4 font-mono text-sm">{{ $asset->serial_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-800 font-medium">{{ $asset->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $asset->category->name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ in_array($asset->status, ['in_stock', 'Available']) ? 'bg-green-100 text-green-700' : '' }}
                                        {{ in_array($asset->status, ['assigned', 'Assigned']) ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ in_array($asset->status, ['maintenance', 'Maintenance']) ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ in_array($asset->status, ['retired', 'Retired']) ? 'bg-gray-100 text-gray-700' : '' }}
                                    ">
                                        {{ $asset->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <button wire:click="edit({{ $asset->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                    <button wire:click="delete({{ $asset->id }})" class="text-red-600 hover:text-red-900" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $assets->links() }}
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 h-fit">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                {{ $editingAssetId ? 'Edit Asset' : 'Add Asset' }}
            </h2>
            <form wire:submit.prevent="store" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Asset Tag</label>
                    <input type="text" wire:model="asset_tag" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('asset_tag') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                    <input type="text" wire:model="serial_number" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('serial_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select wire:model="category_id" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model="status" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="in_stock">In Stock</option>
                        <option value="assigned">Assigned</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <select wire:model="location_id" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Location</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Assign To</label>
                    <select wire:model="assigned_to_user_id" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Purchase Date</label>
                    <input type="date" wire:model="purchase_date" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Purchase Cost</label>
                    <input type="number" step="0.01" wire:model="purchase_cost" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Warranty Expiry</label>
                    <input type="date" wire:model="warranty_expiry" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea wire:model="notes" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="resetFields" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
