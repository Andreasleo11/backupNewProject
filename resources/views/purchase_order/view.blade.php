@extends('new.layouts.app')

@section('content')
    @php
        $statusEnum = $purchaseOrder->getStatusEnum();
        
        // Build activity feed
        $activities = collect();
        
        // 1. Initial Submission
        if ($purchaseOrder->approvalRequest && $purchaseOrder->approvalRequest->submitted_at) {
            $activities->push((object)[
                'type' => 'submission',
                'date' => $purchaseOrder->approvalRequest->submitted_at,
                'user' => $purchaseOrder->user->name,
                'label' => 'Submitted for Approval',
                'icon' => 'bi-send',
                'color' => 'indigo'
            ]);
        }
        
        // 2. Approval Actions
        if ($purchaseOrder->approvalRequest) {
            foreach ($purchaseOrder->approvalRequest->actions as $action) {
                $activities->push((object)[
                    'type' => 'approval',
                    'date' => $action->created_at,
                    'user' => $action->causer->name ?? 'System',
                    'label' => 'Status: ' . $action->to_status,
                    'remarks' => $action->remarks,
                    'icon' => match($action->to_status) {
                        'APPROVED' => 'bi-check-circle',
                        'REJECTED' => 'bi-x-circle',
                        'RETURNED' => 'bi-arrow-left-right',
                        default => 'bi-info-circle'
                    },
                    'color' => match($action->to_status) {
                        'APPROVED' => 'emerald',
                        'REJECTED' => 'rose',
                        'RETURNED' => 'amber',
                        default => 'slate'
                    }
                ]);
            }
        }
        
        // 3. Downloads
        foreach ($purchaseOrder->downloadLogs as $log) {
            $activities->push((object)[
                'type' => 'download',
                'date' => $log->created_at,
                'user' => $log->user->name,
                'label' => 'Downloaded Document',
                'icon' => 'bi-cloud-download',
                'color' => 'blue'
            ]);
        }
        
        $activities = $activities->sortByDesc('date');
    @endphp

    <div class="px-4 sm:px-6 lg:px-8 py-8 space-y-8 max-w-[1600px] mx-auto">
        {{-- Header --}}
        <header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                            {{ $purchaseOrder->po_number }}
                        </h1>
                        @include('partials.po-status', ['po' => $purchaseOrder])
                        
                        @if($purchaseOrder->workflow_status === 'IN_REVIEW' && $purchaseOrder->current_approver)
                            <div class="flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-700 rounded-full border border-amber-100 text-xs font-bold animate-pulse">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                                Currently with: {{ $purchaseOrder->current_approver }}
                            </div>
                        @endif
                    </div>
                    <nav class="mt-3" aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                            <li><a href="{{ route('po.index') }}" class="hover:text-indigo-600 transition-colors">Procurement</a></li>
                            <li><i class="bi bi-chevron-right text-[10px]"></i></li>
                            <li class="text-slate-600">Purchase Order Detail</li>
                        </ol>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('po.index') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-slate-50">
                        <i class="bi bi-arrow-left"></i>
                        Back to List
                    </a>
                    <a href="{{ route('po.download', $purchaseOrder->id) }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98]">
                        <i class="bi bi-cloud-arrow-down-fill text-lg"></i>
                        Download PDF
                    </a>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Main Content: Timeline & PDF --}}
            <div class="lg:col-span-8 space-y-8">
                
                {{-- Activity Feed (The Historian Core) --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-8 py-5 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <i class="bi bi-clock-history text-indigo-500"></i>
                            Activity History
                        </h2>
                        <span class="text-[10px] font-bold text-slate-400">Real-time Audit Trail</span>
                    </div>
                    <div class="p-8">
                        <div class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-indigo-100 before:via-slate-100 before:to-transparent">
                            @forelse($activities as $activity)
                                <div class="relative flex items-start group">
                                    <div class="absolute left-0 flex h-10 w-10 items-center justify-center rounded-2xl bg-white ring-4 ring-slate-50 transition-all group-hover:scale-110 group-hover:shadow-md">
                                        <i class="bi {{ $activity->icon }} text-{{ $activity->color }}-500 text-lg"></i>
                                    </div>
                                    <div class="ml-16">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
                                            <span class="text-sm font-black text-slate-800">{{ $activity->label }}</span>
                                            <time class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter flex items-center gap-1">
                                                <i class="bi bi-calendar3"></i>
                                                {{ $activity->date->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}
                                            </time>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Performed by <span class="font-bold text-slate-900">{{ $activity->user }}</span>
                                        </p>
                                        @if(isset($activity->remarks) && $activity->remarks)
                                            <div class="mt-3 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-600 leading-relaxed italic shadow-inner">
                                                "{{ $activity->remarks }}"
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="bi bi-inbox text-slate-300 text-2xl"></i>
                                    </div>
                                    <p class="text-slate-400 text-sm font-medium">No activity history found.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- PDF View --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-8 py-5 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <i class="bi bi-file-earmark-pdf-fill text-rose-500"></i>
                            Original Document
                        </h2>
                        <div class="flex gap-2">
                            <button onclick="document.querySelector('iframe').contentWindow.print()" class="h-8 w-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-500 transition-colors">
                                <i class="bi bi-printer"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-2 bg-slate-800">
                        <iframe src="{{ asset('storage/pdfs/' . $purchaseOrder->filename) }}#toolbar=0"
                            class="w-full h-[900px] rounded-2xl shadow-2xl" frameborder="0"></iframe>
                    </div>
                </div>

                {{-- Revisions --}}
                @if ($purchaseOrder->status === 4 || $purchaseOrder->revision_count > 0 || $revisions->count() > 0)
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-8 py-5 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <i class="bi bi-layers-half text-amber-500"></i>
                                Version History
                            </h2>
                            @if($purchaseOrder->canBeEdited())
                                <form action="{{ route('po.create') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="parent_po_number" value="{{ $purchaseOrder->po_number }}">
                                    <button type="submit" class="text-xs font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">
                                        + Create New Version
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50/80 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-8 py-4">PO Number</th>
                                        <th class="px-8 py-4">Status</th>
                                        <th class="px-8 py-4 text-right">Amount</th>
                                        <th class="px-8 py-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($revisions as $rev)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-8 py-4 font-bold text-slate-900">{{ $rev->po_number }}</td>
                                            <td class="px-8 py-4">@include('partials.po-status', ['po' => $rev])</td>
                                            <td class="px-8 py-4 text-right font-mono font-bold text-slate-700">
                                                {{ number_format($rev->total, 0, ',', '.') }}
                                            </td>
                                            <td class="px-8 py-4">
                                                <a href="{{ route('po.view', $rev->id) }}" class="inline-flex items-center gap-1 text-indigo-600 font-bold hover:gap-2 transition-all">
                                                    View <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar: Summary & Actions --}}
            <aside class="lg:col-span-4 space-y-6">
                
                {{-- Quick Actions Card (If Director) --}}
                @if ($purchaseOrder->workflow_status === 'IN_REVIEW' && $director)
                    <div class="bg-indigo-600 rounded-3xl shadow-xl p-8 text-white relative overflow-hidden group" 
                         x-data="poApproval({
                            signUrl: '{{ route('po.sign') }}',
                            rejectUrl: '{{ route('po.reject') }}',
                            id: '{{ $purchaseOrder->id }}',
                            filename: '{{ $purchaseOrder->filename }}',
                            csrf: '{{ csrf_token() }}'
                         })">
                        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        <h3 class="text-xl font-black mb-2 flex items-center gap-3">
                            <i class="bi bi-shield-lock-fill"></i>
                            Action Required
                        </h3>
                        <p class="text-indigo-100 text-sm font-medium mb-8 leading-relaxed">
                            Please review the document and provide your digital signature to authorize this purchase.
                        </p>
                        
                        <div class="space-y-4">
                            <button @click="openSign()" :disabled="loading"
                                    class="w-full flex items-center justify-center gap-3 bg-white text-indigo-600 py-4 rounded-2xl font-black shadow-lg hover:shadow-indigo-900/20 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50">
                                <span x-show="!loading" class="flex items-center gap-2">
                                    <i class="bi bi-vector-pen text-lg"></i>
                                    Sign & Approve
                                </span>
                                <span x-show="loading" class="animate-spin h-5 w-5 border-3 border-indigo-600 border-t-transparent rounded-full"></span>
                            </button>
                            <button @click="openReject()" :disabled="loading"
                                    class="w-full flex items-center justify-center gap-2 bg-white/10 text-white border border-white/20 py-3.5 rounded-2xl font-bold hover:bg-white/20 transition-all">
                                Reject with Reason
                            </button>
                        </div>

                        {{-- Modals --}}
                        <template x-teleport="body">
                            <div x-show="showSignConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"></div>
                                <div @click.outside="showSignConfirm=false" class="relative w-full max-w-md rounded-[2.5rem] bg-white shadow-2xl p-8 space-y-6">
                                    <div class="h-16 w-16 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mx-auto">
                                        <i class="bi bi-vector-pen text-3xl"></i>
                                    </div>
                                    <div class="text-center space-y-2">
                                        <h3 class="text-2xl font-black text-slate-900">Authorize Purchase</h3>
                                        <p class="text-sm text-slate-500 leading-relaxed">
                                            Your digital signature will be applied to the official PDF. This action is irreversible and legally binding.
                                        </p>
                                    </div>
                                    <div class="flex flex-col gap-3">
                                        <button @click="submitSign" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-lg hover:bg-indigo-700 transition-all">
                                            Apply Digital Signature
                                        </button>
                                        <button @click="showSignConfirm=false" class="w-full py-3 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">
                                            Maybe later
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-teleport="body">
                            <div x-show="showReject" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"></div>
                                <div @click.outside="!loading && (showReject=false)" class="relative w-full max-w-md rounded-[2.5rem] bg-white shadow-2xl p-8 space-y-6 text-center">
                                    <div class="h-16 w-16 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 mx-auto">
                                        <i class="bi bi-x-octagon text-3xl"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <h3 class="text-2xl font-black text-slate-900">Reject Order</h3>
                                        <p class="text-sm text-slate-500">Provide a reason to help the requester improve this PO.</p>
                                    </div>
                                    <textarea x-model="reason" rows="4" class="w-full rounded-2xl border-slate-200 focus:border-rose-500 focus:ring-rose-500 text-sm placeholder:text-slate-300" placeholder="e.g., Price mismatch with quotation..."></textarea>
                                    <div class="flex flex-col gap-3">
                                        <button @click="submitReject" :disabled="!reason.trim() || loading" class="w-full py-4 bg-rose-600 text-white rounded-2xl font-black shadow-lg hover:bg-rose-700 transition-all disabled:opacity-50">
                                            Confirm Rejection
                                        </button>
                                        <button @click="showReject=false" class="w-full py-3 text-sm font-bold text-slate-400 hover:text-slate-600">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @endif

                {{-- Financial & Info Card --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden divide-y divide-slate-50">
                    <div class="p-8">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="h-14 w-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <i class="bi bi-wallet2 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Valuation</p>
                                <p class="text-2xl font-black text-slate-900 mt-1">
                                    <span class="text-sm font-bold text-slate-300 uppercase mr-1">{{ $purchaseOrder->currency }}</span>
                                    {{ number_format($purchaseOrder->total, 2, '.', ',') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Vendor Profile</label>
                                <p class="text-sm font-extrabold text-slate-800 mt-1">{{ $purchaseOrder->vendor_name }}</p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Department Category</label>
                                <p class="text-sm font-extrabold text-slate-800 mt-1">{{ $purchaseOrder->category->name ?? 'General Procurement' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 bg-slate-50/30 grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Inv. Date</label>
                            <p class="text-xs font-bold text-slate-700 mt-1">{{ $purchaseOrder->invoice_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pymt. Date</label>
                            <p class="text-xs font-bold text-slate-700 mt-1">{{ \Carbon\Carbon::parse($purchaseOrder->tanggal_pembayaran)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Related Files --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-8 py-5 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">Attachments</h2>
                        @if ($user->id == $purchaseOrder->creator_id || $user->hasRole('purchaser'))
                            <button @click="$dispatch('open-upload-modal')" class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        @endif
                    </div>
                    <div class="p-8">
                        @include('partials.file-attachments', [
                            'files' => $files,
                            'showDelete' => $user->id === $purchaseOrder->creator_id || $user->hasRole('purchaser'),
                            'title' => ''
                        ])
                    </div>
                </div>

                {{-- Requester Info Footer --}}
                <div class="p-6 bg-slate-900 rounded-3xl text-white flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center font-black text-lg">
                        {{ substr($purchaseOrder->user->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">Originator</p>
                        <p class="text-sm font-bold truncate">{{ $purchaseOrder->user->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">Dept</p>
                        <p class="text-xs font-bold text-indigo-300">{{ $purchaseOrder->user->department->name ?? 'N/A' }}</p>
                    </div>
                </div>

            </aside>
        </div>
    </div>

    @push('modals')
        @include('partials.upload-files-modal', ['doc_id' => $purchaseOrder->po_number])
    @endpush

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
