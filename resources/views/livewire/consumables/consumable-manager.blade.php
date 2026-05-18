<div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Consumables</h1>
            <p class="text-gray-600">Manage stock and issue consumables.</p>
        </div>
        <button wire:click="$toggle('showForm')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            @if($showForm)
                Hide Form
            @else
                + Add Consumable
            @endif
        </button>
    </div>

    <!-- Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 {{ $showForm || $selectedConsumableId ? 'lg:grid-cols-3' : '' }} gap-6">
        <!-- Table Section -->
        <div class="{{ $showForm || $selectedConsumableId ? 'lg:col-span-2' : '' }} bg-white rounded-2xl shadow-md overflow-hidden">
            <!-- Search & Filter -->
            <div class="p-4 border-b border-gray-100 flex flex-wrap gap-4">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search name or SKU..." class="px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                
                <select wire:model="selectedCategory" class="px-4 py-2 border rounded-lg">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-sm">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">SKU</th>
                            <th class="px-6 py-3">Stock</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($consumables as $consumable)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-800 font-medium">{{ $consumable->name }}</td>
                                <td class="px-6 py-4 font-mono text-sm">{{ $consumable->sku }}</td>
                                <td class="px-6 py-4 text-gray-800 font-bold">{{ $consumable->current_stock }} {{ $consumable->unit }}</td>
                                <td class="px-6 py-4">
                                    @if($consumable->current_stock <= $consumable->min_stock)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Low Stock</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">OK</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <button wire:click="edit({{ $consumable->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                    <button wire:click="openTransactionModal({{ $consumable->id }})" class="text-green-600 hover:text-green-900 mr-2">Stock In/Out</button>
                                    <button wire:click="delete({{ $consumable->id }})" class="text-red-600 hover:text-red-900" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">Delete</button>
                                </td>
                            </tr>
                        @endforeach

                        @if($consumables->isEmpty())
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">No consumables found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $consumables->links() }}
            </div>
        </div>

        <!-- Sidebar Section (Forms) -->
        @if($showForm || $selectedConsumableId)
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 h-fit">
            @if($selectedConsumable)
                <!-- Stock Transaction Form -->
                <h2 class="text-xl font-bold text-gray-800 mb-4">Stock Transaction — {{ $selectedConsumable->name }}</h2>
                <form wire:submit.prevent="submitTransaction" class="space-y-4" x-data="{ type: @entangle('transactionType') }">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select wire:model="transactionType" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="In">Stock In</option>
                            <option value="Out">Stock Out</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" wire:model="transactionQuantity" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            @error('transactionQuantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="w-40">
                            <label class="block text-sm font-medium text-gray-700">Available</label>
                            <div class="mt-1 px-4 py-2 border rounded-lg bg-gray-50 font-bold">{{ $selectedConsumable->current_stock }} {{ $selectedConsumable->unit }}</div>
                        </div>
                    </div>

                    <div x-show="type === 'Out'">
                        <label class="block text-sm font-medium text-gray-700">Issue To</label>
                        <select wire:model="targetUserId" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <input type="text" wire:model="notes" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reference (Ticket #)</label>
                        <input type="text" wire:model="reference" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="$set('selectedConsumableId', null)" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Submit</button>
                    </div>
                </form>
            @else
                <!-- Consumable CRUD Form -->
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    {{ $editingConsumableId ? 'Edit Consumable' : 'Add Consumable' }}
                </h2>
                <form wire:submit.prevent="store" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">SKU</label>
                        <input type="text" wire:model="sku" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                        <select wire:model="category_id" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Stock</label>
                        <input type="number" wire:model="current_stock" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Min Stock</label>
                        <input type="number" wire:model="min_stock" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unit</label>
                        <input type="text" wire:model="unit" placeholder="pcs, box, etc." class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reorder Point</label>
                        <input type="number" wire:model="reorder_point" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="resetFields" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Save</button>
                    </div>
                </form>
            @endif
        </div>
        @endif
    </div>

    <!-- Transactions Table -->
    <div class="mt-8 bg-white rounded-2xl shadow-md overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800">Recent Transactions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-sm">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Consumable</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Quantity</th>
                        <th class="px-6 py-3">By</th>
                        <th class="px-6 py-3">To</th>
                        <th class="px-6 py-3">Notes</th>
                        <th class="px-6 py-3">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($transactions as $tx)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $tx->consumable->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $tx->type === 'In' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}
                                ">
                                    {{ $tx->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold">{{ $tx->quantity }}</td>
                            <td class="px-6 py-4 text-sm">{{ $tx->user->name ?? 'System' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $tx->targetUser->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $tx->notes }}</td>
                            <td class="px-6 py-4 text-sm font-mono">{{ $tx->reference ?? '-' }}</td>
                        </tr>
                    @endforeach

                    @if($transactions->isEmpty())
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-400">No transactions found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
