<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Maintenance Reports</h1>
            <p class="text-gray-600">Track and manage periodic (caturwulan) inspections of company assets.</p>
        </div>
        <div>
            @if($showForm || $showDetail)
                <button wire:click="resetFields" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-semibold">
                    ← Back to List
                </button>
            @else
                <button wire:click="showAddForm()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-semibold">
                    + Create Report
                </button>
            @endif
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg shadow-sm">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($showForm)
        <!-- Form Section -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                {{ $editingReportId ? 'Edit Maintenance Report' : 'Create Maintenance Report' }}
            </h2>

            <form wire:submit.prevent="store" class="space-y-6">
                <!-- Header Fields -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-150 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Select Asset <span class="text-red-500">*</span></label>
                        <select wire:model="assetId" {{ $editingReportId ? 'disabled' : '' }} class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="">-- Choose an Asset --</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">
                                    {{ $asset->asset_tag }} — {{ $asset->name }} ({{ $asset->username ?? 'No Operator' }})
                                </option>
                            @endforeach
                        </select>
                        @error('assetId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Revision Date</label>
                        <input type="date" wire:model="revisionDate" class="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        @error('revisionDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Checklist Cards -->
                <div class="bg-white rounded-xl border border-gray-150 overflow-hidden shadow-sm">
                    <div class="p-4 bg-gray-50 border-b border-gray-150 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Inspection Checklist</h3>
                            <p class="text-xs text-gray-500">Check items to inspect, specify their conditions and checkers.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="checkAll" class="px-3 py-1.5 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-lg text-xs font-semibold transition">
                                Check All
                            </button>
                            <button type="button" wire:click="setAllGood" class="px-3 py-1.5 bg-emerald-600 text-white hover:bg-emerald-700 rounded-lg text-xs font-semibold transition">
                                All Good Condition
                            </button>
                            <button type="button" wire:click="setCheckedByMe" class="px-3 py-1.5 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg text-xs font-semibold transition">
                                Checked by Me
                            </button>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-150 p-4 space-y-6">
                        @foreach($checklistGroups as $group)
                            <div class="space-y-3 pt-2 first:pt-0">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-md font-bold text-gray-800">{{ $group->name }}</h4>
                                    <button type="button" wire:click="addNewChecklistItem({{ $group->id }})" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs font-semibold transition">
                                        + Add Item
                                    </button>
                                </div>

                                <!-- Predefined checklist items -->
                                <div class="space-y-3">
                                    @foreach($group->items as $item)
                                        <div class="flex flex-col md:flex-row md:items-center justify-between p-3 rounded-lg border {{ ($checklist[$item->id]['checked'] ?? false) ? 'border-indigo-200 bg-indigo-50/30' : 'border-gray-150 bg-white' }} gap-3 transition">
                                            <div class="flex items-center space-x-3">
                                                <input type="checkbox" wire:model="checklist.{{ $item->id }}.checked" id="chk_{{ $item->id }}" class="h-4.5 w-4.5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="chk_{{ $item->id }}" class="text-sm font-semibold text-gray-800 select-none cursor-pointer">{{ $item->name }}</label>
                                            </div>

                                            <div class="flex flex-wrap md:flex-nowrap items-center gap-2">
                                                <!-- Condition -->
                                                <div>
                                                    <select wire:model="checklist.{{ $item->id }}.condition" {{ !($checklist[$item->id]['checked'] ?? false) ? 'disabled' : '' }} class="px-3 py-1.5 border rounded-lg text-xs bg-white focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:text-gray-400">
                                                        <option value="">-- Condition --</option>
                                                        <option value="good">Good</option>
                                                        <option value="bad">Bad</option>
                                                    </select>
                                                    @error("checklist.{$item->id}.condition") <p class="text-red-500 text-2xs mt-0.5">{{ $message }}</p> @enderror
                                                </div>

                                                <!-- Remark -->
                                                <div>
                                                    <input type="text" placeholder="Remarks" wire:model="checklist.{{ $item->id }}.remark" {{ !($checklist[$item->id]['checked'] ?? false) ? 'disabled' : '' }} class="px-3 py-1.5 border rounded-lg text-xs bg-white focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:text-gray-400 w-44 md:w-56">
                                                    @error("checklist.{$item->id}.remark") <p class="text-red-500 text-2xs mt-0.5">{{ $message }}</p> @enderror
                                                </div>

                                                <!-- Checker -->
                                                <div>
                                                    <select wire:model="checklist.{{ $item->id }}.checked_by" {{ !($checklist[$item->id]['checked'] ?? false) ? 'disabled' : '' }} class="px-3 py-1.5 border rounded-lg text-xs bg-white focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:text-gray-400">
                                                        <option value="">-- Checker --</option>
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error("checklist.{$item->id}.checked_by") <p class="text-red-500 text-2xs mt-0.5">{{ $message }}</p> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Dynamically added checklist items -->
                                    @foreach($newItems as $index => $newItem)
                                        @if($newItem['group_id'] == $group->id)
                                            <div class="flex flex-col md:flex-row md:items-center justify-between p-3 rounded-lg border border-emerald-250 bg-emerald-50/20 gap-3 transition">
                                                <div class="flex items-center space-x-2 flex-1">
                                                    <span class="text-emerald-600 text-xs font-bold font-mono">[NEW]</span>
                                                    <input type="text" placeholder="Enter Custom Item Name..." wire:model="newItems.{{ $index }}.name" class="px-3 py-1.5 border rounded-lg text-xs bg-white focus:ring-indigo-500 focus:border-indigo-500 w-full">
                                                    @error("newItems.{$index}.name") <p class="text-red-500 text-2xs mt-0.5">{{ $message }}</p> @enderror
                                                </div>

                                                <div class="flex flex-wrap md:flex-nowrap items-center gap-2">
                                                    <!-- Condition -->
                                                    <div>
                                                        <select wire:model="newItems.{{ $index }}.condition" class="px-3 py-1.5 border rounded-lg text-xs bg-white focus:ring-indigo-500 focus:border-indigo-500">
                                                            <option value="good">Good</option>
                                                            <option value="bad">Bad</option>
                                                        </select>
                                                        @error("newItems.{$index}.condition") <p class="text-red-500 text-2xs mt-0.5">{{ $message }}</p> @enderror
                                                    </div>

                                                    <!-- Remark -->
                                                    <div>
                                                        <input type="text" placeholder="Remarks" wire:model="newItems.{{ $index }}.remark" class="px-3 py-1.5 border rounded-lg text-xs bg-white focus:ring-indigo-500 focus:border-indigo-500 w-40 md:w-48">
                                                        @error("newItems.{$index}.remark") <p class="text-red-500 text-2xs mt-0.5">{{ $message }}</p> @enderror
                                                    </div>

                                                    <!-- Checker -->
                                                    <div>
                                                        <select wire:model="newItems.{{ $index }}.checked_by" class="px-3 py-1.5 border rounded-lg text-xs bg-white focus:ring-indigo-500 focus:border-indigo-500">
                                                            <option value="">-- Checker --</option>
                                                            @foreach($users as $user)
                                                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error("newItems.{$index}.checked_by") <p class="text-red-500 text-2xs mt-0.5">{{ $message }}</p> @enderror
                                                    </div>

                                                    <!-- Remove -->
                                                    <button type="button" wire:click="removeNewChecklistItem({{ $index }})" class="px-2 py-1.5 bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-800 rounded-lg text-xs transition font-semibold">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <button type="button" wire:click="resetFields" class="px-4 py-2 bg-gray-250 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-semibold">
                        Save Report
                    </button>
                </div>
            </form>
        </div>
    @elseif($showDetail)
        <!-- Detail Section -->
        @if($activeReport)
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 max-w-5xl mx-auto space-y-6">
                <!-- Header Metadata -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-150 pb-4 gap-4">
                    <div>
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Document details</span>
                        <h2 class="text-2xl font-bold text-gray-800 mt-0.5">{{ $activeReport->document_number }}</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="px-3 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold">
                            Caturwulan {{ $activeReport->period }} — {{ $activeReport->year }}
                        </div>
                        @if($activeReport->revision_date)
                            <div class="px-3 py-1.5 bg-gray-100 border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold">
                                Revision Date: {{ \Carbon\Carbon::parse($activeReport->revision_date)->format('d F Y') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Asset Profile Box -->
                <div class="bg-gray-50 border border-gray-150 rounded-xl p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <span class="block text-2xs uppercase font-bold text-gray-400">Asset Name</span>
                        <a href="{{ route('assets.show', $activeReport->asset_id) }}" class="text-sm font-bold text-indigo-600 hover:underline">{{ $activeReport->asset->name }}</a>
                    </div>
                    <div>
                        <span class="block text-2xs uppercase font-bold text-gray-400">Asset Tag</span>
                        <span class="text-sm font-mono text-gray-700">{{ $activeReport->asset->asset_tag }}</span>
                    </div>
                    <div>
                        <span class="block text-2xs uppercase font-bold text-gray-400">IP Address</span>
                        <span class="text-sm text-gray-700 font-mono">{{ $activeReport->asset->ip_address ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-2xs uppercase font-bold text-gray-400">Operator</span>
                        <span class="text-sm text-gray-700">{{ $activeReport->asset->username ?? '-' }}</span>
                    </div>
                </div>

                <!-- Checklist Details Grouped -->
                <div class="space-y-6">
                    @php
                        $groupedDetails = $activeReport->details->groupBy(function($detail) {
                            return $detail->checklistItem->group->name;
                        });
                    @endphp

                    @forelse($groupedDetails as $groupName => $details)
                        <div class="border border-gray-150 rounded-xl overflow-hidden shadow-sm">
                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-150 font-bold text-gray-800 text-sm">
                                {{ $groupName }}
                            </div>
                            <table class="w-full text-left">
                                <thead class="bg-white border-b border-gray-100 text-2xs text-gray-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-4 py-2">Item</th>
                                        <th class="px-4 py-2">Condition</th>
                                        <th class="px-4 py-2">Remark</th>
                                        <th class="px-4 py-2">Checked By</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-sm">
                                    @foreach($details as $detail)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $detail->checklistItem->name }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $detail->condition === 'good' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ ucfirst($detail->condition) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 text-xs italic">{{ $detail->remark ?? '-' }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $detail->checked_by }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <p class="text-center text-gray-400 py-8">No checklist items logged for this report.</p>
                    @endforelse
                </div>
            </div>
        @endif
    @else
        <!-- List View -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
            <!-- Search & Filters -->
            <div class="p-4 border-b border-gray-100 flex flex-wrap items-center gap-3">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search report by asset, tag, IP..." class="flex-1 min-w-48 px-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white">

                <select wire:model="selectedPeriod" class="px-4 py-2 border rounded-lg bg-white">
                    <option value="">All Periods</option>
                    <option value="1">Caturwulan 1</option>
                    <option value="2">Caturwulan 2</option>
                    <option value="3">Caturwulan 3</option>
                </select>

                <select wire:model="selectedYear" class="px-4 py-2 border rounded-lg bg-white">
                    <option value="">All Years</option>
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Document No</th>
                            <th class="px-6 py-3">Asset</th>
                            <th class="px-6 py-3">Period</th>
                            <th class="px-6 py-3">Revision Date</th>
                            <th class="px-6 py-3">Items Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($reports as $report)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-bold text-gray-800">{{ $report->document_number }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-800 font-semibold">{{ $report->asset->name }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $report->asset->asset_tag }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg">
                                        Caturwulan {{ $report->period }} — {{ $report->year }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $report->revision_date ? \Carbon\Carbon::parse($report->revision_date)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $total = $report->details->count();
                                        $bad = $report->details->where('condition', 'bad')->count();
                                    @endphp
                                    <div class="flex items-center space-x-2 text-xs">
                                        <span class="font-bold text-gray-700">{{ $total }} inspected</span>
                                        @if($bad > 0)
                                            <span class="px-2 py-0.5 bg-red-100 text-red-750 font-bold rounded">{{ $bad }} bad</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-green-100 text-green-750 font-bold rounded">All Good</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap font-medium">
                                    <button wire:click="show({{ $report->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3 transition font-semibold">View</button>
                                    <button wire:click="edit({{ $report->id }})" class="text-emerald-600 hover:text-emerald-900 mr-3 transition font-semibold">Edit</button>
                                    <button wire:click="delete({{ $report->id }})" class="text-red-650 hover:text-red-900 transition font-semibold" onclick="confirm('Are you sure you want to delete this report?') || event.stopImmediatePropagation()">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">No maintenance reports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100">
                {{ $reports->links() }}
            </div>
        </div>
    @endif
</div>
