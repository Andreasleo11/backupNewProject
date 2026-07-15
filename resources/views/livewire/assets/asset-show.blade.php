<div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Asset Details</h1>
            <p class="text-gray-600">Viewing information for {{ $asset->name }}</p>
        </div>
        <a href="{{ route('assets.manage') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- QR Code & Quick Info -->
        <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center justify-center border border-gray-100">
            <div class="mb-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <!-- Using endroid/qr-code which is in composer.json -->
                @php
                    try {
                        $qrCodeObj = new \Endroid\QrCode\QrCode(
                            data: $asset->asset_tag,
                            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
                            size: 150,
                            margin: 5
                        );
                        $writer = new \Endroid\QrCode\Writer\PngWriter();
                        $qrCodeResult = $writer->write($qrCodeObj);
                        $qrCodeBase64 = base64_encode($qrCodeResult->getString());
                    } catch (\Exception $e) {
                        $qrCodeBase64 = null;
                    }
                @endphp
                
                @if(!$qrCodeBase64)
                    <div class="text-red-500 text-sm">Error generating QR Code.</div>
                @else
                    <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code" class="w-36 h-36">
                @endif
            </div>
            <div class="text-center">
                <div class="text-lg font-bold text-gray-800 font-mono">{{ $asset->asset_tag }}</div>
                <p class="text-sm text-gray-500">Scan this code to identify the asset.</p>
            </div>

            <div class="mt-6 w-full border-t border-gray-100 pt-4">
                <div class="flex justify-between py-2">
                    <span class="text-gray-500 text-sm">Status</span>
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
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-500 text-sm">Serial Number</span>
                    <span class="text-gray-800 font-mono text-sm">{{ $asset->serial_number ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Detailed Info -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-md p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 border-b border-gray-100 pb-2">Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Asset Name</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->name }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Brand</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->brand ?? '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Category</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->category->name }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Location</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->location->name ?? '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Assigned To</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->employee->name ?? $asset->assignedTo->name ?? 'Unassigned' }}</div>
                    </div>
                </div>

                <div>
                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Purchase Date</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') : '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Purchase Cost</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->purchase_cost ? '$' . number_format($asset->purchase_cost, 2) : '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Warranty Expiry</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->warranty_expiry ? \Carbon\Carbon::parse($asset->warranty_expiry)->format('Y-m-d') : '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">IP Address</label>
                        <div class="text-lg font-medium text-gray-800 font-mono">{{ $asset->ip_address ?? '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Username (Operator)</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->username ?? '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Purpose</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->purpose ?? '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Operating System</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->os ?? '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Department</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->department->name ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-4 border-t border-gray-100 pt-4">
                <label class="text-xs font-medium text-gray-400 uppercase">Notes</label>
                <div class="text-gray-700 mt-1 bg-gray-50 p-4 rounded-lg min-h-20">
                    {{ $asset->notes ?? 'No notes available.' }}
                </div>
            </div>

            @if($asset->position_image)
                <div class="mt-4 border-t border-gray-100 pt-4">
                    <label class="text-xs font-medium text-gray-400 uppercase">Position Image</label>
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $asset->position_image) }}" alt="Position Image" class="max-w-md rounded-lg shadow-sm border">
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Components Section -->
    <div class="mt-6 bg-white rounded-2xl shadow-md p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Asset Components</h2>
                <p class="text-sm text-gray-500">Hardware parts and installed software license tracking.</p>
            </div>
            <button wire:click="showAddComponentForm" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
                + Add Component
            </button>
        </div>

        @if(session()->has('component_message'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                {{ session('component_message') }}
            </div>
        @endif

        <!-- Component Form -->
        @if($showComponentForm)
            <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100 animate-fadeIn">
                <h3 class="text-md font-bold text-gray-800 mb-3">
                    {{ $editingComponentId ? 'Edit Component' : 'Add Component' }}
                </h3>
                <form wire:submit.prevent="saveComponent" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select wire:model="componentType" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                                <option value="hardware">Hardware</option>
                                <option value="software">Software</option>
                            </select>
                            @error('componentType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type Name</label>
                            <select wire:model="componentTypeName" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                                <option value="">Select type...</option>
                                @if($componentType === 'hardware')
                                    @foreach($hardwareTypes as $ht)
                                        <option value="{{ $ht->name }}">{{ $ht->name }}</option>
                                    @endforeach
                                @else
                                    @foreach($softwareTypes as $st)
                                        <option value="{{ $st->name }}">{{ $st->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('componentTypeName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name / Spec</label>
                            <input type="text" wire:model="componentName" placeholder="e.g. 16GB DDR4, Windows 11 Pro" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @error('componentName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Brand</label>
                            <input type="text" wire:model="componentBrand" placeholder="e.g. Corsair, Microsoft" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @error('componentBrand') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($componentType === 'hardware')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                                <input type="text" wire:model="componentSerialNumber" placeholder="e.g. SN123456" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('componentSerialNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700">License Key</label>
                                <input type="text" wire:model="componentLicense" placeholder="e.g. OEM / Retail Key" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('componentLicense') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Remark</label>
                            <input type="text" wire:model="componentRemark" placeholder="e.g. Slot 1, Expiry date" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @error('componentRemark') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="resetComponentFields" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Save Component</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Hardware Table -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                    <span class="mr-2">🔌</span> Hardware Components
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-250 text-gray-500">
                                <th class="pb-2">Type</th>
                                <th class="pb-2">Spec</th>
                                <th class="pb-2">Brand</th>
                                <th class="pb-2">Serial</th>
                                <th class="pb-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($asset->components->where('component_type', 'hardware') as $comp)
                                <tr class="hover:bg-gray-100 transition">
                                    <td class="py-2 text-gray-800 font-semibold">{{ $comp->type_name }}</td>
                                    <td class="py-2 text-gray-600">{{ $comp->name }}</td>
                                    <td class="py-2 text-gray-600">{{ $comp->brand ?? '-' }}</td>
                                    <td class="py-2 text-gray-500 font-mono">{{ $comp->serial_number ?? '-' }}</td>
                                    <td class="py-2 whitespace-nowrap">
                                        <button wire:click="editComponent({{ $comp->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                        <button wire:click="deleteComponent({{ $comp->id }})" class="text-red-600 hover:text-red-900" onclick="confirm('Delete component?') || event.stopImmediatePropagation()">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-400">No hardware components installed.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Software Table -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                    <span class="mr-2">💾</span> Installed Software
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-250 text-gray-500">
                                <th class="pb-2">Type</th>
                                <th class="pb-2">Software Name</th>
                                <th class="pb-2">Brand</th>
                                <th class="pb-2">License</th>
                                <th class="pb-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($asset->components->where('component_type', 'software') as $comp)
                                <tr class="hover:bg-gray-100 transition">
                                    <td class="py-2 text-gray-800 font-semibold">{{ $comp->type_name }}</td>
                                    <td class="py-2 text-gray-600">{{ $comp->name }}</td>
                                    <td class="py-2 text-gray-600">{{ $comp->brand ?? '-' }}</td>
                                    <td class="py-2 text-gray-500 font-mono">{{ $comp->license ?? 'Not License' }}</td>
                                    <td class="py-2 whitespace-nowrap">
                                        <button wire:click="editComponent({{ $comp->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                        <button wire:click="deleteComponent({{ $comp->id }})" class="text-red-600 hover:text-red-900" onclick="confirm('Delete component?') || event.stopImmediatePropagation()">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-400">No software programs tracked.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Records Section -->
    <div class="mt-6 bg-white rounded-2xl shadow-md p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Service & Repair History</h2>
                <p class="text-sm text-gray-500">Track part replacements, software installations, and general repairs.</p>
            </div>
            <button wire:click="showAddServiceForm" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
                + Log Service Record
            </button>
        </div>

        @if(session()->has('service_message'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                {{ session('service_message') }}
            </div>
        @endif
        @if(session()->has('service_error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                {{ session('service_error') }}
            </div>
        @endif

        <!-- Service Form -->
        @if($showServiceForm)
            <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100 animate-fadeIn">
                <h3 class="text-md font-bold text-gray-800 mb-3">
                    {{ $editingServiceId ? 'Edit Service Record' : 'Log Service Record' }}
                </h3>
                <form wire:submit.prevent="saveService" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Requested By</label>
                            <input type="text" wire:model="serviceRequestedBy" placeholder="e.g. Employee name" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @error('serviceRequestedBy') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Action Type</label>
                            <select wire:model="serviceAction" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                                <option value="repair">General Repair (No part swap)</option>
                                <option value="replacement">Replace Existing Component</option>
                                <option value="installation">Install New Component</option>
                            </select>
                            @error('serviceAction') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($serviceAction !== 'repair')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Component Type</label>
                                <select wire:model="serviceComponentType" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                                    <option value="hardware">Hardware</option>
                                    <option value="software">Software</option>
                                </select>
                                @error('serviceComponentType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    @if($serviceAction === 'replacement')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Old Part to Replace <span class="text-red-500">*</span></label>
                            <select wire:model="serviceOldPart" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                                <option value="">Select current part...</option>
                                @foreach($asset->components->where('component_type', $serviceComponentType) as $c)
                                    <option value="{{ $c->name }}">{{ $c->type_name }} ({{ $c->name }})</option>
                                @endforeach
                            </select>
                            @error('serviceOldPart') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    @if($serviceAction !== 'repair')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-200 pt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Type Name</label>
                                <select wire:model="serviceNewTypeName" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                                    <option value="">Select component type...</option>
                                    @if($serviceComponentType === 'hardware')
                                        @foreach($hardwareTypes as $ht)
                                            <option value="{{ $ht->name }}">{{ $ht->name }}</option>
                                        @endforeach
                                    @else
                                        @foreach($softwareTypes as $st)
                                            <option value="{{ $st->name }}">{{ $st->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('serviceNewTypeName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Name / Spec</label>
                                <input type="text" wire:model="serviceNewName" placeholder="e.g. 16GB DDR4, Windows 11 Pro" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('serviceNewName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Brand</label>
                                <input type="text" wire:model="serviceNewBrand" placeholder="e.g. Corsair" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($serviceComponentType === 'hardware')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">New Serial Number</label>
                                    <input type="text" wire:model="serviceNewSerialNumber" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">New License Key</label>
                                    <input type="text" wire:model="serviceNewLicense" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                            @endif
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Remark / Notes</label>
                        <input type="text" wire:model="serviceRemark" placeholder="Details of repair/reason" class="mt-1 block w-full px-3 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="resetServiceFields" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">Cancel</button>
                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">Save Service Record</button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Service Records List -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-500">
                        <th class="pb-2">Date Logged</th>
                        <th class="pb-2">Requested By</th>
                        <th class="pb-2">Action</th>
                        <th class="pb-2">Target Part</th>
                        <th class="pb-2">New Spec</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($asset->serviceRecords as $record)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 text-gray-600">{{ $record->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 text-gray-800 font-medium">{{ $record->requested_by }}</td>
                            <td class="py-3">
                                @php
                                    $actionClasses = [
                                        'repair' => 'bg-yellow-100 text-yellow-700',
                                        'replacement' => 'bg-blue-100 text-blue-700',
                                        'installation' => 'bg-green-100 text-green-700',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $actionClasses[$record->action] ?? 'bg-gray-100' }}">
                                    {{ ucfirst($record->action) }}
                                </span>
                            </td>
                            <td class="py-3 text-gray-600">
                                @if($record->action === 'replacement')
                                    <span class="line-through text-red-500">{{ $record->old_part }}</span>
                                @elseif($record->action === 'installation')
                                    <span class="text-green-600">New ({{ $record->new_type_name }})</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 text-gray-600">
                                @if($record->action !== 'repair')
                                    {{ $record->new_name }} @if($record->new_brand) ({{ $record->new_brand }}) @endif
                                @else
                                    {{ $record->remark ?? '-' }}
                                @endif
                            </td>
                            <td class="py-3">
                                @if($record->action_date)
                                    <span class="inline-flex items-center text-xs font-semibold text-green-700">
                                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span>
                                        Applied ({{ \Carbon\Carbon::parse($record->action_date)->format('Y-m-d') }} by {{ $record->performer->name ?? 'System' }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-xs font-semibold text-amber-700">
                                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-amber-500"></span>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 whitespace-nowrap">
                                @if(!$record->action_date)
                                    <button wire:click="applyService({{ $record->id }})" class="text-green-600 hover:text-green-900 mr-2 font-semibold">Apply Action</button>
                                    <button wire:click="editService({{ $record->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                @endif
                                <button wire:click="deleteService({{ $record->id }})" class="text-red-600 hover:text-red-900" onclick="confirm('Delete service record?') || event.stopImmediatePropagation()">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-400">No service or repair history logged.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
