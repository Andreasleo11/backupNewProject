<?php

declare(strict_types=1);

namespace App\Livewire\Signature;

use App\Application\Signature\DTOs\CreateSignatureDTO;
use App\Application\Signature\UseCases\CreateSignature;
use App\Domain\Signature\ValueObjects\SignatureKind;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

final class CaptureSignature extends Component
{
    use WithFileUploads;

    #[Url]
    public ?string $return_to = null;

    public string $label = 'Primary';

    public string $mode = 'draw'; // 'draw' or 'upload'

    public ?string $pngDataUrl = null; // data:image/png;base64,...

    public ?string $svgText = null; // <svg ...>...</svg>

    public $signatureImage; // for uploaded file

    protected function rules()
    {
        return [
            'label' => 'nullable|string|max:100',
            'mode' => 'required|in:draw,upload',
            'pngDataUrl' => 'required_if:mode,draw|nullable|string',
            'svgText' => 'nullable|string',
            'signatureImage' => 'required_if:mode,upload|nullable|image|max:2048', // 2MB max
        ];
    }

    public function save(CreateSignature $useCase)
    {
        $this->validate();

        $dir = 'signatures/' . auth()->id();
        $pngPath = null;
        $svgPath = null;
        $bytes = null;
        $meta = null;
        $kind = SignatureKind::DRAWN;

        if ($this->mode === 'draw') {
            // decode PNG from canvas
            [$meta, $base64] = explode(',', $this->pngDataUrl, 2);
            $bytes = base64_decode($base64, true);
            abort_unless($bytes !== false, 422, 'Invalid PNG payload');

            $pngPath = $dir . '/' . Str::uuid() . '.png';
            Storage::disk('private')->put($pngPath, $bytes);

            if ($this->svgText) {
                $kind = SignatureKind::SVG;
                $svgPath = $dir . '/' . Str::uuid() . '.svg';
                Storage::disk('private')->put($svgPath, $this->svgText);
            }
        } else {
            // handle uploaded image
            $kind = SignatureKind::UPLOADED;
            $extension = $this->signatureImage->getClientOriginalExtension();
            $pngPath = $dir . '/' . Str::uuid() . '.' . $extension;

            // Store the file directly to private disk
            $this->signatureImage->storeAs(path: $dir, name: basename($pngPath), options: 'private');

            // Get raw bytes for hashing
            $bytes = file_get_contents($this->signatureImage->getRealPath());
        }

        $dto = new CreateSignatureDTO(
            userId: auth()->id(),
            label: $this->label ?: null,
            kind: $kind,
            filePath: $pngPath,
            svgPath: $svgPath,
            rawBytesForHash: $bytes,
            metadata: [
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
                'canvas_meta' => $meta ?? null,
                'mode' => $this->mode,
            ],
            setAsDefault: true,
        );

        $useCase->handle($dto);

        $this->dispatch('toast', message: 'Signature captured.');

        if ($this->return_to) {
            return redirect()->to($this->return_to);
        }

        return redirect()->route('signatures.manage');
    }

    public function render()
    {
        return view('livewire.signature.capture-signature');
    }
}
