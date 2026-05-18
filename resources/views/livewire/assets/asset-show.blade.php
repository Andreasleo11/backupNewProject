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
                <!-- Using milon/barcode which is in composer.json -->
                @php
                    try {
                        $qrCode = DNS2D::getBarcodeSVG($asset->asset_tag, 'QRCODE', 6, 6);
                    } catch (\Exception $e) {
                        $qrCode = 'QR Code Error';
                    }
                @endphp
                
                @if($qrCode === 'QR Code Error')
                    <div class="text-red-500 text-sm">Error generating QR Code.</div>
                @else
                    {!! $qrCode !!}
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
                        <label class="text-xs font-medium text-gray-400 uppercase">Category</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->category->name }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Location</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->location->name ?? '-' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs font-medium text-gray-400 uppercase">Assigned To</label>
                        <div class="text-lg font-medium text-gray-800">{{ $asset->assignedTo->name ?? 'Unassigned' }}</div>
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
                </div>
            </div>

            <div class="mt-4 border-t border-gray-100 pt-4">
                <label class="text-xs font-medium text-gray-400 uppercase">Notes</label>
                <div class="text-gray-700 mt-1 bg-gray-50 p-4 rounded-lg min-h-20">
                    {{ $asset->notes ?? 'No notes available.' }}
                </div>
            </div>
        </div>
    </div>
</div>
