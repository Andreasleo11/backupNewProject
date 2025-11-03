<?php

declare(strict_types=1);

namespace App\Application\Signature\UseCases;

use App\Application\Signature\DTOs\CreateSignatureDTO;
use App\Domain\Signature\Repositories\UserSignatureRepository;
use App\Domain\Signature\Services\SignatureHasher;

final class CreateSignature
{
    public function __construct(
        private readonly UserSignatureRepository $repo,
        private readonly SignatureHasher $hasher,
    ) {}

    public function handle(CreateSignatureDTO $dto): void
    {
        $hash = $this->hasher->sha256($dto->rawBytesForHash);

        if ($dto->setAsDefault) {
            $this->repo->unsetDefaultForUser($dto->userId);
        }

        $sig = $this->repo->create(
            userId: $dto->userId,
            label: $dto->label,
            kind: $dto->kind,
            filePath: $dto->filePath,
            svgPath: $dto->svgPath,
            sha256: $hash,
            isDefault: $dto->setAsDefault,
            metadata: $dto->metadata
        );

        $this->repo->recordEvent($sig->id ?? 0, 'created');
        if ($dto->setAsDefault) {
            $this->repo->recordEvent($sig->id ?? 0, 'set_default');
        }
    }
}
