<div>
  {{-- Page header / view toggle --}}
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">File Library</h1>

    <div class="d-flex align-items-center gap-2">
      {{-- Grid / List toggle --}}
      <div class="btn-group" role="group" aria-label="View mode">
        <input type="radio" class="btn-check" id="viewTable" value="table" autocomplete="off"
          wire:model.live="viewMode">
        <label class="btn btn-outline-secondary" for="viewTable">List</label>

        <input type="radio" class="btn-check" id="viewGrid" value="grid" autocomplete="off"
          wire:model.live="viewMode">
        <label class="btn btn-outline-secondary" for="viewGrid">Grid</label>
      </div>
    </div>
  </div>

  {{-- Upload area --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <form wire:submit.prevent="store">
        <div class="row g-3 align-items-center">
          <div class="col-md-8">
            <label x-data="{ dragging: false, progress: 0 }" x-on:dragover.prevent="dragging=true"
              x-on:dragleave.prevent="dragging=false"
              x-on:drop.prevent="
                                dragging=false;

                                // Prefer items (lets us ignore non-file drags cleanly)
                                let files = [];
                                if ($event.dataTransfer.items && $event.dataTransfer.items.length) {
                                    for (const item of $event.dataTransfer.items) {
                                        if (item.kind === 'file') {
                                            const f = item.getAsFile();
                                            if (f) files.push(f);
                                        }
                                    }
                                } else {
                                    // Fallback to FileList -> Array
                                    files = Array.from($event.dataTransfer.files || []);
                                }

                                // Filter to real File objects only
                                files = files.filter(f => f instanceof File);

                                if (files.length) {
                                    $wire.uploadMultiple(
                                        'newFiles',
                                        files,
                                        () => { progress = 0; },                // finish
                                        () => { progress = 0; },                // error
                                        (e) => { progress = e.detail.progress } // progress
                                    );
                                }
                            "
              class="w-100 text-center border border-2 rounded-3 py-5 bg-light-subtle"
              :class="dragging ? 'border-primary bg-primary-subtle' : ''"
              style="border-style: dashed; cursor: pointer;" for="fileInput">
              <div class="fw-medium mb-1">Drag & drop files here, or click to browse</div>
              <small class="text-muted">Up to 20MB per file · Stored on <code>public</code>
                disk</small>

              <div class="progress mt-3" style="height:6px;" x-show="progress>0 && progress<100">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                  :style="`width:${progress}%`"></div>
              </div>
            </label>

            <input id="fileInput" type="file"
              class="d-none @error('newFiles.*') is-invalid @enderror" wire:model="newFiles"
              multiple>
            @error('newFiles.*')
              <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
            @enderror

            {{-- Upload progress / selected files preview --}}
            <div wire:loading wire:target="newFiles" class="progress mt-3" style="height:6px;">
              <div class="progress-bar progress-bar-striped progress-bar-animated"
                style="width:100%">
              </div>
            </div>
            @if ($newFiles)
              <ul class="list-unstyled small mt-2 mb-0">
                @foreach ($newFiles as $file)
                  <li class="text-muted">• {{ $file->getClientOriginalName() }}</li>
                @endforeach
              </ul>
            @endif
          </div>
          <div class="col-md-4 text-md-end">
            <button class="btn btn-primary px-4" type="submit" @disabled(!$newFiles)
              wire:loading.attr="disabled">
              Upload
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Filters / actions --}}
  <div class="card">
    <div class="card-body">
      <div class="row g-2 align-items-center mb-3">
        <div class="col-lg-5">
          <div class="input-group">
            <input type="search" class="form-control" placeholder="Search file name…"
              wire:model.live.debounce.400ms="search">
            @if ($search !== '')
              <button class="btn btn-outline-secondary" type="button"
                wire:click="$set('search','')">Clear</button>
            @endif
          </div>
        </div>

        <div class="col-lg-4">
          {{-- Type pills --}}
          <div class="d-flex flex-wrap gap-1">
            @php $types = ['all'=>'All','image'=>'Images','pdf'=>'PDF','doc'=>'Docs','sheet'=>'Sheets','audio'=>'Audio','video'=>'Video','archive'=>'Archives','other'=>'Other']; @endphp
            @foreach ($types as $val => $label)
              <button type="button"
                class="btn btn-sm {{ $type === $val ? 'btn-dark' : 'btn-outline-secondary' }}"
                wire:click="$set('type','{{ $val }}')">
                {{ $label }}
              </button>
            @endforeach
            @if ($type !== 'all')
              <button type="button" class="btn btn-sm btn-outline-secondary"
                wire:click="$set('type','all')">Reset</button>
            @endif
          </div>
          {{-- Tag Filter --}}
          @php
            $visible = $this->topTags;
            $allCount = $this->allTagsCount;
            $rest = max(0, $allCount - $visible->count());
          @endphp

          <div class="d-flex flex-wrap gap-1 align-items-center mt-2">
            <span class="fw-semibold">Tags :</span>
            {{-- visible/top tags as pills --}}
            @foreach ($visible as $tag)
              <button type="button"
                class="btn btn-sm {{ in_array($tag->slug, $tagsFilter, true) ? 'btn-dark' : 'btn-outline-secondary' }}"
                wire:click="toggleTagFilter('{{ $tag->slug }}')">
                {{ $tag->name }}
              </button>
            @endforeach

            <div x-data="{ open: @entangle('tagDropdownOpen').live }" class="dropdown" wire:key="tag-filter-dropdown"
              @keydown.escape.window="open=false">

              <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                x-on:click="open = !open" :aria-expanded="open.toString()">
                More tags{{ $rest > 0 ? " (+{$rest})" : '' }}
              </button>

              <div class="dropdown-menu p-2 mt-2" x-show="open" x-transition
                :class="open ? 'show' : ''" @click.outside="open = false"
                style="width:280px; display:block;">
                <input type="search" class="form-control form-control-sm mb-2"
                  placeholder="Search tags…" wire:model.live.debounce.300ms="tagSearch"
                  x-on:click.stop>

                <div class="border rounded p-1" style="max-height:220px; overflow:auto;"
                  x-on:click.stop>
                  @forelse ($this->allTags as $tag)
                    <label class="d-flex align-items-center gap-2 py-1 px-1 rounded"
                      wire:key="tag-{{ $tag->id }}">
                      <input type="checkbox" class="form-check-input"
                        value="{{ $tag->slug }}" wire:model.live="tagsFilter">
                      <span class="flex-grow-1">{{ $tag->name }}</span>
                      @if (isset($tag->uses))
                        <span class="badge text-bg-light">{{ $tag->uses }}</span>
                      @endif
                    </label>
                  @empty
                    <div class="text-muted small px-1 py-2">No matching tags.</div>
                  @endforelse
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                  <button type="button" class="btn btn-sm btn-light"
                    wire:click="$set('tagsFilter', [])">Clear</button>
                  <button type="button" class="btn btn-sm btn-primary"
                    x-on:click="open=false">Apply</button>
                </div>
              </div>
            </div>

            @if (!empty($tagsFilter))
              <button type="button" class="btn btn-sm btn-outline-secondary"
                wire:click="$set('tagsFilter', [])">
                Reset tag filter
              </button>
            @endif
          </div>
        </div>

        <div class="col-lg-3 d-flex justify-content-lg-end gap-2">
          {{-- Sort --}}
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"
              type="button">
              Sort
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button class="dropdown-item"
                  wire:click="sortBy('created_at')">Uploaded</button>
              </li>
              <li><button class="dropdown-item" wire:click="sortBy('original_name')">Name</button>
              </li>
              <li><button class="dropdown-item" wire:click="sortBy('size')">Size</button></li>
            </ul>
          </div>

          {{-- Per page --}}
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"
              type="button">
              Show: {{ $perPage }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button class="dropdown-item" wire:click="$set('perPage',10)">10</button></li>
              <li><button class="dropdown-item" wire:click="$set('perPage',25)">25</button></li>
              <li><button class="dropdown-item" wire:click="$set('perPage',50)">50</button></li>
            </ul>
          </div>
        </div>
      </div>

      {{-- Bulk actions (top-right in desktop) --}}
      <div class="d-flex justify-content-end mb-2 gap-2">
        <button class="btn btn-outline-danger" wire:click="deleteSelected"
          @disabled(count($checked) === 0)>
          Delete selected ({{ count($checked) }})
        </button>
        <button class="btn btn-outline-primary" @disabled(!$selectAllResults && count($checked) === 0)
          wire:click="$set('showTagModal', true)">
          Tag selected
        </button>
        <button class="btn btn-outline-warning" wire:click="$set('showRemoveTagModal', true)"
          @disabled(!$selectAllResults && count($checked) === 0)>Remove Tags</button>
      </div>

      {{-- CONTENT --}}
      @if ($viewMode === 'grid')
        {{-- GRID VIEW --}}
        <div class="row g-3">
          @forelse ($items as $it)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3" wire:key="card-{{ $it->id }}">
              <div class="card h-100 shadow-sm">
                <div class="position-relative">
                  @if ($it->category === 'image')
                    <img src="{{ Storage::disk($it->disk)->url($it->path) }}"
                      alt="{{ $it->original_name }}" class="card-img-top"
                      style="aspect-ratio:4/3; object-fit:cover;">
                  @else
                    <div class="d-flex align-items-center justify-content-center bg-body-secondary"
                      style="aspect-ratio:4/3;">
                      <div class="display-6" aria-hidden="true">
                        @switch($it->category)
                          @case('pdf')
                            📕
                          @break

                          @case('doc')
                            📄
                          @break

                          @case('sheet')
                            📊
                          @break

                          @case('audio')
                            🎵
                          @break

                          @case('video')
                            🎞️
                          @break

                          @case('archive')
                            🗜️
                          @break

                          @default
                            📁
                        @endswitch
                      </div>
                    </div>
                  @endif
                  <div class="form-check position-absolute top-0 start-0 m-2">
                    <input class="form-check-input" type="checkbox"
                      value="{{ (string) $it->id }}" wire:model.live="checked">
                  </div>
                </div>

                <div class="card-body">
                  <div class="fw-semibold text-truncate" title="{{ $it->original_name }}">
                    {{ $it->original_name }}</div>
                  <div class="text-muted small">{{ strtoupper($it->category) }} ·
                    {{ $it->size_for_humans }}</div>
                  <div class="text-muted small">Uploaded
                    {{ $it->created_at_wib->diffForHumans() }}
                  </div>
                  @if ($it->tags->isNotEmpty())
                    <div class="mt-1 d-flex flex-wrap gap-1">
                      @foreach ($it->tags as $tag)
                        <span class="badge text-bg-light border">{{ $tag->name }}</span>
                      @endforeach
                    </div>
                  @endif
                </div>

                <div class="card-footer bg-transparent d-flex gap-2">
                  <a class="btn btn-sm btn-outline-secondary flex-fill"
                    href="{{ route('files.preview', $it) }}" target="_blank">Preview</a>
                  <a class="btn btn-sm btn-outline-primary flex-fill"
                    href="{{ route('files.download', $it) }}">Download</a>
                  <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                      data-bs-toggle="dropdown" type="button">
                      More
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><button type="button" class="dropdown-item"
                          wire:click="confirmRename({{ $it->id }})">Rename</button>
                      </li>
                      <li><button type="button" class="dropdown-item"
                          wire:click="confirmReplace({{ $it->id }})">Replace</button>
                      </li>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li><button type="button" class="dropdown-item text-danger"
                          wire:click="deleteOne({{ $it->id }})"
                          onclick="return confirm('Delete this file?')">Delete</button></li>
                      <li>
                        <button type="button" x-data
                          x-on:click="
                                                        navigator.clipboard.writeText('{{ Storage::disk($it->disk)->url($it->path) }}');
                                                        $dispatch('toast', { message: 'Link copied' });
                                                    "
                          class="dropdown-item">
                          Copy link
                        </button>
                      </li>
                    </ul>

                  </div>
                </div>
              </div>
            </div>
            @empty
              <div class="col-12">
                <div class="text-center text-muted py-5">
                  <div class="display-6 mb-2">🗂️</div>
                  <div class="mb-2">No files match your filters.</div>
                  <button type="button" class="btn btn-light btn-sm"
                    wire:click="$set('search',''); $set('type','all')">Clear filters</button>
                </div>
              </div>
            @endforelse
          </div>
        @else
          {{-- debug only --}}
          {{-- @if (app()->environment('local'))
                        <pre class="small text-muted">checked: {{ json_encode($checked) }} | allResults: {{ $selectAllResults ? 'yes' : 'no' }}</pre>
                    @endif --}}
          {{-- LIST VIEW (table) --}}
          @if ($selectPage && !$selectAllResults)
            <div
              class="alert alert-light border d-flex justify-content-between align-items-center py-2">
              <div>You selected {{ count($checked) }} items on this page.</div>
              <button type="button" class="btn btn-sm btn-outline-primary"
                wire:click="selectAllResultsAction">
                Select all {{ $items->total() }} results
              </button>
            </div>
          @elseif($selectAllResults)
            <div
              class="alert alert-light border d-flex justify-content-between align-items-center py-2">
              <div>All {{ $items->total() }} results are selected across pages.</div>
              <button type="button" class="btn btn-sm btn-outline-secondary"
                wire:click="$call('resetSelection')">
                Clear selection
              </button>
            </div>
          @endif
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th style="width:30px">
                    <input class="form-check-input" type="checkbox" wire:model.live="selectPage">
                  </th>
                  <th role="button" wire:click="sortBy('original_name')">
                    Name
                    @if ($sortField === 'original_name')
                      <x-sort :dir="$sortDirection" />
                    @endif
                  </th>
                  <th>Type</th>
                  <th role="button" wire:click="sortBy('size')">
                    Size
                    @if ($sortField === 'size')
                      <x-sort :dir="$sortDirection" />
                    @endif
                  </th>
                  <th role="button" wire:click="sortBy('created_at')">
                    Uploaded
                    @if ($sortField === 'created_at')
                      <x-sort :dir="$sortDirection" />
                    @endif
                  </th>
                  <th>Tags</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($items as $it)
                  <tr wire:key="row-{{ $it->id }}">
                    <td>
                      <input class="form-check-input" type="checkbox"
                        value="{{ (string) $it->id }}" wire:model.live="checked">
                    </td>
                    <td class="text-truncate" style="max-width:360px">
                      <span class="me-2" aria-hidden="true">
                        @switch($it->category)
                          @case('image')
                            🖼️
                          @break

                          @case('pdf')
                            📕
                          @break

                          @case('doc')
                            📄
                          @break

                          @case('sheet')
                            📊
                          @break

                          @case('audio')
                            🎵
                          @break

                          @case('video')
                            🎞️
                          @break

                          @case('archive')
                            🗜️
                          @break

                          @default
                            📁
                        @endswitch
                      </span>
                      <span class="fw-semibold">{{ $it->original_name }}</span>
                    </td>
                    <td><span class="text-muted small">{{ strtoupper($it->category) }}</span></td>
                    <td>{{ $it->size_for_humans }}</td>
                    <td>
                      <div class="small">{{ $it->created_at_wib->format('Y-m-d H:i') }}</div>
                      <div class="text-muted small">{{ $it->created_at_wib->diffForHumans() }}</div>
                    </td>
                    <td>
                      @foreach ($it->tags as $tag)
                        <span x-data="{
                            holding: false,
                            timer: null,
                            startAt: 0,
                            duration: 2000,
                            progress: 0,
                            start() {
                                if (this.holding) return;
                                this.holding = true;
                                this.startAt = performance.now();
                                this.loop();
                                this.timer = setTimeout(() => {
                                    $wire.removeTagFromItem({{ $it->id }}, {{ $tag->id }});
                                    this.reset();
                                }, this.duration);
                            },
                            cancel() {
                                if (!this.holding) return;
                                clearTimeout(this.timer);
                                this.reset();
                            },
                            loop() {
                                if (!this.holding) return;
                                const elapsed = performance.now() - this.startAt;
                                this.progress = Math.min(100, Math.round(elapsed / this.duration * 100));
                                requestAnimationFrame(() => this.loop());
                            },
                            reset() {
                                this.holding = false;
                                this.progress = 0;
                            }
                        }"
                          class="position-relative d-inline-flex align-items-center me-2 mb-2">
                          <span
                            class="badge text-bg-light border d-inline-flex align-items-center gap-1"
                            :class="holding ? 'opacity-75' : ''"
                            :style="holding
                                ?
                                `transition: transform .2s; transform: scale(.98);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            background: linear-gradient(to right, rgba(220,53,69,.15) 0%, rgba(220,53,69,.15) ${progress}%,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            transparent ${progress}%, transparent 100%);` :
                                ''">
                            {{ $tag->name }}

                            <button type="button"
                              class="btn btn-sm btn-link p-0 ms-1 text-danger text-decoration-none"
                              title="Hold to remove" x-on:pointerdown="start"
                              x-on:pointerup="cancel" x-on:pointerleave="cancel"
                              x-on:touchstart.prevent="start"
                              x-on:touchend="cancel">&times;</button>
                          </span>

                          <!-- progress bar line -->
                          <span x-show="holding" x-transition class="position-absolute start-0"
                            style="bottom:-2px; height:2px; background:rgba(220,53,69,.65);"
                            :style="`width:${progress}%;`"></span>
                        </span>
                      @endforeach
                    </td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-secondary"
                        href="{{ route('files.preview', $it) }}" target="_blank">Preview</a>
                      <a class="btn btn-sm btn-outline-primary"
                        href="{{ route('files.download', $it) }}">Download</a>
                      <button type="button" class="btn btn-sm btn-outline-warning"
                        wire:click="confirmRename({{ $it->id }})">Rename</button>
                      <button type="button" class="btn btn-sm btn-outline-info"
                        wire:click="confirmReplace({{ $it->id }})">Replace</button>
                      <button type="button" class="btn btn-sm btn-outline-danger"
                        wire:click="deleteOne({{ $it->id }})"
                        onclick="return confirm('Delete this file?')">Delete</button>
                      <button type="button" x-data
                        x-on:click="
                                                        navigator.clipboard.writeText('{{ Storage::disk($it->disk)->url($it->path) }}');
                                                        $dispatch('toast', { message: 'Link copied' });
                                                    "
                        class="btn btn-sm btn-outline-secondary">
                        Copy link
                      </button>
                    </td>
                  </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-muted py-5">
                        <div class="display-6 mb-2">🗂️</div>
                        <div class="mb-2">No files match your filters.</div>
                        <button type="button" class="btn btn-light btn-sm"
                          wire:click="$set('search',''); $set('type','all')">Clear filters</button>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @endif

          {{-- Pagination --}}
          <div class="mt-3">
            {{ $items->onEachSide(1)->links() }}
          </div>
        </div>
      </div>

      {{-- Sticky selection bar --}}
      @if (count($checked) > 0)
        <div class="position-fixed bottom-0 start-0 end-0 bg-body border-top shadow py-2"
          style="z-index: 1030;">
          <div class="container d-flex align-items-center justify-content-between">
            <div><strong>{{ count($checked) }}</strong> selected</div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary btn-sm"
                wire:click="$set('checked', [])">Clear</button>
              <button type="button" class="btn btn-danger btn-sm" wire:click="deleteSelected">Delete
                selected</button>
            </div>
          </div>
        </div>
      @endif

      {{-- Rename Modal --}}
      <div class="modal @if ($showRename) show d-block @endif" tabindex="-1"
        @if ($showRename) style="background: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog">
          <div class="modal-content" wire:ignore.self>
            <div class="modal-header">
              <h5 class="modal-title">Rename file</h5>
              <button type="button" class="btn-close"
                wire:click="$set('showRename', false)"></button>
            </div>
            <form wire:submit.prevent="rename">
              <div class="modal-body">
                @if ($selected)
                  <div class="mb-3">
                    <label class="form-label">New name (without extension)</label>
                    <input type="text" class="form-control @error('newName') is-invalid @enderror"
                      wire:model.defer="newName">
                    @error('newName')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="text-muted small">Current: <code>{{ $selected->original_name }}</code>
                  </div>
                @endif
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light"
                  wire:click="$set('showRename', false)">Cancel</button>
                <button type="submit" class="btn btn-warning">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- Replace Modal --}}
      <div class="modal @if ($showReplace) show d-block @endif" tabindex="-1"
        @if ($showReplace) style="background: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog">
          <div class="modal-content" wire:ignore.self>
            <div class="modal-header">
              <h5 class="modal-title">Replace file</h5>
              <button type="button" class="btn-close"
                wire:click="$set('showReplace', false)"></button>
            </div>
            <form wire:submit.prevent="replace">
              <div class="modal-body">
                @if ($selected)
                  <p class="mb-2 text-muted small">Current:
                    <code>{{ $selected->original_name }}</code>
                  </p>
                  <input type="file"
                    class="form-control @error('replacement') is-invalid @enderror"
                    wire:model="replacement">
                  @error('replacement')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <div wire:loading wire:target="replacement" class="form-text">Uploading…</div>
                @endif
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light"
                  wire:click="$set('showReplace', false)">Cancel</button>
                <button type="submit" class="btn btn-info">Replace</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- Add Tag Modal --}}
      <div class="modal @if ($showTagModal) show d-block @endif" tabindex="-1"
        @if ($showTagModal) style="background: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog">
          <div class="modal-content" wire:ignore.self>
            <div class="modal-header">
              <h5 class="modal-title">Add tags</h5>
              <button type="button" class="btn-close"
                wire:click="$set('showTagModal', false)"></button>
            </div>

            <form wire:submit.prevent="addTagsToSelection">
              <div class="modal-body">
                <label class="form-label">Tags (comma or newline)</label>
                <textarea rows="2" class="form-control" wire:model.defer="newTags"
                  placeholder="e.g. urgent, invoice, Q3"></textarea>
                <div class="form-text">
                  {{ $selectAllResults ? 'Will apply to ALL filtered results.' : 'Will apply to selected rows.' }}
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light"
                  wire:click="$set('showTagModal', false)">Cancel</button>
                <button type="submit" class="btn btn-primary">Add tags</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- Remove Tag Modal --}}
      <div class="modal @if ($showRemoveTagModal) show d-block @endif" tabindex="-1"
        @if ($showRemoveTagModal) style="background: rgba(0,0,0,.5);" @endif>
        <div class="modal-dialog">
          <div class="modal-content" wire:ignore.self>
            <div class="modal-header">
              <h5 class="modal-title">Remove tags</h5>
              <button type="button" class="btn-close"
                wire:click="$set('showRemoveTagModal', false)"></button>
            </div>

            <form wire:submit.prevent="removeCheckedTagsFromSelection">
              <div class="modal-body">
                @forelse ($this->availableTags as $tag)
                  <label class="form-check d-block">
                    <input class="form-check-input" type="checkbox" wire:model="removeTagIds"
                      value="{{ $tag->id }}">
                    <span class="form-check-label">{{ $tag->name }}</span>
                  </label>
                @empty
                  <div class="text-muted">No tags in current selection.</div>
                @endforelse
                <div class="form-text">
                  {{ $selectAllResults ? 'Will remove from ALL filtered results.' : 'Will remove from selected rows.' }}
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light"
                  wire:click="$set('showRemoveTagModal', false)">Cancel</button>
                <button type="submit" class="btn btn-warning" @disabled(!$selectAllResults && count($checked) === 0)>
                  Remove tags
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>

    {{-- tiny blade component for sort chevron (unchanged) --}}
    @once
      @push('components')
        @verbatim
          <x-sort :dir="$dir" />
        @endverbatim
      @endpush
    @endonce
