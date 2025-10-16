<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Signature\Entities\UserSignature as DomainUserSignature;
use App\Domain\Signature\Repositories\UserSignatureRepository;
use App\Domain\Signature\ValueObjects\SignatureKind;
use App\Infrastructure\Persistence\Eloquent\Models\SignatureEvent as EloquentSignatureEvent;
use App\Infrastructure\Persistence\Eloquent\Models\UserSignature as EloquentUserSignature;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class EloquentUserSignatureRepository implements UserSignatureRepository
{
    public function findById(int $id): ?DomainUserSignature
    {
        $m = EloquentUserSignature::query()->find($id);

        return $m ? $this->toDomain($m) : null;
    }

    public function listByUser(int $userId, bool $onlyActive = true): array
    {
        $q = EloquentUserSignature::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at');

        if ($onlyActive) {
            $q->whereNull('revoked_at');
        }

        return $q->get()->map(fn ($m) => $this->toDomain($m))->all();
    }

    public function create(
        int $userId,
        ?string $label,
        SignatureKind $kind,
        ?string $filePath,
        ?string $svgPath,
        string $sha256,
        bool $isDefault,
        ?array $metadata
    ): DomainUserSignature {
        $m = new EloquentUserSignature;
        $m->user_id = $userId;
        $m->label = $label;
        $m->kind = $kind->value;
        $m->file_path = $filePath;
        $m->svg_path = $svgPath;
        $m->sha256 = $sha256;
        $m->is_default = $isDefault;
        $m->metadata = $metadata;
        $m->save();

        return $this->toDomain($m->refresh());
    }

    public function unsetDefaultForUser(int $userId): void
    {
        EloquentUserSignature::query()->where('user_id', $userId)->update(['is_default' => false]);
    }

    public function setDefault(int $signatureId): void
    {
        EloquentUserSignature::query()->whereKey($signatureId)->update(['is_default' => true]);
    }

    public function revoke(int $signatureId, DateTimeImmutable $revokedAt): void
    {
        EloquentUserSignature::query()->whereKey($signatureId)->update([
            'revoked_at' => $revokedAt->format('Y-m-d H:i:s'),
            'is_default' => false,
        ]);
    }

    public function recordEvent(
        int $signatureId,
        string $event,
        ?array $context = null,
        ?DateTimeImmutable $at = null
    ): void {
        EloquentSignatureEvent::query()->create([
            'user_signature_id' => $signatureId,
            'event' => $event,
            'context' => $context,
            'created_at' => ($at ?? new DateTimeImmutable('now'))->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Helper to run a safe switch of default signature.
     */
    public function switchDefault(int $userId, int $newSignatureId): void
    {
        DB::transaction(function () use ($userId, $newSignatureId) {
            $this->unsetDefaultForUser($userId);
            $this->setDefault($newSignatureId);
        });
    }

    private function toDomain(EloquentUserSignature $m): DomainUserSignature
    {
        return new DomainUserSignature(
            id: (int) $m->getKey(),
            userId: (int) $m->user_id,
            label: $m->label,
            kind: SignatureKind::from($m->kind),
            filePath: $m->file_path,
            svgPath: $m->svg_path,
            sha256: $m->sha256,
            isDefault: (bool) $m->is_default,
            metadata: $m->metadata ?? null,
            createdAt: new DateTimeImmutable($m->created_at?->format('Y-m-d H:i:s') ?? 'now'),
            updatedAt: new DateTimeImmutable($m->updated_at?->format('Y-m-d H:i:s') ?? 'now'),
            revokedAt: $m->revoked_at ? new DateTimeImmutable($m->revoked_at->format('Y-m-d H:i:s')) : null,
        );
    }
}
