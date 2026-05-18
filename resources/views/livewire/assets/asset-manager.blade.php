<div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Assets</h1>
            <p class="text-gray-600">Manage your company assets.</p>
        </div>
        <button wire:click="$toggle('showForm')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            @if($showForm)
                Hide Form
            @else
                + Add Asset
            @endif
        </button>
    </div>

    <!-- Message -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 {{ $showForm ? 'lg:grid-cols-3' : '' }} gap-6">
        <!-- Table Section -->
        <div class="{{ $showForm ? 'lg:col-span-2' : '' }} bg-white rounded-2xl shadow-md overflow-hidden">
            <!-- Search & Filter -->
            <div class="p-4 border-b border-gray-100 flex flex-wrap gap-3">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search name, tag, serial..." class="flex-1 min-w-40 px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">

                <select wire:model="selectedCategory" class="px-4 py-2 border rounded-lg">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <select wire:model="selectedStatus" class="px-4 py-2 border rounded-lg">
                    <option value="">All Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="assigned">Assigned</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="retired">Retired</option>
                </select>

                <script>
                    document.addEventListener('livewire:load', function () {
                        Livewire.hook('message.processed', () => {
                            // Ensure selectedStatus remains a string (fix for filter mismatch)
                            const el = document.querySelector('[wire\\:model="selectedStatus"]');
                            if (el && el.value === '0') el.value = '';
                        });
                    });
                </script>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-sm">
                        <tr>
                            <th class="px-4 py-3">Tag</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Brand</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Assigned To</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($assets as $asset)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $asset->asset_tag }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-800 font-medium">{{ $asset->name }}</div>
                                    @if($asset->serial_number)
                                        <div class="text-xs text-gray-400 font-mono">{{ $asset->serial_number }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-sm">{{ $asset->brand ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600 text-sm">{{ $asset->category->name }}</td>
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
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $asset->location->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $asset->assignedTo->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                                    <a href="{{ route('assets.show', $asset->id) }}" class="text-green-600 hover:text-green-900 mr-3">View</a>
                                    <button wire:click="edit({{ $asset->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <button wire:click="delete({{ $asset->id }})" class="text-red-600 hover:text-red-900" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">Delete</button>
                                </td>
                            </tr>
                        @endforeach

                        @if($assets->isEmpty())
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-400">No assets found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $assets->links() }}
            </div>
        </div>

        <!-- Form Section — only visible when adding/editing -->
        @if($showForm)
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 h-fit">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                {{ $editingAssetId ? 'Edit Asset' : 'Add Asset' }}
            </h2>
            <form wire:submit.prevent="store" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="name" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Brand</label>
                    <input type="text" wire:model="brand" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('brand') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Asset Tag <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="asset_tag" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        @error('asset_tag') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                        <input type="text" wire:model="serial_number" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                        <select wire:model="category_id" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select...</option>
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
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <select wire:model="location_id" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select...</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Assign To</label>
                        <select wire:model="assigned_to_user_id" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Purchase Date</label>
                        <input type="date" wire:model="purchase_date" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Purchase Cost</label>
                        <input type="number" step="0.01" wire:model="purchase_cost" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Warranty Expiry</label>
                    <input type="date" wire:model="warranty_expiry" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea wire:model="notes" rows="2" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" wire:click="resetFields" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Save</button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
