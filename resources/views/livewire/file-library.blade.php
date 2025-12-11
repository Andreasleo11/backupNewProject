<div class="space-y-4">

    {{-- Page header / view toggle --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-lg font-semibold text-gray-900">File Library</h1>

        <div class="flex items-center gap-2">
            {{-- Grid / List toggle --}}
            <div class="inline-flex rounded-full border border-gray-200 bg-white p-1 text-sm">
                <label class="inline-flex items-center gap-1 rounded-full px-3 py-1 cursor-pointer"
                    :class="@js($viewMode === 'table') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-600'" x-data>
                    <input type="radio" class="hidden" id="viewTable" value="table" wire:model.live="viewMode">
                    <span>List</span>
                </label>

                <label class="inline-flex items-center gap-1 rounded-full px-3 py-1 cursor-pointer"
                    :class="@js($viewMode === 'grid') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-600'" x-data>
                    <input type="radio" class="hidden" id="viewGrid" value="grid" wire:model.live="viewMode">
                    <span>Grid</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Upload area --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-4 sm:p-5">
            <form wire:submit.prevent="store">
                <div class="grid gap-4 md:grid-cols-3 md:items-center">
                    <div class="md:col-span-2">
                        <label x-data="{ dragging: false, progress: 0 }" x-on:dragover.prevent="dragging = true"
                            x-on:dragleave.prevent="dragging = false"
                            x-on:drop.prevent="
                                dragging = false;

                                let files = [];
                                if ($event.dataTransfer.items && $event.dataTransfer.items.length) {
                                    for (const item of $event.dataTransfer.items) {
                                        if (item.kind === 'file') {
                                            const f = item.getAsFile();
                                            if (f) files.push(f);
                                        }
                                    }
                                } else {
                                    files = Array.from($event.dataTransfer.files || []);
                                }

                                files = files.filter(f => f instanceof File);

                                if (files.length) {
                                    $wire.uploadMultiple(
                                        'newFiles',
                                        files,
                                        () => { progress = 0 },
                                        () => { progress = 0 },
                                        (e) => { progress = e.detail.progress },
                                    );
                                }
                            "
                            for="fileInput"
                            class="flex flex-col items-center justify-center w-full rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center transition
                                   cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/40"
                            :class="dragging ? 'border-indigo-500 bg-indigo-50/80' : ''">
                            <div class="flex flex-col items-center gap-1">
                                <div
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 mb-1">
                                    📁
                                </div>
                                <p class="text-sm font-medium text-gray-900">
                                    Drag &amp; drop files here, or <span class="text-indigo-600 underline">click to
                                        browse</span>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Up to 20MB per file · Stored on <code class="font-mono text-xs">public</code> disk
                                </p>
                            </div>

                            {{-- progress for manual uploadMultiple --}}
                            <div class="mt-3 w-full max-w-xs" x-show="progress > 0 && progress < 100">
                                <div class="h-1.5 w-full rounded-full bg-gray-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-indigo-500 transition-all"
                                        :style="`width:${progress}%`"></div>
                                </div>
                            </div>
                        </label>

                        <input id="fileInput" type="file" class="hidden" wire:model="newFiles" multiple>

                        @error('newFiles.*')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        {{-- Livewire upload progress --}}
                        <div wire:loading wire:target="newFiles" class="mt-3 w-full max-w-xs">
                            <div class="h-1.5 w-full rounded-full bg-gray-200 overflow-hidden">
                                <div class="h-full w-full rounded-full bg-indigo-500 animate-pulse"></div>
                            </div>
                        </div>

                        @if ($newFiles)
                            <ul class="mt-2 space-y-1 text-xs text-gray-500">
                                @foreach ($newFiles as $file)
                                    <li>• {{ $file->getClientOriginalName() }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="md:col-span-1 flex md:justify-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm
                                   hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                            @disabled(!$newFiles) wire:loading.attr="disabled">
                            Upload
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Filters / actions --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-4 sm:p-5 space-y-4">
            <div class="grid gap-3 lg:grid-cols-3 lg:items-center">
                {{-- Search --}}
                <div class="lg:col-span-1">
                    <div
                        class="flex rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 focus-within:ring-1 focus-within:ring-indigo-500">
                        <input type="search"
                            class="flex-1 border-0 bg-transparent text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0"
                            placeholder="Search file name…" wire:model.live.debounce.400ms="search">
                        @if ($search !== '')
                            <button type="button"
                                class="ml-2 inline-flex items-center rounded-md border border-gray-200 bg-white px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-50"
                                wire:click="$set('search','')">
                                Clear
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Types + Tags --}}
                <div class="lg:col-span-1 space-y-2">
                    {{-- Type pills --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        @php
                            $types = [
                                'all' => 'All',
                                'image' => 'Images',
                                'pdf' => 'PDF',
                                'doc' => 'Docs',
                                'sheet' => 'Sheets',
                                'audio' => 'Audio',
                                'video' => 'Video',
                                'archive' => 'Archives',
                                'other' => 'Other',
                            ];
                        @endphp

                        @foreach ($types as $val => $label)
                            <button type="button"
                                class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium
                                      {{ $type === $val
                                          ? 'border-gray-900 bg-gray-900 text-white'
                                          : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50' }}"
                                wire:click="$set('type','{{ $val }}')">
                                {{ $label }}
                            </button>
                        @endforeach

                        @if ($type !== 'all')
                            <button type="button"
                                class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1 text-xs text-gray-600 hover:bg-gray-50"
                                wire:click="$set('type','all')">
                                Reset
                            </button>
                        @endif
                    </div>

                    {{-- Tag Filter --}}
                    @php
                        $visible = $this->topTags;
                        $allCount = $this->allTagsCount;
                        $rest = max(0, $allCount - $visible->count());
                    @endphp

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-gray-700">Tags:</span>

                        {{-- visible/top tags as pills --}}
                        @foreach ($visible as $tag)
                            <button type="button"
                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs
                                      {{ in_array($tag->slug, $tagsFilter, true)
                                          ? 'border-gray-900 bg-gray-900 text-white'
                                          : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}"
                                wire:click="toggleTagFilter('{{ $tag->slug }}')">
                                {{ $tag->name }}
                            </button>
                        @endforeach

                        {{-- "More tags" dropdown --}}
                        <div x-data="{ open: @entangle('tagDropdownOpen').live }" wire:key="tag-filter-dropdown" class="relative"
                            @keydown.escape.window="open = false">
                            <button type="button"
                                class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs text-gray-700 hover:bg-gray-50"
                                x-on:click="open = !open" :aria-expanded="open.toString()">
                                More tags{{ $rest > 0 ? " (+{$rest})" : '' }}
                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none">
                                    <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>

                            <div class="absolute z-20 mt-2 w-72 origin-top-left rounded-xl border border-gray-200 bg-white shadow-lg"
                                x-show="open" x-transition @click.outside="open = false">
                                <div class="p-2 border-b border-gray-100">
                                    <input type="search"
                                        class="w-full rounded-md border border-gray-200 px-2 py-1.5 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Search tags…" wire:model.live.debounce.300ms="tagSearch"
                                        x-on:click.stop>
                                </div>

                                <div class="max-h-56 overflow-y-auto p-1 text-xs" x-on:click.stop>
                                    @forelse ($this->allTags as $tag)
                                        <label
                                            class="flex items-center justify-between gap-2 rounded-md px-2 py-1 hover:bg-gray-50"
                                            wire:key="tag-{{ $tag->id }}">
                                            <span class="inline-flex items-center gap-2">
                                                <input type="checkbox"
                                                    class="h-3 w-3 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    value="{{ $tag->slug }}" wire:model.live="tagsFilter">
                                                <span>{{ $tag->name }}</span>
                                            </span>
                                            @if (isset($tag->uses))
                                                <span
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-600">
                                                    {{ $tag->uses }}
                                                </span>
                                            @endif
                                        </label>
                                    @empty
                                        <div class="px-2 py-3 text-xs text-gray-500">No matching tags.</div>
                                    @endforelse
                                </div>

                                <div class="flex items-center justify-between border-t border-gray-100 px-3 py-2">
                                    <button type="button" class="text-xs text-gray-600 hover:text-gray-800"
                                        wire:click="$set('tagsFilter', [])">
                                        Clear
                                    </button>
                                    <button type="button"
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1 text-xs font-medium text-white hover:bg-indigo-700"
                                        x-on:click="open = false">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if (!empty($tagsFilter))
                            <button type="button"
                                class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1 text-xs text-gray-700 hover:bg-gray-50"
                                wire:click="$set('tagsFilter', [])">
                                Reset tag filter
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Sort + per page --}}
                <div class="lg:col-span-1 flex justify-start gap-2 lg:justify-end">
                    {{-- Sort dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                            x-on:click="open = !open">
                            Sort
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <div class="absolute right-0 z-20 mt-2 w-40 origin-top-right rounded-xl border border-gray-200 bg-white shadow-lg text-xs"
                            x-show="open" x-transition @click.outside="open = false">
                            <button type="button" class="block w-full px-3 py-2 text-left hover:bg-gray-50"
                                wire:click="sortBy('created_at')" x-on:click="open = false">
                                Uploaded
                            </button>
                            <button type="button" class="block w-full px-3 py-2 text-left hover:bg-gray-50"
                                wire:click="sortBy('original_name')" x-on:click="open = false">
                                Name
                            </button>
                            <button type="button" class="block w-full px-3 py-2 text-left hover:bg-gray-50"
                                wire:click="sortBy('size')" x-on:click="open = false">
                                Size
                            </button>
                        </div>
                    </div>

                    {{-- Per page dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                            x-on:click="open = !open">
                            Show: {{ $perPage }}
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none">
                                <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div class="absolute right-0 z-20 mt-2 w-32 origin-top-right rounded-xl border border-gray-200 bg-white shadow-lg text-xs"
                            x-show="open" x-transition @click.outside="open = false">
                            <button type="button" class="block w-full px-3 py-2 text-left hover:bg-gray-50"
                                wire:click="$set('perPage',10)" x-on:click="open = false">
                                10
                            </button>
                            <button type="button" class="block w-full px-3 py-2 text-left hover:bg-gray-50"
                                wire:click="$set('perPage',25)" x-on:click="open = false">
                                25
                            </button>
                            <button type="button" class="block w-full px-3 py-2 text-left hover:bg-gray-50"
                                wire:click="$set('perPage',50)" x-on:click="open = false">
                                50
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bulk actions --}}
            <div class="flex justify-end gap-2">
                <button type="button"
                    class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600
                           hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:click="deleteSelected" @disabled(count($checked) === 0)>
                    Delete selected ({{ count($checked) }})
                </button>

                <button type="button"
                    class="inline-flex items-center rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-medium text-indigo-700
                           hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:click="$set('showTagModal', true)" @disabled(!$selectAllResults && count($checked) === 0)>
                    Tag selected
                </button>

                <button type="button"
                    class="inline-flex items-center rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-medium text-amber-700
                           hover:bg-amber-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:click="$set('showRemoveTagModal', true)" @disabled(!$selectAllResults && count($checked) === 0)>
                    Remove tags
                </button>
            </div>

            {{-- CONTENT --}}
            <div>
                @if ($viewMode === 'grid')
                    {{-- GRID VIEW --}}
                    <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        @forelse ($items as $it)
                            <div class="group relative rounded-xl border border-gray-100 bg-white shadow-sm"
                                wire:key="card-{{ $it->id }}">
                                <div class="relative">
                                    @if ($it->category === 'image')
                                        <img src="{{ Storage::disk($it->disk)->url($it->path) }}"
                                            alt="{{ $it->original_name }}"
                                            class="h-40 w-full rounded-t-xl object-cover">
                                    @else
                                        <div
                                            class="flex h-40 w-full items-center justify-center rounded-t-xl bg-gray-50">
                                            <div class="text-4xl" aria-hidden="true">
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

                                    <label
                                        class="absolute left-2 top-2 inline-flex items-center rounded-md bg-white/80 px-1.5 py-1 shadow-sm">
                                        <input
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            type="checkbox" value="{{ (string) $it->id }}"
                                            wire:model.live="checked">
                                    </label>
                                </div>

                                <div class="p-3 space-y-1">
                                    <div class="truncate text-sm font-medium text-gray-900"
                                        title="{{ $it->original_name }}">
                                        {{ $it->original_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ strtoupper($it->category) }} · {{ $it->size_for_humans }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Uploaded {{ $it->created_at_wib->diffForHumans() }}
                                    </div>

                                    @if ($it->tags->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach ($it->tags as $tag)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-700">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="flex gap-1 border-t border-gray-100 px-3 py-2 text-xs">
                                    <a href="{{ route('files.preview', $it) }}" target="_blank"
                                        class="flex-1 inline-flex items-center justify-center rounded-md border border-gray-200 px-2 py-1 text-gray-700 hover:bg-gray-50">
                                        Preview
                                    </a>
                                    <a href="{{ route('files.download', $it) }}"
                                        class="flex-1 inline-flex items-center justify-center rounded-md border border-indigo-200 px-2 py-1 text-indigo-700 hover:bg-indigo-50">
                                        Download
                                    </a>

                                    <div x-data="{ open: false }" class="relative">
                                        <button type="button"
                                            class="inline-flex items-center justify-center rounded-md border border-gray-200 px-2 py-1 text-gray-600 hover:bg-gray-50"
                                            x-on:click="open = !open">
                                            ⋯
                                        </button>
                                        <div x-show="open" x-transition @click.outside="open = false"
                                            class="absolute right-0 z-20 mt-1 w-40 origin-top-right rounded-xl border border-gray-200 bg-white shadow-lg text-xs">
                                            <button type="button"
                                                class="block w-full px-3 py-2 text-left hover:bg-gray-50"
                                                wire:click="confirmRename({{ $it->id }})"
                                                x-on:click="open=false">
                                                Rename
                                            </button>
                                            <button type="button"
                                                class="block w-full px-3 py-2 text-left hover:bg-gray-50"
                                                wire:click="confirmReplace({{ $it->id }})"
                                                x-on:click="open=false">
                                                Replace
                                            </button>
                                            <div class="my-1 border-t border-gray-100"></div>
                                            <button type="button"
                                                class="block w-full px-3 py-2 text-left text-red-600 hover:bg-red-50"
                                                wire:click="deleteOne({{ $it->id }})"
                                                onclick="return confirm('Delete this file?')" x-on:click="open=false">
                                                Delete
                                            </button>
                                            <button type="button"
                                                class="block w-full px-3 py-2 text-left hover:bg-gray-50" x-data
                                                x-on:click="
                                                        navigator.clipboard.writeText('{{ Storage::disk($it->disk)->url($it->path) }}');
                                                        $dispatch('toast', { message: 'Link copied' });
                                                        open=false;
                                                    ">
                                                Copy link
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-10 text-center text-gray-500">
                                    <div class="mb-2 text-4xl">🗂️</div>
                                    <p class="text-sm mb-2">No files match your filters.</p>
                                    <button type="button"
                                        class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                                        wire:click="$set('search',''); $set('type','all')">
                                        Clear filters
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    @else
                        {{-- LIST VIEW --}}
                        @if ($selectPage && !$selectAllResults)
                            <div
                                class="mb-2 flex items-center justify-between rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                <div>You selected {{ count($checked) }} items on this page.</div>
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-amber-200 bg-white px-2 py-1 text-[11px] text-amber-700 hover:bg-amber-50"
                                    wire:click="selectAllResultsAction">
                                    Select all {{ $items->total() }} results
                                </button>
                            </div>
                        @elseif($selectAllResults)
                            <div
                                class="mb-2 flex items-center justify-between rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                                <div>All {{ $items->total() }} results are selected across pages.</div>
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-emerald-200 bg-white px-2 py-1 text-[11px] text-emerald-700 hover:bg-emerald-50"
                                    wire:click="$call('resetSelection')">
                                    Clear selection
                                </button>
                            </div>
                        @endif

                        <div class="overflow-x-auto rounded-xl border border-gray-100 bg-white">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50">
                                    <tr class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <th class="px-3 py-2">
                                            <input type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                wire:model.live="selectPage">
                                        </th>
                                        <th class="px-3 py-2 text-left cursor-pointer select-none"
                                            wire:click="sortBy('original_name')">
                                            Name
                                            @if ($sortField === 'original_name')
                                                <x-sort :dir="$sortDirection" />
                                            @endif
                                        </th>
                                        <th class="px-3 py-2 text-left">Type</th>
                                        <th class="px-3 py-2 text-left cursor-pointer select-none"
                                            wire:click="sortBy('size')">
                                            Size
                                            @if ($sortField === 'size')
                                                <x-sort :dir="$sortDirection" />
                                            @endif
                                        </th>
                                        <th class="px-3 py-2 text-left cursor-pointer select-none"
                                            wire:click="sortBy('created_at')">
                                            Uploaded
                                            @if ($sortField === 'created_at')
                                                <x-sort :dir="$sortDirection" />
                                            @endif
                                        </th>
                                        <th class="px-3 py-2 text-left">Tags</th>
                                        <th class="px-3 py-2 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($items as $it)
                                        <tr wire:key="row-{{ $it->id }}" class="hover:bg-gray-50/50">
                                            <td class="px-3 py-2">
                                                <input type="checkbox"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    value="{{ (string) $it->id }}" wire:model.live="checked">
                                            </td>
                                            <td class="px-3 py-2 max-w-xs">
                                                <div class="flex items-center gap-2">
                                                    <span aria-hidden="true">
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
                                                    <span class="truncate font-medium text-gray-900">
                                                        {{ $it->original_name }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-xs text-gray-500">
                                                {{ strtoupper($it->category) }}
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-700">
                                                {{ $it->size_for_humans }}
                                            </td>
                                            <td class="px-3 py-2 text-xs text-gray-600">
                                                <div>{{ $it->created_at_wib->format('Y-m-d H:i') }}</div>
                                                <div class="text-[11px] text-gray-400">
                                                    {{ $it->created_at_wib->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
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
                                                        class="relative inline-flex items-center me-1 mb-1">
                                                        <span
                                                            class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px]
                                                               border-gray-200 bg-gray-100 text-gray-700"
                                                            :class="holding ? 'opacity-80' : ''"
                                                            :style="holding
                                                                ?
                                                                `transform: scale(.98);
                                                                                                                                                                                                                                                                                                                                                                           background: linear-gradient(to right,
                                                                                                                                                                                                                                                                                                                                                                             rgba(220,38,38,.15) 0%,
                                                                                                                                                                                                                                                                                                                                                                             rgba(220,38,38,.15) ${progress}%,
                                                                                                                                                                                                                                                                                                                                                                             transparent ${progress}%,
                                                                                                                                                                                                                                                                                                                                                                             transparent 100%);` :
                                                                ''">
                                                            {{ $tag->name }}
                                                            <button type="button" class="text-[10px] text-red-500"
                                                                title="Hold to remove" x-on:pointerdown="start"
                                                                x-on:pointerup="cancel" x-on:pointerleave="cancel"
                                                                x-on:touchstart.prevent="start" x-on:touchend="cancel">
                                                                &times;
                                                            </button>
                                                        </span>
                                                        <span x-show="holding" x-transition
                                                            class="absolute left-0 h-0.5 bg-red-500/80"
                                                            :style="`bottom:-2px;width:${progress}%;`"></span>
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td class="px-3 py-2 text-right text-xs">
                                                <div class="inline-flex flex-wrap justify-end gap-1">
                                                    <a href="{{ route('files.preview', $it) }}" target="_blank"
                                                        class="inline-flex items-center rounded-md border border-gray-200 px-2 py-1 text-gray-700 hover:bg-gray-50">
                                                        Preview
                                                    </a>
                                                    <a href="{{ route('files.download', $it) }}"
                                                        class="inline-flex items-center rounded-md border border-indigo-200 px-2 py-1 text-indigo-700 hover:bg-indigo-50">
                                                        Download
                                                    </a>
                                                    <button type="button"
                                                        class="inline-flex items-center rounded-md border border-amber-200 px-2 py-1 text-amber-700 hover:bg-amber-50"
                                                        wire:click="confirmRename({{ $it->id }})">
                                                        Rename
                                                    </button>
                                                    <button type="button"
                                                        class="inline-flex items-center rounded-md border border-sky-200 px-2 py-1 text-sky-700 hover:bg-sky-50"
                                                        wire:click="confirmReplace({{ $it->id }})">
                                                        Replace
                                                    </button>
                                                    <button type="button"
                                                        class="inline-flex items-center rounded-md border border-red-200 px-2 py-1 text-red-600 hover:bg-red-50"
                                                        wire:click="deleteOne({{ $it->id }})"
                                                        onclick="return confirm('Delete this file?')">
                                                        Delete
                                                    </button>
                                                    <button type="button"
                                                        class="inline-flex items-center rounded-md border border-gray-200 px-2 py-1 text-gray-600 hover:bg-gray-50"
                                                        x-data
                                                        x-on:click="
                                                            navigator.clipboard.writeText('{{ Storage::disk($it->disk)->url($it->path) }}');
                                                            $dispatch('toast', { message: 'Link copied' });
                                                        ">
                                                        Copy link
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-10 text-center text-gray-500">
                                                    <div class="mb-2 text-4xl">🗂️</div>
                                                    <p class="text-sm mb-2">No files match your filters.</p>
                                                    <button type="button"
                                                        class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                                                        wire:click="$set('search',''); $set('type','all')">
                                                        Clear filters
                                                    </button>
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
            </div>

            {{-- Sticky selection bar --}}
            @if (count($checked) > 0)
                <div class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 backdrop-blur">
                    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-2 text-sm">
                        <div><strong>{{ count($checked) }}</strong> selected</div>
                        <div class="flex gap-2">
                            <button type="button"
                                class="inline-flex items-center rounded-md border border-gray-200 px-3 py-1 text-xs text-gray-700 hover:bg-gray-50"
                                wire:click="$set('checked', [])">
                                Clear
                            </button>
                            <button type="button"
                                class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-100"
                                wire:click="deleteSelected">
                                Delete selected
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Rename Modal --}}
            @if ($showRename)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-900">Rename file</h2>
                            <button type="button" class="text-gray-400 hover:text-gray-600"
                                wire:click="$set('showRename', false)">
                                ✕
                            </button>
                        </div>
                        <form wire:submit.prevent="rename">
                            <div class="px-4 py-3 space-y-3">
                                @if ($selected)
                                    <div class="space-y-1">
                                        <label class="block text-xs font-medium text-gray-700">
                                            New name (without extension)
                                        </label>
                                        <input type="text"
                                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                           @error('newName') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                            wire:model.defer="newName">
                                        @error('newName')
                                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <p class="text-[11px] text-gray-500">
                                        Current: <code class="text-xs">{{ $selected->original_name }}</code>
                                    </p>
                                @endif
                            </div>
                            <div class="flex justify-end gap-2 border-t border-gray-100 px-4 py-3">
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-gray-200 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                                    wire:click="$set('showRename', false)">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Replace Modal --}}
            @if ($showReplace)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-900">Replace file</h2>
                            <button type="button" class="text-gray-400 hover:text-gray-600"
                                wire:click="$set('showReplace', false)">
                                ✕
                            </button>
                        </div>
                        <form wire:submit.prevent="replace">
                            <div class="px-4 py-3 space-y-3">
                                @if ($selected)
                                    <p class="text-[11px] text-gray-500">
                                        Current: <code class="text-xs">{{ $selected->original_name }}</code>
                                    </p>
                                    <input type="file"
                                        class="w-full text-sm
                                       @error('replacement') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                        wire:model="replacement">
                                    @error('replacement')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    <div wire:loading wire:target="replacement" class="text-[11px] text-gray-500">
                                        Uploading…
                                    </div>
                                @endif
                            </div>
                            <div class="flex justify-end gap-2 border-t border-gray-100 px-4 py-3">
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-gray-200 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                                    wire:click="$set('showReplace', false)">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-600">
                                    Replace
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Add Tag Modal --}}
            @if ($showTagModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-900">Add tags</h2>
                            <button type="button" class="text-gray-400 hover:text-gray-600"
                                wire:click="$set('showTagModal', false)">
                                ✕
                            </button>
                        </div>
                        <form wire:submit.prevent="addTagsToSelection">
                            <div class="px-4 py-3 space-y-2">
                                <label class="block text-xs font-medium text-gray-700">
                                    Tags (comma or newline)
                                </label>
                                <textarea rows="2"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g. urgent, invoice, Q3" wire:model.defer="newTags"></textarea>
                                <p class="text-[11px] text-gray-500">
                                    {{ $selectAllResults ? 'Will apply to ALL filtered results.' : 'Will apply to selected rows.' }}
                                </p>
                            </div>
                            <div class="flex justify-end gap-2 border-t border-gray-100 px-4 py-3">
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-gray-200 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                                    wire:click="$set('showTagModal', false)">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                    Add tags
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Remove Tag Modal --}}
            @if ($showRemoveTagModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-900">Remove tags</h2>
                            <button type="button" class="text-gray-400 hover:text-gray-600"
                                wire:click="$set('showRemoveTagModal', false)">
                                ✕
                            </button>
                        </div>
                        <form wire:submit.prevent="removeCheckedTagsFromSelection">
                            <div class="px-4 py-3 space-y-2 max-h-72 overflow-y-auto">
                                @forelse ($this->availableTags as $tag)
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            type="checkbox" wire:model="removeTagIds" value="{{ $tag->id }}">
                                        <span>{{ $tag->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-500">
                                        No tags in current selection.
                                    </p>
                                @endforelse
                                <p class="text-[11px] text-gray-500">
                                    {{ $selectAllResults ? 'Will remove from ALL filtered results.' : 'Will remove from selected rows.' }}
                                </p>
                            </div>
                            <div class="flex justify-end gap-2 border-t border-gray-100 px-4 py-3">
                                <button type="button"
                                    class="inline-flex items-center rounded-md border border-gray-200 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                                    wire:click="$set('showRemoveTagModal', false)">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600
                                       disabled:opacity-50 disabled:cursor-not-allowed"
                                    @disabled(!$selectAllResults && count($checked) === 0)>
                                    Remove tags
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
