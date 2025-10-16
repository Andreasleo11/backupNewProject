{{-- Minimal, dependency-free canvas capture (PNG + optional SVG) --}}
<div class="container py-4">
    <h1 class="h5 mb-3">Capture Signature</h1>


    <div class="mb-2">
        <label class="form-label">Label (optional)</label>
        <input type="text" class="form-control" wire:model.defer="label" placeholder="e.g. Primary">
        @error('label')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>


    <div class="border rounded p-3 mb-2">
        <canvas id="sig-canvas" width="600" height="200"
            style="width:100%;max-width:600px;border:1px dashed #ccc;background:#fff"></canvas>
        <div class="mt-2 d-flex gap-2">
            <button type="button" class="btn btn-sm btn-secondary" id="sig-clear">Clear</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="sig-invert">Invert</button>
        </div>
    </div>


    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" id="save-btn">Save Signature</button>
        <a href="{{ route('signatures.manage') }}" class="btn btn-light">Cancel</a>
    </div>
    <script>
        (function() {
            const canvas = document.getElementById('sig-canvas');
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
                const x = (e.touches ? e.touches[0].clientX : e.clientX) - r.left;
                const y = (e.touches ? e.touches[0].clientY : e.clientY) - r.top;
                return {
                    x: x * (canvas.width / r.width),
                    y: y * (canvas.height / r.height)
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


            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', move);
            window.addEventListener('mouseup', end);
            canvas.addEventListener('touchstart', start, {
                passive: true
            });
            canvas.addEventListener('touchmove', move, {
                passive: true
            });
            window.addEventListener('touchend', end);


            document.getElementById('sig-clear').addEventListener('click', () => {
                ctx.fillStyle = inverted ? '#000' : '#fff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.strokeStyle = inverted ? '#fff' : '#000';
            });


            document.getElementById('sig-invert').addEventListener('click', () => {
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


            document.getElementById('save-btn').addEventListener('click', () => {
                // export PNG
                const dataUrl = canvas.toDataURL('image/png');
                // optional: naive SVG path export (simple placeholder)
                const svg = null; // keep null to avoid low-quality vectorization here
                // push to Livewire component
                const lw = window.Livewire.find('{{ $this->id() }}'); // Livewire v3
                lw.set('pngDataUrl', dataUrl, false);
                lw.set('svgText', svg, false);
                lw.call('save');
            });
        })();
    </script>
</div>
