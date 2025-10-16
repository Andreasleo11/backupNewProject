<?php

namespace App\Livewire\Signature;

use App\Application\Signature\DTOs\CreateSignatureDTO;
use App\Application\Signature\UseCases\CreateSignature;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

final class Capture extends Component
{
    public string $label = 'Primary';

    public ?string $pngDataUrl = null; // data:image/png;base64,...

    public ?string $svgText = null;     // raw <svg>...</svg>

    // public function save(CreateSignature $useCase)
    // {
    //     $this->validate([
    //         'pngDataUrl' => 'required|string',
    //         'svgText' => 'nullable|string',
    //     ]);

    //     // decode png
    //     [$meta, $base64] = explode(',', $this->pngDataUrl, 2);
    //     $bytes = base64_decode($base64, true);
    //     abort_unless($bytes !== false, 422, 'Invalid image');

    //     $pngPath = 'signatures/'.auth()->id().'/'.Str::uuid().'.png';
    //     Storage::disk('private')->put($pngPath, $bytes);

    //     $svgPath = null;
    //     if ($this->svgText) {
    //         $svgPath = 'signatures/'.auth()->id().'/'.Str::uuid().'.svg';
    //         Storage::disk('private')->put($svgPath, $this->svgText);
    //     }

    //     $dto = new CreateSignatureDTO(
    //         userId: auth()->id(),
    //         label: $this->label,
    //         kind: 'drawn',
    //         filePath: $pngPath,
    //         svgPath: $svgPath,
    //         rawBytesForHash: $bytes,
    //         metadata: [
    //             'ip' => request()->ip(),
    //             'ua' => request()->userAgent(),
    //             'canvas_meta' => $meta,
    //         ],
    //         setAsDefault: true,
    //     );

    //     $useCase->handle($dto);

    //     $this->dispatch('toast', 'Signature saved.');
    //     $this->reset(['pngDataUrl', 'svgText']);
    // }

    // public function render()
    // {
    //     return view('livewire.signature.capture');
    // }
}
