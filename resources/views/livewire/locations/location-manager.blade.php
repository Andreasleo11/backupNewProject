<div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Locations</h1>
            <p class="text-gray-600">Manage asset locations.</p>
        </div>
        <button wire:click="resetFields" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            Add Location
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
            <!-- Search -->
            <div class="p-4 border-b border-gray-100">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search location..." class="px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-sm">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($locations as $location)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-800 font-medium">{{ $location->name }}</td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <button wire:click="edit({{ $location->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                    <button wire:click="delete({{ $location->id }})" class="text-red-600 hover:text-red-900" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $locations->links() }}
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 h-fit">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                {{ $editingLocationId ? 'Edit Location' : 'Add Location' }}
            </h2>
            <form wire:submit.prevent="store" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="resetFields" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
