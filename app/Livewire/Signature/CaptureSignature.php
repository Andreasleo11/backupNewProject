<?php

declare(strict_types=1);

namespace App\Livewire\Signature;

use App\Application\Signature\DTOs\CreateSignatureDTO;
use App\Application\Signature\UseCases\CreateSignature;
use App\Domain\Signature\ValueObjects\SignatureKind;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

final class CaptureSignature extends Component
{
    public string $label = 'Primary';

    public ?string $pngDataUrl = null; // data:image/png;base64,...

    public ?string $svgText = null; // <svg ...>...</svg>

    protected $rules = [
        'label' => 'nullable|string|max:100',
        'pngDataUrl' => 'required|string',
        'svgText' => 'nullable|string',
    ];

    public function save(CreateSignature $useCase)
    {
        $this->validate();

        // decode PNG
        [$meta, $base64] = explode(',', $this->pngDataUrl, 2);
        $bytes = base64_decode($base64, true);
        abort_unless($bytes !== false, 422, 'Invalid PNG payload');

        $dir = 'signatures/'.auth()->id();
        $pngPath = $dir.'/'.Str::uuid().'.png';
        Storage::disk('private')->put($pngPath, $bytes);

        $svgPath = null;
        if ($this->svgText) {
            $svgPath = $dir.'/'.Str::uuid().'.svg';
            Storage::disk('private')->put($svgPath, $this->svgText);
        }

        $dto = new CreateSignatureDTO(
            userId: auth()->id(),
            label: $this->label ?: null,
            kind: $this->svgText ? SignatureKind::SVG : SignatureKind::DRAWN,
            filePath: $pngPath,
            svgPath: $svgPath,
            rawBytesForHash: $bytes,
            metadata: [
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
                'canvas_meta' => $meta ?? null,
            ],
            setAsDefault: true,
        );

        $useCase->handle($dto);

        $this->dispatch('toast', message: 'Signature captured.');

        return redirect()->route('signatures.manage');
    }

    public function render()
    {
        return view('livewire.signature.capture-signature');
    }
}
