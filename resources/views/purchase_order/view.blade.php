@extends('new.layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">
                    Purchase Order Detail
                </h1>

                <nav class="mt-2" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1 text-sm text-slate-500">
                        <li>
                            <a href="{{ route('po.dashboard') }}" class="hover:text-slate-700">
                                Dashboard
                            </a>
                        </li>
                        <li class="px-1 text-slate-400">/</li>
                        <li>
                            <a href="{{ route('po.index') }}" class="hover:text-slate-700">
                                Purchase Orders
                            </a>
                        </li>
                        <li class="px-1 text-slate-400">/</li>
                        <li class="text-slate-700 font-medium">
                            {{ $purchaseOrder->po_number }}
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="flex flex-wrap gap-3 justify-end">
                <a href="{{ route('po.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Back to List
                </a>
                <a href="{{ route('po.download', $purchaseOrder->id) }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Download PDF
                </a>
            </div>
        </div>

        {{-- Summary card --}}
        <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl p-5 space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Purchase Order {{ $purchaseOrder->po_number }}
                    </h2>
                    <p class="text-sm text-slate-500">
                        Uploaded
                        <span class="font-medium text-slate-700">
                            {{ \Carbon\Carbon::parse($purchaseOrder->created_at)->format('d-m-Y') }}
                        </span>
                        by
                        <span class="font-medium text-slate-700">
                            {{ $purchaseOrder->user->name }}
                        </span>
                    </p>

                    @if ($purchaseOrder->approved_date)
                        <p class="text-sm text-slate-500">
                            Approved on
                            <span class="font-medium text-slate-700">
                                {{ \Carbon\Carbon::parse($purchaseOrder->approved_date)->setTimezone('Asia/Jakarta')->format('d-m-Y (H:i)') }}
                            </span>
                        </p>
                    @elseif($purchaseOrder->reason)
                        <p class="text-sm text-red-600">
                            Rejection reason:
                            <span class="font-medium">{{ $purchaseOrder->reason }}</span>
                        </p>
                    @endif

                    <div class="mt-2">
                        {{-- status badge partial --}}
                        @include('partials.po-status', ['po' => $purchaseOrder])
                    </div>
                </div>

                {{-- Director actions --}}
                @if ($purchaseOrder->status === 1 && $director)
                    <div x-data="poApproval({
                        signUrl: '{{ route('po.sign') }}',
                        rejectUrl: '{{ route('po.reject') }}',
                        id: '{{ $purchaseOrder->id }}',
                        filename: '{{ $purchaseOrder->filename }}',
                        csrf: '{{ csrf_token() }}'
                    })" class="flex flex-col gap-2 sm:items-end">
                        <div class="flex flex-wrap gap-2 justify-end">
                            <button type="button" @click="openSign()"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="loading">
                                <span x-show="!loading">Sign PDF</span>
                                <span x-show="loading" class="inline-flex items-center gap-2">
                                    <span
                                        class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                    Processing…
                                </span>
                            </button>

                            <button type="button" @click="openReject()"
                                class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="loading">
                                Reject PO
                            </button>
                        </div>

                        {{-- Sign confirm modal --}}
                        <div x-show="showSignConfirm" x-cloak
                            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 px-4">
                            <div @click.outside="showSignConfirm=false"
                                class="w-full max-w-md rounded-xl bg-white shadow-lg ring-1 ring-slate-200 p-5 space-y-4">
                                <h3 class="text-sm font-semibold text-slate-900">
                                    Confirm Signature
                                </h3>
                                <p class="text-sm text-slate-600">
                                    This will sign and approve the current purchase order PDF.
                                    You can review the document in the preview section below before confirming.
                                </p>
                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="showSignConfirm=false"
                                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                        :disabled="loading">
                                        Cancel
                                    </button>
                                    <button type="button" @click="submitSign"
                                        class="inline-flex items-center rounded-lg bg-indigo-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="loading">
                                        Confirm
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Reject modal --}}
                        <div x-show="showReject" x-cloak
                            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 px-4">
                            <div @click.outside="!loading && (showReject=false)"
                                class="w-full max-w-md rounded-xl bg-white shadow-lg ring-1 ring-slate-200 p-5 space-y-4">
                                <h3 class="text-sm font-semibold text-slate-900">
                                    Reject Purchase Order
                                </h3>
                                <p class="text-sm text-slate-600">
                                    Please provide a clear reason for rejecting this PO. The reason will be stored with the
                                    record.
                                </p>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        Rejection reason
                                    </label>
                                    <textarea x-model="reason" rows="3"
                                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-red-500 focus:ring-red-500"
                                        placeholder="Explain why this purchase order is rejected..."></textarea>
                                </div>
                                <div class="flex justify-between items-center pt-2">
                                    <p class="text-xs text-slate-400" x-text="reason.length + ' / 500'"></p>
                                    <div class="flex gap-2">
                                        <button type="button" @click="showReject=false"
                                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                            :disabled="loading">
                                            Cancel
                                        </button>
                                        <button type="button" @click="submitReject"
                                            class="inline-flex items-center rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                                            :disabled="loading || !reason.trim()">
                                            Reject PO
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Details table-ish layout --}}
            <div class="border-t border-slate-100 pt-4 grid gap-4 sm:grid-cols-2 text-sm text-slate-800">
                <div class="space-y-1">
                    <div class="text-xs font-semibold uppercase text-slate-500">
                        Vendor
                    </div>
                    <div>{{ $purchaseOrder->vendor_name }}</div>

                    <div class="mt-3 text-xs font-semibold uppercase text-slate-500">
                        Invoice Number
                    </div>
                    <div>{{ $purchaseOrder->invoice_number }}</div>

                    <div class="mt-3 text-xs font-semibold uppercase text-slate-500">
                        Category
                    </div>
                    <div>{{ $purchaseOrder->category->name ?? '-' }}</div>
                </div>

                <div class="space-y-1">
                    <div class="text-xs font-semibold uppercase text-slate-500">
                        Payment Date
                    </div>
                    <div>
                        {{ \Carbon\Carbon::parse($purchaseOrder->tanggal_pembayaran)->format('d-m-Y') }}
                    </div>

                    <div class="mt-3 text-xs font-semibold uppercase text-slate-500">
                        Total
                    </div>
                    <div class="font-semibold">
                        {{ $purchaseOrder->currency . ' ' . number_format($purchaseOrder->total, 2, '.', ',') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- PDF preview card --}}
        <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-900">
                    Invoice Document
                </h2>
                <span class="text-xs text-slate-500">
                    PDF preview
                </span>
            </div>
            <div class="bg-slate-50">
                <iframe src="{{ asset('storage/pdfs/' . $purchaseOrder->filename) }}" width="100%" height="700"
                    class="block w-full border-0"></iframe>
            </div>
        </div>

        {{-- Upload section (non-director) --}}
        @if (!$director)
            <div class="container mb-4 space-y-4" x-data="{ openUploadFiles: false }">
                @if ($user->id == $purchaseOrder->creator_id || $user->specification->name === 'PURCHASER' || $user->is_head === 1)
                    <div class="flex justify-end">
                        <button type="button" @click="openUploadFiles = true"
                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                            Upload related files
                        </button>
                    </div>

                    {{-- Modal needs to live inside the same x-data scope to see openUploadFiles --}}
                    @include('partials.upload-files-modal', [
                        'doc_id' => $purchaseOrder->po_number,
                    ])
                @endif

                <section aria-label="uploaded">
                    @include('partials.uploaded-section', [
                        'showDeleteButton' => $user->id === $purchaseOrder->creator_id || $user->specification->name === 'PURCHASER',
                    ])
                </section>
            </div>
        @endif


        {{-- Revision history --}}
        @if ($purchaseOrder->status === 4 || $purchaseOrder->revision_count > 0)
            <section aria-label="history" class="space-y-4">
                <div class="flex justify-end">
                    <form action="{{ route('po.create') }}" method="post">
                        @csrf
                        <input type="hidden" name="parent_po_number" value="{{ $purchaseOrder->po_number }}">
                        <button type="submit"
                            class="inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                            Create Revision
                        </button>
                    </form>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl">
                    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">
                            Revision History
                        </h2>
                        <p class="text-xs text-slate-500">
                            All revisions linked to this PO number.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <th class="px-4 py-2">PO Number</th>
                                    <th class="px-4 py-2">Category</th>
                                    <th class="px-4 py-2">Vendor</th>
                                    <th class="px-4 py-2">Invoice Date</th>
                                    <th class="px-4 py-2">Invoice No.</th>
                                    <th class="px-4 py-2">Payment Date</th>
                                    <th class="px-4 py-2">Currency</th>
                                    <th class="px-4 py-2">Total</th>
                                    <th class="px-4 py-2">Created At</th>
                                    <th class="px-4 py-2">Created By</th>
                                    <th class="px-4 py-2">Approved Date</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2">Action</th>
                                    <th class="px-4 py-2">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($revisions as $revision)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->po_number }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->category->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->vendor_name }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->invoice_date }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->invoice_number }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->tanggal_pembayaran }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->currency }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ number_format($revision->total, 2, '.', ',') }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->created_at }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->user->name }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->approved_date }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            @include('partials.po-status', ['po' => $revision])
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            @include('partials.po-actions', ['po' => $revision])
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $revision->reason }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="px-4 py-6 text-center text-sm text-slate-500">
                                            No revision data.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <script>
        function poApproval(config) {
            return {
                loading: false,
                showSignConfirm: false,
                showReject: false,
                reason: '',
                ...config,

                openSign() {
                    this.showSignConfirm = true;
                },

                openReject() {
                    this.showReject = true;
                    this.reason = '';
                },

                async submitSign() {
                    if (this.loading) return;
                    this.loading = true;

                    try {
                        const res = await fetch(this.signUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                            },
                            body: JSON.stringify({
                                filename: this.filename,
                                id: this.id,
                            }),
                        });

                        const data = await res.json();
                        alert(data.message || 'Purchase order signed successfully.');
                        window.location.reload();
                    } catch (e) {
                        console.error(e);
                        alert('Failed to sign purchase order.');
                    } finally {
                        this.loading = false;
                        this.showSignConfirm = false;
                    }
                },

                async submitReject() {
                    if (this.loading || !this.reason.trim()) return;
                    this.loading = true;

                    try {
                        const res = await fetch(this.rejectUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                            },
                            body: JSON.stringify({
                                filename: this.filename,
                                id: this.id,
                                reason: this.reason,
                            }),
                        });

                        const data = await res.json();
                        alert(data.message || 'Purchase order rejected.');
                        window.location.reload();
                    } catch (e) {
                        console.error(e);
                        alert('Failed to reject purchase order.');
                    } finally {
                        this.loading = false;
                        this.showReject = false;
                    }
                },
            };
        }
    </script>
@endsection
