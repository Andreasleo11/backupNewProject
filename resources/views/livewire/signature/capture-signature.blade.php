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

            {{-- If validation fails on save, pngDataUrl will be required --}}
            @error('pngDataUrl')
                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Canvas Area --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-semibold text-slate-900">Signature Pad</div>
                <div class="text-xs text-slate-500">Tip: use finger / mouse</div>
            </div>

            <div class="mt-3 overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-white">
                {{-- Important: keep width/height attributes for consistent export --}}
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

        {{-- Actions --}}
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('signatures.manage') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Cancel
            </a>

            <button type="button" id="save-btn"
                class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Save Signature
            </button>
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
