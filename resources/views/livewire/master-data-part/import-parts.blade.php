 <!-- resources/views/livewire/master-data-part/import-parts.blade.php -->
 <div class="container py-4">
     <!-- Page Header -->
     <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
         <div>
             <span class="mb-0 h4">Master Data Parts — Import/Update</span>
             @if ($this->job)
                 <span
                     class="badge p-2
                            @if ($this->job->status === 'running') text-bg-primary
                            @elseif($this->job->status === 'completed') text-bg-success
                            @elseif($this->job->status === 'failed') text-bg-danger
                            @else text-bg-secondary @endif">
                     {{ strtoupper($this->job->status) }}
                 </span>
             @endif
             <div class="text-muted small">
                 @if ($this->job)
                     Last updated:
                     <span x-data="{
                         diff: @js(optional($this->job?->updated_at)->diffForHumans() ?? '—'),
                         abs: @js(optional($this->job?->updated_at)->toDateTimeString() ?? '')
                     }"
                         x-on:job-heartbeat.window="diff = $event.detail.diff; abs = $event.detail.absolute"
                         x-text="diff" :title="abs">
                     </span>
                 @else
                     No active import
                 @endif
             </div>
         </div>
         <div class="d-flex gap-2">
             <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="refreshNow"
                 wire:loading.attr="disabled" title="Refresh (keeps progress)">
                 <span wire:loading.remove>
                     <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                 </span>
                 <span class="d-none" wire:loading.class.remove="d-none">
                     <span class="spinner-border spinner-border-sm me-1"></span>
                     Refreshing…
                 </span>
             </button>

             @if ($jobId)
                 <button type="button" class="btn btn-outline-danger btn-sm" wire:click="resetTracking"
                     title="Clear current tracking (does not cancel the job)">
                     <i class="bi bi-x-circle me-1"></i> Reset Tracking
                 </button>
             @endif
             {{-- @if (!$jobId)
                 <span class="text-muted small">
                     Tracking is cleared. You can start a new import, or refresh to attach to any running job.
                 </span>
             @endif --}}
         </div>
     </div>

     <div class="row g-4">
         <!-- LEFT: Upload + Progress -->
         <div class="col-lg-7">
             <div class="card shadow-sm border-0">
                 <div class="card-body">
                     <!-- Upload -->
                     @if (!$jobId)
                         <div class="mb-4">
                             <label class="form-label fw-semibold">Upload Excel (.xlsx/.xls/.csv)</label>
                             <input type="file" class="form-control" wire:model="file" accept=".xlsx,.xls,.csv">
                             @error('file')
                                 <div class="text-danger small mt-1">{{ $message }}</div>
                             @enderror
                             <div class="small text-muted mt-2">
                                 Expected headers: <code>Item No.</code>, <code>Item Description</code>, <code>Item
                                     Group</code>, <code>Active</code>.
                                 <span class="ms-2">Rule: <code>Active</code> = <code>Y</code> → <code>1</code>,
                                     else
                                     <code>0</code>.</span>
                             </div>

                             <!-- Uploading spinner -->
                             <div class="alert alert-info d-flex align-items-center gap-2 mt-3 d-none"
                                 wire:loading.class.remove="d-none" wire:target="file">
                                 <div class="spinner-border spinner-border-sm" role="status"></div>
                                 <div>Uploading file…</div>
                             </div>
                         </div>
                     @endif

                     {{-- Progress --}}
                     @if ($jobId)
                         <livewire:master-data-part.import-progress-panel :job-id="$jobId" :key="'progress-' . $jobId" />
                     @endif

                    
                 </div>

                 <div class="card-footer bg-white d-flex justify-content-end">
                     @if (!$jobId)
                         @if ($file)
                             <button type="button" class="btn btn-primary" wire:click="import"
                                 wire:loading.attr="disabled" wire:target="import" @disabled(!$file)>
                                 <span wire:loading.remove wire:target="import">Start Import</span>
                                 <span class="d-none" wire:loading.class.remove="d-none" wire:target="import">
                                     <span class="spinner-border spinner-border-sm me-2"></span> Starting…
                                 </span>
                             </button>
                         @else
                             <span class="text-muted small">Choose a file to enable import.</span>
                         @endif
                     @endif
                 </div>
             </div>
         </div>

         <!-- RIGHT: Change Details & Meta -->
         <div class="col-lg-5">
             <div class="card shadow-sm border-0">
                 <div class="card-header bg-white">
                     <div class="d-flex align-items-center justify-content-between">
                         <div class="fw-semibold">Change Details</div>
                     </div>
                 </div>
                 <div class="card-body">
                     @if ($this->job)
                         <div class="row text-center g-3">
                             <div class="col-4">
                                 <div class="p-3 rounded-3 bg-light">
                                     <div class="small text-muted">Created</div>
                                     <div class="fs-5 fw-semibold">
                                         {{ number_format($this->job->created_rows ?? 0) }}
                                     </div>
                                 </div>
                             </div>
                             <div class="col-4">
                                 <div class="p-3 rounded-3 bg-light">
                                     <div class="small text-muted">Updated</div>
                                     <div class="fs-5 fw-semibold">
                                         {{ number_format($this->job->updated_rows ?? 0) }}
                                     </div>
                                 </div>
                             </div>
                             <div class="col-4">
                                 <div class="p-3 rounded-3 bg-light">
                                     <div class="small text-muted">Skipped</div>
                                     <div class="fs-5 fw-semibold">
                                         {{ number_format($this->job->skipped_rows ?? 0) }}
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <hr>

                         <dl class="row mb-0 small">
                             <dt class="col-5 text-muted">Started</dt>
                             <dd class="col-7">{{ optional($this->job->started_at)->toDayDateTimeString() ?? '—' }}
                             </dd>

                             <dt class="col-5 text-muted">Finished</dt>
                             <dd class="col-7">
                                 {{ optional($this->job->finished_at)->toDayDateTimeString() ?? '—' }}
                             </dd>

                             <dt class="col-5 text-muted">Last Updated</dt>
                             <dd class="col-7">{{ optional($this->job->updated_at)->toDayDateTimeString() ?? '—' }}
                             </dd>

                             <dt class="col-5 text-muted">Status</dt>
                             <dd class="col-7">{{ ucfirst($this->job->status ?? '—') }}</dd>

                             @if (!empty($this->job->error))
                                 <dt class="col-5 text-muted">Error</dt>
                                 <dd class="col-7">
                                     <code class="d-block text-wrap">{{ $this->job->error }}</code>
                                 </dd>
                             @endif

                             @if (!empty($this->job->error_log_path))
                                 <dt class="col-5 text-muted">Error Log</dt>
                                 <dd class="col-7">
                                     <a class="link-underline-primary"
                                         href="{{ Storage::url($this->job->error_log_path) }}" target="_blank"
                                         rel="noopener">
                                         Download log
                                     </a>
                                 </dd>
                             @endif
                         </dl>
                     @else
                         <div class="text-muted">No import details yet. Upload a file to begin.</div>
                     @endif
                 </div>
             </div>
         </div>
     </div>
 </div>
