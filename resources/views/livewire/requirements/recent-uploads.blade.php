{{-- resources/views/livewire/requirements/recent-uploads.blade.php --}}
<div>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="recentUploads" wire:ignore.self>
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">
                Recent uploads — {{ $requirement?->code ?? '' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            @if (!$requirement || !$department)
                <div class="text-muted">Pick a requirement…</div>
            @else
                <div class="small text-muted mb-2">
                    {{ $requirement-> code }} · {{ $department->name }}
                </div>
                <div class="list-group list-group-flush">
                    @forelse($uploads as $u)
                    @php
                        $previewable = str_starts_with($u->mime_type, 'image/') || $u->mime_type === 'application/pdf';
                        $publicUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($u->path);
                        $downloadUrl = URL::signedRoute('uploads.download', ['upload' => $u->id]);
                        $color = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$u->status] ?? 'secondary';
                    @endphp
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="me-3">
                                    <div class="fw-semibold">{{ $u->original_name }}</div>
                                    <div class="small text-muted">
                                        {{ $u->mime_type }} · {{ number_format($u->size / 1024, 1) }} KB ·
                                        {{ $u->created_at->format('Y-m-d H:i') }}
                                        @if ($u->valid_until)
                                            · valid ≤ {{ $u->valid_until->format('Y-m-d') }}
                                        @endif
                                    </div>
                                    @if ($u->review_notes)
                                        <div class="small">{{ $u->review_notes }}</div>
                                    @endif
                                </div>

                                <div class="text-end">
                                    <span class="badge text-bg-{{$color}}">{{ ucfirst($u->status) }}</span>
                                    <div class="mt-2 d-flex gap-2 justify-content-end">
                                        <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        @if($previewable)
                                            <a href="{{ $publicUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-arrow-up-right"></i> Open
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted p-3">No uploads yet.</div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    @pushOnce('extraJs')
        <script>
            window.addEventListener('show-recent-uploads', () => {
                const el = document.getElementById('recentUploads');
                bootstrap.Offcanvas.getOrCreateInstance(el).show();
            });
        </script>
    @endPushOnce
</div>
