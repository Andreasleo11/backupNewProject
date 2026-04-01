<div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-5 flex items-start justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Capture Signature</h1>
            <p class="mt-1 text-sm text-slate-600">
                Draw your signature below, then save it.
            </p>
        </div>

        <a href="{{ route('signatures.manage') }}"
            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Back
        </a>
    </div>

    {{-- Mode Toggle --}}
    <div class="mb-5 flex items-center justify-center">
        <div class="inline-flex rounded-xl bg-slate-100 p-1">
            <button type="button" wire:click="$set('mode', 'draw')"
                class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition {{ $mode === 'draw' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Draw
            </button>
            <button type="button" wire:click="$set('mode', 'upload')"
                class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition {{ $mode === 'upload' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Upload
            </button>
        </div>
    </div>

    {{-- Card --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        {{-- Label --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold text-slate-700">Label (optional)</label>
            <input type="text"
                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                wire:model.defer="label" placeholder="e.g. Primary">

            @error('label')
                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
            @enderror

            @error('pngDataUrl')
                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
            @enderror

            @error('signatureImage')
                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        @if($mode === 'draw')
            {{-- Canvas Area --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4" wire:key="draw-mode">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-slate-900">Signature Pad</div>
                    <div class="text-xs text-slate-500">Tip: use finger / mouse</div>
                </div>

                <div class="mt-3 overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-white">
                    <canvas id="sig-canvas" width="600" height="200" class="block h-[220px] w-full touch-none">
                    </canvas>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button type="button" id="sig-clear"
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Clear
                    </button>

                    <button type="button" id="sig-invert"
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Invert
                    </button>
                </div>
            </div>
        @else
            {{-- Upload Area --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4" wire:key="upload-mode">
                <div class="text-sm font-semibold text-slate-900">Upload Signature Image</div>
                <p class="mt-1 text-xs text-slate-500">Recommended: PNG with transparent background</p>

                <div class="mt-4">
                    <div class="relative flex min-h-[220px] flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white p-6 transition hover:border-indigo-400">
                        @if ($signatureImage)
                            <div class="mb-4 overflow-hidden rounded-xl border border-slate-200">
                                <img src="{{ $signatureImage->temporaryUrl() }}" class="max-h-[160px] object-contain">
                            </div>
                            <button type="button" wire:click="$set('signatureImage', null)"
                                class="text-xs font-semibold text-rose-600 hover:underline">
                                Remove and try another
                            </button>
                        @else
                            <input type="file" wire:model="signatureImage" class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0" accept="image/*">
                            <div class="flex flex-col items-center">
                                <div class="mb-3 rounded-full bg-indigo-50 p-3 text-indigo-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">Click to upload or drag and drop</span>
                                <span class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG up to 2MB</span>
                            </div>
                        @endif

                        {{-- Loading Indicator --}}
                        <div wire:loading wire:target="signatureImage" class="absolute inset-0 z-20 flex items-center justify-center rounded-2xl bg-white/80 backdrop-blur-sm">
                            <div class="flex flex-col items-center">
                                <svg class="h-8 w-8 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="mt-2 text-xs font-semibold text-slate-600">Uploading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Actions --}}
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('signatures.manage') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Cancel
            </a>

            @if($mode === 'draw')
                <button type="button" id="save-btn"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Save Signature
                </button>
            @else
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Save Signature</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Toast (same event name you dispatch in PHP: toast) --}}
    <div x-data="{ show: false, msg: '', timer: null }"
        x-on:toast.window="
            msg = $event.detail?.message ?? 'Done';
            show = true;
            clearTimeout(timer);
            timer = setTimeout(() => show = false, 2500);
         "
        class="pointer-events-none fixed bottom-4 right-4 z-50">
        <div x-show="show" x-transition x-cloak
            class="pointer-events-auto flex items-center gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg">
            <span x-text="msg"></span>
            <button type="button" class="rounded-lg p-1 text-white/80 hover:bg-white/10 hover:text-white"
                x-on:click="show = false" aria-label="Close toast">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
        </div>
    </div>

    {{-- JS --}}
    <script>
        (function() {
            const canvas = document.getElementById('sig-canvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            let drawing = false;
            let inverted = false;

            // init white background
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';

            function pos(e) {
                const r = canvas.getBoundingClientRect();
                const point = (e.touches && e.touches[0]) ? e.touches[0] : e;
                const x = point.clientX - r.left;
                const y = point.clientY - r.top;
                return {
                    x: x * (canvas.width / r.width),
                    y: y * (canvas.height / r.height),
                };
            }

            function start(e) {
                drawing = true;
                ctx.beginPath();
                const p = pos(e);
                ctx.moveTo(p.x, p.y);
            }

            function move(e) {
                if (!drawing) return;
                const p = pos(e);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
            }

            function end() {
                drawing = false;
            }

            // mouse
            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', move);
            window.addEventListener('mouseup', end);

            // touch
            canvas.addEventListener('touchstart', start, {
                passive: true
            });
            canvas.addEventListener('touchmove', move, {
                passive: true
            });
            window.addEventListener('touchend', end);

            document.getElementById('sig-clear')?.addEventListener('click', () => {
                ctx.fillStyle = inverted ? '#000' : '#fff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.strokeStyle = inverted ? '#fff' : '#000';
            });

            document.getElementById('sig-invert')?.addEventListener('click', () => {
                inverted = !inverted;
                const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
                for (let i = 0; i < img.data.length; i += 4) {
                    img.data[i] = 255 - img.data[i]; // R
                    img.data[i + 1] = 255 - img.data[i + 1]; // G
                    img.data[i + 2] = 255 - img.data[i + 2]; // B
                }
                ctx.putImageData(img, 0, 0);
                ctx.strokeStyle = inverted ? '#fff' : '#000';
            });

            document.getElementById('save-btn')?.addEventListener('click', () => {
                const dataUrl = canvas.toDataURL('image/png');
                const svg = null;

                // Livewire v3
                const lw = window.Livewire.find('{{ $this->id() }}');
                lw.set('pngDataUrl', dataUrl, false);
                lw.set('svgText', svg, false);
                lw.call('save');
            });
        })();
    </script>
</div>
