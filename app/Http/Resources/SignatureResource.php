<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var UserSignatureDTO $dto */
        $dto = $this->resource;

        return [
            'id' => $dto->id,
            'user_id' => $dto->userId,
            'label' => $dto->label,
            'kind' => $dto->kind,
            'is_default' => $dto->isDefault,
            'active' => $dto->active,
            'created_at' => $dto->createdAt,
            'updated_at' => $dto->updatedAt,
            'revoked_at' => $dto->revokedAt,
            'url' => route('signatures.show', $dto->id),
            'metadata' => $dto->metadata,
        ];
    }
}
