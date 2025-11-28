<?php

namespace App\Application\Verification\DTOs;

final class ItemData
{
    public ?int $id;

    public string $name;

    public ?string $notes;

    public float $amount;

    public function __construct(
        ?int $id,
        string $name,
        ?string $notes,
        float $amount
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->notes = $notes;
        $this->amount = $amount;
    }

    public static function fromArray(array $a): self
    {
        return new self(
            $a['id'] ?? null,
            (string) ($a['name'] ?? ''),
            $a['notes'] ?? null,
            (float) ($a['amount'] ?? 0)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'notes' => $this->notes,
            'amount' => $this->amount,
        ];
    }
}
