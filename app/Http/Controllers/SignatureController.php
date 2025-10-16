<?php

namespace App\Http\Controllers;

use App\Domain\Signature\Repositories\UserSignatureRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SignatureController extends Controller
{
    public function __construct(private readonly UserSignatureRepository $repo) {}

    public function show(int $id): StreamedResponse
    {
        $signature = $this->repo->findById($id);
        abort_unless($signature, 404);

        Gate::authorize('view', $signature);
        abort_if($signature->revokedAt !== null, 410, 'This signature is revoked');

        $path = $signature->filePath ?? $signature->svgPath;
        abort_unless($path, 404);

        return Storage::disk('private')->response($path);
    }
}
