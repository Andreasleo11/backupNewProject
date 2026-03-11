{{-- Requirements Form (Create/Edit) — Livewire component view --}}
{{-- Tailwind, synced with new.layouts.app --}}

@section('title', $requirement?->exists ? 'Edit Requirement' : 'New Requirement')
@section('page-title', $requirement?->exists ? 'Edit Requirement' : 'New Requirement')
@section('page-subtitle', 'Configure compliance definitions, cadences, and allowed file types.')

<div x-data="{ showDeleteModal: false, showCustomMimes: false, showMimePeek: false }"
    @hide-delete-modal.window="showDeleteModal = false">

    {{-- Header / Breadcrumbs --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('requirements.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
                        Requirements
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="bx bx-chevron-right text-slate-400 text-lg"></i>
                        <span class="ml-1 text-sm font-medium text-slate-800 md:ml-2">{{ $requirement?->exists ? 'Edit' : 'Create' }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        @if ($requirement?->exists)
            <button @click="showDeleteModal = true" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 px-4 py-2 text-sm font-semibold transition-all">
                <i class="bx bx-trash text-base"></i> Delete
            </button>
        @endif
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
            <i class="bx bx-check-circle text-emerald-500 text-xl mt-0.5"></i>
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-100 flex items-start gap-3">
            <i class="bx bx-x-circle text-rose-500 text-xl mt-0.5"></i>
            <div>
                <p class="text-sm font-bold text-rose-800">Please fix the following issues:</p>
                <ul class="mt-1 list-disc list-inside text-xs font-medium text-rose-700">
                    @foreach ($errors->keys() as $field)
                        <li>{{ Str::headline(Str::afterLast($field, '.')) }} has an issue.</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Main 2-column layout --}}
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Left: Form --}}
        <div class="flex-1 w-full lg:w-2/3 space-y-6">
            <div class="glass-card p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <h2 class="text-lg font-bold text-slate-800">Requirement {{ $requirement?->exists ? 'Editor' : 'Creator' }}</h2>
                    @if ($requirement?->exists)
                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500 border border-slate-200">
                            #{{ $requirement->id }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Code --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Code <span class="text-rose-500">*</span></label>
                        <div class="flex rounded-xl overflow-hidden border {{ $errors->has('code') ? 'border-rose-300' : 'border-slate-200' }} focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400">
                            <span class="flex items-center px-3 bg-slate-50 border-r border-slate-200 text-slate-400">
                                <i class="bx bx-tag text-lg"></i>
                            </span>
                            <input type="text"
                                wire:model.live.debounce.400ms="code"
                                wire:keydown.debounce.400ms="checkCodeUnique"
                                placeholder="ORG_STRUCTURE"
                                class="w-full py-2 px-3 text-sm outline-none">
                            <button type="button" class="flex items-center px-3 bg-slate-50 border-l border-slate-200 text-slate-500 hover:text-indigo-600 hover:bg-white transition-colors"
                                onclick="navigator.clipboard.writeText('{{ $code }}')" title="Copy Code">
                                <i class="bx bx-copy"></i>
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5">Uppercase, digits, /, _ or -.</p>

                        @if (!is_null($code_is_unique))
                            <p class="text-xs font-semibold mt-1 flex items-center gap-1 {{ $code_is_unique ? 'text-emerald-500' : 'text-rose-500' }}">
                                <i class="bx {{ $code_is_unique ? 'bx-check-circle' : 'bx-x-circle' }}"></i>
                                {{ $code_is_unique ? 'Available' : 'Already used' }}
                            </p>
                        @endif
                        @error('code')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Name <span class="text-rose-500">*</span></label>
                        <input type="text"
                            wire:model.live.debounce.300ms="name"
                            placeholder="Organization Structure"
                            class="w-full rounded-xl border {{ $errors->has('name') ? 'border-rose-300 focus:ring-rose-400' : 'border-slate-200 focus:ring-indigo-400' }} text-sm py-2 px-3 focus:ring-2 outline-none transition-all">
                        @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Description</label>
                        <textarea rows="3"
                            wire:model.live.debounce.300ms="description"
                            placeholder="Explain what files satisfy this requirement, who maintains them, renewal cadence, etc."
                            class="w-full rounded-xl border {{ $errors->has('description') ? 'border-rose-300 focus:ring-rose-400' : 'border-slate-200 focus:ring-indigo-400' }} text-sm py-2 px-3 focus:ring-2 outline-none transition-all"></textarea>
                        @error('description')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Categories & Presets --}}
                <div class="mt-8 border-t border-slate-100 pt-6">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-semibold text-slate-700">Allowed file types</label>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="selectAllPresets" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">Select all</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" wire:click="clearPresets" class="text-xs font-semibold text-slate-500 hover:text-slate-700 hover:underline">Clear</button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach ($this->selected_preset_meta as $p)
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 border border-slate-200">
                                {{ $p['label'] }}
                                <button type="button" wire:click="removePreset('{{ $p['key'] }}')" class="text-slate-400 hover:text-rose-500 transition-colors ml-1 leading-none rounded-full">
                                    <i class="bx bx-x text-sm"></i>
                                </button>
                            </span>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $presets = $this->mimePresets();
                            $icons = [
                                'pdf' => 'bx-file-pdf text-rose-500',
                                'images' => 'bx-image text-emerald-500',
                                'word' => 'bx-file text-blue-500',
                                'excel' => 'bx-table text-emerald-600',
                                'ppt' => 'bx-slideshow text-orange-500',
                                'text' => 'bx-text text-slate-500',
                                'zip' => 'bx-archive text-amber-500',
                                'visio' => 'bx-network-chart text-sky-500',
                                'cad' => 'bx-cube text-indigo-500',
                            ];
                        @endphp
                        @foreach ($presets as $key => $p)
                            @php $isActive = in_array($key, $selected_presets); @endphp
                            <label class="relative flex items-start gap-3 p-3 rounded-xl border {{ $isActive ? 'border-indigo-500 bg-indigo-50/30 shadow-sm shadow-indigo-100/50' : 'border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300' }} cursor-pointer transition-all group">
                                <div class="mt-0.5 relative flex items-center justify-center">
                                    <input type="checkbox"
                                        wire:model="selected_presets"
                                        wire:click.prevent="togglePreset('{{ $key }}')"
                                        value="{{ $key }}"
                                        class="sr-only">
                                    <div class="w-4 h-4 rounded border {{ $isActive ? 'bg-indigo-500 border-indigo-500' : 'bg-white border-slate-300' }} transition-colors flex items-center justify-center">
                                        @if($isActive)
                                            <i class="bx bx-check text-white text-xs"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <i class="bx {{ $icons[$key] ?? 'bx-file' }} text-base"></i>
                                        <span class="text-sm font-semibold {{ $isActive ? 'text-indigo-900' : 'text-slate-700' }}">{{ $p['label'] }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 leading-tight">Opens with: {{ $p['apps'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Advanced specific types --}}
                    <div class="mt-4">
                        <button type="button" @click="showCustomMimes = !showCustomMimes" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1 transition-colors">
                            Advanced: add specific custom types <i class="bx" :class="showCustomMimes ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                        </button>
                        <div x-show="showCustomMimes" class="mt-3 p-4 rounded-xl bg-slate-50 border border-slate-200" x-collapse>
                            <div class="flex flex-wrap gap-2 mb-3">
                                @forelse($custom_mimes as $i => $m)
                                    <span class="inline-flex items-center gap-1 rounded bg-white px-2 py-1 text-xs font-mono font-medium text-slate-600 border border-slate-200 shadow-sm">
                                        {{ $m }}
                                        <button type="button" wire:click="removeCustom({{ $i }})" class="text-slate-400 hover:text-rose-500 transition-colors ml-1">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </span>
                                @empty
                                    <span class="text-[11px] text-slate-400 italic">No custom types added.</span>
                                @endforelse
                            </div>
                            <div class="flex rounded-lg overflow-hidden border border-slate-300 focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400">
                                <span class="flex items-center px-3 bg-slate-100 border-r border-slate-200 text-slate-500">
                                    <i class="bx bx-plus"></i>
                                </span>
                                <input type="text"
                                    wire:model.defer="custom_input"
                                    wire:keydown.enter.prevent="addCustom"
                                    placeholder="e.g. application/json or pdf/jpg/xlsx"
                                    class="w-full py-1.5 px-3 text-sm outline-none bg-white">
                                <button type="button" wire:click="addCustom" class="px-4 bg-slate-100 hover:bg-slate-200 border-l border-slate-200 text-sm font-semibold text-slate-700 transition-colors">
                                    Add
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2">Tip: you can type short forms like <code>pdf</code>, <code>jpg</code>, <code>xlsx</code> or full MIME types.</p>
                        </div>
                    </div>
                    @error('allowed_mimetypes')<p class="text-rose-500 text-xs mt-2">{{ $message }}</p>@enderror
                </div>

                {{-- Numbers & Cadence --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8 border-t border-slate-100 pt-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Min files <span class="text-rose-500">*</span></label>
                        <input type="number" min="1" max="20"
                            wire:model.live="min_count"
                            class="w-full rounded-xl border {{ $errors->has('min_count') ? 'border-rose-300' : 'border-slate-200' }} text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
                        @error('min_count')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Validity (days)</label>
                        <input type="number" min="1" max="3650"
                            wire:model.live="validity_days"
                            placeholder="e.g. 365"
                            class="w-full rounded-xl border {{ $errors->has('validity_days') ? 'border-rose-300' : 'border-slate-200' }} text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
                        <p class="text-[10px] text-slate-400 mt-1">Leave empty for no expiry.</p>
                        @error('validity_days')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Frequency</label>
                        <div class="flex bg-slate-100 p-1 rounded-xl border border-slate-200 w-full">
                            @foreach (['once' => 'Once', 'yearly' => 'Yearly', 'quarterly' => 'Quarterly', 'monthly' => 'Monthly'] as $val => $label)
                                <button type="button"
                                    wire:click="$set('frequency', '{{ $val }}')"
                                    class="flex-1 rounded-lg py-1.5 text-xs font-semibold transition-all {{ $frequency === $val ? 'bg-white text-indigo-700 shadow-sm border border-slate-200/50' : 'text-slate-600 hover:text-slate-800 hover:bg-slate-200/50' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        @error('frequency')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="col-span-full">
                        <label class="flex items-start gap-4 p-4 rounded-xl border border-amber-200 bg-amber-50 cursor-pointer group hover:bg-amber-100/50 transition-colors">
                            <div class="relative flex items-center mt-0.5">
                                <input type="checkbox" wire:model="requires_approval" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-amber-900">Requires admin approval to count</p>
                                <p class="text-[11px] text-amber-700 leading-relaxed mt-1">Uploaded documents strictly require manual review by an admin to be marked 'OK' and count towards compliance score.</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('requirements.index') }}" class="px-5 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                        Cancel
                    </a>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-6 py-2 rounded-xl text-sm font-bold bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 shadow-sm transition-all flex items-center gap-1.5">
                        <i class="bx bx-check text-lg"></i>
                        <span wire:loading.remove wire:target="save">Save Requirement</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Right: Live Summary --}}
        <div class="w-full lg:w-1/3 flex flex-col gap-5">
            <div class="glass-card p-6 sticky top-6">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide border-b border-slate-100 pb-2 mb-4">Live Summary</p>

                <div class="mb-5">
                    <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide">Requirement Name</p>
                    <p class="text-base font-bold text-slate-800 mt-1 leading-tight">{{ $name ?: '—' }}</p>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $code ?: '—' }}</p>
                </div>

                <div class="mb-5">
                    <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide mb-2">Allowed file types</p>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($this->selected_preset_meta as $p)
                            <span class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200">
                                {{ $p['label'] }}
                            </span>
                        @empty
                            <span class="text-[11px] text-slate-400 italic">Any format allowed</span>
                        @endforelse
                    </div>
                    @if (count($this->selected_preset_meta))
                        <p class="text-[10px] text-slate-400 mt-2 leading-tight">
                            Opens with: {{ collect($this->selected_preset_meta)->pluck('apps')->unique()->implode(', ') }}
                        </p>
                    @endif
                </div>

                <div class="mb-5 rounded-lg bg-indigo-50 border border-indigo-100 p-3">
                    <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wide mb-1">What Counts</p>
                    <p class="text-sm font-semibold text-indigo-900 leading-snug">{{ $this->policy_line }}</p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-slate-50">
                        <span class="text-sm font-medium text-slate-500">Minimum files</span>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-700">
                            {{ $min_count ?: '—' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-50">
                        <span class="text-sm font-medium text-slate-500">Validity</span>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $validity_days ? $validity_days . ' days' : 'No expiry' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-50">
                        <span class="text-sm font-medium text-slate-500">Frequency</span>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                            {{ $this->frequencyLabel() }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-50">
                        <span class="text-sm font-medium text-slate-500">Approval</span>
                        @if ($requires_approval)
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="bx bx-shield-alt-2"></i> Required
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Not required
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Technical Peek --}}
                <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                    <button type="button" @click="showMimePeek = !showMimePeek" class="text-[11px] font-semibold text-slate-400 hover:text-indigo-600 transition-colors">
                        Show technical details ({{ count($allowed_mimetypes) }} type{{ count($allowed_mimetypes) == 1 ? '' : 's' }})
                    </button>
                    <div x-show="showMimePeek" class="mt-3 text-left" x-collapse>
                        <div class="flex flex-wrap gap-1">
                            @forelse($allowed_mimetypes as $m)
                                <span class="inline-flex rounded text-[10px] font-mono font-medium bg-slate-50 text-slate-500 px-1.5 py-0.5 border border-slate-200 truncate max-w-full" title="{{ $m }}">
                                    {{ $m }}
                                </span>
                            @empty
                                <span class="text-[10px] text-slate-400 italic">No MIME types</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine Delete Modal Slide-up/Overlay --}}
    @if ($requirement?->exists)
        <div x-show="showDeleteModal" style="display: none;" class="relative z-50">
            <div x-show="showDeleteModal"
                x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showDeleteModal"
                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">

                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b-4 border-rose-500">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="bx bx-error text-xl text-rose-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-lg font-bold leading-6 text-slate-900">Delete Requirement</h3>
                                    <div class="mt-2 text-sm text-slate-600 space-y-3">
                                        <p>You’re about to permanently delete <strong>{{ $requirement->name }}</strong> (<code>{{ $requirement->code }}</code>).</p>

                                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-semibold text-rose-900">Assignments</span>
                                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-white text-rose-700 font-bold border border-rose-200">{{ $usage['assignments'] }}</span>
                                            </div>
                                            <div class="flex items-center justify-between mb-3">
                                                <span class="font-semibold text-rose-900">Uploads</span>
                                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-white text-rose-700 font-bold border border-rose-200">{{ $usage['uploads'] }}</span>
                                            </div>
                                            @if ($usage['assignments'] || $usage['uploads'])
                                                <p class="text-xs font-bold text-rose-700 bg-rose-100/50 p-2 rounded border border-rose-200/60 leading-tight">
                                                    You must detach all assignments and remove uploads before deletion can proceed.
                                                </p>
                                            @else
                                                <p class="text-xs font-medium text-emerald-700 bg-emerald-50 p-2 rounded border border-emerald-200 leading-tight">
                                                    No assignments or uploads detected. Safe to delete.
                                                </p>
                                            @endif
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Type the code to confirm</label>
                                            <input type="text"
                                                wire:model.defer="delete_confirm_input"
                                                placeholder="{{ $requirement->code }}"
                                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 sm:text-sm outline-none px-3 py-2 border font-mono">
                                            @error('delete_confirm_input')<p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="button"
                                wire:click="deleteRequirement"
                                @disabled($usage['assignments'] || $usage['uploads'])
                                class="inline-flex w-full justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto transition-colors">
                                @if ($usage['assignments'] || $usage['uploads'])
                                    Resolve usage first
                                @else
                                    Delete Permanently
                                @endif
                            </button>
                            <button type="button" @click="showDeleteModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
