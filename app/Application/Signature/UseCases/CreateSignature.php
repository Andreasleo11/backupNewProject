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
        private readonly \App\Application\Signature\Services\BackfillUserSignaturesService $backfillService,
    ) {}

    /**
     * @deprecated Triggering backfill on first signature creation is a temporary transition feature.
     * Remove once migration is fully stabilized and all active users have onboarded.
     */
    public function handle(CreateSignatureDTO $dto): void
    {
        // 1. Detect if this is the user's first active signature (before creation)
        $isFirstSignature = empty($this->repo->listByUser($dto->userId, onlyActive: true));

        // 2. Hash the new signature
        $hash = $this->hasher->sha256($dto->rawBytesForHash);

        if ($dto->setAsDefault) {
            $this->repo->unsetDefaultForUser($dto->userId);
        }

        // 3. Create the record
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

        // 4. If first-time onboarding, backfill all previous unverified approval steps
        if ($isFirstSignature) {
            $this->backfillService->backfill($dto->userId, $sig);
        }
    }
}
