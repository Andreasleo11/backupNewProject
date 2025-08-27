<div>
    <div x-data="{ q: '', previewUrl: null, isImg: false }" x-init="$nextTick(() => {
        const uploadPreviewModal = document.getElementById('uploadPreviewModal');
        if (!uploadPreviewModal) return;
    
        uploadPreviewModal.addEventListener('show.bs.modal', (ev) => {
            const btn = ev.relatedTarget;
            previewUrl = btn?.dataset.previewUrl || '';
            isImg = btn?.dataset.isImg === 'true';
        });
    
        uploadPreviewModal.addEventListener('hidden.bs.modal', () => {
            previewUrl = null;
            isImg = false;
        });
    });">
        @if ($uploads && $uploads->count())

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Related Uploads</strong>
                        <span class="text-muted">({{ $uploads->count() }})</span>
                    </div>
                    <input type="text" class="form-control form-control-sm w-auto" placeholder="Search files..."
                        x-model="q">
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($uploads as $u)
                            <template
                                x-if="('{{ Str::of($u->original_name ?? ($u->filename ?? '#' . $u->id))->lower() }}'.includes(q.toLowerCase()))">
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <div class="card h-100 shadow-sm">
                                        {{-- Thumb / placeholder --}}
                                        @php
                                            $url = \Illuminate\Support\Facades\Storage::url(
                                                $u->path ?? ($u->file_path ?? ''),
                                            );
                                            $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                                            $isExcelish = in_array($ext, ['xls', 'xlsx', 'xlsm', 'xlt', 'ods', 'csv']);
                                            // If you built an HTML preview route for Excel, use it here instead of disabling.
                                            // $previewTarget = $isExcelish ? route('uploads.preview', $u) : $url;
                                            $previewTarget = $isExcelish ? '' : $url; // keep disabled for now
                                            $isImage = isset($u->mime_type)
                                                ? str_starts_with($u->mime_type, 'image/')
                                                : false;
                                            $isPdf = isset($u->mime_type) ? $u->mime_type === 'application/pdf' : false;
                                        @endphp

                                        <div class="ratio ratio-16x9 overflow-hidden rounded-3">
                                            @if ($isImage && $url)
                                                <img src="{{ $url }}" class="w-100 h-100 object-fit-cover"
                                                    alt="{{ $u->original_name ?? 'file' }}" loading="lazy"
                                                    decoding="async">
                                            @else
                                                <div
                                                    class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                                                    <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="card-body p-2">
                                            <div class="small fw-semibold text-truncate"
                                                title="{{ $u->original_name ?? ($u->filename ?? '') }}">
                                                {{ $u->original_name ?? ($u->filename ?? 'Unnamed file') }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $u->created_at ? $u->created_at->format('d M Y H:i') : '' }}
                                            </div>

                                            {{-- Optional tags display --}}
                                            @if (method_exists($u, 'tags') && $u->relationLoaded('tags'))
                                                <div class="mt-1">
                                                    @foreach ($u->tags as $t)
                                                        <span class="badge text-bg-secondary">{{ $t->name }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div class="card-footer p-2 d-flex gap-2">
                                            @if ($url)
                                                <a class="btn btn-sm btn-outline-secondary flex-fill"
                                                    href="{{ $url }}" target="_blank">
                                                    Open
                                                </a>
                                                <a class="btn btn-sm btn-outline-secondary flex-fill"
                                                    href="{{ $url }}" download>
                                                    Download
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-primary flex-fill"
                                                    data-bs-toggle="modal" data-bs-target="#uploadPreviewModal"
                                                    data-preview-url="{{ $previewTarget }}"
                                                    data-is-img="{{ $isImage ? 'true' : 'false' }}"
                                                    @disabled($isExcelish)
                                                    title="{{ $isExcelish ? 'Excel preview not supported here' : '' }}">
                                                    Preview
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </template>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-secondary d-flex align-items-center gap-2">
                <i class="bi bi-info-circle"></i>
                No uploads found for this part.
            </div>
        @endif
        {{-- Modal Preview (image uses <img>, others via <iframe>) --}}
        <template x-teleport="body">
            <div class="modal fade" id="uploadPreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Use x-if so elements mount fresh each time, and :key to force reload -->
                            <template x-if="isImg">
                                <img :src="previewUrl" :key="'img:' + previewUrl" class="img-fluid w-100"
                                    alt="Preview">
                            </template>

                            <template x-if="!isImg">
                                <iframe :src="previewUrl" :key="'frame:' + previewUrl"
                                    style="width:100%; height:70vh;" frameborder="0"></iframe>
                            </template>

                            <!-- Optional: fallback open-in-new-tab (helpful if X-Frame-Options blocks PDF) -->
                            <div class="mt-2" x-show="previewUrl && !isImg">
                                <a class="btn btn-sm btn-outline-secondary" :href="previewUrl" target="_blank">Open
                                    in new tab</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    @php
        $showDocumentInfo = false;
    @endphp
    <div>
        @if ($dimensions)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <div x-data="{ value: @entangle('start_time').live, fp: null }" x-init="fp = flatpickr($refs.tf, {
                                enableTime: true,
                                noCalendar: true,
                                time_24hr: true,
                                minuteIncrement: 15,
                                defaultDate: value, // ← real string like '11:30'
                                allowInput: true,
                            
                                onChange(selectedDates, dateStr) {
                                    value = dateStr; // pushes to Livewire
                                }
                            });
                            
                            /* if Livewire changes the value later, update Flatpickr */
                            $watch('value', v => fp.setDate(v, false));">
                                <input type="text" x-ref="tf"
                                    class="form-control @error('start_time') is-invalid @enderror" readonly>
                            </div>
                            @error('start_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>`
                        <div class="col">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <div x-data="{ value: @entangle('end_time').live, fp: null }" x-init="fp = flatpickr($refs.tf, {
                                enableTime: true,
                                noCalendar: true,
                                time_24hr: true,
                                minuteIncrement: 15,
                                defaultDate: value, // ← real string like '11:30'
                                allowInput: true,
                            
                                onChange(selectedDates, dateStr) {
                                    value = dateStr; // pushes to Livewire
                                }
                            });
                            
                            /* if Livewire changes the value later, update Flatpickr */
                            $watch('value', v => fp.setDate(v, false));">
                                <input type="text" x-ref="tf"
                                    class="form-control @error('end_time') is-invalid @enderror" readonly>
                            </div>
                            @error('end_time')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @foreach ($dimensions as $index => $row)
            <div class="card mb-3 p-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-12 @if (!$showDocumentInfo) d-none @endif">
                        <label class="form-label">Inspection Report Document Number <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-secondary-subtle"
                            wire:model.blur="dimensions.{{ $index }}.inspection_report_document_number"
                            readonly>
                        @error("dimensions.$index.inspection_report_document_number")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model.live="dimensions.{{ $index }}.limit_uom">
                            <option value="" selected>-- Select Unit --</option>
                            <option value="cm">cm</option>
                            <option value="mm">mm</option>
                        </select>
                    </div>

                    <div class="col">
                        <label class="form-label">Lower Limit <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control"
                                wire:model.blur="dimensions.{{ $index }}.lower_limit">
                            <span class="input-group-text">{{ data_get($dimensions, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("dimensions.$index.lower_limit")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Upper Limit <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control"
                                wire:model.blur="dimensions.{{ $index }}.upper_limit">
                            <span class="input-group-text">{{ data_get($dimensions, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("dimensions.$index.upper_limit")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Actual Value <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control"
                                wire:model.blur="dimensions.{{ $index }}.actual_value">
                            <span class="input-group-text">{{ data_get($dimensions, "$index.limit_uom", '') }}</span>
                        </div>
                        @error("dimensions.$index.actual_value")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Area/Section <span class="text-danger">*</span></label>
                        <input type="text" class="form-control"
                            wire:model.blur="dimensions.{{ $index }}.area">
                        @error("dimensions.$index.area")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col">
                        <label class="form-label">Judgement <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model.live="dimensions.{{ $index }}.judgement">
                            <option value="" disabled>--Select Judgement--</option>
                            <option value="OK">OK</option>
                            <option value="NG">NG</option>
                        </select>
                        @error("dimensions.$index.judgement")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (($row['judgement'] ?? '') === 'NG')
                        <div class="col">
                            <label class="form-label">Remarks <span class="text-danger">*</span></label>
                            <input type="text" class="form-control"
                                wire:model.blur="dimensions.{{ $index }}.remarks">
                            @error("dimensions.$index.remarks")
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="col-auto align-self-end mb-3">
                        <button type="button" class="btn btn-link text-danger btn-sm"
                            wire:click="removeDimension({{ $index }})">Remove</button>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="justify-content-between d-flex">
            <button type="button" class="btn btn-outline-secondary" wire:click="addDimension">
                + Add Dimension
            </button>
            @if (count($dimensions) > 0)
                @php
                    $buttonDisabled = true;

                    if (
                        $dimensions[0]['limit_uom'] !== '' &&
                        $dimensions[0]['upper_limit'] !== '' &&
                        $dimensions[0]['lower_limit'] !== '' &&
                        $dimensions[0]['area'] !== ''
                    ) {
                        $buttonDisabled = false;
                    }
                @endphp
                <div>
                    <button type="button" class="btn btn-outline-primary" wire:click="saveStep"
                        @disabled($buttonDisabled)>
                        Save Dimension
                    </button>
                    <button type="button" class="btn btn-outline-danger" wire:click="resetStep"
                        @disabled($buttonDisabled)>Reset</button>
                </div>
            @endif
        </div>
    </div>
</div>
