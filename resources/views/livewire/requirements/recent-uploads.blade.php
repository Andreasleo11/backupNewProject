{{-- Recent Uploads Slide-Over — Livewire component view --}}
{{-- Tailwind & Alpine.js --}}
<div x-data="{
    open: false,
    openModal(detail) {
        this.open = true;
        $wire.open(detail);
    }
}" @trigger-history-modal.window="openModal($event.detail)"
    @hide-recent-uploads.window="open = false" class="relative z-[100]" x-cloak x-show="open">

    {{-- Backdrop --}}
    <div x-show="open" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="open = false"></div>

    <div class="fixed inset-0 overflow-hidden z-10">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                {{-- Slide-over panel --}}
                <div x-show="open" x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
                    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
                    x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                    class="pointer-events-auto w-screen max-w-md">

                    <div class="flex h-full flex-col bg-white shadow-2xl">
                        {{-- Header --}}
                        <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between bg-slate-50">
                            <div>
                                <h1 class="text-base font-bold text-slate-800">Recent Uploads</h1>
                                @if ($requirement && $department)
                                    <p class="text-[11px] font-semibold text-slate-500 mt-1">
                                        <span class="text-indigo-600">{{ $requirement->code }}</span> •
                                        {{ $department->name }}
                                    </p>
                                @endif
                            </div>
                            <button @click="open = false"
                                class="text-slate-400 hover:text-rose-500 transition-colors shrink-0">
                                <span class="sr-only">Close panel</span>
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        {{-- Body: Uploads List --}}
                        <div class="flex-1 overflow-y-auto w-full">
                            @if (!$requirement || !$department)
                                <div class="px-6 py-12 text-center">
                                    <i class="bx bx-ghost text-4xl text-slate-300 mb-2"></i>
                                    <p class="text-sm text-slate-500">Pick a requirement to view its history.</p>
                                </div>
                            @else
                                <div class="divide-y divide-slate-100">
                                    @forelse($uploads as $u)
                                        @php
                                            $previewable =
                                                str_starts_with($u->mime_type, 'image/') ||
                                                $u->mime_type === 'application/pdf';
                                            $publicUrl = \Illuminate\Support\Facades\Storage::disk('public')->url(
                                                $u->path,
                                            );
                                            $downloadUrl = URL::signedRoute('uploads.download', ['upload' => $u->id]);
                                            $colors = [
                                                'pending' => 'bg-amber-100 text-amber-700',
                                                'approved' => 'bg-emerald-100 text-emerald-700',
                                                'rejected' => 'bg-rose-100 text-rose-700',
                                            ];
                                            $badge = $colors[$u->status] ?? 'bg-slate-100 text-slate-600';
                                        @endphp
                                        <div class="p-5 hover:bg-slate-50/50 transition-colors group">
                                            <div class="flex items-start justify-between gap-4 mb-3">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-bold text-slate-800 truncate"
                                                        title="{{ $u->original_name }}">{{ $u->original_name }}</p>
                                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                                        {{ str_replace('application/', '', $u->mime_type) }} •
                                                        {{ number_format($u->size / 1024, 1) }} KB
                                                    </p>
                                                </div>
                                                <span
                                                    class="inline-flex shrink-0 items-center justify-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $badge }}">
                                                    {{ $u->status }}
                                                </span>
                                            </div>

                                            <div
                                                class="flex items-center gap-4 text-[10px] font-medium text-slate-400 mb-3">
                                                <div class="flex items-center gap-1">
                                                    <i class="bx bx-time text-slate-300"></i>
                                                    {{ $u->created_at->format('d M y, H:i') }}
                                                </div>
                                                @if ($u->valid_until)
                                                    <div class="flex items-center gap-1 text-slate-500">
                                                        <i class="bx bx-calendar text-slate-300"></i> Valid ≤
                                                        {{ $u->valid_until->format('d M y') }}
                                                    </div>
                                                @endif
                                            </div>

                                            @if ($u->review_notes)
                                                <div class="rounded bg-slate-100 border border-slate-200 p-2 mb-3">
                                                    <div class="flex items-start gap-1.5">
                                                        <i class="bx bx-comment-detail text-slate-400 mt-0.5"></i>
                                                        <p class="text-xs text-slate-600 leading-tight">
                                                            {{ $u->review_notes }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ $downloadUrl }}"
                                                    class="inline-flex items-center justify-center h-7 px-3 rounded border border-slate-200 bg-white text-slate-600 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 text-[11px] font-bold transition-colors">
                                                    <i class="bx bx-download mr-1 text-sm"></i> Download
                                                </a>
                                                @if ($previewable)
                                                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener"
                                                        class="inline-flex items-center justify-center h-7 px-3 rounded border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white text-[11px] font-bold transition-all">
                                                        Open <i class="bx bx-link-external ml-1"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-6 py-12 text-center">
                                            <i class="bx bx-folder-open text-3xl text-slate-200 mb-2"></i>
                                            <p class="text-xs text-slate-400 italic">No uploads found.</p>
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
