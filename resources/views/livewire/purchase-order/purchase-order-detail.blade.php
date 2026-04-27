<div>
    {{-- Purchase Order Detail Modal --}}
    <div x-data="{ open: @entangle('showModal').live }"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-on:keydown.escape.window="open = false; $wire.closeModal()">

        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-gray-500 opacity-75" x-on:click="open = false; $wire.closeModal()"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div class="max-h-screen overflow-y-auto">
                    {{-- Header --}}
                    <div class="bg-white px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Purchase Order #{{ $purchaseOrder->po_number ?? 'N/A' }}
                                </h3>
                                <x-status-badge :status="$purchaseOrder->status ?? 'draft'" class="ml-3" />
                            </div>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="px-4 py-5 sm:p-6">
                        @if($purchaseOrder)
                            <div class="space-y-6">
                                {{-- Basic Information --}}
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Basic Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">PO Number</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->po_number }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vendor</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->vendor_name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Invoice Date</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->invoice_date ? \Carbon\Carbon::parse($purchaseOrder->invoice_date)->format('d/m/Y') : 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Invoice Number</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->invoice_number ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Amount</dt>
                                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $purchaseOrder->currency ?? 'IDR' }} {{ number_format($purchaseOrder->total ?? 0, 0, ',', '.') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Payment Date</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->tanggal_pembayaran ? \Carbon\Carbon::parse($purchaseOrder->tanggal_pembayaran)->format('d/m/Y') : 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Category</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->category->name ?? 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Created By</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->user->name ?? 'N/A' }}</dd>
                                        </div>
                                    </div>
                                </div>

                                {{-- Approval Workflow --}}
                                @if($purchaseOrder->approvalRequest && $purchaseOrder->approvalRequest->steps->count() > 0)
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-blue-900 mb-3">Approval Workflow</h4>
                                        <div class="space-y-3">
                                            @foreach($purchaseOrder->approvalRequest->steps as $step)
                                                <div class="flex items-center justify-between p-3 bg-white rounded border">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0">
                                                            @if($step->status === 'APPROVED')
                                                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                            @elseif($step->status === 'REJECTED')
                                                                <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                            @else
                                                                <svg class="h-5 w-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                            @endif
                                                        </div>
                                                        <div class="ml-3">
                                                            <p class="text-sm font-medium text-gray-900">
                                                                Step {{ $step->sequence }}: {{ $step->approver_snapshot_name ?? 'Unknown' }}
                                                            </p>
                                                            <p class="text-xs text-gray-500">
                                                                {{ $step->approver_snapshot_role_slug ?? 'N/A' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <x-status-badge :status="strtolower($step->status)" size="sm" />
                                                        @if($step->acted_at)
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                {{ \Carbon\Carbon::parse($step->acted_at)->format('d/m/Y H:i') }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- PDF Preview --}}
                                @if($pdfUrl)
                                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900">PDF Document</h4>
                                            <a href="{{ route('po.download', $purchaseOrder->id) }}"
                                               target="_blank"
                                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100">
                                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Download
                                            </a>
                                        </div>
                                        <div class="p-4">
                                            <iframe src="{{ $pdfUrl }}" class="w-full h-96 border border-gray-300 rounded"></iframe>
                                        </div>
                                    </div>
                                @endif

                                {{-- Revision History --}}
                                @if($purchaseOrder->parent_po_number)
                                    <div class="bg-yellow-50 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-yellow-900 mb-2">Revision History</h4>
                                        <p class="text-sm text-yellow-700">
                                            This is a revision of Purchase Order #{{ $purchaseOrder->parent_po_number }}.
                                            Revision count: {{ $purchaseOrder->revision_count ?? 0 }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.203-2.47M12 22a9 9 0 100-18 9 9 0 000 18z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Purchase Order Not Found</h3>
                                <p class="mt-1 text-sm text-gray-500">The requested purchase order could not be loaded.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        @if($canEdit)
                            <button wire:click="edit"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ml-3">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                        @endif

                        @if($canApprove)
                            <button wire:click="approve"
                                    wire:confirm="Are you sure you want to approve this purchase order?"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 ml-3">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Approve
                            </button>
                        @endif

                        @if($canReject)
                            <button wire:click="$dispatch('open-reject-modal', {{ $purchaseOrder->id ?? 'null' }})"
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 ml-3">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Reject
                            </button>
                        @endif

                        <button wire:click="closeModal"
                                type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>